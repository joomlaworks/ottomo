<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * Ottomo's toolbars (header, subheader, batch): a list of buttons rendered with layouts/buttonStandard.php or
 * buttonBatch.php. No Joomla toolbar (its button classes differ between Joomla 3 and 4+).
 */
class OttomoToolbar
{
    protected $items = array();

    /** $type: OttomoStandard or OttomoBatch; $name: the icon/button name; $list: a selection is needed first. */
    public function appendButton($type, $name, $text, $task = '', $list = false)
    {
        $this->items[] = array($type, $name, $text, $task, $list);
    }

    public function getItems()
    {
        return $this->items;
    }

    public function renderButton($button)
    {
        list($type, $name, $text, $task, $list) = array_pad((array)$button, 5, '');
        $text = Text::_($text);
        $buttonClass = $type === 'OttomoStandard' && $name ? ' otm-btn--'.$name : '';
        $iconClass = $name ? ' fa-'.$name : '';
        $onclick = self::command($task, $list);
        ob_start();
        include JPATH_ADMINISTRATOR.'/components/com_ottomo/layouts/'.($type === 'OttomoBatch' ? 'buttonBatch.php' : 'buttonStandard.php');
        return ob_get_clean();
    }

    protected static function command($task, $list)
    {
        $task = addslashes((string)$task);
        if ($list) {
            $message = addslashes(Text::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'));
            return "if (document.adminForm.boxchecked.value==0){alert('$message');}else{ Joomla.submitbutton('$task')}";
        }
        return "Joomla.submitbutton('$task')";
    }
}
