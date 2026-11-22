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
 * AcyMailing's subscribers: AcyMailing 6 and later (com_acym) or AcyMailing 5 (com_acymailing).
 *
 * - Active: an enabled user, subscribed to at least one list, and confirmed when AcyMailing requires confirmation
 *   (its "require_confirmation" setting, on by default): those AcyMailing itself would send to.
 * - Unsubscribed (status 2): a user who left every list they were on, so Ottomo keeps them suppressed.
 * - Everyone else (unconfirmed, disabled, on no list) is left out.
 */
class OttomoImporterAcymailing extends OttomoImporter
{
    /** Table and column names of each generation */
    protected $schemas = array(
        'acym' => array(
            'component' => 'com_acym',
            'users' => '#__acym_user', 'id' => 'id', 'active' => 'active', 'confirmed' => 'confirmed', 'accept' => null,
            'subs' => '#__acym_user_has_list', 'subUser' => 'user_id', 'subscribed' => 1, 'unsubscribed' => 0,
            'config' => '#__acym_configuration', 'configName' => 'name', 'configValue' => 'value',
        ),
        'acymailing' => array(
            'component' => 'com_acymailing',
            'users' => '#__acymailing_subscriber', 'id' => 'subid', 'active' => 'enabled', 'confirmed' => 'confirmed', 'accept' => 'accept',
            'subs' => '#__acymailing_listsub', 'subUser' => 'subid', 'subscribed' => 1, 'unsubscribed' => -1,
            'config' => '#__acymailing_config', 'configName' => 'namekey', 'configValue' => 'value',
        ),
    );

    /** @var array|null The schema of the installed generation */
    protected $schema;

    public function name()
    {
        return 'acymailing';
    }

    public function label()
    {
        return 'AcyMailing';
    }

    public function isAvailable()
    {
        return $this->schema() !== null;
    }

    public function total()
    {
        if (!$this->schema()) {
            return 0;
        }
        $query = $this->query()->select('COUNT(*)');
        $this->db->setQuery($query);
        return (int)$this->db->loadResult();
    }

    public function batch($cursor, $limit)
    {
        $s = $this->schema();
        if (!$s) {
            return array(array(), null);
        }
        $db = $this->db;
        $id = $db->quoteName('u.'.$s['id']);
        $query = $this->query()
            ->select($id.' AS '.$db->quoteName('id'))
            ->select($db->quoteName(array('u.email', 'u.name')))
            ->select($this->exists($s['subscribed']).' AS '.$db->quoteName('subscribed'))
            ->where($id.' > '.(int)$cursor)
            ->order($id.' ASC');
        $db->setQuery($query, 0, (int)$limit);
        $rows = $db->loadObjectList();

        $records = array();
        foreach ($rows as $row) {
            $records[] = $this->record($row->email, $row->name, (int)$row->subscribed ? 1 : 2);
        }
        return array($records, count($rows) < $limit ? null : (int)end($rows)->id);
    }

    /** The users to import: active subscribers, and those unsubscribed from every list. */
    protected function query()
    {
        $s = $this->schema();
        $db = $this->db;
        $active = array($db->quoteName('u.'.$s['active']).' = 1', $this->exists($s['subscribed']));
        if ($s['accept']) {
            $active[] = $db->quoteName('u.'.$s['accept']).' = 1';
        }
        if ($this->requiresConfirmation()) {
            $active[] = $db->quoteName('u.'.$s['confirmed']).' = 1';
        }
        $unsubscribed = $this->exists($s['unsubscribed']).' AND NOT '.$this->exists($s['subscribed']);
        return $db->getQuery(true)
            ->from($db->quoteName($s['users'], 'u'))
            ->where('(('.implode(' AND ', $active).') OR ('.$unsubscribed.'))');
    }

    /** EXISTS (a list membership of the user with this status) */
    protected function exists($status)
    {
        $s = $this->schema();
        $db = $this->db;
        return 'EXISTS (SELECT 1 FROM '.$db->quoteName($s['subs']).' AS '.$db->quoteName('l')
            .' WHERE '.$db->quoteName('l.'.$s['subUser']).' = '.$db->quoteName('u.'.$s['id'])
            .' AND '.$db->quoteName('l.status').' = '.(int)$status.')';
    }

    /** AcyMailing's double opt-in setting (on unless switched off) */
    protected function requiresConfirmation()
    {
        $s = $this->schema();
        $db = $this->db;
        $query = $db->getQuery(true)
            ->select($db->quoteName($s['configValue']))
            ->from($db->quoteName($s['config']))
            ->where($db->quoteName($s['configName']).' = '.$db->quote('require_confirmation'));
        $db->setQuery($query, 0, 1);
        $value = $db->loadResult();
        return $value === null || (string)$value === '1';
    }

    protected function schema()
    {
        if ($this->schema === null) {
            $this->schema = false;
            foreach ($this->schemas as $schema) {
                if ($this->componentEnabled($schema['component']) && $this->tablesExist(array($schema['users'], $schema['subs'], $schema['config']))) {
                    $this->schema = $schema;
                    break;
                }
            }
        }
        return $this->schema ?: null;
    }
}
