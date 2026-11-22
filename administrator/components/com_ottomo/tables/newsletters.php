<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

class OttomoTableNewsletters extends JTable
{
    public function __construct($db)
    {
        parent::__construct('#__ottomo_email_campaigns', 'id', $db);
    }

    public function check()
    {
        jimport('joomla.mail.helper');
        if (trim($this->subject) == '') {
            $this->setError(JText::_('COM_OTTOMO_NEWSLETTER_MUST_HAVE_A_SUBJECT'));
            return false;
        }
        // Sender name and address are optional (empty: Settings > Sending, see OttomoHelperSettings::sender())
        $this->senderName = trim((string)$this->senderName);
        $this->senderEmail = trim((string)$this->senderEmail);
        if ($this->senderEmail !== '' && !JMailHelper::isEmailAddress($this->senderEmail)) {
            $this->setError(JText::_('COM_OTTOMO_INVALID_EMAIL_ADDRESS'));
            return false;
        }
        // TEXT columns cannot have a default on MySQL: never leave them NULL (strict mode)
        foreach (array('plain', 'snapshot', 'preheader') as $field) {
            $this->$field = (string)$this->$field;
        }
        if (empty($this->sent) || strpos((string)$this->sent, '0000-00-00') === 0) {
            $this->sent = null; // a draft (NULL is not inserted: the column defaults to NULL)
        }
        $this->template = (int)$this->template;
        if (!$this->template) {
            $this->setError(JText::_('COM_OTTOMO_NEWSLETTER_MUST_HAVE_A_TEMPLATE'));
            return false;
        }
        return true;
    }

    public function delete($id = null)
    {
        $db = JFactory::getDbo();
        $id = (array)$id;
        JArrayHelper::toInteger($id);

        $query = $db->getQuery(true);
        $query->delete($db->quoteName('#__ottomo_email_campaigns'));
        $query->where($db->quoteName('id').' IN('.implode(',', $id).')');
        $db->setQuery($query);
        $db->execute();

        $query = $db->getQuery(true);
        $query->delete($db->quoteName('#__ottomo_email_campaigns_xref'));
        $query->where($db->quoteName('campaignID').' IN('.implode(',', $id).')');
        $db->setQuery($query);
        $db->execute();

        // The campaign's content (positions)
        $query = $db->getQuery(true);
        $query->delete($db->quoteName('#__ottomo_email_campaigns_data'));
        $query->where($db->quoteName('campaignID').' IN('.implode(',', $id).')');
        $db->setQuery($query);
        $db->execute();

        // Its statistics stay (they are part of the totals over time) but are detached from its id, so a later
        // campaign given the same id (auto-increment values can be reused) doesn't inherit sends, opens and clicks
        $query = $db->getQuery(true);
        $query->update($db->quoteName('#__ottomo_statistics'));
        $query->set($db->quoteName('campaignID').' = 0');
        $query->where($db->quoteName('campaignID').' IN('.implode(',', $id).')');
        $db->setQuery($query);
        $db->execute();

        return true;
    }

    public function batch($data)
    {
        $db = JFactory::getDbo();
        $ids = (array)$data['cid'];
        $topics = (array)$data['topics'];
        JArrayHelper::toInteger($ids);
        JArrayHelper::toInteger($topics);
        if (!count($ids) || !count($topics)) {
            return false;
        }

        $query = $db->getQuery(true);
        $query->delete($db->quoteName('#__ottomo_email_campaigns_xref'));
        $query->where($db->quoteName('campaignID').' IN('.implode(',', $ids).')');
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
            $query->insert($db->quoteName('#__ottomo_email_campaigns_xref'));
            foreach ($values as $value) {
                $query->values(implode(',', $value));
            }
            $db->setQuery($query);
            $db->execute();
        }
        return true;
    }

}
