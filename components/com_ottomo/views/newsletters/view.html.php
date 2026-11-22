<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

class OttomoViewNewsletters extends OttomoView
{
    public function display($tpl = null)
    {
        // Get row
        $model = OttomoModel::getInstance('Newsletters');
        $model->setState('id', JFactory::getApplication()->input->getInt('id'));
        $row = $model->getRow();

        //Check that newsletter exists
        if (!$row->id || !$row->published) {
            JError::raiseError(404, JText::_('COM_OTTOMO_NOT_FOUND'));
            return;
        }

        //Get newsletter topics
        $row->topics = $model->getNewsletterTopics();

        //Get newsletter data
        $row->data = $model->getNewsletterData();

        // Check for subscriber data passed in the URL ( This happens when a user clicks to open a mail from the mailbox to the browser)
        $subscriber = false;
        $application = JFactory::getApplication();
        $subscriberId = $application->input->getInt('subscriber');
        $subscriberHash = $application->input->getCmd('hash');
        // Only a genuine subscriber link personalises the page (an invalid hash shows the public version)
        if ($subscriberId && $subscriberHash && OttomoHelperData::checkHash($row->id, $subscriberId, $subscriberHash)) {
            $subscriber = new stdClass();
            $subscriber->id = $subscriberId;
            $subscriber->hash = $subscriberHash;
        }

        // Render the template
        $this->addHelperPath(JPATH_COMPONENT_ADMINISTRATOR.'/helpers');
        $this->loadHelper('template');
        echo ottomoHelperTemplate::render($row, 'view', false, $subscriber);
        jexit();
    }

}
