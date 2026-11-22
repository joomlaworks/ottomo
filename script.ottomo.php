<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

class Com_OttomoInstallerScript
{
    public function postflight($type, $parent)
    {
        // Load language
        $language = JFactory::getLanguage();
        $language->load('com_ottomo');

        // Get database
        $db = JFactory::getDbo();

        // Get manifest
        $src = $parent->getParent()->getPath('source');
        $manifest = $parent->getParent()->manifest;

        // Install plugins
        $plugins = $manifest->xpath('plugins/plugin');

        foreach ($plugins as $plugin) {
            // Get plugin variables
            $name = (string)$plugin->attributes()->plugin;
            $group = (string)$plugin->attributes()->group;
            $path = $src.'/plugins/'.$group.'/'.$name;

            // Already on the site? (an update keeps the state the site chose)
            $query = $db->getQuery(true);
            $query->select('COUNT(*)')->from($db->quoteName('#__extensions'))->where($db->quoteName('type').' = '.$db->quote('plugin'))->where($db->quoteName('element').' = '.$db->quote($name))->where($db->quoteName('folder').' = '.$db->quote($group));
            $db->setQuery($query);
            $existed = (int)$db->loadResult() > 0;

            // Install
            $installer = new JInstaller();
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
            $installer = new JInstaller();
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

        // Install templates
        require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/models/model.php';
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_ottomo/models');
        $model = JModelLegacy::getInstance('Templates', 'OttomoModel');
        $templates = JFolder::folders($src.'/components/com_ottomo/templates');
        foreach ($templates as $template) {
            $model->setState('source', 'path');
            $model->setState('folder', $src.'/components/com_ottomo/templates/'.$template);
            $model->install();
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
        }

    }

    public function uninstall($parent)
    {

        // Load language
        $language = JFactory::getLanguage();
        $language->load('com_ottomo');

        // Get database
        $db = JFactory::getDBO();

        // Get manifest
        $manifest = $parent->getParent()->manifest;

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
                $installer = new JInstaller();
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
                    $installer = new JInstaller();
                    $result = $installer->uninstall('module', $id);
                }
            }
        }
    }

}
