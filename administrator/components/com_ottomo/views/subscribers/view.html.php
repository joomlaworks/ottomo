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

require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/compat.php';

class OttomoViewSubscribers extends OttomoView
{
    public function display($tpl = null)
    {
        // Get rows
        $model = OttomoModel::getInstance('Subscribers');
        $model->setState('limit', $this->limit);
        $model->setState('limitstart', $this->limitstart);
        $model->setState('ordering', $this->ordering);
        $model->setState('orderingDir', $this->orderingDir);
        $model->setState('topic', $this->topic);
        $model->setState('status', $this->status);
        $model->setState('search', $this->search);
        $rows = $model->getList();
        $this->rows = $rows;

        // Get total
        $total = $model->getListCount();
        $this->total = $total;

        // Unsubscribed addresses, for "Delete All Unsubscribed" (its confirmation says how many)
        $this->unsubscribedCount = $model->countByStatus(OttomoModelSubscribers::STATUS_UNSUBSCRIBED);

        // Add columns
        $this->addColumn('COM_OTTOMO_NAME', 'link', 'name');
        $this->addColumn('COM_OTTOMO_EMAIL', 'email', 'email');
        $this->addColumn('COM_OTTOMO_STATUS', 'status', 'status');
        $this->addColumn('COM_OTTOMO_TOPICS', 'topics', null, 'class="otm-listedtopics"');
        $this->addColumn('COM_OTTOMO_DATE_ADDED', 'createdDate', 'created');

        // Add filters
        $searchFilter = OttomoHelperHTML::search($this->search);
        $this->addFilter($searchFilter);

        $statusFilter = OttomoHelperHTML::state($this->status, 'status', '', true, 'COM_OTTOMO_ACTIVE', 'COM_OTTOMO_PENDING', array(2 => 'COM_OTTOMO_UNSUBSCRIBED'));
        $this->addFilter($statusFilter, Text::_('COM_OTTOMO_STATUS'));

        $topicFilter = OttomoHelperHTML::topics('list', $this->topic);
        $this->addFilter($topicFilter, Text::_('COM_OTTOMO_TOPIC'));

        // Add batch operations fields
        $batchTopics = OttomoHelperHTML::topics('list', null, 'topics[]', 'multiple="multiple" size="10"', false);
        $this->batchTopics = $batchTopics;

        // Display
        parent::display($tpl);
    }

    public function edit($tpl = null)
    {
        // Get row
        $model = OttomoModel::getInstance('Subscribers');
        $model->setState('id', OttomoCompat::input()->getInt('id'));
        $row = $model->getRow();
        $row->topics = $model->getSubscriberTopics();
        $this->row = $row;

        // Get topics field
        $topics = OttomoHelperHTML::topics('checkboxes', $row->topics, 'topics[]', '', false);
        $this->topics = $topics;

        // Set layout
        $this->setLayout('subscriber');

        // Display
        parent::display($tpl);
    }
}
