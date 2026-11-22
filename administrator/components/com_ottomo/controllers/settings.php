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

require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/compat.php';

class OttomoControllerSettings extends OttomoController
{
    public function apply()
    {
        $response = $this->saveSettings();
        $this->setRedirect('index.php?option=com_ottomo&view=settings&task=edit', $response->message, $response->type);
    }

    public function cancel()
    {
        $this->setRedirect('index.php?option=com_ottomo');
    }

    public function save()
    {
        $response = $this->saveSettings();
        $this->setRedirect('index.php?option=com_ottomo', $response->message, $response->type);
    }

    protected function saveSettings()
    {

        $application = Factory::getApplication();
        $user = OttomoCompat::user();
        if (!Session::checkToken()) {
            $application->enqueueMessage(Text::_('JINVALID_TOKEN'));
            $application->redirect('index.php');
        }
        if (!$user->authorise('core.admin', 'com_ottomo')) {
            $application->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'));
            $application->redirect('index.php');
        }

        $data = $this->input->get('jform', array(), 'array');

        // Validate against Ottomo's settings form (config.xml): filtered, then checked
        $model = OttomoModel::getInstance('Settings');
        $form = $model->getForm();
        $params = $form->filter($data);
        if ($form->validate($params) !== true) {
            $errors = $form->getErrors();
            $error = count($errors) ? $errors[0] : Text::_('COM_OTTOMO_AN_ERROR_OCCURED');
            $response = new stdClass();
            $response->message = $error instanceof Exception ? $error->getMessage() : (string)$error;
            $response->type = 'warning';
            return $response;
        }

        // Always com_ottomo's own asset and extension row (OttomoModelSettings::save()), never ids from the request
        $data = array('params' => $params);

        $model = OttomoModel::getInstance('Settings', 'OttomoModel');
        $model->setState('option', 'com_ottomo');
        $model->setState('data', $data);

        $response = new stdClass();
        if ($model->save()) {
            $response->message = Text::_('COM_OTTOMO_SETTINGS_SAVED');
            $response->type = 'message';
        } else {
            $response->message = htmlspecialchars((string)$model->getError(), ENT_QUOTES, 'UTF-8');
            $response->type = 'error';
        }
        return $response;
    }

}
