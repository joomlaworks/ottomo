<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

class OttomoModelTopics extends OttomoModel
{
    public function getList()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName('#__ottomo_topics'));
        $this->setQueryConditions($query);
        $db->setQuery($query, (int)$this->getState('limitstart'), (int)$this->getState('limit'));
        $rows = $db->loadObjectList();
        if ($this->getState('countSubscribers')) {
            foreach ($rows as $row) {
                $row->subscribers = $this->countTopicSubscribers($row->id);
            }
        }
        return $rows;
    }

    public function getListCount()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('COUNT(*)');
        $query->from($db->quoteName('#__ottomo_topics'));
        $this->setQueryConditions($query);
        $db->setQuery($query);
        $result = $db->loadResult();
        return $result;
    }

    protected function setQueryConditions(&$query)
    {
        $db = $this->getDbo();
        $search = trim((string)$this->getState('search'));
        if ($search) {
            $search = $db->quote('%'.$db->escape($search, true).'%');
            $query->where($db->quoteName('name').' LIKE '.$search);
        }
        if ($this->getState('published') != -1) {
            $query->where($db->quoteName('published').' = '.(int)$this->getState('published'));
        }
        // The site: published topics that are not private (private topics are lists managed in the admin)
        if ($this->getState('public')) {
            $query->where($db->quoteName('published').' = 1');
            $query->where($db->quoteName('private').' = 0');
        }
        if ($this->getState('id')) {
            $query->where($db->quoteName('id').' IN ('.implode(',', array_map('intval', (array)$this->getState('id'))).')');
        }
        if ($this->getState('ordering') && $this->getState('orderingDir')) {
            $query->order($db->quoteName($this->getState('ordering')).' '.(strtoupper($this->getState('orderingDir')) === 'ASC' ? 'ASC' : 'DESC'));
        }

    }

    /** A topic's subscribers per status and the campaigns sent (or to be sent) to it */
    public function getSummary($topicId)
    {
        $db = $this->getDbo();
        $summary = (object)array('active' => 0, 'pending' => 0, 'unsubscribed' => 0, 'campaigns' => 0);
        $query = $db->getQuery(true);
        $query->select(array($db->quoteName('subscriber.status'), 'COUNT(*) AS '.$db->quoteName('total')));
        $query->from($db->quoteName('#__ottomo_subscribers_xref', 'xref'));
        $query->join('INNER', $db->quoteName('#__ottomo_subscribers', 'subscriber').' ON '.$db->quoteName('subscriber.id').' = '.$db->quoteName('xref.subscriberID'));
        $query->where($db->quoteName('xref.topicID').' = '.(int)$topicId);
        $query->group($db->quoteName('subscriber.status'));
        $names = array(0 => 'pending', 1 => 'active', 2 => 'unsubscribed');
        foreach ((array)$db->setQuery($query)->loadObjectList() as $row) {
            if (isset($names[(int)$row->status])) {
                $summary->{$names[(int)$row->status]} = (int)$row->total;
            }
        }
        $query = $db->getQuery(true)->select('COUNT(*)')->from($db->quoteName('#__ottomo_email_campaigns_xref'))->where($db->quoteName('topicID').' = '.(int)$topicId);
        $summary->campaigns = (int)$db->setQuery($query)->loadResult();
        return $summary;
    }

    public function countTopicSubscribers($topicId)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('COUNT(*)');
        $query->from($db->quoteName('#__ottomo_subscribers_xref', 'xref'));
        $query->join('INNER', $db->quoteName('#__ottomo_subscribers', 'subscriber').' ON '.$db->quoteName('subscriber.id').' = '.$db->quoteName('xref.subscriberID'));
        $query->where($db->quoteName('xref.topicID').' = '.(int)$topicId);
        $query->where($db->quoteName('subscriber.status').' = 1'); // active only: what a send reaches
        $db->setQuery($query);
        return (int)$db->loadResult();
    }

    public function countUniqueSubscribers($topics)
    {
        if (count($topics) == 0) {
            return 0;
        }
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('COUNT(DISTINCT '.$db->quoteName('xref.subscriberID').')');
        $query->from($db->quoteName('#__ottomo_subscribers_xref', 'xref'));
        $query->join('INNER', $db->quoteName('#__ottomo_subscribers', 'subscriber').' ON '.$db->quoteName('subscriber.id').' = '.$db->quoteName('xref.subscriberID'));
        $query->where($db->quoteName('xref.topicID').' IN ('.implode(',', array_map('intval', $topics)).')');
        $query->where($db->quoteName('subscriber.status').' = 1'); // active only: what a send reaches
        $db->setQuery($query);
        return (int)$db->loadResult();
    }

}
