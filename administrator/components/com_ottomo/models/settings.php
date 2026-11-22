<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;

class OttomoModelSettings extends OttomoModel
{
    protected $extensionID = null;

    public function getForm()
    {
        $component = ComponentHelper::getComponent('com_ottomo');
        $this->extensionID = $component->id;
        Form::addFormPath(JPATH_ADMINISTRATOR.'/components/com_ottomo');
        $form = Form::getInstance('com_ottomo.settings', 'config', array('control' => 'jform'), false, '/config');
        $form->bind($component->params);
        return $form;
    }

    /**
     * Saves com_ottomo's parameters and permissions: the asset and the extension row are always com_ottomo's own
     * (never taken from the request), and only the params column is written.
     */
    public function save()
    {
        $data = (array)$this->getState('data');
        $params = isset($data['params']) && is_array($data['params']) ? $data['params'] : array();
        $option = 'com_ottomo';

        $table = Table::getInstance('extension');

        // Save the rules.
        if (isset($params['rules'])) {
            $rules = new \Joomla\CMS\Access\Rules((array)$params['rules']);
            $asset = Table::getInstance('asset');

            if (!$asset->loadByName($option)) {
                $root = Table::getInstance('asset');
                $root->loadByName('root.1');
                $asset->name = $option;
                $asset->title = $option;
                $asset->setLocation($root->id, 'last-child');
            }
            $asset->rules = (string)$rules;

            if (!$asset->check() || !$asset->store()) {
                $this->setError($asset->getError());
                return false;
            }

            // We don't need this anymore
            unset($params['rules']);
        }

        // Load the previous Data
        $component = ComponentHelper::getComponent($option);
        if (!$component->id || !$table->load($component->id) || $table->element !== $option) {
            $this->setError($table->getError() ?: Text::_('COM_OTTOMO_AN_ERROR_OCCURED'));
            return false;
        }

        // Bind the data.
        if (!$table->bind(array('params' => $params))) {
            $this->setError($table->getError());
            return false;
        }

        // Check the data.
        if (!$table->check()) {
            $this->setError($table->getError());
            return false;
        }

        // Store the data.
        if (!$table->store()) {
            $this->setError($table->getError());
            return false;
        }

        // Clean the component cache.
        $this->cleanCache('_system');

        return true;

    }

    public function getExtensionID()
    {
        return $this->extensionID;
    }

}
