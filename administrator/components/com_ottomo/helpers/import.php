<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

JLoader::register('OttomoImporter', __DIR__.'/importers/importer.php');

/** Import sources (helpers/importers/*.php) and the save step shared by every way of importing. */
class OttomoHelperImport
{
    /** The sources, alphabetically (the order of the import page's buttons): file name => class */
    protected static $adapters = array(
        'acymailing' => 'OttomoImporterAcymailing',
        'hikashop' => 'OttomoImporterHikashop',
        'joomla' => 'OttomoImporterJoomla',
        'kunena' => 'OttomoImporterKunena',
        'meela' => 'OttomoImporterMeela',
        'virtuemart' => 'OttomoImporterVirtuemart',
    );

    /** @return OttomoImporter[] Every adapter, installed or not, by name */
    public static function adapters()
    {
        $adapters = array();
        foreach (self::$adapters as $name => $class) {
            JLoader::register($class, __DIR__.'/importers/'.$name.'.php');
            $adapters[$name] = new $class();
        }
        return $adapters;
    }

    /** @return OttomoImporter|null An installed adapter by name */
    public static function adapter($name)
    {
        $adapters = self::adapters();
        return isset($adapters[$name]) && $adapters[$name]->isAvailable() ? $adapters[$name] : null;
    }

    /**
     * Save a batch of records (objects with email, name, status 1|2 and optional profile fields).
     * New addresses are added (active ones to $topics); addresses already in Ottomo are left as they are:
     * they are counted, not reported as errors.
     *
     * @param   array  $counts  Totals updated in place: added, existing, unsubscribed (new suppression entries,
     *                          and addresses Ottomo already has as unsubscribed)
     * @return  array  Error messages (invalid addresses and failed saves)
     */
    public static function save(array $records, array $topics, array &$counts)
    {
        foreach (array('added', 'existing', 'unsubscribed') as $key) {
            if (!isset($counts[$key])) {
                $counts[$key] = 0;
            }
        }
        $errors = array();
        $known = self::known($records);
        $model = OttomoModel::getInstance('Subscribers');
        $fields = array('company', 'address', 'city', 'state', 'zip', 'country', 'phone', 'mobile', 'fax', 'vat');

        foreach ($records as $record) {
            $email = strtolower(trim((string)$record->email));
            if ($email === '') {
                continue;
            }
            if (isset($known[$email])) {
                // Unsubscribed addresses stay unsubscribed: counted with the new suppression entries
                $counts[$known[$email] === 2 ? 'unsubscribed' : 'existing']++;
                continue;
            }
            $status = isset($record->status) && (int)$record->status === 2 ? 2 : 1;
            $data = array('email' => $email, 'status' => $status, 'topics' => $status === 1 ? $topics : array());
            if (isset($record->name) && $record->name !== '') {
                $data['name'] = $record->name;
            }
            foreach ($fields as $field) {
                if (isset($record->$field) && $record->$field !== '') {
                    $data[$field] = JString::substr($record->$field, 0, 255);
                }
            }
            $model->setState('data', $data);
            if ($model->save()) {
                $counts[$status === 1 ? 'added' : 'unsubscribed']++;
            } else {
                $errors[] = JText::sprintf('COM_OTTOMO_COULD_NOT_IMPORT_USER', $email, $model->getError());
            }
            $known[$email] = $status; // the same address twice in one batch
        }
        return $errors;
    }

    /** The addresses of $records that Ottomo already has (lowercased => status), in one query. */
    protected static function known(array $records)
    {
        $db = JFactory::getDbo();
        $emails = array();
        foreach ($records as $record) {
            $email = strtolower(trim((string)$record->email));
            if ($email !== '') {
                $emails[$email] = $db->quote($email);
            }
        }
        if (!$emails) {
            return array();
        }
        $query = $db->getQuery(true)
            ->select($db->quoteName(array('email', 'status')))
            ->from($db->quoteName('#__ottomo_subscribers'))
            ->where($db->quoteName('email').' IN ('.implode(',', $emails).')');
        $db->setQuery($query);
        $known = array();
        foreach ($db->loadObjectList() as $row) {
            $known[strtolower($row->email)] = (int)$row->status;
        }
        return $known;
    }

    /** The summary at the end of an import. */
    public static function summary(array $counts)
    {
        $parts = array(JText::sprintf('COM_OTTOMO_IMPORT_ADDED', isset($counts['added']) ? (int)$counts['added'] : 0));
        if (!empty($counts['existing'])) {
            $parts[] = JText::sprintf('COM_OTTOMO_IMPORT_EXISTING', (int)$counts['existing']);
        }
        if (!empty($counts['unsubscribed'])) {
            $parts[] = JText::sprintf('COM_OTTOMO_IMPORT_UNSUBSCRIBED', (int)$counts['unsubscribed']);
        }
        return implode(' · ', $parts);
    }
}
