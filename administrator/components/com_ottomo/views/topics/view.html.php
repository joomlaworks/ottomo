<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

class OttomoViewTopics extends OttomoView
{
    /** Alphabetical */
    protected $defaultOrdering = array('name', 'asc');

    public function display($tpl = null)
    {
        // Get rows
        $model = OttomoModel::getInstance('Topics');
        $model->setState('limit', $this->limit);
        $model->setState('limitstart', $this->limitstart);
        $model->setState('ordering', $this->ordering);
        $model->setState('orderingDir', $this->orderingDir);
        $model->setState('published', $this->published);
        $model->setState('search', $this->search);
        $model->setState('countSubscribers', true);
        $this->rows = $model->getList();

        // Get total
        $this->total = $model->getListCount();

        // Add columns
        $this->addColumn('COM_OTTOMO_NAME', 'link', 'name');
        $this->addColumn('COM_OTTOMO_ACTIVE_SUBSCRIBERS', 'subscribersLink', null, 'class="otm-text-end"');
        $this->addColumn('COM_OTTOMO_PUBLISHED', 'published', 'published', 'class="otm-text-center"');
        $this->addColumn('COM_OTTOMO_ID', 'id', 'id', 'class="otm-text-center"');

        // Add filters
        $searchFilter = OttomoHelperHTML::search($this->search);
        $this->addFilter($searchFilter);

        $stateFilter = OttomoHelperHTML::state($this->published);
        $this->addFilter($stateFilter, JText::_('COM_OTTOMO_STATE'));

        // Display
        parent::display($tpl);

    }

    public function edit($tpl = null)
    {
        // Get row
        $model = OttomoModel::getInstance('Topics');
        $model->setState('id', JFactory::getApplication()->input->getInt('id'));
        $this->row = $model->getRow();
        $this->summary = $this->row->id ? $model->getSummary($this->row->id) : null;

        // Published field
        $this->published = JHTML::_('select.booleanlist', 'published', '', $this->row->published);

        // Set layout
        $this->setLayout('topic');

        // Display
        parent::display($tpl);
    }

}
