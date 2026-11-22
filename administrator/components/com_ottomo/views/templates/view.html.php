<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

class OttomoViewTemplates extends OttomoView
{
    public function display($tpl = null)
    {
        // Get rows
        $model = OttomoModel::getInstance('Templates');
        $model->setState('limit', $this->limit);
        $model->setState('limitstart', $this->limitstart);
        // Alphabetical
        $model->setState('ordering', 'name');
        $model->setState('orderingDir', 'ASC');
        $this->rows = $model->getList();

        // Get total
        $this->total = $model->getListCount();

        // Set layout
        $this->setLayout('templates');

        // Display
        parent::display($tpl);
    }

    public function edit($tpl = null)
    {
        if (!OttomoHelperHTML::canManageTemplates()) {
            JFactory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
            JFactory::getApplication()->redirect(JRoute::_('index.php?option=com_ottomo&view=templates', false));
        }
        // Get row
        $model = OttomoModel::getInstance('Templates');
        $model->setState('id', JFactory::getApplication()->input->getInt('id'));
        $this->row = $model->getRow();

        // Get files
        require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/template.php';
        $this->files = OttomoHelperTemplate::getFiles($this->row);

        // Set layout
        $this->setLayout('template');

        // Display
        parent::display($tpl);
    }


}
