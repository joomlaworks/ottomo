<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

// Control panel module: Ottomo at a glance (totals, quick actions) + the statistics widget of the component dashboard
defined('_JEXEC') or die ;

JFactory::getLanguage()->load('com_ottomo', JPATH_ADMINISTRATOR.'/components/com_ottomo');
require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/views/dashboard/view.html.php';
JLoader::register('OttomoHelperHTML', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/html.php');

$base = JURI::base(true).'/components/com_ottomo';
JFactory::getDocument()->addStyleSheet(OttomoHelperHTML::asset($base.'/static/css/ottomo-graphs.css'));

// Totals
$model = OttomoModel::getInstance('Newsletters');
$model->setState('published', -1);
$numOfNewsletters = (int)$model->getListCount();

$model = OttomoModel::getInstance('Subscribers');
$numOfSubscribers = (int)$model->getListCount();

// Last sent campaign
$model = OttomoModel::getInstance('Newsletters');
$model->setState('published', 1);
$model->setState('limit', 1);
$model->setState('limitstart', 0);
$model->setState('ordering', 'newsletter.sent');
$model->setState('orderingDir', 'DESC');
$rows = $model->getList();
$lastSent = count($rows) && (int)$rows[0]->sent ? $rows[0] : null;
if ($lastSent) {
    $stats = $model->getCampaignStats(array($lastSent->id));
    $lastSent->stats = $stats[(int)$lastSent->id];
}

// Statistics widget (renders layouts/dashboard_chart.php)
ob_start();
$view = new OttomoViewDashboard();
$view->chart('module', $params);
$chart = ob_get_clean();

require JModuleHelper::getLayoutPath('mod_ottomo', $params->get('layout', 'default'));
