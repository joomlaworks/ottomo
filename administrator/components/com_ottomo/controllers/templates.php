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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

class OttomoControllerTemplates extends OttomoController
{
    // Permissions (ottomo.templates): OttomoController::execute()

    public function install()
    {
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
        $model = OttomoModel::getInstance('Templates');
        $package = $this->input->files->get('package', null, 'raw');
        $model->setState('package', $package);
        if ($model->install()) {
            $this->setRedirect('index.php?option=com_ottomo&view=templates', Text::_('COM_OTTOMO_TEMPLATE_INSTALLED'));
        } else {
            $this->setRedirect('index.php?option=com_ottomo&view=templates', $model->getError(), 'error');
        }
    }

    public function remove()
    {
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
        $model = OttomoModel::getInstance('Templates');
        $id = $this->input->get('cid', array(), 'array');
        $model->setState('id', $id);
        if ($model->uninstall()) {
            $this->setRedirect('index.php?option=com_ottomo&view=templates', Text::_('COM_OTTOMO_TEMPLATES_DELETED'));
        } else {
            $this->setRedirect('index.php?option=com_ottomo&view=templates', $model->getError(), 'error');
        }
    }

    public function download()
    {
        Session::checkToken('get') or jexit(Text::_('JINVALID_TOKEN'));
        $id = $this->input->getInt('id');
        $model = OttomoModel::getInstance('Templates');
        $table = $model->getTable();
        $table->load($id);
        if ($table->id) {
            JLoader::register('OttomoHelperTemplate', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/template.php');
            $source = OttomoHelperTemplate::location($table->folder)->path;
            if ($table->folder && is_dir($source)) {
                // Built under an unguessable name and deleted as soon as it is read
                $filename = $table->folder.'.zip';
                $target = JPATH_SITE.'/tmp/ottomo-template-'.bin2hex(random_bytes(16)).'.zip';
                $adapter = JArchive::getAdapter('zip');
                if ($adapter->isSupported()) {
                    $files = Folder::files($source, '.', true, true);
                    $data = array();
                    foreach ($files as $file) {
                        $tmp = array();
                        $tmp['name'] = str_replace($source, '', $file);
                        $tmp['data'] = file_get_contents($file);
                        $tmp['time'] = filemtime($file);
                        $data[] = $tmp;
                    }
                    $result = $adapter->create($target, $data);
                    if ($result) {
                        $length = filesize($target);
                        $buffer = file_get_contents($target);
                        File::delete($target);
                        ob_end_clean();
                        Factory::getApplication()->clearHeaders();
                        Factory::getApplication()->setHeader('Pragma', 'public', true);
                        Factory::getApplication()->setHeader('Expires', '0', true);
                        Factory::getApplication()->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
                        Factory::getApplication()->setHeader('Content-Type', 'application/zip', true);
                        Factory::getApplication()->setHeader('Content-Disposition', 'attachment; filename="'.$filename.'"', true);
                        Factory::getApplication()->setHeader('Content-Transfer-Encoding', 'binary', true);
                        Factory::getApplication()->setHeader('Content-Length', $length, true);
                        Factory::getApplication()->sendHeaders();
                        echo $buffer;
                        exit ;
                    } else {
                        $this->setRedirect('index.php?option=com_ottomo&view=templates', Text::_('COM_OTTOMO_COULD_NOT_CREATE_DOWNLOAD_FILE'), 'error');
                    }
                } else {
                    $this->setRedirect('index.php?option=com_ottomo&view=templates', Text::_('COM_OTTOMO_NO_ZIP_SUPPORT'), 'error');
                }

            }
        }
        return $this;

    }

}
