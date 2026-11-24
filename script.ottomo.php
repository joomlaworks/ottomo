<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Version;
use Joomla\Filesystem\Folder;

/**
 * The package's installer script, the same for Joomla 3.x UTD and Joomla 4/5/6 (self-contained: on a first install
 * none of Ottomo's files are on the site yet when it runs).
 */
class Com_OttomoInstallerScript
{
    /** Requirements: PHP 7.1 and Joomla 3.10 (Joomla 3.x UTD) or newer. */
    public function preflight($type, $parent)
    {
        if ($type === 'uninstall') {
            return true;
        }
        if (version_compare(PHP_VERSION, '7.1', 'lt')) {
            Factory::getApplication()->enqueueMessage(sprintf('Ottomo requires PHP 7.1 or newer (this site runs PHP %s).', PHP_VERSION), 'error');
            return false;
        }
        $version = new Version();
        if (!$version->isCompatible('3.10')) {
            Factory::getApplication()->enqueueMessage(sprintf('Ottomo requires Joomla 3.x UTD (or Joomla 3.10), 4, 5 or 6 (this site runs Joomla %s).', JVERSION), 'error');
            return false;
        }
        return true;
    }

    public function postflight($type, $parent)
    {
        if ($type === 'uninstall') {
            return true; // Joomla 4+ runs postflight after an uninstall too
        }

        $db = $this->db();
        $modern = $this->isModern();

        // Get manifest
        $src = $parent->getParent()->getPath('source');
        $manifest = $this->manifest($parent);

        // Install plugins
        $plugins = $manifest->xpath('plugins/plugin');

        foreach ($plugins as $plugin) {
            // Get plugin variables
            $name = (string)$plugin->attributes()->plugin;
            $group = (string)$plugin->attributes()->group;
            $path = $src.'/plugins/'.$group.'/'.$name;

            // K2 exists for Joomla 3 only
            if ($group === 'ottomo-content' && $name === 'k2' && $modern) {
                continue;
            }

            // Already on the site? (an update keeps the state the site chose)
            $query = $db->getQuery(true);
            $query->select('COUNT(*)')->from($db->quoteName('#__extensions'))->where($db->quoteName('type').' = '.$db->quote('plugin'))->where($db->quoteName('element').' = '.$db->quote($name))->where($db->quoteName('folder').' = '.$db->quote($group));
            $db->setQuery($query);
            $existed = (int)$db->loadResult() > 0;

            // Install
            $installer = $this->installer($db);
            $result = $installer->install($path);

            // Enable the plugin when it is new to the site (a fresh installation, or a plugin added by an update)
            if ($type == 'install' || !$existed) {
                $query = $db->getQuery(true);
                $query->update($db->quoteName('#__extensions'))->set($db->quoteName('enabled').' = 1')->where($db->quoteName('type').' = '.$db->quote('plugin'))->where($db->quoteName('element').' = '.$db->quote($name))->where($db->quoteName('folder').' = '.$db->quote($group));
                $db->setQuery($query);
                $db->execute();
            }
        }

        // Install modules
        $modules = $manifest->xpath('modules/module');
        foreach ($modules as $module) {
            // Get module variables
            $name = (string)$module->attributes()->module;
            $client = (string)$module->attributes()->client;

            // Set client if it's null
            if (is_null($client)) {
                $client = 'site';
            }

            // Detect path
            $path = ($client == 'administrator') ? $src.'/administrator/modules/'.$name : $src.'/modules/'.$name;

            // Install
            $installer = $this->installer($db);
            $result = $installer->install($path);

            // Publish the administrator modules. Only for fresh installations, not updates!
            if ($type == 'install' && $client == 'administrator') {
                // Detect target position
                $position = 'cpanel';

                // Publish the module (title hidden: the module has its own header)
                $query = $db->getQuery(true);
                $query->update($db->quoteName('#__modules'))->set($db->quoteName('position').' = '.$db->quote($position))->set($db->quoteName('published').' = 1')->set($db->quoteName('showtitle').' = 0')->where($db->quoteName('module').' = '.$db->quote($name))->where($db->quoteName('client_id').' = 1'); // the site module has the same name
                $db->setQuery($query);
                $db->execute();

                $query = $db->getQuery(true);
                $query->select($db->quoteName('id'))->from($db->quoteName('#__modules'))->where($db->quoteName('module').' = '.$db->quote($name))->where($db->quoteName('client_id').' = 1');
                $db->setQuery($query);
                $id = (int)$db->loadResult();
                if ($id) {
                    $query = $db->getQuery(true);
                    $query->select('COUNT(*)')->from($db->quoteName('#__modules_menu'))->where($db->quoteName('moduleid').' = '.$id);
                    $db->setQuery($query);
                    $exists = (int)$db->loadResult();
                    if (!$exists) {
                        $query = $db->getQuery(true);
                        $query->insert($db->quoteName('#__modules_menu'))->columns('moduleid, menuid')->values($id.',0');
                        $db->setQuery($query);
                        $db->execute();
                    }
                }
            }
        }

        // Register the built-in email templates (their files came with the component)
        require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/compat.php';
        require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/models/model.php';
        require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/models/templates.php';
        $model = new OttomoModelTemplates();
        foreach (Folder::folders($src.'/components/com_ottomo/templates') as $template) {
            $model->setState('source', 'path');
            $model->setState('folder', JPATH_SITE.'/components/com_ottomo/templates/'.$template);
            $model->install(true);
        }

        // Updates: schema changes since 1.0 (each step checks first)
        if ($type == 'update') {
            $this->upgrade($db);
        }

        // Tasks for fresh installations
        if ($type == 'install') {
            // Add default statistics types
            $query = $db->getQuery(true);
            $query->insert($db->quoteName('#__ottomo_statistics_types'))->columns($db->quoteName('id').','.$db->quoteName('type'))->values('1,'.$db->quote('send'));
            $db->setQuery($query);
            $db->execute();
            $query = $db->getQuery(true);
            $query->insert($db->quoteName('#__ottomo_statistics_types'))->columns($db->quoteName('id').','.$db->quoteName('type'))->values('2,'.$db->quote('click'));
            $db->setQuery($query);
            $db->execute();
            $query = $db->getQuery(true);
            $query->insert($db->quoteName('#__ottomo_statistics_types'))->columns($db->quoteName('id').','.$db->quoteName('type'))->values('3,'.$db->quote('open'));
            $db->setQuery($query);
            $db->execute();

            // A first topic, so that imported subscribers have one to go in (campaigns are sent to topics)
            $query = $db->getQuery(true);
            $query->select('COUNT(*)')->from($db->quoteName('#__ottomo_topics'));
            $db->setQuery($query);
            if (!(int)$db->loadResult()) {
                $application = Factory::getApplication();
                $language = method_exists($application, 'getLanguage') ? $application->getLanguage() : Factory::getLanguage();
                $language->load('com_ottomo', JPATH_ADMINISTRATOR.'/components/com_ottomo');
                $name = $language->hasKey('COM_OTTOMO_DEFAULT_TOPIC') ? $language->_('COM_OTTOMO_DEFAULT_TOPIC') : 'Updates';
                $query = $db->getQuery(true);
                $query->insert($db->quoteName('#__ottomo_topics'))->columns($db->quoteName('name').','.$db->quoteName('published'))->values($db->quote($name).',1');
                $db->setQuery($query);
                $db->execute();
            }
        }

    }

