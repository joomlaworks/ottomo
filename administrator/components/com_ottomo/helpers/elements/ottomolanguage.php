<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

jimport('joomla.form.formfield');

/**
 * Loads Ottomo's backend language file for a module's settings form (the modules have no language files of their
 * own). Put it first in the fieldset: Joomla creates all the fields of a fieldset before rendering their labels.
 * Renders nothing.
 */
class JFormFieldOttomoLanguage extends JFormField
{
    public $type = 'OttomoLanguage';

    protected $hidden = true;

    public function setup(SimpleXMLElement $element, $value, $group = null)
    {
        $language = JFactory::getLanguage();
        $language->load('com_ottomo', JPATH_ADMINISTRATOR) || $language->load('com_ottomo', JPATH_ADMINISTRATOR.'/components/com_ottomo');
        return parent::setup($element, $value, $group);
    }

    protected function getInput()
    {
        return '';
    }

    protected function getLabel()
    {
        return '';
    }
}
