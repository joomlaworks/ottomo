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
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Session\Session;

require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/compat.php';

class OttomoControllerNewsletters extends OttomoController
{
    public function send()
    {
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
        $params = ComponentHelper::getParams('com_ottomo');
        $model = OttomoModel::getInstance('Newsletters');
        $model->setState('id', $this->input->getInt('id'));
        $limit = max(1, (int)$params->get('limit', 50));
        $model->setState('limit', $limit);
        $model->setState('after', $this->input->getInt('after'));
        $sent = $model->send();
        $response = new stdClass();
        $response->processed = (int)$sent;
        $response->failed = count((array)$model->getState('failed', array()));
        // The cursor for the next batch, and whether this was the last one
        $response->after = (int)$model->getState('lastID');
        $response->finished = $sent === false || (int)$sent < (int)$model->getState('batchSize', $limit);
        if ($sent === false) {
            $response->error = $model->getError();
        }
        echo json_encode($response);
        return $this;
    }

    /** The saved campaign's size as sent, against Gmail's clipping limit (pre-flight checklist): read only. */
    public function size()
    {
        JLoader::register('OttomoHelperTemplate', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/template.php');
        $model = OttomoModel::getInstance('Newsletters');
        echo json_encode(array('bytes' => $model->getSendSize($this->input->getInt('id')), 'limit' => OttomoHelperTemplate::GMAIL_CLIP_BYTES));
        return $this;
    }

    /** The sender authentication of an address's domain (pre-flight checklist): read only. */
    public function senderCheck()
    {
        JLoader::register('OttomoHelperSettings', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/settings.php');
        echo json_encode(OttomoHelperSettings::senderAuthentication($this->input->getString('email')));
        return $this;
    }

    public function statistics()
    {
        $source = $this->input->getCmd('source');
        $range = $this->input->getInt('range');
        $topicId = $this->input->getInt('topicId');
        if ($range) {
            $date = Factory::getDate();
            if ($range == 1) {
                $startDate = $date->format('Y-m-d');
            } else {
                $endDate = $date->format('Y-m-d');
                $startDate = date('Y-m-d', strtotime('-'.$range.' days', strtotime($endDate)));
            }
        } else {
            $startDate = $this->input->getCmd('startDate');
            $endDate = $this->input->getCmd('endDate');
        }

        if ($source == 'core') {
            $model = OttomoModel::getInstance('Newsletters');
            if (isset($startDate)) {
                $model->setState('startDate', $startDate);
            }
            if (isset($endDate)) {
                $model->setState('endDate', $endDate);
            }
            if ($topicId) {
                $model->setState('topic', $topicId);
            }
            $model->setState('ordering', OttomoCompat::db()->quoteName('date'));
            $model->setState('orderingDir', 'ASC');
            $model->setState('type', 1);
            $sent = $model->getStatistics();
            $model->setState('type', 2);
            $clicked = $model->getStatistics();
            $model->setState('type', 3);
            $opened = $model->getStatistics();

            $statistics = array();
            if (!isset($endDate)) {
                $endDate = $startDate;
            }

            $columns = array(Text::_('COM_OTTOMO_DATE'), Text::_('COM_OTTOMO_REQUESTS'), Text::_('COM_OTTOMO_OPENED'), Text::_('COM_OTTOMO_CLICKED'));
            $properties = array('sent', 'opens', 'clicks');
            $colors = array('#b8b8b8', '#00aced', '#33b6b2');

            // Build response object
            $object = new stdClass();
            $object->chart = array();
            $stats = new stdClass();
            foreach ($properties as $key => $property) {
                $stat = new stdClass();
                $stat->total = 0;
                $stat->percentage = 0;
                $stat->label = $columns[$key + 1];
                $stat->color = $colors[$key];
                $stats->$property = $stat;
            }
            $object->stats = $stats;

            while (strtotime($startDate) <= strtotime($endDate)) {
                $row = new stdClass();
                $row->sent = 0;
                $row->clicked = 0;
                $row->opened = 0;
                $statistics[$startDate] = $row;
                $startDate = date("Y-m-d", strtotime("+1 day", strtotime($startDate)));
            }

            foreach ($sent as $email) {
                $statistics[$email->date]->sent = (int)$email->counter;
            }

            foreach ($clicked as $email) {
                $statistics[$email->date]->clicked = (int)$email->counter;

            }
            foreach ($opened as $email) {
                $statistics[$email->date]->opened = (int)$email->counter;
            }

            ksort($statistics);
            foreach ($statistics as $date => $statistic) {
                $sentValue = isset($statistic->sent) ? (int)$statistic->sent : 0;
                $openedValue = isset($statistic->opened) ? (int)$statistic->opened : 0;
                $clickedValue = isset($statistic->clicked) ? (int)$statistic->clicked : 0;
                $object->chart[] = array($date, $sentValue, $openedValue, $clickedValue);
                $object->stats->sent->total = (int)$object->stats->sent->total + (int)$sentValue;
                $object->stats->opens->total = (int)$object->stats->opens->total + (int)$openedValue;
                $object->stats->clicks->total = (int)$object->stats->clicks->total + (int)$clickedValue;
            }

            // Compute percentage values
            foreach ($object->stats as $key => $stat) {
                if ($key != 'sent' && $object->stats->sent->total > 0) {
                    $stat->percentage = ceil(($stat->total / $object->stats->sent->total) * 100);
                }
            }

            array_unshift($object->chart, $columns);
            $data = $object;

        } else {
            PluginHelper::importPlugin('ottomo-providers', $source);
            if (!isset($endDate)) {
                $endDate = $startDate;
            }
            try {
                $results = OttomoCompat::trigger('onOttomoStatistics', array($startDate, $endDate, $topicId));
                $data = $results[0];
            } catch (Exception $e) {
                Factory::getApplication()->setHeader('Status', $e->getCode());
                $data = $e->getMessage();
            }
        }

        echo json_encode($data);
        return $this;
    }

}
