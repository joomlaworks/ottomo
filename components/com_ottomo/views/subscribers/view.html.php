<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

class OttomoViewSubscribers extends OttomoView
{
    public function display($tpl = null)
    {
        // Get subscriber
        $model = OttomoModel::getInstance('Subscribers');
        $model->setState('id', JFactory::getApplication()->input->getInt('id'));
        $this->row = $model->getRow();

        // Check that we have a valid subscriber
        if (!$this->row->id || !$this->row->status) {
            $application = JFactory::getApplication();
            $application->enqueueMessage(JText::_('COM_OTTOMO_NOT_FOUND'), 'error');
            return;
        }

        // Get subscriber topics
        $this->row->topics = $model->getSubscriberTopics();

        // Get all topics for creating the fields
        $model = OttomoModel::getInstance('Topics');
        $model->setState('published', '1');
        $model->setState('ordering', 'name');
        $model->setState('orderingDir', 'ASC');
        $this->topics = $model->getList();

        // Pass the hash variable to the view
        $this->hash = JFactory::getApplication()->input->getCmd('hash');

        // Pass the newsletter id to the view
        $this->newsletterId = JFactory::getApplication()->input->getInt('newsletter');

        // Set the layout
        $this->setLayout('subscriber');

        // Display
        parent::display($tpl);
    }

}
