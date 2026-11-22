<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;

require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/compat.php';

class OttomoViewExtension extends OttomoView
{
    public function display($tpl = null)
    {
        // Pass the view to the plugin
        PluginHelper::importPlugin('ottomo-content', $this->extension);
        OttomoCompat::trigger('onOttomoExtensionView', array($this));

        // Set layout
        $this->setLayout('extension');

        // Display
        parent::display($tpl);
    }

    protected function addTitle()
    {
        $this->title = Text::_('PLG_OTTOMO_CONTENT_'.strtoupper($this->extension).'_LABEL'); // the content plugin's name, e.g. Joomla Articles
        ToolbarHelper::title($this->title, 'ottomo');
    }

}
