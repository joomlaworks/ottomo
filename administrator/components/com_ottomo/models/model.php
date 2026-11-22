<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;

BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_ottomo/models');
Table::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_ottomo/tables');

class OttomoModel extends BaseDatabaseModel
{
    public static function getInstance($type, $prefix = 'OttomoModel', $config = array())
    {
        return parent::getInstance($type, $prefix, $config);
    }

    public function getRow()
    {
        $table = $this->getTable();
        $class = get_class($table);
        static $instances = array();
        if (isset($instances[$class][$this->getState('id')])) {
            return $instances[$class][$this->getState('id')];
        }
        $table->load($this->getState('id'));
        $instances[$class][$this->getState('id')] = $table;
        return $table;
    }

    public function save()
    {
        $table = $this->getTable();
        $data = $this->getState('data');
        $this->setState('id', $data['id']);
        if (!$table->save($data)) {
            $this->setError($table->getError());
            return false;
        }
        $this->setState('id', $table->id);
        return true;
    }

    public function publish()
    {
        $table = $this->getTable();
        $table->publish($this->getState('id'), 1);
    }

    public function unpublish()
    {
        $table = $this->getTable();
        $table->publish($this->getState('id'), 0);
    }

    public function remove()
    {
        $table = $this->getTable();
        if (!$table->delete($this->getState('id'))) {
            $this->setError($table->getError());
            return false;
        }
        return true;
    }

    public function batch()
    {
        $table = $this->getTable();
        $table->batch($this->getState('data'));
    }

    public function getTable($name = '', $prefix = 'OttomoTable', $options = array())
    {
        return parent::getTable($name, $prefix, $options);
    }

}