    public function uninstall($parent)
    {
        $db = $this->db();
        $manifest = $this->manifest($parent);

        // Uninstall plugins
        $plugins = $manifest->xpath('plugins/plugin');
        foreach ($plugins as $plugin) {
            // Get plugin variables
            $name = (string)$plugin->attributes()->plugin;
            $group = (string)$plugin->attributes()->group;

            // Get extension id
            $query = $db->getQuery(true);
            $query->select($db->quoteName('extension_id'))->from($db->quoteName('#__extensions'))->where($db->quoteName('type').' = '.$db->quote('plugin'))->where($db->quoteName('element').' = '.$db->quote($name))->where($db->quoteName('folder').' = '.$db->quote($group));
            $db->setQuery($query);
            $id = $db->loadResult();
            if ($id) {
                // Uninstall
                $installer = $this->installer($db);
                $result = $installer->uninstall('plugin', $id);
            }

        }

        // Uninstall modules
        $modules = $manifest->xpath('modules/module');
        foreach ($modules as $module) {
            // Get module variables
            $name = (string)$module->attributes()->module;
            $client = (string)$module->attributes()->client;

            // Get all module instances ids
            $query = $db->getQuery(true);
            $query->select($db->quoteName('extension_id'))->from($db->quoteName('#__extensions'))->where($db->quoteName('type').' = '.$db->quote('module'))->where($db->quoteName('element').' = '.$db->quote($name))->where($db->quoteName('client_id').' = '.($client == 'administrator' ? 1 : 0)); // site and administrator mod_ottomo
            $db->setQuery($query);
            $instances = $db->loadColumn();

            // Uninstall all module instances
            if (count($instances)) {
                foreach ($instances as $id) {
                    // Unistall module instance
                    $installer = $this->installer($db);
                    $result = $installer->uninstall('module', $id);
                }
            }
        }
    }

