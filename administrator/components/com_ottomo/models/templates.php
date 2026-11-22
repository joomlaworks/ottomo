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
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;

require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/compat.php';


class ottomoModelTemplates extends OttomoModel
{
    public function getList()
    {
        $this->discover();
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName('#__ottomo_templates'));
        $this->setQueryConditions($query);
        $db->setQuery($query, (int)$this->getState('limitstart'), (int)$this->getState('limit'));
        $rows = $db->loadObjectList();
        return $rows;
    }

    public function getListCount()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('COUNT(*)');
        $query->from($db->quoteName('#__ottomo_templates'));
        $this->setQueryConditions($query);
        $db->setQuery($query);
        $result = $db->loadResult();
        return $result;
    }

    protected function setQueryConditions(&$query)
    {
        $db = $this->getDbo();
        $search = trim((string)$this->getState('search'));
        if ($search) {
            $search = $db->quote('%'.$db->escape($search, true).'%');
            $query->where($db->quoteName('name').' LIKE '.$search);
        }
        if ($this->getState('folder')) {
            $query->where($db->quoteName('folder').' = '.$db->quote($this->getState('folder')));
        }
        if ($this->getState('id')) {
            $query->where($db->quoteName('id').' IN ('.implode(',', array_map('intval', (array)$this->getState('id'))).')');
        }
        if ($this->getState('ordering') && $this->getState('orderingDir')) {
            $query->order($db->quoteName($this->getState('ordering')).' '.(strtoupper($this->getState('orderingDir')) === 'ASC' ? 'ASC' : 'DESC'));
        }

    }

    /**
     * Installs an email template package (an upload, or a folder: the built-in templates on installation). No Joomla
     * installer adapter (their API differs between Joomla 3 and 4+): the files listed in manifest.xml are copied to
     * components/com_ottomo/templates/<folder> and the template is registered (or updated, when the folder is taken and
     * the manifest says method="upgrade", or when $overwrite is set).
     */
    public function install($overwrite = false)
    {
        $source = $this->getState('source');
        $package = $source == 'path' ? $this->getPackageFromFolder($this->getState('folder')) : $this->getPackageFromUpload();
        if (!$package) {
            return false;
        }

        try {
            $result = $this->installFolder($package['dir'], $overwrite);
        } catch (RuntimeException $e) {
            $this->setError($e->getMessage());
            $result = false;
        }
        $this->setState('result', $result);

        // Cleanup the install files (an uploaded package; a folder install has none and keeps its folder)
        if (!empty($package['packagefile'])) {
            if (!is_file($package['packagefile'])) {
                $package['packagefile'] = OttomoCompat::config('tmp_path').'/'.$package['packagefile'];
            }
            InstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);
        }

        return $result;
    }

    protected function installFolder($dir, $overwrite)
    {
        $xml = is_file($dir.'/manifest.xml') ? @simplexml_load_file($dir.'/manifest.xml') : false;
        if (!$xml || $xml->getName() !== 'extension' || (string)$xml['type'] !== 'ottomotemplate') {
            throw new RuntimeException(Text::_('COM_OTTOMO_INVALID_PACKAGE_TYPE'));
        }
        // One directory name inside components/com_ottomo/templates: never a path
        $folder = (string)$xml['folder'];
        if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9_.-]*$/', $folder)) {
            throw new RuntimeException(Text::_('COM_OTTOMO_TEMPLATE_MUST_HAVE_A_FOLDER'));
        }
        $db = $this->getDbo();
        $id = (int)$db->setQuery($db->getQuery(true)->select($db->quoteName('id'))->from($db->quoteName('#__ottomo_templates'))->where($db->quoteName('folder').' = '.$db->quote($folder)))->loadResult();
        if ($id && !$overwrite && (string)$xml['method'] !== 'upgrade') {
            throw new RuntimeException(Text::_('JLIB_INSTALLER_ABORT_TPL_INSTALL_ALREADY_INSTALLED'));
        }

        // The files the manifest lists (<files>, <images>, <css>; a folder="" attribute is their place in the package)
        $target = JPATH_SITE.'/components/com_ottomo/templates/'.$folder;
        if (realpath($dir) !== realpath($target)) {
            if (!is_dir($target)) {
                Folder::create($target);
            }
            foreach (array('files', 'images', 'css') as $group) {
                foreach ($xml->$group as $node) {
                    $base = trim((string)$node['folder'], '/');
                    foreach ($node->children() as $child) {
                        $name = trim((string)$child);
                        if ($name === '' || !preg_match('#^[A-Za-z0-9_.-]+(/[A-Za-z0-9_.-]+)*$#', $name) || strpos($name, '..') !== false) {
                            continue;
                        }
                        $from = $dir.'/'.($base !== '' ? $base.'/' : '').$name;
                        if ($child->getName() === 'folder' && is_dir($from)) {
                            Folder::copy($from, $target.'/'.$name, '', true);
                        } elseif ($child->getName() === 'filename' && is_file($from)) {
                            if (!is_dir(dirname($target.'/'.$name))) {
                                Folder::create(dirname($target.'/'.$name));
                            }
                            File::copy($from, $target.'/'.$name);
                        }
                    }
                }
            }
            if (!is_file($target.'/manifest.xml')) {
                File::copy($dir.'/manifest.xml', $target.'/manifest.xml');
            }
        }

        $table = $this->getTable();
        if ($id) {
            $table->load($id);
        }
        $manifest = self::manifestData($xml, $folder);
        $table->name = $manifest['name'];
        $table->folder = $folder;
        $table->manifest = json_encode($manifest);
        if (!$table->store()) {
            throw new RuntimeException($table->getError());
        }
        $this->setState('name', $manifest['name']);
        return true;
    }

    /** The manifest cache stored with a template (name, layout, author...). */
    public static function manifestData($xml, $folder)
    {
        $manifest = array('name' => trim((string)$xml->name) !== '' ? trim((string)$xml->name) : $folder, 'type' => 'ottomotemplate');
        foreach (array('creationDate', 'author', 'copyright', 'authorEmail', 'authorUrl', 'version', 'description', 'layout') as $key) {
            $manifest[$key] = trim((string)$xml->$key);
        }
        return $manifest;
    }

    /**
     * Custom templates in the site template's html/ottomo/<folder>/ (with manifest.xml and default.php) are registered
     * as soon as they are there: campaigns refer to a template by its id. A folder named like a built-in template
     * overrides it (same id).
     */
    public function discover()
    {
        static $done = false;
        JLoader::register('OttomoHelperTemplate', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/template.php');
        $site = OttomoHelperTemplate::siteTemplate();
        if ($done || $site === '') {
            return;
        }
        $done = true;
        $root = JPATH_SITE.'/templates/'.$site.'/html/ottomo';
        if (!is_dir($root)) {
            return;
        }
        $db = $this->getDbo();
        $known = $db->setQuery($db->getQuery(true)->select($db->quoteName('folder'))->from($db->quoteName('#__ottomo_templates')))->loadColumn();
        foreach (Folder::folders($root) as $folder) {
            if (in_array($folder, $known, true) || !preg_match('/^[A-Za-z0-9_.-]+$/', $folder) || !is_file($root.'/'.$folder.'/default.php') || !is_file($root.'/'.$folder.'/manifest.xml')) {
                continue;
            }
            $xml = @simplexml_load_file($root.'/'.$folder.'/manifest.xml');
            if (!$xml) {
                continue;
            }
            $manifest = self::manifestData($xml, $folder);
            $table = $this->getTable();
            $table->name = $manifest['name'];
            $table->folder = $folder;
            $table->manifest = json_encode($manifest);
            $table->store();
        }
    }

    public function uninstall()
    {
        $table = $this->getTable();
        $db = $this->getDbo();
        $inUse = array();
        foreach ((array)$this->getState('id') as $id) {
            $id = (int)$id;
            $table->load($id);
            // Campaigns are rendered from their template every time (editing, sending, "view online" links of
            // emails already sent): a template still used by a campaign is never deleted
            $query = $db->getQuery(true)->select('COUNT(*)')->from($db->quoteName('#__ottomo_email_campaigns'))->where($db->quoteName('template').' = '.$id);
            $campaigns = (int)$db->setQuery($query)->loadResult();
            if ($table->id && $campaigns > 0) {
                $inUse[] = Text::plural('COM_OTTOMO_TEMPLATE_IN_USE_N', $campaigns, $table->name);
                continue;
            }
            // In the site template: its files are not Ottomo's to delete (and the template would be found again)
            JLoader::register('OttomoHelperTemplate', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/template.php');
            if ($table->id && OttomoHelperTemplate::location($table->folder)->custom) {
                $inUse[] = Text::sprintf('COM_OTTOMO_TEMPLATE_IN_SITE_TEMPLATE', $table->name);
                continue;
            }
            if ($table->id && OttomoTableTemplates::validFolder($table->folder) && $table->folder != 'default') {
                $path = JPATH_SITE.'/components/com_ottomo/templates/'.$table->folder;
                if (is_dir($path)) {
                    Folder::delete($path);
                }
                $table->delete($id);
            }
        }
        if (count($inUse)) {
            $this->setError(Text::_('COM_OTTOMO_TEMPLATES_NOT_DELETED').' '.implode(' ', $inUse));
            return false;
        }
        return true;
    }

    protected function folderTaken($folder)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true)->select('COUNT(*)')->from($db->quoteName('#__ottomo_templates'))->where($db->quoteName('folder').' = '.$db->quote($folder));
        return (int)$db->setQuery($query)->loadResult() > 0;
    }

    public function copy()
    {
        $ids = $this->getState('id');
        $db = OttomoCompat::db();
        foreach ($ids as $id) {
            $source = $this->getTable();
            $source->load($id);
            if ($source->id) {
                $target = $this->getTable();
                $target->name = Text::sprintf('COM_OTTOMO_COPY_OF', $source->name);
                // A readable, free folder name: linen-copy, linen-copy-2...
                $base = preg_replace('/-copy(-\d+)?$/', '', $source->folder).'-copy';
                $folder = $base;
                for ($n = 2; is_dir(JPATH_SITE.'/components/com_ottomo/templates/'.$folder) || $this->folderTaken($folder); $n++) {
                    $folder = $base.'-'.$n;
                }
                $target->folder = $folder;
                $target->manifest = $source->manifest;
                $target->store();
                // From wherever its files are (the site template too); the copy is a regular Ottomo template
                JLoader::register('OttomoHelperTemplate', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/template.php');
                $path = OttomoHelperTemplate::location($source->folder)->path;
                if ($source->folder && is_dir($path)) {
                    Folder::copy($path, JPATH_SITE.'/components/com_ottomo/templates/'.$target->folder);
                }
            }

        }
    }

    protected function getPackageFromUpload()
    {
        // Get the uploaded file information
        $package = $this->getState('package');

        // Load language file
        $language = OttomoCompat::language();
        $language->load('com_installer');

        // Make sure that file uploads are enabled in php.
        if (!(bool) ini_get('file_uploads')) {
            $this->setError(Text::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLFILE'));
            return false;
        }

        // Make sure that zlib is loaded so that the package can be unpacked.
        if (!extension_loaded('zlib')) {
            $this->setError(Text::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLZLIB'));
            return false;
        }

        // If there is no uploaded file, we have a problem...
        if (!is_array($package)) {
            $this->setError(Text::_('COM_INSTALLER_MSG_INSTALL_NO_FILE_SELECTED'));
            return false;
        }

        // Is the PHP tmp directory missing?
        if ($package['error'] && ($package['error'] == UPLOAD_ERR_NO_TMP_DIR)) {
            $this->setError(Text::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLUPLOADERROR').'<br />'.Text::_('COM_INSTALLER_MSG_WARNINGS_PHPUPLOADNOTSET'));
            return false;
        }

        // Is the max upload size too small in php.ini?
        if ($package['error'] && ($package['error'] == UPLOAD_ERR_INI_SIZE)) {
            $this->setError(Text::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLUPLOADERROR').'<br />'.Text::_('COM_INSTALLER_MSG_WARNINGS_SMALLUPLOADSIZE'));
            return false;
        }

        // Check if there was a different problem uploading the file.
        if ($package['error'] || $package['size'] < 1) {
            $this->setError(Text::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLUPLOADERROR'));
            return false;
        }

        // Build the appropriate paths.
        $tmp_dest = OttomoCompat::config('tmp_path').'/'.$package['name'];
        $tmp_src = $package['tmp_name'];

        // Move uploaded file.
        File::upload($tmp_src, $tmp_dest, false, true);

        // Unpack the downloaded package file.
        $package = InstallerHelper::unpack($tmp_dest, true);

        return $package;

    }

    protected function getPackageFromFolder($path)
    {
        // Get the path to the package to install.
        $p_dir = Path::clean($path);

        // Did you give us a valid directory?
        if (!is_dir($p_dir)) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_INSTALLER_MSG_INSTALL_PLEASE_ENTER_A_PACKAGE_DIRECTORY'), 'warning');

            return false;
        }

        $package['packagefile'] = null;
        $package['extractdir'] = null;
        $package['dir'] = $p_dir;

        return $package;
    }

}
