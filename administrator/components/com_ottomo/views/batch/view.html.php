<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/compat.php';

/**
 * Batch "Set topics" for selected newsletters or subscribers, shown in a SuperModal frame.
 */
class OttomoViewBatch extends OttomoView
{
    public function display($tpl = null)
    {
        $input = OttomoCompat::input();
        $this->type = in_array($input->getCmd('type'), array('newsletters', 'subscribers'), true) ? $input->getCmd('type') : 'subscribers';
        $this->ids = array_values(array_filter(array_map('intval', (array)$input->get('cid', array(), 'array'))));
        // A sole topic comes ticked
        $model = OttomoModel::getInstance('Topics');
        $model->setState('published', '-1');
        $list = $model->getList();
        $this->topics = OttomoHelperHTML::topics('checkboxes', count($list) === 1 ? array((int)$list[0]->id) : array(), 'topics[]', '', false);
        $this->setLayout('batch');
        parent::display($tpl);
    }
}
