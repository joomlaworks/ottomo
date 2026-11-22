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

class OttomoControllerNewsletters extends OttomoController
{
    public function designer($mode = 'edit')
    {
        JLoader::register('OttomoHelperTemplate', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/template.php');
        $model = OttomoModel::getInstance('Newsletters');
        $model->setState('id', $this->input->getInt('id'));
        $row = $model->getRow();
        $row->topics = $model->getNewsletterTopics();
        $row->data = $model->getNewsletterData();
        $template = $this->input->getInt('template');
        if ($template) {
            $row->template = $template;
        }
        echo OttomoHelperTemplate::render($row, $mode, false);
        jexit();
    }

    public function preview()
    {
        $this->designer('view');
    }

    public function popup()
    {
        // Opened in a SuperModal frame (GET): the token travels in the URL
        Session::checkToken('get') or Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
        $this->input->set('tmpl', 'component');
        $view = $this->getView('newsletters', 'html');
        $view->popup();
        return $this;
    }

    public function save()
    {
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
        $model = $this->getModel();
        $data = $this->input->post->getArray();
        $data['snapshot'] = $this->input->getRaw('snapshot', '');
        foreach ($data as $field => $value) {
            if (strpos($field, 'ottomoPosition_') === 0) {
                $data[$field] = $this->input->post->getRaw($field);
            }
        }
        $model->setState('data', $data);
        if ($model->save()) {
            $message = Text::_('COM_OTTOMO_SAVED');
            $type = 'message';
        } else {
            $message = htmlspecialchars((string)$model->getError(), ENT_QUOTES, 'UTF-8');
            $type = 'error';
        }
        if ($this->task == 'save') {
            $url = 'index.php?option=com_ottomo&view='.$this->view;
        } else {
            $url = 'index.php?option=com_ottomo&view='.$this->view.'&task=edit&&id='.$model->getState('id');
        }
        $this->setRedirect($url, $message, $type);
    }

    /** Copies a campaign as a new draft and opens it (list row action, "already sent" banner) */
    public function duplicate()
    {
        Session::checkToken('get') or Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
        $model = OttomoModel::getInstance('Newsletters');
        $model->setState('id', $this->input->getInt('id'));
        $id = $model->duplicate();
        if (!$id) {
            $this->setRedirect('index.php?option=com_ottomo&view=newsletters', htmlspecialchars((string)$model->getError(), ENT_QUOTES, 'UTF-8'), 'error');
            return $this;
        }
        $this->setRedirect('index.php?option=com_ottomo&view=newsletters&task=edit&id='.$id, Text::_('COM_OTTOMO_CAMPAIGN_DUPLICATED'));
        return $this;
    }

    public function sample()
    {
        // Opened in a SuperModal frame (GET): the token travels in the URL
        Session::checkToken('get') or Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
        $application = Factory::getApplication();
        $model = OttomoModel::getInstance('Newsletters');
        $model->setState('id', $this->input->getInt('id'));
        $emails = array_filter(array_map('trim', explode(',', $this->input->getString('emails', ''))));
        $redirect = 'index.php?option=com_ottomo&task=newsletters.sampleResults&tmpl=component';
        if (!count($emails)) {
            $this->setRedirect($redirect, Text::_('COM_OTTOMO_SEND_TEST_NO_EMAILS'), 'error');
            return;
        }
        $model->setState('emails', $emails);
        $model->setState('forcePhpMail', $this->input->getBool('testUsePhpMail', false));
        if ($model->send() === false) {
            $this->setRedirect($redirect, $model->getError() ?: Text::_('COM_OTTOMO_SEND_TEST_FAILED'), 'error');
            return;
        }
        $this->setRedirect($redirect, Text::sprintf('COM_OTTOMO_SEND_TEST_SENT_TO', implode(', ', $emails)));
    }

    public function sampleResults()
    {
        $this->input->set('tmpl', 'component');
        $view = $this->getView('newsletters', 'html');
        $view->sample();
        return $this;
    }

}
