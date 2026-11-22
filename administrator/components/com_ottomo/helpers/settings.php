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

require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/compat.php';

/**
 * Ottomo settings with their defaults, and the sending connection check.
 * Options added after a site was set up are not stored yet: their default applies (new options default to on).
 */
class OttomoHelperSettings
{
    /** Defaults of options whose absence must not mean "off". */
    protected static $defaults = array(
        'oneClickUnsubscribe' => 1,
        'complianceFooter'    => 1,
        'companyAddress'      => '',
        'captcha'             => '',   // '' = the site's default captcha, '0' = none, else a captcha plugin
        'honeypot'            => 1,
        'subscribePage'       => '',
        'whyMessage'          => '',
        'siteName'            => '',
        'preflightRequired'   => 1,
        'dashboardHealth'     => 1,
        'limit'               => 50,
    );

    public static function params()
    {
        return ComponentHelper::getParams('com_ottomo');
    }

    public static function get($name, $default = null)
    {
        if ($default === null && array_key_exists($name, self::$defaults)) {
            $default = self::$defaults[$name];
        }
        return self::params()->get($name, $default);
    }

    /**
     * A campaign's sender: its own name and address, else Settings > Sending, else Joomla's mail settings
     * (the campaign's fields are optional). Without a campaign: the default sender.
     */
    public static function sender($row = null)
    {
        $application = Factory::getApplication();
        $name = $row ? trim((string)$row->senderName) : '';
        $email = $row ? trim((string)$row->senderEmail) : '';
        if ($name === '') {
            $name = trim((string)self::get('senderName'));
            $name = $name !== '' ? $name : (string)$application->get('fromname');
        }
        if ($email === '') {
            $email = trim((string)self::get('senderEmail'));
            $email = $email !== '' ? $email : (string)$application->get('mailfrom');
        }
        return (object)array('name' => $name, 'email' => $email);
    }

    /** The site name in emails: Settings > Compliance, or Joomla's site name */
    public static function siteName()
    {
        $name = trim((string)self::get('siteName'));
        return $name !== '' ? $name : (string)OttomoCompat::config('sitename');
    }

    public static function enabled($name)
    {
        return (bool)(int)self::get($name);
    }

    /** Captcha plugin name to use for signup forms ('' when none). */
    public static function captcha()
    {
        $captcha = (string)self::get('captcha');
        if ($captcha === '') {
            $captcha = (string)OttomoCompat::config('captcha', '0');
        }
        return ($captcha === '0' || !PluginHelper::isEnabled('captcha', $captcha)) ? '' : $captcha;
    }

