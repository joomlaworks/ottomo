<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

class OttomoControllerFiles extends OttomoController
{
    // Permissions (ottomo.templates): OttomoController::execute()

    public function save()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        $templateId = $this->input->getInt('template');
        $contents = $this->input->get('contents', '', 'raw');
        $file = $this->input->get('file');

        $model = OttomoModel::getInstance('Templates');
        $model->setState('id', $templateId);
        $template = $model->getRow();

        $filepath = trim(base64_decode($file));
        JLoader::register('OttomoHelperTemplate', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/template.php');
        $fullpath = OttomoHelperTemplate::location($template->folder)->path.'/'.$filepath;
        $fullpath = str_replace('..', '', $fullpath);
        if ($template->folder && $file && $filepath && JFile::exists($fullpath)) {
            $result = JFile::write($fullpath, $contents);
            if ($result) {
                $message = JText::_('COM_OTTOMO_SAVED');
                $type = 'message';
            } else {
                $message = JText::_('COM_OTTOMO_AN_ERROR_OCCURED');
                $type = 'error';
            }
        } else {
            $message = JText::_('COM_OTTOMO_AN_ERROR_OCCURED');
            $type = 'error';
        }

        if ($this->task == 'save') {
            $url = 'index.php?option=com_ottomo&view=templates&task=edit&id='.$template->id;
        } else {
            $url = 'index.php?option=com_ottomo&view='.$this->view.'&task=edit&template='.$template->id.'&file='.$file;
        }
        $this->setRedirect($url, $message, $type);
    }

    public function cancel()
    {
        $templateId = $this->input->getInt('template');
        $this->setRedirect('index.php?option=com_ottomo&view=templates&task=edit&id='.$templateId);
    }

}
