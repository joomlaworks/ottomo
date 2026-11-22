<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

use Joomla\CMS\Language\Text;

/** Joomla's user accounts: those that can sign in (not blocked, activated). */
class OttomoImporterJoomla extends OttomoImporter
{
    public function name()
    {
        return 'joomla';
    }

    public function label()
    {
        return Text::_('COM_OTTOMO_IMPORT_SOURCE_JOOMLA');
    }

    public function isAvailable()
    {
        return true;
    }

    public function total()
    {
        $query = $this->db->getQuery(true)
            ->select('COUNT(*)')
            ->from($this->db->quoteName('#__users', 'u'))
            ->where($this->activeUserCondition('u'));
        $this->db->setQuery($query);
        return (int)$this->db->loadResult();
    }

    public function batch($cursor, $limit)
    {
        $db = $this->db;
        $query = $db->getQuery(true)
            ->select($db->quoteName(array('u.id', 'u.name', 'u.email')))
            ->from($db->quoteName('#__users', 'u'))
            ->where($this->activeUserCondition('u'))
            ->where($db->quoteName('u.id').' > '.(int)$cursor)
            ->order($db->quoteName('u.id').' ASC');
        $db->setQuery($query, 0, (int)$limit);
        $rows = $db->loadObjectList();

        $records = array();
        foreach ($rows as $row) {
            $records[] = $this->record($row->email, $row->name);
        }
        return array($records, count($rows) < $limit ? null : (int)end($rows)->id);
    }
}