    /**
     * Check the saved sending settings.
     *
     * @param  bool $useCache  reuse a result from the last 10 minutes (dashboard)
     * @return stdClass  {level: ok|warning|error, mailer, message}
     */
    public static function checkConnection($useCache = true)
    {
        $session = OttomoCompat::session();
        $params = self::params();
        $mailer = (string)$params->get('mailer', 'mail');
        $fingerprint = md5(json_encode($params->toArray()));
        $cached = $session->get('com_ottomo.connection', null);
        if ($useCache && is_object($cached) && $cached->fingerprint === $fingerprint && $cached->time > time() - 600) {
            return $cached->result;
        }

        $result = new stdClass();
        $result->mailer = $mailer;
        $result->level = 'ok';
        $result->message = Text::sprintf('COM_OTTOMO_CONNECTION_OK', self::mailerLabel($mailer));

        switch ($mailer) {
            case 'mail':
                if (!function_exists('mail')) {
                    $result->level = 'error';
                    $result->message = Text::_('COM_OTTOMO_CONNECTION_MAIL_UNAVAILABLE');
                } else {
                    $result->level = 'warning';
                    $result->message = Text::_('COM_OTTOMO_CONNECTION_MAIL_WARNING');
                }
                break;

            case 'sendmail':
                $path = trim((string)$params->get('sendmail', OttomoCompat::config('sendmail', '/usr/sbin/sendmail')));
                $binary = preg_replace('/\s.*$/', '', $path);
                if ($binary === '' || !@is_executable($binary)) {
                    $result->level = 'error';
                    $result->message = Text::sprintf('COM_OTTOMO_CONNECTION_SENDMAIL_MISSING', $binary);
                }
                break;

            case 'smtp':
                $mail = OttomoCompat::mailer();
                $mail = clone $mail;
                $auth = $params->get('smtpauth') ? true : null;
                $mail->useSMTP($auth, $params->get('smtphost'), $params->get('smtpuser'), $params->get('smtppass'), $params->get('smtpsecure'), $params->get('smtpport'));
                $mail->Timeout = 5;
                $connected = false;
                try {
                    $connected = $mail->SmtpConnect();
                } catch (Exception $e) {
                    $mail->ErrorInfo = $e->getMessage();
                }
                if ($connected) {
                    $mail->SmtpClose();
                } else {
                    $result->level = 'error';
                    $result->message = Text::sprintf('COM_OTTOMO_CONNECTION_SMTP_FAILED', $params->get('smtphost').':'.$params->get('smtpport'), strip_tags((string)$mail->ErrorInfo));
                }
                break;

            default:
                PluginHelper::importPlugin('ottomo-providers', $mailer);
                $answers = OttomoCompat::trigger('onOttomoVerify');
                if (!count($answers) || $answers[0] !== true) {
                    $result->level = 'error';
                    $result->message = Text::sprintf('COM_OTTOMO_CONNECTION_PROVIDER_FAILED', self::mailerLabel($mailer));
                }
        }

        $entry = new stdClass();
        $entry->fingerprint = $fingerprint;
        $entry->time = time();
        $entry->result = $result;
        $session->set('com_ottomo.connection', $entry);

        return $result;
    }

    /** Free mailbox providers: their DMARC policy rejects or junks bulk email sent "from" them by other servers. */
    protected static $freeDomains = array('gmail.com', 'googlemail.com', 'yahoo.com', 'ymail.com', 'rocketmail.com', 'aol.com', 'outlook.com', 'hotmail.com', 'live.com', 'msn.com', 'icloud.com', 'me.com', 'mac.com', 'gmx.com', 'gmx.net', 'gmx.de', 'web.de', 'mail.com', 'proton.me', 'protonmail.com', 'yandex.com', 'yandex.ru', 'mail.ru', 'zoho.com', 'libero.it', 'orange.fr', 'free.fr');

    /** Usual DKIM selectors of mailbox hosts and mail services (DKIM records can only be found by their selector). */
    protected static $dkimSelectors = array('google', 'default', 'selector1', 'selector2', 'k1', 'k2', 'k3', 's1', 's2', 'smtp', 'mx', 'mail', 'dkim', 'mailjet', 'brevo1', 'brevo2', 'mail1', 'sendpulse', 'sig1', 'pm', 'zoho', 'everlytickey1', 'mandrill', 'resend', 'key1', 'key2');

