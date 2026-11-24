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
use Joomla\CMS\Uri\Uri;

require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/compat.php';

class OttomoControllerExtension extends OttomoController
{
    public function getData()
    {
        JLoader::register('OttomoHelperTemplate', JPATH_SITE.'/administrator/components/com_ottomo/helpers/template.php');
        $extension = $this->input->getCmd('extension');
        $template = $this->input->getCmd('template');
        $type = $this->input->getCmd('type');
        $id = $this->input->getInt('id');
        if ($extension) {
            PluginHelper::importPlugin('ottomo-content', $extension);
            $results = OttomoCompat::trigger('onOttomoExtensionRow'.ucfirst($type), array($id));
            if ($type == 'link') {
                echo implode('', $results);
            } elseif (!empty($results[0])) { // nothing for content this user may not see
                echo OttomoHelperTemplate::renderExtension($extension, $results[0], $template);
            }
        }
        return $this;
    }

    /** A new button for the designer, in the template's style (the "+" menu's Button item) */
    public function button()
    {
        JLoader::register('OttomoHelperTemplate', JPATH_SITE.'/administrator/components/com_ottomo/helpers/template.php');
        echo OttomoHelperTemplate::button($this->input->getCmd('template'), Text::_('COM_OTTOMO_EDITOR_BUTTON_DEFAULT_TEXT'), Uri::root());
        return $this;
    }

}
