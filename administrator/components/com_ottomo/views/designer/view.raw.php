<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

class OttomoViewDesigner extends OttomoView
{
    public function display($tpl = null)
    {
        JLoader::register('OttomoHelperTemplate', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/template.php');
        $application = JFactory::getApplication();
        $model = OttomoModel::getInstance('Newsletters');
        $model->setState('id', $application->input->getInt('id'));
        $row = $model->getRow();
        $row->topics = $model->getNewsletterTopics();
        $row->data = $model->getNewsletterData();
        $template = $application->input->getInt('template');
        if ($template) {
            $row->template = $template;
        }
        echo OttomoHelperTemplate::render($row, 'edit');
    }


}
