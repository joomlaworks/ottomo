<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

JLoader::register('OttomoHelperSettings', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/settings.php');

class OttomoControllerSettings extends OttomoController
{
    /** Test the saved sending settings (Settings > Sending > Test connection). */
    public function testConnection()
    {
        JSession::checkToken() or jexit(json_encode(array('level' => 'error', 'message' => JText::_('JINVALID_TOKEN'))));
        if (!JFactory::getUser()->authorise('core.admin', 'com_ottomo')) {
            jexit(json_encode(array('level' => 'error', 'message' => JText::_('JERROR_ALERTNOAUTHOR'))));
        }
        $result = OttomoHelperSettings::checkConnection(false);
        // The default sender's domain: SPF, DKIM and DMARC
        $result->auth = OttomoHelperSettings::senderAuthentication(OttomoHelperSettings::sender()->email);
        echo json_encode($result);
        return $this;
    }
}