    private function manifest($parent)
    {
        return method_exists($parent, 'getManifest') ? $parent->getManifest() : $parent->getParent()->manifest;
    }

    private function isModern()
    {
        $version = new Version();
        return $version->isCompatible('4.0');
    }

    /**
     * Schema changes since 1.0, through Joomla's driver (MySQL, PostgreSQL and, on Joomla 3.x UTD, SQLite).
     * 1.1: private topics (hidden on the site, for lists managed in the admin). 1.0 already kept unpublished topics
     * off the site while campaigns still went to them: they become published private topics, so nothing changes.
     */
    private function upgrade($db)
    {
        $columns = array_change_key_case($db->getTableColumns('#__ottomo_topics', false), CASE_LOWER);
        if (!isset($columns['private'])) {
            $server = strtolower(method_exists($db, 'getServerType') ? (string)$db->getServerType() : (string)$db->name);
            $type = strpos($server, 'postgres') !== false ? 'smallint' : 'tinyint(1)';
            $db->setQuery('ALTER TABLE '.$db->quoteName('#__ottomo_topics').' ADD COLUMN '.$db->quoteName('private').' '.$type.' NOT NULL DEFAULT 0');
            $db->execute();
            $query = $db->getQuery(true);
            $query->update($db->quoteName('#__ottomo_topics'))
                ->set($db->quoteName('private').' = 1')
                ->set($db->quoteName('published').' = 1')
                ->where($db->quoteName('published').' = 0');
            $db->setQuery($query);
            $db->execute();
        }

        $this->updateServer($db);
    }

    /**
     * Since 1.1 the manifest's update server is update.xml (type "extension"). Sites installed with 1.0 have the old
     * collection.xml server (type "collection"): Joomla 3 would add update.xml next to it, and Joomla 4+ would only change
     * its location (keeping the type, so update.xml would be read as a collection and no update ever found). So the old
     * row becomes the update.xml server here, type included; Joomla then finds it by its location and leaves it as it is.
     */
    private function updateServer($db)
    {
        $collection = 'https://joomlaworks.github.io/ottomo/collection.xml';
        $feed = 'https://joomlaworks.github.io/ottomo/update.xml';
        $query = $db->getQuery(true)
            ->select($db->quoteName(array('update_site_id', 'location')))
            ->from($db->quoteName('#__update_sites'))
            ->where($db->quoteName('location').' IN ('.$db->quote($collection).', '.$db->quote($feed).')');
        $db->setQuery($query);
        $sites = $db->loadObjectList();
        $old = $new = null;
        foreach ($sites as $site) {
            if ($site->location === $collection) {
                $old = (int)$site->update_site_id;
            } else {
                $new = (int)$site->update_site_id;
            }
        }
        if (!$old) {
            return;
        }
        if ($new) {
            // update.xml is there already: the collection row goes, with its links and the updates it found
            foreach (array('#__update_sites_extensions', '#__updates', '#__update_sites') as $table) {
                $db->setQuery($db->getQuery(true)->delete($db->quoteName($table))->where($db->quoteName('update_site_id').' = '.$old));
                $db->execute();
            }
            return;
        }
        $db->setQuery($db->getQuery(true)
            ->update($db->quoteName('#__update_sites'))
            ->set($db->quoteName('type').' = '.$db->quote('extension'))
            ->set($db->quoteName('location').' = '.$db->quote($feed))
            ->where($db->quoteName('update_site_id').' = '.$old));
        $db->execute();
        // updates found through the collection are found again through update.xml
        $db->setQuery($db->getQuery(true)->delete($db->quoteName('#__updates'))->where($db->quoteName('update_site_id').' = '.$old));
        $db->execute();
    }

    private function db()
    {
        if (method_exists('Joomla\\CMS\\Factory', 'getContainer') && interface_exists('Joomla\\Database\\DatabaseInterface')) {
            try {
                return Factory::getContainer()->get('Joomla\\Database\\DatabaseInterface');
            } catch (\Throwable $e) {
                // below
            }
        }
        return Factory::getDbo();
    }

    /** A separate installer for each bundled extension (the package's own installer is busy). */
    private function installer($db)
    {
        $installer = new Installer();
        if (method_exists($installer, 'setDatabase')) {
            $installer->setDatabase($db);
        }
        return $installer;
    }
}
