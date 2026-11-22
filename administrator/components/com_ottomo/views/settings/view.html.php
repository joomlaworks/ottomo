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
use Joomla\CMS\Plugin\PluginHelper;

require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/compat.php';

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
        $application = Factory::getApplication();
        $user = OttomoCompat::user();

        if (!$user->authorise('core.admin', 'com_ottomo')) {
            $application->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'));
            $application->redirect('index.php');
        }

        $model = $this->getModel();
        $model->setState('option', 'com_ottomo');
        $form = $model->getForm();
        // K2 (Joomla 3 only): its content settings only while Ottomo's K2 plugin is enabled
        if (!PluginHelper::isEnabled('ottomo-content', 'k2')) {
            foreach ($form->getFieldset('layout') as $field) {
                if (strpos((string)$field->fieldname, 'k2') === 0) {
                    $form->removeField($field->fieldname);
                }
            }
        }
        $this->form = $form;
        $id = $model->getExtensionID();
        $this->id = $id;
        $this->setLayout('settings');
        parent::display($tpl);


    }

}
