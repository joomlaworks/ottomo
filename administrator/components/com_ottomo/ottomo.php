<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

// Check component access
$user = JFactory::getUser();
if (!$user->authorise('core.manage', 'com_ottomo')) {
    throw new Exception(JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 403);
}

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

// Add auto load classes
JLoader::register('OttomoController', JPATH_COMPONENT.'/controller.php');
JLoader::register('OttomoModel', JPATH_COMPONENT.'/models/model.php');
JLoader::register('OttomoView', JPATH_COMPONENT.'/views/view.php');

// Bootstrap
$controller = JControllerLegacy::getInstance('Ottomo');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
