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
        // Get topics
        $this->topics = OttomoHelperHTML::topics('checkboxes', array(), 'topics[]', 'multiple="multiple" size="10"');

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
