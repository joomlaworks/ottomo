<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

class OttomoViewSettings extends OttomoView
{
    /**
     * Settings have no list: view=settings without task=edit shows the form too (was a 500, missing layout).
     */
    public function display($tpl = null)
    {
        return $this->edit($tpl);
    }

    public function edit($tpl = null)
    {
        $application = JFactory::getApplication();
        $user = JFactory::getUser();

        if (!$user->authorise('core.admin', 'com_ottomo')) {
            $application->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'));
            $application->redirect('index.php');
        }

        $model = $this->getModel();
        $model->setState('option', 'com_ottomo');
        $form = $model->getForm();
        $this->assignRef('form', $form);
        $id = $model->getExtensionID();
        $this->assignRef('id', $id);
        $this->setLayout('settings');
        parent::display($tpl);


    }

}
