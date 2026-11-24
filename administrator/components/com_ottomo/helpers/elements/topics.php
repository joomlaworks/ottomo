<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;


FormHelper::loadFieldClass('list');

class JFormFieldTopics extends JFormFieldList
{
    public $type = 'Topics';

    protected function getOptions()
    {
        JLoader::register('OttomoModel', JPATH_ADMINISTRATOR.'/components/com_ottomo/models/model.php');
        $model = OttomoModel::getInstance('Topics');
        $model->setState('published', '1');
        $model->setState('public', true); // the signup module offers public topics only
        $model->setState('ordering', 'name');
        $model->setState('orderingDir', 'asc');
        $topics = $model->getList();
        $options = array();
        foreach ($topics as $topic) {
            $options[] = HTMLHelper::_('select.option', $topic->id, $topic->name);
        }
        $options = array_merge(parent::getOptions(), $options);
        return $options;
    }

}
