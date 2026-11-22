<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

jimport('cms.installer.adapter');

/**
 * Ottomo Template installer
 */

class JInstallerAdapterOttomoTemplate extends JInstallerAdapter
{
    protected function setupInstallPaths()
    {

        $this->folder = (string)$this->getManifest()->attributes()->folder;
        // One directory name inside components/com_ottomo/templates: never a path
        if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9_.-]*$/', $this->folder)) {
            throw new RuntimeException(JText::_('COM_OTTOMO_TEMPLATE_MUST_HAVE_A_FOLDER'));
        }
        if (empty($this->element) && empty($this->group)) {
            throw new RuntimeException(JText::sprintf('JLIB_INSTALLER_ABORT_TPL_INSTALL_ROLLBACK', JText::_('JLIB_INSTALLER_'.$this->route)));
        }
        $this->clientId = 0;
        $this->parent->setPath('extension_root', JPATH_SITE.'/components/com_ottomo/templates/'.$this->folder);
    }

    protected function copyBaseFiles()
    {
        // Copy all the necessary files
        if ($this->parent->parseFiles($this->getManifest()->files, -1) === false) {
            throw new RuntimeException(JText::sprintf('JLIB_INSTALLER_ABORT_TPL_INSTALL_COPY_FILES', 'files'));
        }

        if ($this->parent->parseFiles($this->getManifest()->images, -1) === false) {
            throw new RuntimeException(JText::sprintf('JLIB_INSTALLER_ABORT_TPL_INSTALL_COPY_FILES', 'images'));
        }

        if ($this->parent->parseFiles($this->getManifest()->css, -1) === false) {
            throw new RuntimeException(JText::sprintf('JLIB_INSTALLER_ABORT_TPL_INSTALL_COPY_FILES', 'css'));
        }

        // If there is a manifest script, let's copy it.
        if ($this->manifest_script) {
            $path['src'] = $this->parent->getPath('source').'/'.$this->manifest_script;
            $path['dest'] = $this->parent->getPath('extension_root').'/'.$this->manifest_script;

            if (!file_exists($path['dest']) || $this->parent->isOverwrite()) {
                if (!$this->parent->copyFiles(array($path))) {
                    throw new RuntimeException(JText::sprintf('JLIB_INSTALLER_ABORT_MANIFEST', JText::_('JLIB_INSTALLER_'.strtoupper($this->getRoute()))));
                }
            }
        }
    }

    protected function checkExistingExtension()
    {
        $this->currentExtensionId = null;
        JLoader::register('OttomoModel', JPATH_ADMINISTRATOR.'/components/com_ottomo/models/model.php');
        $model = OttomoModel::getInstance('Templates');
        $model->setState('folder', $this->folder);
        $rows = $model->getList();
        if (count($rows)) {
            $this->currentExtensionId = $rows[0]->id;
        }

    }

    protected function storeExtension()
    {
        // Was there a template already installed with the same name?
        if ($this->currentExtensionId && !$this->parent->isOverwrite()) {
            // Install failed, roll back changes
            throw new RuntimeException(JText::_('JLIB_INSTALLER_ABORT_TPL_INSTALL_ALREADY_INSTALLED'));

        }
        JLoader::register('OttomoModel', JPATH_ADMINISTRATOR.'/components/com_ottomo/models/model.php');
        $model = OttomoModel::getInstance('Templates');
        $table = $model->getTable();
        $table->id = $this->currentExtensionId;
        $table->name = $this->name;
        $table->folder = $this->folder;
        $table->manifest = $this->parent->generateManifestCache();

        if (!$table->store()) {
            // Install failed, roll back changes
            throw new RuntimeException(JText::sprintf('JLIB_INSTALLER_ABORT_ROLLBACK', JText::_('JLIB_INSTALLER_'.strtoupper($this->route)), $table->getError()));
        }

    }

    protected function finaliseInstall()
    {
    }

}
