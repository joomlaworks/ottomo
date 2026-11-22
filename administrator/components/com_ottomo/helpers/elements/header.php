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

class JFormFieldHeader extends JFormField
{
    public $type = 'Header';

    public function getInput()
    {
        return '<h4>'.JText::_($this->value).'</h4>';
    }

    public function getLabel()
    {
        return null;
    }

}
