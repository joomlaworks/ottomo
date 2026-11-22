<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

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

    public function install()
    {
        // Get package
        $source = $this->getState('source');
        if ($source == 'path') {
            $package = $this->getPackageFromFolder($this->getState('folder'));
        } else {
            $package = $this->getPackageFromUpload();
        }

        // Ensure we have a package
        if (!$package) {
            return false;
        }

        // Allow only ottomo templates installation from here
        if ($package['type'] != 'ottomotemplate') {
            $this->setError(JText::_('COM_OTTOMO_INVALID_PACKAGE_TYPE'));
            return false;
        }

        // Get installer
        $installer = $this->getInstaller();

        //Install the package
        if (!$installer->install($package['dir'])) {
            $result = false;
        } else {
            $result = true;
        }

        // Set some model state values
        $this->setState('name', $installer->get('name'));
        $this->setState('result', $result);
        $this->setState('message', $installer->message);
        $this->setState('extension.message', $installer->get('extension.message'));

        // Cleanup the install files (an uploaded package; a folder install has none and keeps its folder)
        if (!empty($package['packagefile'])) {
            if (!is_file($package['packagefile'])) {
                $package['packagefile'] = JFactory::getConfig()->get('tmp_path').'/'.$package['packagefile'];
            }
            JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);
        }

        return $result;
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
        foreach (JFolder::folders($root) as $folder) {
            if (in_array($folder, $known, true) || !preg_match('/^[A-Za-z0-9_.-]+$/', $folder) || !is_file($root.'/'.$folder.'/default.php') || !is_file($root.'/'.$folder.'/manifest.xml')) {
                continue;
            }
            $xml = @simplexml_load_file($root.'/'.$folder.'/manifest.xml');
            if (!$xml) {
                continue;
            }
            $manifest = array('name' => trim((string)$xml->name) !== '' ? trim((string)$xml->name) : $folder, 'type' => 'ottomotemplate');
            foreach (array('creationDate', 'author', 'copyright', 'authorEmail', 'authorUrl', 'version', 'description', 'layout') as $key) {
                $manifest[$key] = trim((string)$xml->$key);
            }
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
                $inUse[] = JText::plural('COM_OTTOMO_TEMPLATE_IN_USE_N', $campaigns, $table->name);
                continue;
            }
            // In the site template: its files are not Ottomo's to delete (and the template would be found again)
            JLoader::register('OttomoHelperTemplate', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/template.php');
            if ($table->id && OttomoHelperTemplate::location($table->folder)->custom) {
                $inUse[] = JText::sprintf('COM_OTTOMO_TEMPLATE_IN_SITE_TEMPLATE', $table->name);
                continue;
            }
            if ($table->id && OttomoTableTemplates::validFolder($table->folder) && $table->folder != 'default') {
                $path = JPATH_SITE.'/components/com_ottomo/templates/'.$table->folder;
                if (JFolder::exists($path)) {
                    JFolder::delete($path);
                }
                $table->delete($id);
            }
        }
        if (count($inUse)) {
            $this->setError(JText::_('COM_OTTOMO_TEMPLATES_NOT_DELETED').' '.implode(' ', $inUse));
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
        $db = JFactory::getDbo();
        foreach ($ids as $id) {
            $source = $this->getTable();
            $source->load($id);
            if ($source->id) {
                $target = $this->getTable();
                $target->name = JText::sprintf('COM_OTTOMO_COPY_OF', $source->name);
                // A readable, free folder name: linen-copy, linen-copy-2...
                $base = preg_replace('/-copy(-\d+)?$/', '', $source->folder).'-copy';
                $folder = $base;
                for ($n = 2; JFolder::exists(JPATH_SITE.'/components/com_ottomo/templates/'.$folder) || $this->folderTaken($folder); $n++) {
                    $folder = $base.'-'.$n;
                }
                $target->folder = $folder;
                $target->manifest = $source->manifest;
                $target->store();
                // From wherever its files are (the site template too); the copy is a regular Ottomo template
                JLoader::register('OttomoHelperTemplate', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/template.php');
                $path = OttomoHelperTemplate::location($source->folder)->path;
                if ($source->folder && JFolder::exists($path)) {
                    JFolder::copy($path, JPATH_SITE.'/components/com_ottomo/templates/'.$target->folder);
                }
            }

        }
    }

    protected function getPackageFromUpload()
    {
        // Get the uploaded file information
        $package = $this->getState('package');

        // Load language file
        $language = JFactory::getLanguage();
        $language->load('com_installer');

        // Make sure that file uploads are enabled in php.
        if (!(bool) ini_get('file_uploads')) {
            $this->setError(JText::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLFILE'));
            return false;
        }

        // Make sure that zlib is loaded so that the package can be unpacked.
        if (!extension_loaded('zlib')) {
            $this->setError(JText::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLZLIB'));
            return false;
        }

        // If there is no uploaded file, we have a problem...
        if (!is_array($package)) {
            $this->setError(JText::_('COM_INSTALLER_MSG_INSTALL_NO_FILE_SELECTED'));
            return false;
        }

        // Is the PHP tmp directory missing?
        if ($package['error'] && ($package['error'] == UPLOAD_ERR_NO_TMP_DIR)) {
            $this->setError(JText::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLUPLOADERROR').'<br />'.JText::_('COM_INSTALLER_MSG_WARNINGS_PHPUPLOADNOTSET'));
            return false;
        }

        // Is the max upload size too small in php.ini?
        if ($package['error'] && ($package['error'] == UPLOAD_ERR_INI_SIZE)) {
            $this->setError(JText::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLUPLOADERROR').'<br />'.JText::_('COM_INSTALLER_MSG_WARNINGS_SMALLUPLOADSIZE'));
            return false;
        }

        // Check if there was a different problem uploading the file.
        if ($package['error'] || $package['size'] < 1) {
            $this->setError(JText::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLUPLOADERROR'));
            return false;
        }

        // Build the appropriate paths.
        $config = JFactory::getConfig();
        $tmp_dest = $config->get('tmp_path').'/'.$package['name'];
        $tmp_src = $package['tmp_name'];

        // Move uploaded file.
        jimport('joomla.filesystem.file');
        JFile::upload($tmp_src, $tmp_dest, false, true);

        // Unpack the downloaded package file.
        $package = JInstallerHelper::unpack($tmp_dest, true);

        return $package;

    }

    protected function getPackageFromFolder($path)
    {
        // Get the path to the package to install.
        $p_dir = JPath::clean($path);

        // Did you give us a valid directory?
        if (!is_dir($p_dir)) {
            JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_PLEASE_ENTER_A_PACKAGE_DIRECTORY'));

            return false;
        }

        // Detect the package type
        $type = JInstallerHelper::detectType($p_dir);

        // Did you give us a valid package?
        if (!$type) {
            JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_PATH_DOES_NOT_HAVE_A_VALID_PACKAGE'));
        }

        $package['packagefile'] = null;
        $package['extractdir'] = null;
        $package['dir'] = $p_dir;
        $package['type'] = $type;

        return $package;
    }

    protected function getInstaller()
    {
        // Import the installers
        jimport('joomla.installer.installer');
        jimport('joomla.installer.helper');

        // Get an installer instance
        $installer = new JInstaller(JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/classes', $classprefix = 'JInstallerAdapter', $adapterfolder = 'adapter');
        $installer->setAdapter('ottomotemplate');
        return $installer;
    }

}
