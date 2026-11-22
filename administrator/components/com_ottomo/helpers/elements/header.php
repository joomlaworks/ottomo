<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;


class JFormFieldHeader extends FormField
{
    public $type = 'Header';

    public function getInput()
    {
        return '<h4>'.Text::_($this->value).'</h4>';
    }

    public function getLabel()
    {
        return null;
    }

}
