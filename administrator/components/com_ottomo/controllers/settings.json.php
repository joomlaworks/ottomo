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
use Joomla\CMS\Session\Session;

require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/compat.php';

JLoader::register('OttomoHelperSettings', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/settings.php');

class OttomoControllerSettings extends OttomoController
{
    /** Test the saved sending settings (Settings > Sending > Test connection). */
    public function testConnection()
    {
        Session::checkToken() or jexit(json_encode(array('level' => 'error', 'message' => Text::_('JINVALID_TOKEN'))));
        if (!OttomoCompat::user()->authorise('core.admin', 'com_ottomo')) {
            jexit(json_encode(array('level' => 'error', 'message' => Text::_('JERROR_ALERTNOAUTHOR'))));
        }
        $result = OttomoHelperSettings::checkConnection(false);
        // The default sender's domain: SPF, DKIM and DMARC
        $result->auth = OttomoHelperSettings::senderAuthentication(OttomoHelperSettings::sender()->email);
        echo json_encode($result);
        return $this;
    }
}
