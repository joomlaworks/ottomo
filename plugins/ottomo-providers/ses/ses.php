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
class plgOttomoprovidersSES extends JPlugin
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
        $from = $mailer->FromName
            ? sprintf('%s <%s>', $mailer->FromName, $mailer->From)
            : $mailer->From;

        // One API call per recipient, paced to the account's maximum send rate (emails per second)
        $interval = 1 / max(1.0, $this->maxSendRate());
        $failed = array();
        $lastError = null;

        foreach ($recipients as $recipient) {
            $started = microtime(true);
            // Personalisation: {{field}} / {{field|fallback}}
            $body = OttomoHelperData::personalise((string)$mailer->Body, $recipient);
            $subject = OttomoHelperData::personalise((string)$mailer->Subject, $recipient, false);
            $text = OttomoHelperData::personalise((string)$mailer->AltBody, $recipient, false);

            $payload = array(
                'FromEmailAddress' => $from,
                'Destination' => array(
                    'ToAddresses' => array($recipient->email),
                ),
                'Content' => array(
                    'Simple' => array(
                        'Subject' => array(
                            'Data' => $subject,
                            'Charset' => 'UTF-8',
                        ),
                        'Body' => array(
                            'Html' => array(
                                'Data' => $body,
                                'Charset' => 'UTF-8',
                            ),
                        ),
                    ),
                ),
            );

            if ($text !== '') {
                $payload['Content']['Simple']['Body']['Text'] = array(
                    'Data' => $text,
                    'Charset' => 'UTF-8',
                );
            }

            // One-click unsubscribe (RFC 8058): SES v2 Simple content headers
            foreach (OttomoHelperData::campaignHeaders($newsletterId, $recipient, $topics) as $name => $value) {
                $payload['Content']['Simple']['Headers'][] = array('Name' => $name, 'Value' => $value);
            }

            $replyTo = $params->get('replyTo');
            if (!empty($replyTo)) {
                $payload['ReplyToAddresses'] = array($replyTo);
            }

            try {
                $this->sendWithRetry(json_encode($payload));
            } catch (Exception $e) {
                $failed[$recipient->id] = $recipient->email;
                $lastError = $e;
            }

            $wait = $interval - (microtime(true) - $started);
            if ($wait > 0) {
                usleep((int)round($wait * 1000000));
            }
        }

        // Nothing sent: the batch failed as a whole (credentials, sandbox, daily quota...)
        if ($lastError && count($failed) === count($recipients)) {
            throw $lastError;
        }
        return $failed;
    }

    /** At most what SES accepts in about 20 seconds at the account's rate, so a batch ends within PHP's time limit. */
    public function batchSize($limit)
    {
        return max(1, min((int)$limit, (int)floor($this->maxSendRate() * 20)));
    }

    /**
     * One email, retried after a pause when SES throttles it (HTTP 429 "Maximum sending rate exceeded"),
     * up to 3 times. The daily quota ("Daily message quota exceeded") is not retried: it lasts until tomorrow.
     */
    protected function sendWithRetry($payload)
    {
        for ($attempt = 0; ; $attempt++) {
            try {
                return $this->request('POST', '/v2/email/outbound-emails', $payload, 'application/json');
            } catch (Exception $e) {
                if ((int)$e->getCode() !== 429 || stripos($e->getMessage(), 'daily') !== false || $attempt >= 3) {
                    throw $e;
                }
                usleep((1 << $attempt) * 1000000); // 1 s, 2 s, 4 s
            }
        }
    }

    /** The account's maximum send rate (SendQuota.MaxSendRate, emails per second), read once per request; 1 if unknown. */
    protected function maxSendRate()
    {
        static $rate = null;
        if ($rate === null) {
            $rate = 1.0;
            try {
                $account = json_decode($this->request('GET', '/v2/email/account', '', 'application/json'));
                if (isset($account->SendQuota->MaxSendRate) && (float)$account->SendQuota->MaxSendRate > 0) {
                    $rate = (float)$account->SendQuota->MaxSendRate;
                }
            } catch (Exception $e) {
                // Unknown: one email per second is within every account's limits
            }
        }
        return $rate;
    }

    public function verify()
    {
        $accessKey = trim((string)$this->params->get('sesAccessKey'));
        $secretKey = trim((string)$this->params->get('sesSecretKey'));
        if ($accessKey === '' || $secretKey === '') {
            return false;
        }

        try {
            $this->request('GET', '/v2/email/account', '', 'application/json');
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function statistics($startDate, $endDate, $topicId)
    {
        $columns = array(
            JText::_('COM_OTTOMO_DATE'),
            JText::_('COM_OTTOMO_REQUESTS'),
            JText::_('COM_OTTOMO_BOUNCES'),
            JText::_('COM_OTTOMO_REJECTS'),
            JText::_('COM_OTTOMO_SPAM_REPORTS'),
        );
        $properties = array('DeliveryAttempts', 'Bounces', 'Rejects', 'Complaints');
        $colors = array('#b8b8b8', '#e87e04', '#c74484', '#e74b3c');

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

        // GetSendStatistics is available on SES v1 Query API (Action=GetSendStatistics)
        $xmlBody = $this->request(
            'POST',
            '/',
            http_build_query(array('Action' => 'GetSendStatistics', 'Version' => '2010-12-01')),
            'application/x-www-form-urlencoded'
        );

        $xml = @simplexml_load_string($xmlBody);
        if (!$xml || !isset($xml->GetSendStatisticsResult->SendDataPoints->member)) {
            return $object;
        }

        foreach ($xml->GetSendStatisticsResult->SendDataPoints->member as $entry) {
            $date = JFactory::getDate((string)$entry->Timestamp)->toSql();
            $row = array($date);
            foreach ($properties as $property) {
                $value = (int)$entry->$property;
                $row[] = $value;
                $object->stats->$property->total += $value;
            }
            $object->chart[] = $row;
        }

        foreach ($object->stats as $key => $stat) {
            if ($key !== 'DeliveryAttempts' && $object->stats->DeliveryAttempts->total > 0) {
                $stat->percentage = ceil(($stat->total / $object->stats->DeliveryAttempts->total) * 100);
            }
        }

        array_unshift($object->chart, $columns);
        return $object;
    }

    /**
     * Sign and execute an Amazon SES API request using AWS Signature Version 4.
     *
     * @param  string $method
     * @param  string $uri
     * @param  string $payload
     * @param  string $contentType
     * @return string
     * @throws Exception
     */
    protected function request($method, $uri, $payload = '', $contentType = 'application/json')
    {
        $accessKey = trim((string)$this->params->get('sesAccessKey'));
        $secretKey = trim((string)$this->params->get('sesSecretKey'));
        $region = trim((string)$this->params->get('sesRegion', 'us-east-1'));
        if (!preg_match('/^[a-z]{2}(-[a-z]+)+-[0-9]$/', $region)) {
            $region = 'us-east-1'; // a region code (eu-west-1): nothing else ends up in the API host name
        }

        $service = 'ses';
        $host = 'email.'.$region.'.amazonaws.com';
        $endpoint = 'https://'.$host.$uri;

        $amzDate = gmdate('Ymd\THis\Z');
        $dateStamp = gmdate('Ymd');
        $payloadHash = hash('sha256', $payload);

        $canonicalHeaders = "content-type:{$contentType}\nhost:{$host}\nx-amz-date:{$amzDate}\n";
        $signedHeaders = 'content-type;host;x-amz-date';

        $canonicalRequest = implode("\n", array(
            strtoupper($method),
            $uri,
            '',
            $canonicalHeaders,
            $signedHeaders,
            $payloadHash,
        ));

        $algorithm = 'AWS4-HMAC-SHA256';
        $credentialScope = "{$dateStamp}/{$region}/{$service}/aws4_request";
        $stringToSign = implode("\n", array(
            $algorithm,
            $amzDate,
            $credentialScope,
            hash('sha256', $canonicalRequest),
        ));

        $kDate = hash_hmac('sha256', $dateStamp, 'AWS4'.$secretKey, true);
        $kRegion = hash_hmac('sha256', $region, $kDate, true);
        $kService = hash_hmac('sha256', $service, $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);
        $signature = hash_hmac('sha256', $stringToSign, $kSigning);

        $authorization = "{$algorithm} Credential={$accessKey}/{$credentialScope}, SignedHeaders={$signedHeaders}, Signature={$signature}";

        $headers = array(
            'Content-Type: '.$contentType,
            'Host: '.$host,
            'X-Amz-Date: '.$amzDate,
            'Authorization: '.$authorization,
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        if (strtoupper($method) !== 'GET') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
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
        curl_close($ch);

        if ($status < 200 || $status >= 300) {
            $decoded = json_decode($result);
            $message = isset($decoded->message) ? $decoded->message : (isset($decoded->Message) ? $decoded->Message : 'Amazon SES error (HTTP '.$status.')');
            throw new Exception($message, $status);
        }

        return $result;
    }
}
