<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

#[\AllowDynamicProperties]
class plgOttomoprovidersMailjet extends JPlugin
{
    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        JFactory::getLanguage()->load('com_ottomo', JPATH_ADMINISTRATOR.'/components/com_ottomo'); // Ottomo's backend language file
        $settings = JComponentHelper::getParams('com_ottomo');
        $this->params->merge($settings);
    }

    public function send(&$mailer, $recipients, $params, $topics, $newsletterId = 0)
    {
        JLoader::register('OttomoHelperData', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/data.php');
        $messages = array();
        $replyTo = $params->get('replyTo');
        $campaign = (is_array($topics) && count($topics)) ? 'OttomoTopic' . (int) $topics[0] : '';

        foreach ($recipients as $recipient) {
            // Personalisation: {{field}} / {{field|fallback}}
            $body = OttomoHelperData::personalise((string)$mailer->Body, $recipient);
            $subject = OttomoHelperData::personalise((string)$mailer->Subject, $recipient, false);
            $text = OttomoHelperData::personalise((string)$mailer->AltBody, $recipient, false);

            $to = array('Email' => $recipient->email);
            if (!empty($recipient->name)) {
                $to['Name'] = $recipient->name;
            }

            $message = array(
                'From' => array(
                    'Email' => $mailer->From,
                    'Name' => $mailer->FromName,
                ),
                'To' => array($to),
                'Subject' => $subject,
                'HTMLPart' => $body,
            );

            if ($text !== '') {
                $message['TextPart'] = $text;
            }

            // One-click unsubscribe (RFC 8058)
            $headers = OttomoHelperData::campaignHeaders($newsletterId, $recipient, $topics);
            if (count($headers)) {
                $message['Headers'] = $headers;
            }

            if (!empty($replyTo)) {
                $message['ReplyTo'] = array(
                    'Email' => $replyTo,
                    'Name' => $mailer->FromName,
                );
            }

            if ($campaign !== '') {
                $message['CustomCampaign'] = $campaign;
            }

            $messages[] = $message;
        }

        if (!count($messages)) {
            return array();
        }
        // Mailjet answers per message (same order): those with an error were not sent
        $response = $this->call('https://api.mailjet.com/v3.1/send', array('Messages' => $messages), 'POST');
        $failed = array();
        $recipients = array_values($recipients);
        if (isset($response->Messages) && is_array($response->Messages)) {
            foreach ($response->Messages as $index => $result) {
                if (isset($result->Status) && $result->Status !== 'success' && isset($recipients[$index])) {
                    $failed[$recipients[$index]->id] = $recipients[$index]->email;
                }
            }
        }
        if (count($failed) === count($recipients)) {
            $error = isset($response->Messages[0]->Errors[0]->ErrorMessage) ? $response->Messages[0]->Errors[0]->ErrorMessage : 'Mailjet could not send these emails';
            throw new Exception($error, 400);
        }
        return $failed;
    }

    public function verify()
    {
        $apiKey = trim((string) $this->params->get('mailjetApiKey'));
        $secretKey = trim((string) $this->params->get('mailjetSecretKey'));
        if ($apiKey === '' || $secretKey === '') {
            return false;
        }

        try {
            $this->call('https://api.mailjet.com/v3/REST/myprofile', array(), 'GET');
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function statistics($startDate, $endDate, $topicId)
    {
        if (!$startDate) {
            $start = new DateTime();
            $start->sub(new DateInterval('P7D'));
            $startDate = $start->format('Y-m-d');
        }
        if (!$endDate) {
            $endDate = gmdate('Y-m-d');
        }

        $query = array(
            'CounterSource' => 'APIKey',
            'CounterTiming' => 'Day',
            'CounterResolution' => 'Day',
            'FromTS' => $startDate . 'T00:00:00Z',
            'ToTS' => $endDate . 'T23:59:59Z',
        );

        $response = $this->call('https://api.mailjet.com/v3/REST/statcounters', $query, 'GET');

        $columns = array(
            JText::_('COM_OTTOMO_DATE'),
            JText::_('COM_OTTOMO_REQUESTS'),
            JText::_('COM_OTTOMO_OPENED'),
            JText::_('COM_OTTOMO_CLICKED'),
            JText::_('COM_OTTOMO_BOUNCES'),
            JText::_('COM_OTTOMO_UNSUBSCRIBES'),
            JText::_('COM_OTTOMO_SPAM_REPORTS'),
        );
        $properties = array('sent', 'opens', 'clicks', 'bounces', 'unsubscribes', 'spam');
        $colors = array('#b8b8b8', '#00aced', '#33b6b2', '#e87e04', '#c74484', '#e74b3c');

        $object = new stdClass();
        $object->chart = array();
        $stats = new stdClass();
        foreach ($properties as $key => $property) {
            $stat = new stdClass();
            $stat->total = 0;
            $stat->percentage = 0;
            $stat->label = $columns[$key + 1];
            $stat->color = $colors[$key];
            $stats->$property = $stat;
        }
        $object->stats = $stats;

        if (!$response || empty($response->Data) || !is_array($response->Data)) {
            return $object;
        }

        foreach ($response->Data as $entry) {
            $date = gmdate('Y-m-d', strtotime((string) ($entry->Timeslice ?? $startDate)));
            $bounces = (int) ($entry->MessageSoftBouncedCount ?? 0) + (int) ($entry->MessageHardBouncedCount ?? 0);
            $map = array(
                'sent' => (int) ($entry->MessageSentCount ?? 0),
                'opens' => (int) ($entry->MessageOpenedCount ?? 0),
                'clicks' => (int) ($entry->MessageClickedCount ?? 0),
                'bounces' => $bounces,
                'unsubscribes' => (int) ($entry->MessageUnsubscribedCount ?? 0),
                'spam' => (int) ($entry->MessageSpamCount ?? 0),
            );
            $row = array($date);
            foreach ($properties as $property) {
                $row[] = $map[$property];
                $object->stats->$property->total += $map[$property];
            }
            $object->chart[] = $row;
        }

        foreach ($object->stats as $key => $stat) {
            if ($key !== 'sent' && $object->stats->sent->total > 0) {
                $stat->percentage = ceil(($stat->total / $object->stats->sent->total) * 100);
            }
        }

        array_unshift($object->chart, $columns);
        return $object;
    }

    protected function call($endpoint, $data = array(), $method = 'GET')
    {
        $ch = curl_init();
        $apiKey = trim((string) $this->params->get('mailjetApiKey'));
        $secretKey = trim((string) $this->params->get('mailjetSecretKey'));

        if ($method === 'GET' && !empty($data)) {
            $endpoint .= '?' . http_build_query($data);
        }

        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_USERPWD, $apiKey . ':' . $secretKey);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $result = curl_exec($ch);
        if ($result === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception($error, 500);
        }

        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $response = $result !== '' ? json_decode($result) : null;
        curl_close($ch);

        // A batch where only some messages failed: Mailjet answers 400 with the status of each message
        if ($status === 400 && isset($response->Messages) && is_array($response->Messages)) {
            foreach ($response->Messages as $result) {
                if (isset($result->Status) && $result->Status === 'success') {
                    return $response;
                }
            }
        }

        if ($status < 200 || $status >= 300) {
            $error = isset($response->ErrorMessage)
                ? $response->ErrorMessage
                : (isset($response->Messages[0]->Errors[0]->ErrorMessage) ? $response->Messages[0]->Errors[0]->ErrorMessage : 'Mailjet error occurred (HTTP ' . $status . ')');
            throw new Exception($error, $status);
        }

        return $response;
    }
}
