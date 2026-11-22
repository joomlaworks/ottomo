<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\MailHelper;
use Joomla\CMS\Table\Table;
use Joomla\Utilities\ArrayHelper;

require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/compat.php';

class OttomoTableSubscribers extends Table
{
    public function __construct($db)
    {
        parent::__construct('#__ottomo_subscribers', 'id', $db);
    }

    public function check()
    {
        $this->email = trim($this->email);
        $this->email = strtolower($this->email);
        if (!MailHelper::isEmailAddress($this->email)) {
            $this->setError(Text::_('COM_OTTOMO_INVALID_EMAIL_ADDRESS'));
            return false;
        }
        $existing = $this->exists();
        if ($existing) {
            // An unsubscribed address cannot be re-added by an admin or an import: only by its owner, signing up again
            $this->setError(Text::_((int)$existing->status === 2 ? 'COM_OTTOMO_EMAIL_UNSUBSCRIBED' : 'COM_OTTOMO_EMAIL_EXISTS'));
            return false;
        }

        if (!$this->id && empty($this->created)) {
            $this->created = Factory::getDate()->toSql();
        }
        if (trim((string)$this->name) == '') {
            $this->name = $this->email;
        }
        if (trim((string)$this->birthday) == '') {
            $this->birthday = null;
        } else {
            $date = new Date($this->birthday);
            $this->birthday = $date->toSql();
        }
        return true;
    }

    public function delete($id = null)
    {
        $id = (array)$id;
        ArrayHelper::toInteger($id);

        $db = OttomoCompat::db();
        $query = $db->getQuery(true);
        $query->delete($db->quoteName('#__ottomo_subscribers'));
        $query->where($db->quoteName('id').' IN('.implode(',', $id).')');
        $db->setQuery($query);
        $db->execute();

        $query = $db->getQuery(true);
        $query->delete($db->quoteName('#__ottomo_subscribers_xref'));
        $query->where($db->quoteName('subscriberID').' IN('.implode(',', $id).')');
        $db->setQuery($query);
        $db->execute();

        return true;
    }

    protected function exists()
    {
        $db = OttomoCompat::db();
        $query = $db->getQuery(true);
        $query->select(array($db->quoteName('id'), $db->quoteName('status')));
        $query->from($db->quoteName('#__ottomo_subscribers'));
        $query->where($db->quoteName('email').' = '.$db->quote($this->email));
        if ($this->id) {
            $query->where($db->quoteName('id').' != '.(int)$this->id);
        }
        $db->setQuery($query, 0, 1);
        return $db->loadObject(); // the other record with this email, or null
    }

    public function enable($id = null)
    {
        $id = (array)$id;
        ArrayHelper::toInteger($id);
        $db = OttomoCompat::db();
        $query = $db->getQuery(true);
        $query->update($db->quoteName('#__ottomo_subscribers'));
        $query->set($db->quoteName('status').' = 1');
        $query->where($db->quoteName('id').' IN('.implode(',', $id).')');
        $query->where($db->quoteName('status').' <> 2'); // unsubscribed addresses are never re-enabled (suppression)
        $db->setQuery($query);
        $db->execute();
        return true;
    }

    public function disable($id = null)
    {
        $id = (array)$id;
        ArrayHelper::toInteger($id);
        $db = OttomoCompat::db();
        $query = $db->getQuery(true);
        $query->update($db->quoteName('#__ottomo_subscribers'));
        $query->set($db->quoteName('status').' = 0');
        $query->where($db->quoteName('id').' IN('.implode(',', $id).')');
        $query->where($db->quoteName('status').' <> 2'); // unsubscribed addresses are never re-enabled (suppression)
        $db->setQuery($query);
        $db->execute();
        return true;
    }

    public function batch($data)
    {
        $ids = (array)$data['cid'];
        $topics = (array)$data['topics'];

        ArrayHelper::toInteger($ids);
        ArrayHelper::toInteger($topics);
        if (!count($ids) || !count($topics)) {
            return false;
        }

        $db = OttomoCompat::db();
        $query = $db->getQuery(true);
        $query->delete($db->quoteName('#__ottomo_subscribers_xref'));
        $query->where($db->quoteName('subscriberID').' IN('.implode(',', $ids).')');
        $db->setQuery($query);
        $db->execute();

        $values = array();
        foreach ($ids as $id) {
            foreach ($topics as $topic) {
                $values[] = array($topic, $id);
            }
        }

        if (count($values)) {
            $query = $db->getQuery(true);
            $query->insert($db->quoteName('#__ottomo_subscribers_xref'));
            foreach ($values as $value) {
                $query->values(implode(',', $value));
            }
            $db->setQuery($query);
            $db->execute();
        }
        return true;
    }

}
