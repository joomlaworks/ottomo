<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

class OttomoController extends JControllerLegacy
{
    /**
     * Method to display a view.
     *
     * @param   boolean			If true, the view output will be cached
     * @param   array  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
     *
     * @return  JController		This object to support chaining.
     * @since   1.5
     */
    public function display($cachable = false, $urlparams = false)
    {

        $application = JFactory::getApplication();
        $user = JFactory::getUser();
        $view = $application->input->getCmd('view');

        if ($view == 'subscribers') {
            $newsletterId = $application->input->getInt('newsletter');
            $subscriberId = $application->input->getInt('id');
            $hash = $application->input->getVar('hash');
            if (!OttomoHelperData::checkHash($newsletterId, $subscriberId, $hash)) {
                $application->enqueueMessage(JText::_('COM_OTTOMO_NOT_FOUND'), 'error');
                return;
            }
        }

        if ($user->guest && $application->input->getMethod() == 'GET') {
            $cachable = true;
        }

        $urlparams = array('limit' => 'UINT', 'limitstart' => 'UINT', 'Itemid' => 'INT');
        parent::display($cachable, $urlparams);
        return $this;
    }

}
