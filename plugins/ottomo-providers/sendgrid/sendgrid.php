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
class plgOttomoprovidersSendgrid extends JPlugin
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
        // Personalisation: every subscriber placeholder ({{firstname}}, {{email}}…) becomes a SendGrid substitution
        $body = (string)$mailer->Body;
        $text = (string)$mailer->AltBody;
        $subject = (string)$mailer->Subject;
        $variables = OttomoHelperData::serviceTemplate($body, $text, $subject, '%%%s%%');

        $personalizations = array();
        foreach ($recipients as $recipient) {
            $to = array('email' => $recipient->email);
            if (!empty($recipient->name)) {
                $to['name'] = $recipient->name;
            }
            $substitutions = array();
            foreach (OttomoHelperData::serviceValues($variables, $recipient) as $name => $value) {
                $substitutions['%'.$name.'%'] = (string)$value;
            }
            $personalization = array('to' => array($to));
            if (count($substitutions)) {
                $personalization['substitutions'] = $substitutions;
            }
            // One-click unsubscribe (RFC 8058): per-recipient headers
            $headers = OttomoHelperData::campaignHeaders($newsletterId, $recipient, $topics);
            if (count($headers)) {
                $personalization['headers'] = $headers;
            }
            $personalizations[] = $personalization;
        }

        $content = array();
        if ($text !== '') {
            $content[] = array('type' => 'text/plain', 'value' => $text);
        }
        $content[] = array('type' => 'text/html', 'value' => $body);

        $payload = array(
            'personalizations' => $personalizations,
            'from' => array(
                'email' => $mailer->From,
                'name' => $mailer->FromName,
            ),
            'subject' => $subject,
            'content' => $content,
        );

        $replyTo = $params->get('replyTo');
        if (!empty($replyTo)) {
            $payload['reply_to'] = array(
                'email' => $replyTo,
                'name' => $mailer->FromName,
            );
        }

        if (is_array($topics) && count($topics)) {
            $categories = array();
            foreach (array_slice($topics, 0, 10) as $topicId) {
                $categories[] = 'OttomoTopic'.(int)$topicId;
            }
            $payload['categories'] = $categories;
        }

        $this->call('mail/send', $payload, 'POST');

        return array(); // one call for the batch: it throws when the batch fails
    }

    public function verify()
    {
        $apiKey = trim((string)$this->params->get('sendgridApiKey'));
        if ($apiKey === '') {
            return false;
        }

        try {
            $this->call('user/profile', array(), 'GET');
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function statistics($startDate, $endDate, $topicId)
    {
        $data = array();
        $data['start_date'] = $startDate;
        if ($endDate) {
            $data['end_date'] = $endDate;
        }
        if ($topicId) {
            $function = 'categories/stats';
            $data['categories'] = 'OttomoTopic'.(int)$topicId;
        } else {
            $function = 'stats';
        }

        if (!$data['start_date']) {
            $start = new DateTime();
            $start->sub(new DateInterval('P7D'));
            $data['start_date'] = $start->format('Y-m-d');
        }

        $response = $this->call($function, $data, 'GET');
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
        $properties = array('requests', 'opens', 'clicks', 'delivered', 'bounces', 'unsubscribes', 'spam_reports');
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

        if (!$response || !is_array($response)) {
            return $object;
        }

        foreach ($response as $entry) {
            $row = array($entry->date);
            foreach ($properties as $property) {
                $val = isset($entry->stats[0]->metrics->$property) ? (int)$entry->stats[0]->metrics->$property : 0;
                $row[] = $val;
                $object->stats->$property->total += $val;
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
        $endpoint = 'https://api.sendgrid.com/v3/'.$function;
        $headers = array(
            'Authorization: Bearer '.trim((string)$this->params->get('sendgridApiKey')),
            'Content-Type: application/json',
        );

        if ($method === 'GET') {
            if (!empty($data)) {
                $endpoint .= '?'.http_build_query($data);
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

        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $response = $result !== '' ? json_decode($result) : null;
        curl_close($ch);

        if ($status < 200 || $status >= 300) {
            $error = isset($response->errors[0]->message) ? $response->errors[0]->message : 'SendGrid error occurred (HTTP '.$status.')';
            throw new Exception($error, $status);
        }

        return $response;
    }
}
