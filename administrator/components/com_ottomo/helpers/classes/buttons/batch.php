<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

jimport('cms.toolbar.button');

/**
 * Ottomo Button
 */

class JToolbarButtonOttomoBatch extends JToolbarButton
{
    public function fetchButton($type = 'Ottomo', $name = '', $text = '', $task = '', $list = true)
    {
        JHtml::_('behavior.core');

        $text = JText::_($text);
        $buttonClass = '';
        $iconClass = ($name) ? ' fa-'.$name : '';
        $onclick = $this->_getCommand($text, $task, $list);

        ob_start();
        include JPATH_COMPONENT_ADMINISTRATOR.'/layouts/buttonBatch.php';
        $output = ob_get_clean();

        return $output;
    }

    public function fetchId($type = 'Ottomo', $name = '', $text = '', $task = '', $list = true, $hideMenu = false)
    {
        return $this->_parent->getName().'-'.$name;
    }

    protected function _getCommand($name, $task, $list)
    {
        $message = JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST');
        $message = addslashes($message);

        if ($list) {
            $cmd = "if (document.adminForm.boxchecked.value==0){alert('$message');}else{ Joomla.submitbutton('$task')}";
        } else {
            $cmd = "Joomla.submitbutton('$task')";
        }

        return $cmd;
    }

}
