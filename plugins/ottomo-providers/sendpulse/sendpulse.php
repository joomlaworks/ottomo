<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;

require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/compat.php';

#[\AllowDynamicProperties]
class plgOttomoprovidersSendpulse extends CMSPlugin
{
    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        OttomoCompat::language()->load('com_ottomo', JPATH_ADMINISTRATOR.'/components/com_ottomo'); // Ottomo's backend language file
        $settings = ComponentHelper::getParams('com_ottomo');
        $this->params->merge($settings);
    }

    public function onOttomoSend($mailer, $recipients, $params, $topics, $newsletterId = 0)
    {
        JLoader::register('OttomoHelperData', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/data.php');
        $token = $this->getAccessToken();
        $failed = array();
        $lastError = null;

        foreach ($recipients as $recipient) {
            // (SendPulse documents no custom headers on smtp/emails: no one-click unsubscribe header)
            // Personalisation: {{field}} / {{field|fallback}}
            $body = OttomoHelperData::personalise((string)$mailer->Body, $recipient);
            $subject = OttomoHelperData::personalise((string)$mailer->Subject, $recipient, false);
            $text = OttomoHelperData::personalise((string)$mailer->AltBody, $recipient, false);

            $to = array('email' => $recipient->email);
            if (!empty($recipient->name)) {
                $to['name'] = $recipient->name;
            }

            $email = array(
                'html' => base64_encode($body),
                'text' => $text,
                'subject' => $subject,
                'from' => array(
                    'name' => $mailer->FromName,
                    'email' => $mailer->From,
                ),
                'to' => array($to),
            );

            try {
                $this->callWithRetry('smtp/emails', array('email' => $email), $token);
            } catch (Exception $e) {
                $failed[$recipient->id] = $recipient->email;
                $lastError = $e;
            }
        }

        // Nothing sent: the batch failed as a whole (credentials, quota...)
        if ($lastError && count($failed) === count($recipients)) {
            throw $lastError;
        }
        return $failed;
    }

    /** One call, retried after a pause when SendPulse throttles (HTTP 429), up to 3 times. */
    protected function callWithRetry($endpoint, $data, $token)
    {
        for ($attempt = 0; ; $attempt++) {
            try {
                return $this->call($endpoint, $data, 'POST', $token);
            } catch (Exception $e) {
                if ((int)$e->getCode() !== 429 || $attempt >= 3) {
                    throw $e;
                }
                usleep((1 << $attempt) * 1000000); // 1 s, 2 s, 4 s
            }
        }
    }

    public function onOttomoVerify()
    {
        $clientId = trim((string) $this->params->get('sendpulseClientId'));
        $clientSecret = trim((string) $this->params->get('sendpulseClientSecret'));
        if ($clientId === '' || $clientSecret === '') {
            return false;
        }

        try {
            $token = $this->getAccessToken();
            return $token !== '';
        } catch (Exception $e) {
            return false;
        }
    }

    public function onOttomoStatistics($startDate, $endDate, $topicId)
    {
        $columns = array(
            Text::_('COM_OTTOMO_DATE'),
            Text::_('COM_OTTOMO_REQUESTS'),
            Text::_('COM_OTTOMO_OPENED'),
            Text::_('COM_OTTOMO_CLICKED'),
            Text::_('COM_OTTOMO_BOUNCES'),
            Text::_('COM_OTTOMO_UNSUBSCRIBES'),
            Text::_('COM_OTTOMO_SPAM_REPORTS'),
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

        $token = $this->getAccessToken();
        $query = array();
        if ($startDate) {
            $query['from'] = $startDate;
        }
        if ($endDate) {
            $query['to'] = $endDate;
        }

        $response = $this->call('smtp/emails/stats', $query, 'GET', $token);
        if (!$response || !is_array($response)) {
            return $object;
        }

        foreach ($response as $entry) {
            $date = isset($entry->date) ? (string) $entry->date : gmdate('Y-m-d');
            $map = array(
                'sent' => (int) ($entry->sent ?? 0),
                'opens' => (int) ($entry->opened ?? 0),
                'clicks' => (int) ($entry->clicked ?? 0),
                'bounces' => (int) ($entry->bounced ?? 0),
                'unsubscribes' => (int) ($entry->unsubscribed ?? 0),
                'spam' => (int) ($entry->spam ?? 0),
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

    protected function getAccessToken()
    {
        $response = $this->call('oauth/access_token', array(
            'grant_type' => 'client_credentials',
            'client_id' => trim((string) $this->params->get('sendpulseClientId')),
            'client_secret' => trim((string) $this->params->get('sendpulseClientSecret')),
        ), 'POST');

        if (empty($response->access_token)) {
            throw new Exception('SendPulse authentication failed', 401);
        }

        return (string) $response->access_token;
    }

    protected function call($function, $data = array(), $method = 'GET', $token = '')
    {
        $ch = curl_init();
        $endpoint = 'https://api.sendpulse.com/' . ltrim($function, '/');
        $headers = array('Content-Type: application/json');
        if ($token !== '') {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

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
            $error = isset($response->message) ? $response->message : 'SendPulse error occurred (HTTP ' . $status . ')';
            throw new Exception($error, $status);
        }

        return $response;
    }
}