    /**
     * Sender authentication of an address's domain, as Gmail, Yahoo and Outlook require of bulk senders:
     * SPF, DKIM (at the usual selectors) and DMARC records; free mailbox domains can't be used at all.
     * Cached for 10 minutes per domain (DNS lookups).
     *
     * @return stdClass  {domain, level: ok|warning|error, checks: [{level, message}]}
     */
    public static function senderAuthentication($email)
    {
        $email = strtolower(trim((string)$email));
        $domain = strpos($email, '@') !== false ? substr(strrchr($email, '@'), 1) : '';
        $result = new stdClass();
        $result->domain = $domain;
        $result->level = 'ok';
        $result->checks = array();
        if ($domain === '' || !preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/', $domain)) {
            return $result;
        }
        $session = OttomoCompat::session();
        $cached = $session->get('com_ottomo.senderAuth.'.$domain, null);
        if (is_object($cached) && $cached->time > time() - 600) {
            return $cached->result;
        }

        $add = function ($level, $message) use ($result) {
            $result->checks[] = (object)array('level' => $level, 'message' => $message);
            $rank = array('ok' => 0, 'warning' => 1, 'error' => 2);
            if ($rank[$level] > $rank[$result->level]) {
                $result->level = $level;
            }
        };

        if (in_array($domain, self::$freeDomains, true)) {
            $add('error', Text::sprintf('COM_OTTOMO_AUTH_FREE_DOMAIN', $domain));
        } elseif (!function_exists('dns_get_record')) {
            $add('warning', Text::_('COM_OTTOMO_AUTH_UNAVAILABLE'));
        } elseif (!@checkdnsrr($domain, 'MX') && !@checkdnsrr($domain, 'A')) {
            $add('error', Text::sprintf('COM_OTTOMO_AUTH_NO_DOMAIN', $domain)); // a typo, or a domain without DNS
        } else {
            $txt = function ($host) {
                $records = @dns_get_record($host, DNS_TXT);
                $values = array();
                foreach ((array)$records as $record) {
                    if (isset($record['txt'])) {
                        $values[] = isset($record['entries']) ? implode('', $record['entries']) : $record['txt'];
                    }
                }
                return $values;
            };
            $find = function (array $values, $prefix) {
                foreach ($values as $value) {
                    if (stripos(ltrim($value), $prefix) === 0) {
                        return $value;
                    }
                }
                return null;
            };

            // SPF: a v=spf1 TXT record on the domain
            $spf = $find($txt($domain), 'v=spf1') !== null;
            $add($spf ? 'ok' : 'error', Text::sprintf($spf ? 'COM_OTTOMO_AUTH_SPF_OK' : 'COM_OTTOMO_AUTH_SPF_MISSING', $domain));

            // DKIM: a public key (TXT with a non-empty p=; an empty one means revoked) or a delegation
            // (CNAME, e.g. Amazon SES, SendGrid) at one of the usual selectors
            $selector = null;
            foreach (self::$dkimSelectors as $candidate) {
                $host = $candidate.'._domainkey.'.$domain;
                $key = false;
                foreach ($txt($host) as $value) {
                    if (preg_match('/(?:^|;)\s*p\s*=\s*[A-Za-z0-9+\/]{16,}/', $value)) {
                        $key = true;
                        break;
                    }
                }
                if ($key || @dns_get_record($host, DNS_CNAME)) {
                    $selector = $candidate;
                    break;
                }
            }
            if ($selector !== null) {
                $add('ok', Text::sprintf('COM_OTTOMO_AUTH_DKIM_OK', $domain, $selector));
            } else {
                $add('warning', Text::sprintf('COM_OTTOMO_AUTH_DKIM_UNKNOWN', $domain));
            }

            // DMARC: a v=DMARC1 TXT record on _dmarc.<domain> (p=none is enough to start)
            $dmarc = $find($txt('_dmarc.'.$domain), 'v=DMARC1');
            if ($dmarc !== null) {
                $policy = preg_match('/(?:^|;)\s*p\s*=\s*([a-z]+)/i', $dmarc, $match) ? strtolower($match[1]) : 'none';
                $add('ok', Text::sprintf('COM_OTTOMO_AUTH_DMARC_OK', $domain, $policy));
            } else {
                $add('error', Text::sprintf('COM_OTTOMO_AUTH_DMARC_MISSING', $domain));
            }
        }

        $entry = new stdClass();
        $entry->time = time();
        $entry->result = $result;
        $session->set('com_ottomo.senderAuth.'.$domain, $entry);
        return $result;
    }

    public static function mailerLabel($mailer)
    {
        $core = array('mail' => 'COM_OTTOMO_PHP_MAIL', 'sendmail' => 'COM_OTTOMO_SENDMAIL', 'smtp' => 'COM_OTTOMO_SMTP');
        if (isset($core[$mailer])) {
            return Text::_($core[$mailer]);
        }
        $key = 'PLG_OTTOMO_'.strtoupper($mailer).'_LABEL';
        return OttomoCompat::language()->hasKey($key) ? Text::_($key) : ucfirst($mailer);
    }
}
