<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

use Joomla\CMS\Factory;

/**
 * Kunena's forum members (Kunena 5.x for Joomla 3): the Joomla accounts with a forum profile
 * (#__kunena_users), which can sign in and aren't banned from the forum.
 * Meela, Kunena's continuation, keeps the same element and tables: see meela.php.
 */
class OttomoImporterKunena extends OttomoImporter
{
    /** Author in Meela's manifest: tells Meela from Kunena (both install as com_kunena). */
    const MEELA_AUTHOR = 'JoomlaWorks';

    public function name()
    {
        return 'kunena';
    }

    public function label()
    {
        return 'Kunena';
    }

    public function isAvailable()
    {
        return $this->componentEnabled('com_kunena') && !$this->componentEnabled('com_kunena', self::MEELA_AUTHOR) && $this->tablesExist(array('#__kunena_users'));
    }

    public function total()
    {
        $query = $this->membersQuery()->select('COUNT(*)');
        $this->db->setQuery($query);
        return (int)$this->db->loadResult();
    }

    public function batch($cursor, $limit)
    {
        $db = $this->db;
        $query = $this->membersQuery()
            ->select($db->quoteName(array('u.id', 'u.name', 'u.email')))
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

    /** Forum members who can sign in and aren't banned (a ban is permanent with the null date, else until its date). */
    protected function membersQuery()
    {
        $db = $this->db;
        $now = $db->quote(Factory::getDate()->toSql());
        return $db->getQuery(true)
            ->from($db->quoteName('#__kunena_users', 'k'))
            ->join('INNER', $db->quoteName('#__users', 'u').' ON '.$db->quoteName('u.id').' = '.$db->quoteName('k.userid'))
            ->where($this->activeUserCondition('u'))
            ->where('('.$db->quoteName('k.banned').' IS NULL OR ('.$db->quoteName('k.banned').' <> '.$db->quote($db->getNullDate()).' AND '.$db->quoteName('k.banned').' <= '.$now.'))');
    }
}
