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
 * VirtueMart's customers, with their bill-to address (address type BT):
 * 1. registered shoppers (#__virtuemart_userinfos, with a Joomla account that can sign in);
 * 2. guests who placed orders (#__virtuemart_order_userinfos without an account), once per email address,
 *    from their first order, leaving out addresses that belong to an account.
 * The cursor is "u:<userinfo id>" in the first part and "o:<order userinfo id>" in the second.
 */
class OttomoImporterVirtuemart extends OttomoImporter
{
    public function name()
    {
        return 'virtuemart';
    }

    public function label()
    {
        return 'VirtueMart';
    }

    public function isAvailable()
    {
        return $this->componentEnabled('com_virtuemart') && $this->tablesExist(array('#__virtuemart_userinfos', '#__virtuemart_order_userinfos', '#__virtuemart_countries', '#__virtuemart_states'));
    }

    public function total()
    {
        $db = $this->db;
        $db->setQuery($this->shoppersQuery()->select('COUNT(*)'));
        $shoppers = (int)$db->loadResult();
        $db->setQuery('SELECT COUNT(*) FROM ('.$this->guestsQuery()->select('MIN('.$db->quoteName('o.virtuemart_order_userinfo_id').') AS '.$db->quoteName('id')).') AS '.$db->quoteName('g'));
        return $shoppers + (int)$db->loadResult();
    }

    public function batch($cursor, $limit)
    {
        $cursor = (string)$cursor;
        if ($cursor === '' || strpos($cursor, 'u:') === 0) {
            list($records, $next) = $this->shoppers((int)substr($cursor, 2), $limit);
            // Registered shoppers done: the guests follow in the next batch
            return array($records, $next === null ? 'o:0' : 'u:'.$next);
        }
        list($records, $next) = $this->guests((int)substr($cursor, 2), $limit);
        return array($records, $next === null ? null : 'o:'.$next);
    }

    protected function shoppers($after, $limit)
    {
        $db = $this->db;
        $query = $this->shoppersQuery()
            ->select($db->quoteName('i.virtuemart_userinfo_id', 'id'))
            ->select($db->quoteName('u.email'))
            ->where($db->quoteName('i.virtuemart_userinfo_id').' > '.(int)$after)
            ->order($db->quoteName('i.virtuemart_userinfo_id').' ASC');
        $this->addressColumns($query, 'i');
        $db->setQuery($query, 0, (int)$limit);
        $rows = $db->loadObjectList();
        return array($this->records($rows), count($rows) < $limit ? null : (int)end($rows)->id);
    }

    protected function guests($after, $limit)
    {
        $db = $this->db;
        $first = 'MIN('.$db->quoteName('o.virtuemart_order_userinfo_id').')';
        $query = $this->guestsQuery()
            ->select($first.' AS '.$db->quoteName('id'))
            ->having($first.' > '.(int)$after)
            ->order($db->quoteName('id').' ASC');
        $db->setQuery($query, 0, (int)$limit);
        $ids = array_map('intval', $db->loadColumn());
        if (!$ids) {
            return array(array(), null);
        }
        $query = $db->getQuery(true)
            ->select($db->quoteName('o.virtuemart_order_userinfo_id', 'id'))
            ->select($db->quoteName('o.email'))
            ->from($db->quoteName('#__virtuemart_order_userinfos', 'o'))
            ->where($db->quoteName('o.virtuemart_order_userinfo_id').' IN ('.implode(',', $ids).')')
            ->order($db->quoteName('o.virtuemart_order_userinfo_id').' ASC');
        $this->addressColumns($query, 'o');
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        return array($this->records($rows), count($ids) < $limit ? null : (int)end($ids));
    }

    /** Registered shoppers: their bill-to address, with an account that can sign in. */
    protected function shoppersQuery()
    {
        $db = $this->db;
        return $db->getQuery(true)
            ->from($db->quoteName('#__virtuemart_userinfos', 'i'))
            ->join('INNER', $db->quoteName('#__users', 'u').' ON '.$db->quoteName('u.id').' = '.$db->quoteName('i.virtuemart_user_id'))
            ->where($db->quoteName('i.address_type').' = '.$db->quote('BT'))
            ->where($this->activeUserCondition('u'));
    }

    /** Guest orders, grouped by email address, without the addresses of accounts (imported as shoppers or not). */
    protected function guestsQuery()
    {
        $db = $this->db;
        return $db->getQuery(true)
            ->from($db->quoteName('#__virtuemart_order_userinfos', 'o'))
            ->where($db->quoteName('o.address_type').' = '.$db->quote('BT'))
            ->where($db->quoteName('o.virtuemart_user_id').' = 0')
            ->where($db->quoteName('o.email').' <> '.$db->quote(''))
            ->where('NOT EXISTS (SELECT 1 FROM '.$db->quoteName('#__users', 'a').' WHERE '.$db->quoteName('a.email').' = '.$db->quoteName('o.email').')')
            ->group($db->quoteName('o.email'));
    }

    /** Name, company, address and phones of a userinfo row (alias $a), with the country's and state's names. */
    protected function addressColumns($query, $a)
    {
        $db = $this->db;
        $query->select($db->quoteName(array($a.'.first_name', $a.'.middle_name', $a.'.last_name', $a.'.company', $a.'.address_1', $a.'.address_2', $a.'.city', $a.'.zip', $a.'.phone_1', $a.'.phone_2', $a.'.fax')))
            ->select($db->quoteName('c.country_name', 'country'))
            ->select($db->quoteName('s.state_name', 'state'))
            ->join('LEFT', $db->quoteName('#__virtuemart_countries', 'c').' ON '.$db->quoteName('c.virtuemart_country_id').' = '.$db->quoteName($a.'.virtuemart_country_id'))
            ->join('LEFT', $db->quoteName('#__virtuemart_states', 's').' ON '.$db->quoteName('s.virtuemart_state_id').' = '.$db->quoteName($a.'.virtuemart_state_id'));
    }

    protected function records(array $rows)
    {
        $records = array();
        foreach ($rows as $row) {
            $name = implode(' ', array_filter(array_map('trim', array((string)$row->first_name, (string)$row->middle_name, (string)$row->last_name)), 'strlen'));
            $address = implode(', ', array_filter(array_map('trim', array((string)$row->address_1, (string)$row->address_2)), 'strlen'));
            $records[] = $this->record($row->email, $name, 1, array(
                'company' => $row->company,
                'address' => $address,
                'city' => $row->city,
                'state' => $row->state,
                'zip' => $row->zip,
                'country' => $row->country,
                'phone' => $row->phone_1,
                'mobile' => $row->phone_2,
                'fax' => $row->fax,
            ));
        }
        return $records;
    }
}
