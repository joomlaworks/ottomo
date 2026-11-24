<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

class OttomoViewImport extends OttomoView
{
    public function display($tpl = null)
    {
        // Topics: every imported subscriber needs one (campaigns are sent to topics); a sole topic comes ticked
        $model = OttomoModel::getInstance('Topics');
        $model->setState('published', '1'); // unpublished topics are retired
        $list = $model->getList();
        $this->hasTopics = count($list) > 0;
        $active = count($list) === 1 ? array((int)$list[0]->id) : array();
        $this->topics = OttomoHelperHTML::topics('checkboxes', $active, 'topics[]');

        // The sources (helpers/importers/): every one is shown, those not installed disabled
        JLoader::register('OttomoHelperImport', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/import.php');
        $this->sources = array();
        foreach (OttomoHelperImport::adapters() as $name => $adapter) {
            $available = $adapter->isAvailable();
            $this->sources[] = (object)array('name' => $name, 'label' => $adapter->label(), 'available' => $available, 'total' => $available ? $adapter->total() : 0);
        }

        // We need a custom layout
        $this->setLayout('import');

        // Display
        parent::display($tpl);
    }

}
