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
 * HikaShop's customers: its customer records (#__hikashop_user, one per email address, for registered users
 * and guests alike) with at least one sale order. Registered ones need a Joomla account that can sign in.
 * The address is their published one, billing before shipping, the default one first, else the latest:
 * name, company, street, city, ZIP, state and country (HikaShop zones, English names), phones, fax and VAT number.
 */
class OttomoImporterHikashop extends OttomoImporter
{
    public function name()
    {
        return 'hikashop';
    }

    public function label()
    {
        return 'HikaShop';
    }

    public function isAvailable()
    {
        return $this->componentEnabled('com_hikashop') && $this->tablesExist(array('#__hikashop_user', '#__hikashop_order', '#__hikashop_address', '#__hikashop_zone'));
    }

    public function total()
    {
        $this->db->setQuery($this->customersQuery()->select('COUNT(*)'));
        return (int)$this->db->loadResult();
    }

    public function batch($cursor, $limit)
    {
        $db = $this->db;
        $address = '(SELECT '.$db->quoteName('a.address_id').' FROM '.$db->quoteName('#__hikashop_address', 'a')
            .' WHERE '.$db->quoteName('a.address_user_id').' = '.$db->quoteName('h.user_id')
            .' AND '.$db->quoteName('a.address_published').' = 1'
            .' ORDER BY CASE WHEN '.$db->quoteName('a.address_type').' = '.$db->quote('shipping').' THEN 1 ELSE 0 END, '
            .$db->quoteName('a.address_default').' DESC, '.$db->quoteName('a.address_id').' DESC LIMIT 1)';
        $query = $this->customersQuery()
            ->select($db->quoteName('h.user_id', 'id'))
            ->select('CASE WHEN '.$db->quoteName('u.email').' IS NULL THEN '.$db->quoteName('h.user_email').' ELSE '.$db->quoteName('u.email').' END AS '.$db->quoteName('email'))
            ->select($db->quoteName('u.name', 'account'))
            ->select($db->quoteName(array('ad.address_firstname', 'ad.address_middle_name', 'ad.address_lastname', 'ad.address_company', 'ad.address_street', 'ad.address_street2', 'ad.address_post_code', 'ad.address_city', 'ad.address_telephone', 'ad.address_telephone2', 'ad.address_fax', 'ad.address_vat')))
            ->select($db->quoteName('st.zone_name_english', 'state'))
            ->select($db->quoteName('st.zone_name', 'stateLocal'))
            ->select($db->quoteName('co.zone_name_english', 'country'))
            ->select($db->quoteName('co.zone_name', 'countryLocal'))
            ->join('LEFT', $db->quoteName('#__hikashop_address', 'ad').' ON '.$db->quoteName('ad.address_id').' = '.$address)
            ->join('LEFT', $db->quoteName('#__hikashop_zone', 'st').' ON '.$db->quoteName('st.zone_namekey').' = '.$db->quoteName('ad.address_state'))
            ->join('LEFT', $db->quoteName('#__hikashop_zone', 'co').' ON '.$db->quoteName('co.zone_namekey').' = '.$db->quoteName('ad.address_country'))
            ->where($db->quoteName('h.user_id').' > '.(int)$cursor)
            ->order($db->quoteName('h.user_id').' ASC');
        $db->setQuery($query, 0, (int)$limit);
        $rows = $db->loadObjectList();

        $records = array();
        foreach ($rows as $row) {
            $name = implode(' ', array_filter(array_map('trim', array((string)$row->address_firstname, (string)$row->address_middle_name, (string)$row->address_lastname)), 'strlen'));
            if ($name === '') {
                $name = (string)$row->account;
            }
            $street = implode(', ', array_filter(array_map('trim', array((string)$row->address_street, (string)$row->address_street2)), 'strlen'));
            $records[] = $this->record($row->email, $name, 1, array(
                'company' => $row->address_company,
                'address' => $street,
                'city' => $row->address_city,
                'state' => trim((string)$row->state) !== '' ? $row->state : $row->stateLocal,
                'zip' => $row->address_post_code,
                'country' => trim((string)$row->country) !== '' ? $row->country : $row->countryLocal,
                'phone' => $row->address_telephone,
                'mobile' => $row->address_telephone2,
                'fax' => $row->address_fax,
                'vat' => $row->address_vat,
            ));
        }
        return array($records, count($rows) < $limit ? null : (int)end($rows)->id);
    }

    /** Customer records with a sale order: guests (no account), or registered with an account that can sign in. */
    protected function customersQuery()
    {
        $db = $this->db;
        return $db->getQuery(true)
            ->from($db->quoteName('#__hikashop_user', 'h'))
            ->join('LEFT', $db->quoteName('#__users', 'u').' ON '.$db->quoteName('u.id').' = '.$db->quoteName('h.user_cms_id').' AND '.$db->quoteName('h.user_cms_id').' > 0')
            ->where('EXISTS (SELECT 1 FROM '.$db->quoteName('#__hikashop_order', 'o')
                .' WHERE '.$db->quoteName('o.order_user_id').' = '.$db->quoteName('h.user_id')
                .' AND '.$db->quoteName('o.order_type').' = '.$db->quote('sale').')')
            ->where('(('.$db->quoteName('h.user_cms_id').' = 0 AND '.$db->quoteName('h.user_email').' <> '.$db->quote('').')'
                .' OR ('.$db->quoteName('u.id').' IS NOT NULL AND '.$this->activeUserCondition('u').'))');
    }
}
