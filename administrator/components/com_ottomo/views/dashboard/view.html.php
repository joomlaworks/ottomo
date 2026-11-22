<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/views/view.php';
require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/models/model.php';

class OttomoViewDashboard extends OttomoView
{
    public function display($tpl = null)
    {
        // Get component params
        $params = JComponentHelper::getParams('com_ottomo');

        // Chart
        $this->chart('component', $params);

        // Sending status (Settings > Dashboard), cached for 10 minutes
        JLoader::register('OttomoHelperSettings', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/settings.php');
        $this->health = OttomoHelperSettings::enabled('dashboardHealth') ? OttomoHelperSettings::checkConnection(true) : null;

        // Other statistics
        $model = OttomoModel::getInstance('Newsletters');
        $model->setState('published', -1);
        $this->numOfNewsletters = $model->getListCount();

        $model = OttomoModel::getInstance('Subscribers');
        $this->numOfSubscribers = $model->getListCount();
        $model->setState('limit', $params->get('dashboardSubscribersLimit', 5));
        $model->setState('ordering', 'subscriber.id');
        $model->setState('orderingDir', 'DESC');
        $this->latestSubscribers = $model->getList();

        foreach ($this->latestSubscribers as $row) {
            $model = OttomoModel::getInstance('Topics');
            $ids = $row->topics ? explode(',', (string)$row->topics) : array();
            JArrayHelper::toInteger($ids);
            $topics = array();
            foreach ($ids as $id) {
                $model->setState('id', $id);
                $topic = $model->getRow();
                $topics[] = $topic->name;
            }
            $row->topics = $topics;
            $row->numOfTopics = count($row->topics);
        }

        // Latest campaigns: recently sent (with their open/click rates) and drafts
        $limit = (int)$params->get('dashboardNewslettersLimit', 5) ?: 5;
        $model = OttomoModel::getInstance('Newsletters');
        $model->setState('limit', $limit);
        $model->setState('limitstart', 0);
        $model->setState('published', 1);
        $model->setState('ordering', 'newsletter.sent');
        $model->setState('orderingDir', 'DESC');
        $this->recentSent = $model->getList();
        $stats = $model->getCampaignStats(array_map(function ($row) {
            return $row->id;
        }, $this->recentSent));
        foreach ($this->recentSent as $row) {
            $row->stats = $stats[(int)$row->id];
        }
        $model->setState('published', 0);
        $model->setState('ordering', 'newsletter.id');
        $this->drafts = $model->getList();
        $this->latestNewsletters = array_merge($this->recentSent, $this->drafts);

        // Custom layout
        $this->setLayout('dashboard');

        // Display
        parent::display($tpl);
    }

    public function chart($mode = 'component', $params)
    {
        $language = JFactory::getLanguage();
        $language->load('com_ottomo', JPATH_ADMINISTRATOR.'/components/com_ottomo');

        $document = JFactory::getDocument();
        JLoader::register('OttomoHelperHTML', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/html.php');
        $document->addStyleSheet(OttomoHelperHTML::asset(JURI::base(true).'/components/com_ottomo/static/css/ottomo-graphs.css'));
        $document->addScript(OttomoHelperHTML::asset(JURI::base(true).'/components/com_ottomo/static/js/ottomo.js'));

        $model = OttomoModel::getInstance('Topics');
        $model->setState('limit', 100);
        $model->setState('limitstart', 0);
        $model->setState('ordering', 'id');
        $model->setState('orderingDir', 'DESC');
        $model->setState('published', 1);
        $topics = $model->getList();
        $options = array();
        $options[] = JHtml::_('select.option', 0, JText::_('COM_OTTOMO_SELECT_TOPIC'), 'id', 'name');
        foreach ($topics as $topic) {
            $options[] = JHtml::_('select.option', $topic->id, $topic->name, 'id', 'name');
        }
        $this->topicsFilter = JHtml::_('select.genericlist', $options, 'topicId', '', 'id', 'name');

        if ($mode == 'module') {
            if (!$params->get('source')) {
                $componentParams = JComponentHelper::getParams('com_ottomo');
                if (in_array($componentParams->get('mailer', 'mail'), array('brevo', 'mailgun', 'mailjet', 'sendgrid', 'sendpulse', 'ses'))) {
                    $params->set('source', $componentParams->get('mailer', 'mail'));
                } else {
                    $params->set('source', 'core');
                }
            }

        } elseif ($mode == 'component') {
            if (in_array($params->get('mailer', 'mail'), array('brevo', 'mailgun', 'mailjet', 'sendgrid', 'sendpulse', 'ses'))) {
                $params->set('source', $params->get('mailer', 'mail'));
            } else {
                $params->set('source', 'core');
            }
        }

        $this->params = $params;
        $this->mode = $mode;

        if ($this->mode == 'module') {
            // Custom layout
            $this->setLayout('dashboard_chart');

            // Display
            parent::display(null);
        }
    }

}
