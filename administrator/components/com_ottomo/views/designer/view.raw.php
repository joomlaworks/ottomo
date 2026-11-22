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

require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/compat.php';

class OttomoViewDesigner extends OttomoView
{
    public function display($tpl = null)
    {
        JLoader::register('OttomoHelperTemplate', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/template.php');
        $application = Factory::getApplication();
        $model = OttomoModel::getInstance('Newsletters');
        $model->setState('id', OttomoCompat::input()->getInt('id'));
        $row = $model->getRow();
        $row->topics = $model->getNewsletterTopics();
        $row->data = $model->getNewsletterData();
        $template = OttomoCompat::input()->getInt('template');
        if ($template) {
            $row->template = $template;
        }
        // A new campaign before a template is chosen: an empty frame
        if (!(int)$row->template) {
            echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title></title></head><body></body></html>';
            return;
        }
        echo OttomoHelperTemplate::render($row, 'edit');
    }


}
