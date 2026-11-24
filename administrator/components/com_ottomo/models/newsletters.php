<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Utilities\ArrayHelper;

require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/compat.php';

class OttomoModelNewsletters extends OttomoModel
{
    public function getList()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select($db->quoteName('newsletter').'.*');
        $query->select('MAX('.$db->quoteName('template.name').') AS '.$db->quoteName('templateName'));
        $serverType = method_exists($db, 'getServerType') ? $db->getServerType() : $db->name;
        if ($serverType === 'postgresql') {
            $query->select('STRING_AGG(CAST('.$db->quoteName('xref.topicID').' AS TEXT), '.$db->quote(',').') AS '.$db->quoteName('topics'));
        } else {
            $query->select('GROUP_CONCAT('.$db->quoteName('xref.topicID').' SEPARATOR ",") AS '.$db->quoteName('topics'));
        }
        $query->from($db->quoteName('#__ottomo_email_campaigns').' AS '.$db->quoteName('newsletter'));
        $query->leftJoin($db->quoteName('#__ottomo_email_campaigns_xref').' AS '.$db->quoteName('xref').' ON '.$db->quoteName('xref.campaignID').' = '.$db->quoteName('newsletter.id'));
        $query->leftJoin($db->quoteName('#__ottomo_templates').' AS '.$db->quoteName('template').' ON '.$db->quoteName('template.id').' = '.$db->quoteName('newsletter.template'));
        $this->setQueryConditions($query);
        $query->group($db->quoteName('newsletter.id'));
        $db->setQuery($query, (int)$this->getState('limitstart'), (int)$this->getState('limit'));
        $rows = $db->loadObjectList();
        return $rows;
    }

    public function getListCount()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('COUNT(DISTINCT '.$db->quoteName('newsletter.id').')');
        $query->from($db->quoteName('#__ottomo_email_campaigns').' AS '.$db->quoteName('newsletter'));
        $query->leftJoin($db->quoteName('#__ottomo_email_campaigns_xref').' AS '.$db->quoteName('xref').' ON '.$db->quoteName('xref.campaignID').' = '.$db->quoteName('newsletter.id'));
        $this->setQueryConditions($query);
        $db->setQuery($query);
        $result = $db->loadResult();
        return $result;
    }

    protected function setQueryConditions(&$query)
    {
        $db = $this->getDbo();
        $search = trim((string)$this->getState('search'));
        if ($search) {
            $search = $db->quote('%'.$db->escape($search, true).'%');
            $query->where($db->quoteName('newsletter.subject').' LIKE '.$search);
        }
        if ($this->getState('published') != -1) {
            $query->where($db->quoteName('newsletter.published').' = '.(int)$this->getState('published'));
        }
        if ($this->getState('topic')) {
            $query->where($db->quoteName('xref.topicID').' = '.(int)$this->getState('topic'));
        }
        // The public archive: campaigns sent to at least one public topic (not only to private lists)
        if ($this->getState('public')) {
            $query->where('EXISTS (SELECT 1 FROM '.$db->quoteName('#__ottomo_email_campaigns_xref', 'px')
                .' INNER JOIN '.$db->quoteName('#__ottomo_topics', 'pt').' ON '.$db->quoteName('pt.id').' = '.$db->quoteName('px.topicID')
                .' WHERE '.$db->quoteName('px.campaignID').' = '.$db->quoteName('newsletter.id')
                .' AND '.$db->quoteName('pt.published').' = 1 AND '.$db->quoteName('pt.private').' = 0)');
        }
        if ((int)$this->getState('filterTemplate') > 0) {
            $query->where($db->quoteName('newsletter.template').' = '.(int)$this->getState('filterTemplate'));
        }
        if ($this->getState('ordering') && $this->getState('orderingDir')) {
            $ordering = (string)$this->getState('ordering');
            if (strpos($ordering, '.') === false && in_array($ordering, array('id', 'subject', 'published', 'sent', 'template'), true)) {
                $ordering = 'newsletter.'.$ordering; // the templates table has an id too
            }
            $query->order($db->quoteName($ordering).' '.(strtoupper($this->getState('orderingDir')) === 'ASC' ? 'ASC' : 'DESC'));
        }
    }

    /**
     * Per-campaign figures from #__ottomo_statistics: recipients (sends), unique opens and unique clicks,
     * with the rates against the recipients.
     *
     * @param  int[] $ids campaign ids
     * @return array id => object {recipients, opens, clicks, openRate, clickRate}
     */
    public function getCampaignStats($ids)
    {
        $ids = array_values(array_filter(array_map('intval', (array)$ids)));
        $stats = array();
        foreach ($ids as $id) {
            $stats[$id] = (object)array('recipients' => 0, 'opens' => 0, 'clicks' => 0, 'openRate' => 0, 'clickRate' => 0);
        }
        if (!count($ids)) {
            return $stats;
        }
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select(array($db->quoteName('campaignID'), $db->quoteName('typeID'), 'COUNT(DISTINCT '.$db->quoteName('subscriberID').') AS '.$db->quoteName('total')));
        $query->from($db->quoteName('#__ottomo_statistics'));
        $query->where($db->quoteName('campaignID').' IN ('.implode(',', $ids).')');
        $query->group(array($db->quoteName('campaignID'), $db->quoteName('typeID')));
        $db->setQuery($query);
        $names = array(1 => 'recipients', 2 => 'clicks', 3 => 'opens');
        foreach ((array)$db->loadObjectList() as $row) {
            if (isset($stats[(int)$row->campaignID], $names[(int)$row->typeID])) {
                $stats[(int)$row->campaignID]->{$names[(int)$row->typeID]} = (int)$row->total;
            }
        }
        foreach ($stats as $stat) {
            if ($stat->recipients > 0) {
                $stat->openRate = round(min(100, $stat->opens / $stat->recipients * 100), 1);
                $stat->clickRate = round(min(100, $stat->clicks / $stat->recipients * 100), 1);
            }
        }
        return $stats;
    }

    /** The most clicked links of a campaign: [{link, clicks}] (unique subscribers per link) */
    public function getTopLinks($id, $limit = 5)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select(array($db->quoteName('link'), 'COUNT(DISTINCT '.$db->quoteName('subscriberID').') AS '.$db->quoteName('clicks')));
        $query->from($db->quoteName('#__ottomo_statistics'));
        $query->where($db->quoteName('campaignID').' = '.(int)$id);
        $query->where($db->quoteName('typeID').' = 2');
        $query->where($db->quoteName('link').' <> '.$db->quote(''));
        $query->group($db->quoteName('link'));
        $query->order($db->quoteName('clicks').' DESC');
        $db->setQuery($query, 0, (int)$limit);
        return (array)$db->loadObjectList();
    }

    /**
     * Copies a campaign (content, topics, template) as a new draft.
     *
     * @return int|false the new campaign's id
     */
    public function duplicate()
    {
        $db = $this->getDbo();
        $id = (int)$this->getState('id');
        $source = $this->getRow();
        if (!$id || !$source || (int)$source->id !== $id) {
            $this->setError(Text::_('COM_OTTOMO_INVALID_CAMPAIGN'));
            return false;
        }
        $copy = array();
        foreach (array('subject', 'preheader', 'senderName', 'senderEmail', 'plain', 'template', 'snapshot') as $field) {
            $copy[$field] = isset($source->$field) ? $source->$field : '';
        }
        $copy['subject'] = Text::sprintf('COM_OTTOMO_COPY_OF', $copy['subject']);
        $copy['published'] = 0;
        // Stored like a new campaign saved from the wizard (same defaults for the sent date)
        $table = $this->getTable('Newsletters', 'OttomoTable');
        if (!$table->save($copy)) {
            $this->setError($table->getError());
            return false;
        }
        $newId = (int)$table->id;

        foreach ($this->getNewsletterData() as $data) {
            $row = (object)array('campaignID' => $newId, 'position' => $data->position, 'content' => $data->content);
            $db->insertObject('#__ottomo_email_campaigns_data', $row);
        }
        foreach (array_filter((array)$this->getNewsletterTopics()) as $topicId) {
            $row = (object)array('topicID' => (int)$topicId, 'campaignID' => $newId);
            $db->insertObject('#__ottomo_email_campaigns_xref', $row);
        }
        $this->setState('id', $newId);
        return $newId;
    }

    public function getNewsletterData()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName('#__ottomo_email_campaigns_data'));
        $query->where($db->quoteName('campaignID').' = '.(int)$this->getState('id'));
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        return $rows;
    }

    public function getNewsletterTopics()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select($db->quoteName('topicID'));
        $query->from($db->quoteName('#__ottomo_email_campaigns_xref'));
        $query->where($db->quoteName('campaignID').' = '.(int)$this->getState('id'));
        $db->setQuery($query);
        $rows = $db->loadColumn();
        return $rows;
    }

    public function save()
    {
        $data = $this->getState('data');
        $table = $this->getTable('Newsletters', 'OttomoTable');
        if (!$table->save($data)) {
            $this->setError($table->getError());
            return false;
        }
        $this->setState('id', $table->id);
        $db = $this->getDbo();

        //Newsletter data
        $query = $db->getQuery(true);
        $query->delete($db->quoteName('#__ottomo_email_campaigns_data'));
        $query->where($db->quoteName('campaignID').' = '.(int)$table->id);
        $db->setQuery($query);
        $db->execute();

        $variables = $data;
        $data = array();

        foreach ($variables as $key => $value) {
            if (stristr($key, 'ottomoPosition_')) {
                $data[] = array((int)$table->id, $db->quote(substr($key, 15)), $db->quote($value));
            }
        }
        if (count($data)) {
            $query = $db->getQuery(true);
            $query->insert($db->quoteName('#__ottomo_email_campaigns_data'));
            $query->columns(array($db->quoteName('campaignID'), $db->quoteName('position'), $db->quoteName('content')));
            foreach ($data as $entry) {
                $query->values(implode(',', $entry));
            }
            $db->setQuery($query);
            $db->execute();
        }

        //Newsletter topics
        $query = $db->getQuery(true);
        $query->delete($db->quoteName('#__ottomo_email_campaigns_xref'));
        $query->where($db->quoteName('campaignID').' = '.(int)$table->id);
        $db->setQuery($query);
        $db->execute();

        $topics = isset($variables['topics']) ? (array)$variables['topics'] : array();
        $data = array();
        ArrayHelper::toInteger($topics);
        foreach ($topics as $topic) {
            if ($topic > 0) {
                $data[] = array($topic, (int)$table->id);
            }
        }
        if (count($data)) {
            $query = $db->getQuery(true);
            $query->insert($db->quoteName('#__ottomo_email_campaigns_xref'));
            $query->columns(array($db->quoteName('topicID'), $db->quoteName('campaignID')));
            foreach ($data as $entry) {
                $query->values(implode(',', $entry));
            }
            $db->setQuery($query);
            $db->execute();
        }

        return true;
    }

    public function copy()
    {
        $ids = $this->getState('id');
        foreach ($ids as $id) {
            $table = $this->getTable('Newsletters', 'OttomoTable');
            $table->load($id);
            $this->setState('id', $id);
            $table->topics = $this->getNewsletterTopics();
            $data = ArrayHelper::fromObject($table);
            $data['id'] = null;
            $data['published'] = 0;
            $data['sent'] = null;
            $positions = $this->getNewsletterData();
            foreach ($positions as $position) {
                $data['ottomoPosition_'.$position->position] = $position->content;
            }
            $this->setState('data', $data);
            $this->save();
        }
    }

    public function send()
    {
        // Load helpers
        JLoader::register('OttomoHelperTemplate', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/template.php');
        JLoader::register('OttomoHelperData', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/data.php');

        // Get some variables
        $mainframe = Factory::getApplication();
        $params = ComponentHelper::getParams('com_ottomo');

        // Get newsletter
        $newsletter = $this->getRow();
        $newsletter->data = $this->getNewsletterData();
        $newsletter->topics = $this->getNewsletterTopics();

        if ($this->getState('emails')) {
            $recipients = array();
            $emails = $this->getState('emails');
            foreach ($emails as $email) {
                $recipient = new stdClass();
                $recipient->email = trim($email);
                $recipient->id = $recipient->email;
                $recipient->name = $recipient->email;
                $recipients[] = $recipient;
            }
            if (!count($recipients)) {
                $this->setError(Text::_('COM_OTTOMO_THERE_ARE_NO_SUBSCRIBERS_FOR_THE_SPECIFIC_NEWSLETTER'));
                return false;
            }
        } else {
            // The next batch: subscribers after the cursor who haven't received the campaign yet (see recipientsQuery())
            $newsletter->topics = array_filter($newsletter->topics);
            $db = $this->getDbo();
            $query = $this->recipientsQuery((int)$newsletter->id, $newsletter->topics, $this->sendingStarted((int)$newsletter->id))
                ->select($db->quoteName('s').'.*')
                ->where($db->quoteName('s.id').' > '.(int)$this->getState('after'))
                ->order($db->quoteName('s.id').' ASC');
            $db->setQuery($query, 0, $this->batchSize());
            $recipients = $db->loadObjectList();
            $this->setState('lastID', $recipients ? (int)end($recipients)->id : (int)$this->getState('after'));
            if (!$recipients) {
                return 0;
            }
        }

        // Get the mailer
        $mailer = OttomoCompat::mailer();
        $mailerType = ($this->getState('emails') && $this->getState('forcePhpMail'))
            ? 'mail'
            : $params->get('mailer', 'mail');

        // Set the mailer type
        switch ($mailerType) {
            case 'mail':
                $mailer->IsMail();
                break;

            case 'sendmail':
                $mailer->IsSendmail();
                break;

            case 'smtp':
                $smtpAuth = $params->get('smtpauth') ? (bool)$params->get('smtpauth') : null;
                $mailer->useSMTP($smtpAuth, $params->get('smtphost'), $params->get('smtpuser'), $params->get('smtppass'), $params->get('smtpsecure'), $params->get('smtpport'));
                // One connection for the whole batch (closed after it), not one per email
                $mailer->SMTPKeepAlive = true;
                break;
        }

        // Set sender
        JLoader::register('OttomoHelperSettings', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/settings.php');
        $sender = OttomoHelperSettings::sender($newsletter);
        $senderEmail = $sender->email;
        $senderName = $sender->name;
        $mailer->setSender(array($senderEmail, $senderName));

        // Set reply to
        $replyTo = $params->get('replyTo', $senderEmail);
        $mailer->addReplyTo($replyTo, $senderName);

        // Set subject
        $mailer->setSubject($newsletter->subject);

        // Set email format
        $mailer->IsHTML(true);

        // Set plain message
        $mailer->AltBody = $newsletter->plain;

        // Set message content.
        $tracking = $this->getState('emails') ? false : true;
        $buffer = ottomoHelperTemplate::render($newsletter, 'send', $tracking);
        $mailer->setBody($buffer);

        // Prepare data and send emails for non-services
        $failed = array();
        $campaign = !$this->getState('emails');
        $lastError = '';
        foreach ($recipients as $recipient) {
            // Set the subscriber hash
            $recipient->hash = ottomoHelperData::getHash($newsletter->id, $recipient->id, $recipient->email);

            // Perform personalization and send for non-services
            if ($mailerType == 'mail' || $mailerType == 'sendmail' || $mailerType == 'smtp') {
                // Personalisation: {{field}} / {{field|fallback}}, body and subject
                $mailer->setBody(OttomoHelperData::personalise($buffer, $recipient));
                $mailer->setSubject(OttomoHelperData::personalise($newsletter->subject, $recipient, false));
                $mailer->AltBody = OttomoHelperData::personalise($newsletter->plain, $recipient, false);

                // One-click unsubscribe (RFC 8058), campaign sends only
                $mailer->clearCustomHeaders();
                foreach (OttomoHelperData::campaignHeaders($newsletter->id, $recipient, $newsletter->topics) as $header => $value) {
                    $mailer->addCustomHeader($header, $value);
                }

                // Send the email
                $mailer->ClearAddresses();
                $mailer->AddAddress($recipient->email, $recipient->name);
                if ($mailer->Send() !== true) {
                    $failed[$recipient->id] = $recipient->email;
                    $lastError = (string)$mailer->ErrorInfo;
                } elseif ($campaign) {
                    // Recorded as soon as it's sent: an interrupted send resumes after it
                    $this->recordSent((int)$newsletter->id, array($recipient->id));
                }
            }
        }

        if ($mailerType == 'smtp') {
            $mailer->smtpClose();
        }

        // Send email for services: a plugin returns the recipients it could not send to (id => email) and throws
        // when the whole batch failed (nothing is recorded then, so a resumed send tries the batch again)
        if (!in_array($mailerType, array('mail', 'sendmail', 'smtp'))) {
            PluginHelper::importPlugin('ottomo-providers', $mailerType);
            try {
                $results = OttomoCompat::trigger('onOttomoSend', array($mailer, $recipients, $params, $newsletter->topics, (int)$newsletter->id));
            } catch (Exception $e) {
                $this->setError($e->getMessage());
                return false;
            }
            foreach ((array)$results as $result) {
                if (is_array($result)) {
                    $failed += $result;
                }
            }
            if ($campaign) {
                $sent = array();
                foreach ($recipients as $recipient) {
                    if (!isset($failed[$recipient->id])) {
                        $sent[] = $recipient->id;
                    }
                }
                $this->recordSent((int)$newsletter->id, $sent);
            }
        }
        $this->setState('failed', $failed);

        // A campaign batch where nothing could be delivered stops the send (mail server or service down,
        // wrong credentials...) instead of going through every subscriber
        if ($campaign && count($recipients) && count($failed) === count($recipients)) {
            $this->setError(Text::sprintf('COM_OTTOMO_SEND_BATCH_FAILED', $lastError !== '' ? $lastError : implode(', ', array_slice($failed, 0, 3))));
            return false;
        }

        // A test send where nothing could be delivered is an error
        if ($this->getState('emails') && count($failed) === count($recipients)) {
            $this->setError(Text::sprintf('COM_OTTOMO_SEND_TEST_FAILED_FOR', implode(', ', $failed)));
            return false;
        }

        return sizeof($recipients);
    }

    /**
     * The campaign's audience still waiting for it: active subscribers of its topics without a "sent" record,
     * created before the send began (people who sign up later don't get an old campaign; null: not begun).
     * Used for every batch (with a cursor on the subscriber id: no OFFSET, so the list may change meanwhile),
     * for the send dialog's count and for "Resume sending".
     */
    public function recipientsQuery($newsletterId, array $topics, $started = null)
    {
        $db = $this->getDbo();
        $topics = array_values(array_filter(array_map('intval', $topics)));
        $query = $db->getQuery(true)
            ->from($db->quoteName('#__ottomo_subscribers', 's'))
            ->where($db->quoteName('s.status').' = 1')
            ->where('EXISTS (SELECT 1 FROM '.$db->quoteName('#__ottomo_subscribers_xref', 'x')
                .' WHERE '.$db->quoteName('x.subscriberID').' = '.$db->quoteName('s.id')
                .' AND '.$db->quoteName('x.topicID').' IN ('.($topics ? implode(',', $topics) : '0').'))')
            ->where('NOT EXISTS (SELECT 1 FROM '.$db->quoteName('#__ottomo_statistics', 'st')
                .' WHERE '.$db->quoteName('st.campaignID').' = '.(int)$newsletterId
                .' AND '.$db->quoteName('st.typeID').' = 1'
                .' AND '.$db->quoteName('st.subscriberID').' = '.$db->quoteName('s.id').')');
        if ($started) {
            $query->where($db->quoteName('s.created').' <= '.$db->quote($started));
        }
        return $query;
    }

    /**
     * Recipients per batch: Settings > Sending (limit), or less when the mail service asks for less
     * (a "batchSize" method of the provider plugin, e.g. SES at its account's sending rate).
     */
    public function batchSize()
    {
        $limit = max(1, (int)$this->getState('limit'));
        $mailer = ComponentHelper::getParams('com_ottomo')->get('mailer', 'mail');
        if (!in_array($mailer, array('mail', 'sendmail', 'smtp'), true)) {
            PluginHelper::importPlugin('ottomo-providers', $mailer);
            foreach ((array)OttomoCompat::trigger('onOttomoBatchSize', array($limit)) as $size) {
                if ((int)$size > 0) {
                    $limit = min($limit, (int)$size);
                }
            }
        }
        $this->setState('batchSize', $limit);
        return $limit;
    }

    /** Size in bytes of the saved campaign's HTML as it is sent (minified, with tracking), for Gmail's clipping limit. */
    public function getSendSize($newsletterId)
    {
        $this->setState('id', (int)$newsletterId);
        $newsletter = $this->getRow();
        if (!$newsletter || empty($newsletter->id)) {
            return 0;
        }
        $newsletter->data = $this->getNewsletterData();
        $newsletter->topics = $this->getNewsletterTopics();
        JLoader::register('OttomoHelperTemplate', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/template.php');
        return strlen(OttomoHelperTemplate::render($newsletter, 'send', true));
    }

    /** When the campaign's sending began (its first "sent" record, Y-m-d H:i:s), or null when it hasn't. */
    public function sendingStarted($newsletterId)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName(array('date', 'time')))
            ->from($db->quoteName('#__ottomo_statistics'))
            ->where($db->quoteName('campaignID').' = '.(int)$newsletterId)
            ->where($db->quoteName('typeID').' = 1')
            ->order($db->quoteName('id').' ASC');
        $db->setQuery($query, 0, 1);
        $row = $db->loadObject();
        return $row ? $row->date.' '.$row->time : null;
    }

    /** How many subscribers are still waiting for the campaign (all of its audience before it is sent). */
    public function getRemaining($newsletterId = null)
    {
        $newsletterId = (int)($newsletterId ?: $this->getState('id'));
        $this->setState('id', $newsletterId);
        $topics = array_filter((array)$this->getNewsletterTopics());
        if (!$topics) {
            return 0;
        }
        $db = $this->getDbo();
        $db->setQuery($this->recipientsQuery($newsletterId, $topics, $this->sendingStarted($newsletterId))->select('COUNT(*)'));
        return (int)$db->loadResult();
    }

    /** "Sent" records for these subscribers (one INSERT), and the campaign marked as sent on its first one. */
    protected function recordSent($newsletterId, array $subscriberIds)
    {
        $subscriberIds = array_filter(array_map('intval', $subscriberIds));
        if (!$subscriberIds) {
            return;
        }
        $db = $this->getDbo();
        $now = Factory::getDate();
        $date = $db->quote($now->format('Y-m-d'));
        $time = $db->quote($now->format('H:i:s'));
        $query = $db->getQuery(true)
            ->insert($db->quoteName('#__ottomo_statistics'))
            ->columns($db->quoteName(array('typeID', 'campaignID', 'subscriberID', 'date', 'time', 'link')));
        foreach ($subscriberIds as $subscriberId) {
            $query->values('1, '.(int)$newsletterId.', '.$subscriberId.', '.$date.', '.$time.', '.$db->quote(''));
        }
        $db->setQuery($query);
        $db->execute();

        // Once per request: the campaign is marked as sent on its first record
        static $marked = array();
        if (isset($marked[$newsletterId])) {
            return;
        }
        $marked[$newsletterId] = true;
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__ottomo_email_campaigns'))
            ->set($db->quoteName('published').' = 1')
            ->set($db->quoteName('sent').' = '.$date)
            ->where($db->quoteName('id').' = '.(int)$newsletterId)
            ->where($db->quoteName('published').' = 0');
        $db->setQuery($query);
        $db->execute();
    }

    public function track()
    {
        $db = $this->getDbo();
        $id = (int)$this->getState('id');
        $subscriber = (int)$this->getState('subscriber');
        $type = (int)$this->getState('type');
        $link = $type == 2 ? substr((string)$this->getState('link'), 0, 2048) : '';

        // Validation for front-end
        $application = Factory::getApplication();
        if ($application->isClient('site')) {
            // Validate campaign id
            $query = $db->getQuery(true);
            $query->select('*')->from($db->quoteName('#__ottomo_email_campaigns'))->where($db->quoteName('id').' = '.$id)->where($db->quoteName('published').' = 1');
            $db->setQuery($query);
            $row = $db->loadObject();
            if (!$row) {
                $this->setState('errorType', 'campaign');
                $this->setError(Text::_('COM_OTTOMO_INVALID_CAMPAIGN'));
                return false;
            }

            // Validate subscriber id
            $query = $db->getQuery(true);
            $query->select('*')->from($db->quoteName('#__ottomo_subscribers'))->where($db->quoteName('id').' = '.$subscriber)->where($db->quoteName('status').' = 1');
            $db->setQuery($query);
            $row = $db->loadObject();
            if (!$row) {
                $this->setState('errorType', 'subscriber');
                $this->setError(Text::_('COM_OTTOMO_INVALID_SUBSCRIBER'));
                return false;
            }

            // Track events only once per campaign/user (clicks: once per campaign/user/link)
            $query = $db->getQuery(true);
            $query->select('*')->from($db->quoteName('#__ottomo_statistics'))->where($db->quoteName('typeID').' = '.$type)->where($db->quoteName('campaignID').' = '.$id)->where($db->quoteName('subscriberID').' = '.$subscriber);
            if ($type == 2) {
                $query->where($db->quoteName('link').' = '.$db->quote($link));
            }
            $db->setQuery($query);
            $row = $db->loadObject();
            if ($row) {
                $this->setState('errorType', 'duplicate');
                $this->setError(Text::_('COM_OTTOMO_DUPLICATE_TRACKING_DETECTED'));
                return false;
            }

        }

        $jDate = Factory::getDate();
        $date = $jDate->format('Y-m-d');
        $time = $jDate->format('H:i:s');
        $query = $db->getQuery(true);
        $query->insert($db->quoteName('#__ottomo_statistics'));
        $query->columns(array($db->quoteName('typeID'), $db->quoteName('campaignID'), $db->quoteName('subscriberID'), $db->quoteName('date'), $db->quoteName('time'), $db->quoteName('link')));
        $query->values($type.', '.$id.', '.$subscriber.', '.$db->quote($date).', '.$db->quote($time).', '.$db->quote($link));
        $db->setQuery($query);
        $db->execute();
        $query = $db->getQuery(true);
        $query->update($db->quoteName('#__ottomo_email_campaigns'));
        $query->set($db->quoteName('published').' = 1');
        $query->set($db->quoteName('sent').' = '.$db->quote($date));
        $query->where($db->quoteName('id').' = '.(int)$id);
        $db->setQuery($query);
        $db->execute();
        return true;
    }

    public function getStatistics()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('COUNT(*) AS '.$db->quoteName('counter'));
        $query->select($db->quoteName('date'));
        if ((int)$this->getState('type') === 2) {
            // Clicks are stored once per link: count a subscriber once per campaign (on the day of the first click)
            $query->from('(SELECT '.$db->quoteName('campaignID').', '.$db->quoteName('subscriberID').', MIN('.$db->quoteName('date').') AS '.$db->quoteName('date')
                .' FROM '.$db->quoteName('#__ottomo_statistics').' WHERE '.$db->quoteName('typeID').' = 2'
                .' GROUP BY '.$db->quoteName('campaignID').', '.$db->quoteName('subscriberID').') AS '.$db->quoteName('stats'));
        } else {
            $query->from($db->quoteName('#__ottomo_statistics').' AS '.$db->quoteName('stats'));
        }
        if ($this->getState('newsletter')) {
            $query->where($db->quoteName('stats.campaignID').' = '.(int)$this->getState('newsletter'));
        }
        if ($this->getState('topic')) {
            $query->leftJoin($db->quoteName('#__ottomo_email_campaigns_xref').' AS '.$db->quoteName('xref').' ON '.$db->quoteName('xref.campaignID').' = '.$db->quoteName('stats.campaignID'));
            $query->where($db->quoteName('xref.topicID').' = '.(int)$this->getState('topic'));
        }
        if ($this->getState('type') && (int)$this->getState('type') !== 2) {
            $query->where($db->quoteName('stats.typeID').' = '.(int)$this->getState('type'));
        }
        if ($this->getState('startDate')) {
            $query->where($db->quoteName('stats.date').' >= '.$db->quote($this->getState('startDate')));
        }
        if ($this->getState('endDate')) {
            $query->where($db->quoteName('stats.date').'<= '.$db->quote($this->getState('endDate')));
        }
        $query->group($db->quoteName('date'));
        if ($this->getState('ordering') && $this->getState('orderingDir')) {
            $query->order($this->getState('ordering').' '.$this->getState('orderingDir'));
        }
        $db->setQuery($query);
        $statistics = $db->loadObjectList();
        return $statistics;
    }

}
