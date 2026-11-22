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
class plgOttomoprovidersMailgun extends JPlugin
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
        $domain = trim((string) $this->params->get('mailgunDomain'));
        JLoader::register('OttomoHelperData', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/data.php');
        // Personalisation: every subscriber placeholder ({{firstname}}, {{email}}…) becomes a Mailgun recipient variable.
        // (Mailgun adds its own List-Unsubscribe header when its unsubscribe tracking is on: none is set here.)
        $body = (string) $mailer->Body;
        $text = (string) $mailer->AltBody;
        $subject = (string) $mailer->Subject;
        $variables = OttomoHelperData::serviceTemplate($body, $text, $subject, '%%recipient.%s%%');

        $from = self::address($mailer->From, $mailer->FromName);

        $toList = array();
        $recipientVars = array();
        foreach ($recipients as $recipient) {
            $toList[] = self::address($recipient->email, !empty($recipient->name) ? $recipient->name : '');
            // always at least one variable: it makes Mailgun send one message per recipient (no shared To:)
            $recipientVars[$recipient->email] = array('id' => (string) $recipient->id) + OttomoHelperData::serviceValues($variables, $recipient);
        }

        $postFields = array(
            'from' => $from,
            'to' => implode(', ', $toList),
            'subject' => $subject,
            'html' => $body,
            'recipient-variables' => json_encode($recipientVars),
        );

        if ($text !== '') {
            $postFields['text'] = $text;
        }

        $replyTo = $params->get('replyTo');
        if (!empty($replyTo)) {
            $postFields['h:Reply-To'] = $replyTo;
        }

        if (is_array($topics) && count($topics)) {
            // Mailgun accepts multiple o:tag fields, use first topic or comma-joined tag
            $postFields['o:tag'] = 'OttomoTopic' . (int) $topics[0];
        }

        // Feedback-ID (Gmail Postmaster Tools), campaign sends only
        if ($newsletterId) {
            $postFields['h:Feedback-ID'] = OttomoHelperData::feedbackId($newsletterId, $topics);
        }

        $this->call($domain . '/messages', $postFields, 'POST');

        return array(); // one call for the batch: it throws when the batch fails
    }

    public function verify()
    {
        $apiKey = trim((string) $this->params->get('mailgunApiKey'));
        $domain = trim((string) $this->params->get('mailgunDomain'));
        if ($apiKey === '' || $domain === '') {
            return false;
        }

        try {
            $this->call('domains/' . $domain, array(), 'GET');
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function statistics($startDate, $endDate, $topicId)
    {
        $domain = trim((string) $this->params->get('mailgunDomain'));
        if (!$startDate) {
            $start = new DateTime();
            $start->sub(new DateInterval('P7D'));
            $startDate = $start->format('Y-m-d');
        }
        if (!$endDate) {
            $endDate = gmdate('Y-m-d');
        }

        $query = http_build_query(array(
            'start' => $startDate . 'T00:00:00Z',
            'end' => $endDate . 'T23:59:59Z',
            'resolution' => 'day',
        ));
        $events = '&event=accepted&event=opened&event=clicked&event=delivered&event=failed&event=unsubscribed&event=complained';

        $response = $this->call($domain . '/stats/total?' . $query . $events, array(), 'GET');

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
        $properties = array('accepted', 'opened', 'clicked', 'delivered', 'failed', 'unsubscribed', 'complained');
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

        if (!$response || empty($response->stats) || !is_array($response->stats)) {
            return $object;
        }

        foreach ($response->stats as $entry) {
            $date = gmdate('Y-m-d', strtotime((string) $entry->time));
            $row = array($date);
            foreach ($properties as $property) {
                $val = 0;
                if (isset($entry->$property)) {
                    if (is_object($entry->$property) && isset($entry->$property->total)) {
                        $val = (int) $entry->$property->total;
                    } elseif ($property === 'failed' && isset($entry->failed->permanent->total)) {
                        $val = (int) $entry->failed->permanent->total + (int) ($entry->failed->temporary->total ?? 0);
                    }
                }
                $row[] = $val;
                $object->stats->$property->total += $val;
            }
            $object->chart[] = $row;
        }

        foreach ($object->stats as $key => $stat) {
            if ($key !== 'accepted' && $object->stats->accepted->total > 0) {
                $stat->percentage = ceil(($stat->total / $object->stats->accepted->total) * 100);
            }
        }

        array_unshift($object->chart, $columns);
        return $object;
    }

    protected function call($path, $data = array(), $method = 'GET')
    {
        $region = $this->params->get('mailgunRegion', 'us');
        $base = ($region === 'eu') ? 'https://api.eu.mailgun.net/v3/' : 'https://api.mailgun.net/v3/';
        $endpoint = $base . ltrim($path, '/');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_USERPWD, 'api:' . trim((string) $this->params->get('mailgunApiKey')));
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
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

        if ($status < 200 || $status >= 300) {
            $error = isset($response->message) ? $response->message : 'Mailgun error occurred (HTTP ' . $status . ')';
            throw new Exception($error, $status);
        }

        return $response;
    }

    /**
     * "Name" <email>, the name as an RFC 5322 quoted string: a subscriber's name (typed in the signup form) can never
     * add an address to the comma-separated "to" list.
     */
    protected static function address($email, $name = '')
    {
        $name = trim(preg_replace('/[\x00-\x1F\x7F]+/', ' ', (string)$name));
        if ($name === '') {
            return (string)$email;
        }
        return '"'.addcslashes($name, '"\\').'" <'.$email.'>';
    }
}
