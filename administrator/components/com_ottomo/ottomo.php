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
use Joomla\CMS\MVC\Controller\BaseController;

require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/compat.php';

// Check component access
$user = OttomoCompat::user();
if (!$user->authorise('core.manage', 'com_ottomo')) {
    throw new Exception(Text::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 403);
}


// Add auto load classes
JLoader::register('OttomoController', JPATH_COMPONENT.'/controller.php');
JLoader::register('OttomoModel', JPATH_COMPONENT.'/models/model.php');
JLoader::register('OttomoView', JPATH_COMPONENT.'/views/view.php');

// Bootstrap
$controller = BaseController::getInstance('Ottomo');
$controller->execute(OttomoCompat::input()->get('task'));
$controller->redirect();
