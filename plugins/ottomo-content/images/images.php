<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

class plgOttomocontentImages extends JPlugin
{
    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        JFactory::getLanguage()->load('com_ottomo', JPATH_ADMINISTRATOR.'/components/com_ottomo'); // Ottomo's backend language file
        $this->isInstalled = $this->isInstalled();
        $settings = JComponentHelper::getParams('com_ottomo');
        $this->params->merge($settings);
    }

    public function ottomoExtensionEditorButton()
    {
        if (!$this->isInstalled) {
            return false;
        }

        $button = new stdClass();
        $button->extension = $this->_name;
        $button->label = JText::_('PLG_OTTOMO_CONTENT_IMAGES_LABEL');
        $button->link = JURI::base(true).'/index.php?option=com_media&view=images&tmpl=component&asset=com_ottomo&folder=';
        return $button;
    }

    protected function isInstalled()
    {
        return true;
    }

}
