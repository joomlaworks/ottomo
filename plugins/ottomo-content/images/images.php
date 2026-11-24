<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;

require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/compat.php';

class plgOttomocontentImages extends CMSPlugin
{
    /** Whether the content source (com_content, K2...) is on the site. */
    protected $isInstalled = false;

    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        OttomoCompat::language()->load('com_ottomo', JPATH_ADMINISTRATOR.'/components/com_ottomo'); // Ottomo's backend language file
        $this->isInstalled = $this->isInstalled();
        $settings = ComponentHelper::getParams('com_ottomo');
        $this->params->merge($settings);
    }

    public function onOttomoExtensionEditorButton()
    {
        if (!$this->isInstalled) {
            return false;
        }

        $button = new stdClass();
        $button->extension = $this->_name;
        $button->label = Text::_('PLG_OTTOMO_CONTENT_IMAGES_LABEL');
        // Joomla 3: the image picker (its Insert calls jInsertFieldValue()); Joomla 4+: the media manager, whose selection
        // the designer's frame inserts (see Ottomo.mediaPicker() in static/js/ottomo.js)
        $button->link = OttomoCompat::isModern()
            ? Uri::base(true).'/index.php?option=com_media&view=media&tmpl=component&mediatypes=0&asset=com_ottomo&path=local-images:/'
            : Uri::base(true).'/index.php?option=com_media&view=images&tmpl=component&asset=com_ottomo&folder=';
        return $button;
    }

    protected function isInstalled()
    {
        return true;
    }

}
