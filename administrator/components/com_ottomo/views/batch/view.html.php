<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

/**
 * Batch "Set topics" for selected newsletters or subscribers, shown in a SuperModal frame.
 */
class OttomoViewBatch extends OttomoView
{
    public function display($tpl = null)
    {
        $input = JFactory::getApplication()->input;
        $this->type = in_array($input->getCmd('type'), array('newsletters', 'subscribers'), true) ? $input->getCmd('type') : 'subscribers';
        $this->ids = array_values(array_filter(array_map('intval', (array)$input->get('cid', array(), 'array'))));
        $this->topics = OttomoHelperHTML::topics('checkboxes', array(), 'topics[]', '', false);
        $this->setLayout('batch');
        parent::display($tpl);
    }
}
