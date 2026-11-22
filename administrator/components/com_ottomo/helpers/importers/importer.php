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
 * Base class of the import adapters (one file per source in helpers/importers/, listed by OttomoHelperImport).
 *
 * An adapter reads its source in batches ordered by a numeric key and resumes after the last key it returned
 * (keyset paging: no OFFSET, so the 300th batch of a large site costs the same as the first).
 */
abstract class OttomoImporter
{
    /** @var JDatabaseDriver */
    protected $db;

    public function __construct()
    {
        $this->db = JFactory::getDbo();
    }

    /** Machine name, also the value of the source's checkbox (e.g. "kunena"). */
    abstract public function name();

    /** Name shown on the source's button: the product's own name, or a language string. */
    abstract public function label();

    /** Whether the source is installed and its tables can be read. */
    abstract public function isAvailable();

    /** Number of addresses the import will go through (for the progress bar and the button's counter). */
    abstract public function total();

    /**
     * The next batch after $cursor (null for the first one).
     *
     * @return  array  array($records, $nextCursor): records are objects with email, name, status (1 active,
     *                 2 unsubscribed) and optional profile fields (company, address, city, state, zip, country,
     *                 phone, mobile, fax, vat); $nextCursor is null when the source has no more records.
     */
    abstract public function batch($cursor, $limit);

    /** Whether a component is installed and enabled; with $author, also that its manifest names that author. */
    protected function componentEnabled($element, $author = null)
    {
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName(array('enabled', 'manifest_cache')))
            ->from($this->db->quoteName('#__extensions'))
            ->where($this->db->quoteName('type').' = '.$this->db->quote('component'))
            ->where($this->db->quoteName('element').' = '.$this->db->quote($element));
        $this->db->setQuery($query, 0, 1);
        $row = $this->db->loadObject();
        if (!$row || !(int)$row->enabled) {
            return false;
        }
        if ($author === null) {
            return true;
        }
        $manifest = json_decode((string)$row->manifest_cache);
        return isset($manifest->author) && stripos((string)$manifest->author, $author) !== false;
    }

    /** Whether all the tables exist (#__ prefix). */
    protected function tablesExist(array $tables)
    {
        $prefix = $this->db->getPrefix();
        $existing = array_map('strtolower', $this->db->getTableList());
        foreach ($tables as $table) {
            if (!in_array(strtolower(str_replace('#__', $prefix, $table)), $existing, true)) {
                return false;
            }
        }
        return true;
    }

    /** A record for the import (trimmed strings; empty profile fields left out). */
    protected function record($email, $name, $status = 1, array $fields = array())
    {
        $record = new stdClass();
        $record->email = trim((string)$email);
        $record->name = trim(preg_replace('/\s+/u', ' ', (string)$name));
        $record->status = (int)$status;
        foreach ($fields as $field => $value) {
            $value = trim((string)$value);
            if ($value !== '') {
                $record->$field = $value;
            }
        }
        return $record;
    }

    /** Condition for Joomla accounts that can sign in: not blocked, activated. */
    protected function activeUserCondition($alias)
    {
        $db = $this->db;
        return $db->quoteName($alias.'.block').' = 0 AND ('.$db->quoteName($alias.'.activation').' = '.$db->quote('').' OR '.$db->quoteName($alias.'.activation').' = '.$db->quote('0').')';
    }
}
