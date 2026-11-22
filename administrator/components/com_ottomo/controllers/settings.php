<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

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

        $application = JFactory::getApplication();
        $user = JFactory::getUser();
        if (!JSession::checkToken()) {
            $application->enqueueMessage(JText::_('JINVALID_TOKEN'));
            $application->redirect('index.php');
        }
        if (!$user->authorise('core.admin', 'com_ottomo')) {
            $application->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'));
            $application->redirect('index.php');
        }

        $data = $this->input->get('jform', array(), 'array');

        require_once JPATH_SITE.'/components/com_config/model/cms.php';
        require_once JPATH_SITE.'/components/com_config/model/form.php';

        // Get form
        $model = OttomoModel::getInstance('Settings');
        $form = $model->getForm();

        // Validate the form
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_config/models');
        $model = JModelLegacy::getInstance('Component', 'ConfigModel');
        $params = $model->validate($form, $data);
        if ($params === false) {
            $errors = $model->getErrors();
            $response = new stdClass();
            $response->message = $errors[0] instanceof Exception ? $errors[0]->getMessage() : $errors[0];
            $response->type = 'warning';
            return $response;
        }

        // Always com_ottomo's own asset and extension row (OttomoModelSettings::save()), never ids from the request
        $data = array('params' => $params);

        $model = OttomoModel::getInstance('Settings', 'OttomoModel');
        $model->setState('option', 'com_ottomo');
        $model->setState('data', $data);

        $options = array('defaultgroup' => '_system', 'cachebase' => JPATH_ADMINISTRATOR.'/cache');
        $cache = JCache::getInstance('callback', $options);
        $cache->clean();

        $response = new stdClass();
        if ($model->save()) {
            $response->message = JText::_('COM_OTTOMO_SETTINGS_SAVED');
            $response->type = 'message';
        } else {
            $response->message = htmlspecialchars((string)$model->getError(), ENT_QUOTES, 'UTF-8');
            $response->type = 'error';
        }
        return $response;
    }

}
