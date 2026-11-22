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
class plgOttomoprovidersBrevo extends JPlugin
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
        // Personalisation: every subscriber placeholder ({{firstname}}, {{email}}…) becomes a Brevo param.
        // (Brevo adds its own List-Unsubscribe header: none is set here.)
        $body = (string) $mailer->Body;
        $text = (string) $mailer->AltBody;
        $subject = (string) $mailer->Subject;
        $variables = OttomoHelperData::serviceTemplate($body, $text, $subject, '{{params.%s}}');

        $messageVersions = array();
        foreach ($recipients as $recipient) {
            $to = array('email' => $recipient->email);
            if (!empty($recipient->name)) {
                $to['name'] = $recipient->name;
            }
            $messageVersions[] = array(
                'to' => array($to),
                'params' => array('id' => (string) $recipient->id) + OttomoHelperData::serviceValues($variables, $recipient),
            );
        }

        $payload = array(
            'sender' => array(
                'email' => $mailer->From,
                'name' => $mailer->FromName,
            ),
            'subject' => $subject,
            'htmlContent' => $body,
            'messageVersions' => $messageVersions,
        );

        if ($text !== '') {
            $payload['textContent'] = $text;
        }

        $replyTo = $params->get('replyTo');
        if (!empty($replyTo)) {
            $payload['replyTo'] = array(
                'email' => $replyTo,
                'name' => $mailer->FromName,
            );
        }

        if (is_array($topics) && count($topics)) {
            $tags = array();
            foreach ($topics as $topicId) {
                $tags[] = 'OttomoTopic' . (int) $topicId;
            }
            $payload['tags'] = $tags;
        }

        // Feedback-ID (Gmail Postmaster Tools), campaign sends only
        if ($newsletterId) {
            $payload['headers'] = array('Feedback-ID' => OttomoHelperData::feedbackId($newsletterId, $topics));
        }

        $this->call('smtp/email', $payload, 'POST');

        return array(); // one call for the batch: it throws when the batch fails
    }

    public function verify()
    {
        $apiKey = trim((string) $this->params->get('brevoApiKey'));
        if ($apiKey === '') {
            return false;
        }

        try {
            $this->call('account', array(), 'GET');
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
            'startDate' => $startDate,
            'endDate' => $endDate,
        );
        if ($topicId) {
            $query['tag'] = 'OttomoTopic' . (int) $topicId;
        }

        $response = $this->call('smtp/statistics/reports', $query, 'GET');

        $columns = array(
            JText::_('COM_OTTOMO_DATE'),
            JText::_('COM_OTTOMO_REQUESTS'),
            JText::_('COM_OTTOMO_OPENED'),
            JText::_('COM_OTTOMO_CLICKED'),
            JText::_('COM_OTTOMO_DELIVERED'),
            JText::_('COM_OTTOMO_BOUNCES'),
            JText::_('COM_OTTOMO_UNSUBSCRIBES'),
            JText::_('COM_OTTOMO_SPAM_REPORTS'),
        );
        $properties = array('requests', 'opens', 'clicks', 'delivered', 'bounces', 'unsubscribed', 'spamReports');
        $colors = array('#b8b8b8', '#00aced', '#33b6b2', '#3fa800', '#e87e04', '#c74484', '#e74b3c');

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

        if (!$response || empty($response->reports) || !is_array($response->reports)) {
            return $object;
        }

        foreach ($response->reports as $entry) {
            $bounces = (int) ($entry->hardBounces ?? 0) + (int) ($entry->softBounces ?? 0);
            $opens = (int) ($entry->uniqueOpens ?? ($entry->opens ?? 0));
            $clicks = (int) ($entry->uniqueClicks ?? ($entry->clicks ?? 0));
            $map = array(
                'requests' => (int) ($entry->requests ?? 0),
                'opens' => $opens,
                'clicks' => $clicks,
                'delivered' => (int) ($entry->delivered ?? 0),
                'bounces' => $bounces,
                'unsubscribed' => (int) ($entry->unsubscribed ?? 0),
                'spamReports' => (int) ($entry->spamReports ?? 0),
            );
            $row = array($entry->date);
            foreach ($properties as $property) {
                $row[] = $map[$property];
                $object->stats->$property->total += $map[$property];
            }
            $object->chart[] = $row;
        }

        foreach ($object->stats as $key => $stat) {
            if ($key !== 'requests' && $object->stats->requests->total > 0) {
                $stat->percentage = ceil(($stat->total / $object->stats->requests->total) * 100);
            }
        }

        array_unshift($object->chart, $columns);
        return $object;
    }

    protected function call($function, $data = array(), $method = 'GET')
    {
        $ch = curl_init();
        $endpoint = 'https://api.brevo.com/v3/' . ltrim($function, '/');
        $headers = array(
            'api-key: ' . trim((string) $this->params->get('brevoApiKey')),
            'Content-Type: application/json',
            'Accept: application/json',
        );

        if ($method === 'GET') {
            if (!empty($data)) {
                $endpoint .= '?' . http_build_query($data);
            }
            curl_setopt($ch, CURLOPT_URL, $endpoint);
        } else {
            curl_setopt($ch, CURLOPT_URL, $endpoint);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
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

        if ($status < 200 || $status >= 300) {
            $error = isset($response->message) ? $response->message : 'Brevo error occurred (HTTP ' . $status . ')';
            throw new Exception($error, $status);
        }

        return $response;
    }
}
