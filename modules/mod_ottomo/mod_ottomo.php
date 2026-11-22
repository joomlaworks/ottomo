<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

// Ottomo's frontend language file (site language folder first, then the component's own)
$language = JFactory::getLanguage();
$language->load('com_ottomo', JPATH_SITE) || $language->load('com_ottomo', JPATH_SITE.'/components/com_ottomo');

require_once dirname(__FILE__).'/helper.php';

$layout = modOttomoHelper::getLayout($params);
$topics = modOttomoHelper::getTopics($params);
$essentialFields = modOttomoHelper::getEssentialFields($params, $module);
$profileFields = modOttomoHelper::getProfileFields($params, $module);
$pretext = modOttomoHelper::pretext($params);
$honeypot = modOttomoHelper::honeypot($module);
$captcha = modOttomoHelper::captcha($module);
$moduleclass_sfx = htmlspecialchars((string)$params->get('moduleclass_sfx'), ENT_QUOTES, 'UTF-8');

modOttomoHelper::loadAssets($layout);
require JModuleHelper::getLayoutPath('mod_ottomo', $layout.'/default');
