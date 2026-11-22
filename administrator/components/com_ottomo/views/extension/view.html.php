<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

class OttomoViewExtension extends OttomoView
{
    public function display($tpl = null)
    {
        // Pass the view to the plugin
        $dispatcher = JDispatcher::getInstance();
        JPluginHelper::importPlugin('ottomo-content', $this->extension);
        $dispatcher->trigger('ottomoExtensionView', array(&$this));

        // Set layout
        $this->setLayout('extension');

        // Display
        parent::display($tpl);
    }

    protected function addTitle()
    {
        $this->title = JText::_('COM_OTTOMO_'.strtoupper($this->view).'_'.strtoupper($this->extension));
        JToolBarHelper::title($this->title, 'ottomo');
    }

}
