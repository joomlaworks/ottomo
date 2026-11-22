<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Table\Table;

require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/compat.php';

Table::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.'/tables');

JLoader::register('OttomoController', JPATH_COMPONENT.'/controller.php');
JLoader::register('OttomoModel', JPATH_COMPONENT_ADMINISTRATOR.'/models/model.php');
JLoader::register('OttomoView', JPATH_COMPONENT_ADMINISTRATOR.'/views/view.php');
JLoader::register('OttomoHelperData', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/data.php');

$controller = BaseController::getInstance('Ottomo');
$controller->execute(OttomoCompat::input()->get('task'));
$controller->redirect();
