<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

JLoader::register('OttomoHelperHTML', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/html.php');

class OttomoHelperData
{
    public static function prepare(&$rows, $view = null, $task = null)
    {
        $mainframe = JFactory::getApplication();
        if ($mainframe->isAdmin()) {
            self::prepareAdminRows($rows, $view, $task);
        } else {
            self::prepareSiteRows($rows, $view, $task);
        }
    }

    private static function prepareAdminRows(&$rows, $view = null, $task = null)
    {
        foreach ($rows as $key => $row) {
            JFilterOutput::objectHTMLSafe($row, ENT_QUOTES, array('manifest'));
            $row->parity = ($key + 1) % 2;
            $rawPublished = property_exists($row, 'published') ? (int)$row->published : null;
            $rawStatus = property_exists($row, 'status') ? (int)$row->status : null;
            if (property_exists($row, 'published')) {
                $row->published = JHtml::_('jgrid.published', $row->published, $key);
            }
            if (property_exists($row, 'state')) {
                $row->state = JHtml::_('jgrid.published', $row->state, $key);
            }
            if (property_exists($row, 'status')) {
                // Status pills; active and pending toggle with a click, unsubscribed never does (the subscriber's opt-out)
                $pills = array(
                    0 => array('COM_OTTOMO_STATUS_PENDING', 'warning', 'subscribers.enable', 'COM_OTTOMO_ENABLE'),
                    1 => array('COM_OTTOMO_STATUS_SUBSCRIBED', 'success', 'subscribers.disable', 'COM_OTTOMO_DISABLE'),
                    2 => array('COM_OTTOMO_UNSUBSCRIBED', 'danger', '', ''),
                );
                $pill = isset($pills[$rawStatus]) ? $pills[$rawStatus] : $pills[0];
                if ($pill[2] !== '') {
                    $row->status = '<button type="button" class="otm-pill otm-pill--'.$pill[1].' otm-pill--toggle" data-status="'.(int)$rawStatus.'" onclick="return listItemTask(\'cb'.(int)$key.'\',\''.$pill[2].'\')" title="'.htmlspecialchars(JText::_($pill[3]), ENT_QUOTES, 'UTF-8').'">'.htmlspecialchars(JText::_($pill[0]), ENT_QUOTES, 'UTF-8').'</button>';
                } else {
                    $row->status = OttomoHelperHTML::pill(JText::_($pill[0]), $pill[1], 'data-status="'.(int)$rawStatus.'" title="'.htmlspecialchars(JText::_('COM_OTTOMO_UNSUBSCRIBED_DESC'), ENT_QUOTES, 'UTF-8').'"');
                }
            }
            if (!property_exists($row, 'title') && property_exists($row, 'name')) {
                $row->title = $row->name;
            }
            if (property_exists($row, 'topics')) {
                $model = OttomoModel::getInstance('Topics');
                $ids = $row->topics ? explode(',', (string)$row->topics) : array();
                JArrayHelper::toInteger($ids);
                $topics = array();
                foreach ($ids as $id) {
                    $model->setState('id', $id);
                    $topic = $model->getRow();
                    $topics[] = htmlspecialchars((string)$topic->name, ENT_QUOTES, 'UTF-8'); // the row was escaped before
                }
                $row->topics = implode(', ', $topics);
                if (!$row->topics) {
                    $row->topics = JText::_('COM_OTTOMO_NONE');
                }
            }
            switch ($view) {
                case 'newsletters':
                    $row->link = JHTML::link(JRoute::_('index.php?option=com_ottomo&view=newsletters&task=edit&id='.$row->id), $row->subject);
                    $sent = $rawPublished === 1;
                    JLoader::register('OttomoHelperTemplate', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/template.php');
                    $stat = isset($row->stats) && is_object($row->stats) ? $row->stats : (object)array('recipients' => 0, 'opens' => 0, 'clicks' => 0, 'openRate' => 0, 'clickRate' => 0);
                    $none = OttomoHelperHTML::date('');
                    $row->statusPill = $sent ? OttomoHelperHTML::pill(JText::_('COM_OTTOMO_STATUS_SENT'), 'success') : OttomoHelperHTML::pill(JText::_('COM_OTTOMO_STATUS_DRAFT'), 'neutral');
                    $row->sentDate = $sent && isset($row->sent) ? OttomoHelperHTML::date($row->sent) : $none;
                    $row->recipients = $stat->recipients ? '<span class="otm-num">'.number_format($stat->recipients).'</span>' : $none;
                    $row->openRate = $stat->recipients ? OttomoHelperHTML::rate($stat->openRate, $stat->opens) : $none;
                    $row->clickRate = $stat->recipients ? OttomoHelperHTML::rate($stat->clickRate, $stat->clicks) : $none;
                    $preview = JText::_('COM_OTTOMO_PREVIEW');
                    $duplicate = JText::_('COM_OTTOMO_DUPLICATE');
                    $row->actions = '<span class="otm-row-actions">'
                        .'<a href="'.JRoute::_('index.php?option=com_ottomo&task=newsletters.preview&id='.(int)$row->id).'" class="otm-icon-btn otm-preview-link" data-role="preview-button" data-id="'.(int)$row->id.'"'.($sent ? ' data-public-url="'.OttomoHelperTemplate::publicLink($row->id).'"' : '').' title="'.$preview.'">'.OttomoHelperHTML::icon('eye').'<span class="visually-hidden">'.$preview.'</span></a>'
                        .'<a href="'.JRoute::_('index.php?option=com_ottomo&task=newsletters.duplicate&id='.(int)$row->id.'&'.JSession::getFormToken().'=1').'" class="otm-icon-btn" data-role="duplicate" title="'.$duplicate.'">'.OttomoHelperHTML::icon('files').'<span class="visually-hidden">'.$duplicate.'</span></a>'
                        .'</span>';
                    break;
                case 'topics':
                    $row->link = JHTML::link(JRoute::_('index.php?option=com_ottomo&view=topics&task=edit&id='.$row->id), $row->name);
                    if (isset($row->subscribers)) {
                        // The active subscribers of this topic: the Subscribers list, filtered
                        $row->subscribersLink = '<a class="otm-count-link" data-role="topic-subscribers" href="'.JRoute::_('index.php?option=com_ottomo&view=subscribers&topic='.(int)$row->id.'&status=1&search=&limitstart=0').'" title="'.htmlspecialchars(JText::_('COM_OTTOMO_VIEW_ACTIVE_SUBSCRIBERS'), ENT_QUOTES, 'UTF-8').'">'
                            .'<span class="otm-num">'.number_format((int)$row->subscribers).'</span>'.OttomoHelperHTML::icon('people').'</a>';
                    }
                    break;
                case 'subscribers':
                    $row->link = JHTML::link(JRoute::_('index.php?option=com_ottomo&view=subscribers&task=edit&id='.$row->id), $row->name);
                    $row->createdDate = OttomoHelperHTML::date(isset($row->created) ? $row->created : '');
                    break;
                case 'templates':
                    JLoader::register('OttomoHelperTemplate', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/template.php');
                    $location = OttomoHelperTemplate::location($row->folder);
                    $row->custom = $location->custom; // in the site template's html/ottomo/ folder
                    // No preview image: the template itself, with its sample content
                    $preview = OttomoHelperTemplate::image($location, 'preview');
                    $thumbnail = OttomoHelperTemplate::image($location, 'thumbnail');
                    $row->preview = $preview !== '' ? JURI::root(true).'/'.$preview : JRoute::_('index.php?option=com_ottomo&view=designer&format=raw&id=0&template='.(int)$row->id);
                    $row->thumbnail = $thumbnail !== '' ? JURI::root(true).'/'.$thumbnail : '';
                    // Templates page cards are tall: the full-height preview image (sharp, top-aligned), else the thumbnail
                    $row->cardImage = $preview !== '' ? JURI::root(true).'/'.$preview : $row->thumbnail;
                    $row->cardImageFull = $preview !== ''; // false: a thumbnail, filling the card
                    $row->manifest = json_decode($row->manifest);
                    // The manifest is not escaped with the row: its values are, here
                    $e = function ($value) {
                        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
                    };
                    $date = isset($row->manifest->creationDate) ? (string)$row->manifest->creationDate : '';
                    $row->version = isset($row->manifest->version) ? $e($row->manifest->version) : '';
                    $row->date = $e($date);
                    $row->shortDate = $e(self::shortDate($date));
                    $row->author = isset($row->manifest->author) ? $e($row->manifest->author) : '';
                    $row->layout = self::templateLayout($row);
                    $row->layoutLabel = $row->layout !== '' ? JText::_('COM_OTTOMO_TEMPLATE_LAYOUT_'.strtoupper($row->layout)) : '';
                    $row->downloadLink = JRoute::_('index.php?option=com_ottomo&task=templates.download&id='.(int)$row->id.'&'.JSession::getFormToken().'=1');
                    $row->editLink = JRoute::_('index.php?option=com_ottomo&view=templates&task=edit&id='.$row->id);
                    break;
                case 'extension':
                    $row->title .= '<br/>';
                    $row->title .= JHTML::link($row->id, OttomoHelperHTML::icon('file-earmark-text').JText::_('COM_OTTOMO_INSERT_CONTENT'), 'class="otm-row-title--inline otm-insert-content-button"');
                    $row->title .= JHTML::link($row->id, OttomoHelperHTML::icon('link-45deg').JText::_('COM_OTTOMO_INSERT_LINK'), 'class="otm-row-title--inline otm-insert-link-button"');
                    if (isset($row->published)) {
                        $row->published = strip_tags($row->published, '<i>');
                    }
                    if (isset($row->state)) {
                        $row->state = strip_tags($row->state, '<i>');
                    }
                    break;
            }
        }
    }

    private static function prepareSiteRows(&$rows, $view = null, $task = null)
    {
        foreach ($rows as $key => $row) {
            JFilterOutput::objectHTMLSafe($row);
            switch ($view) {
                case 'archive':
                    $row->link = JRoute::_('index.php?option=com_ottomo&view=newsletters&id='.$row->id);
                    break;
            }
        }
    }

    /** A template's layout (manifest <layout>: 1col, 2col, 3col, sections), or '' */
    public static function templateLayout($row)
    {
        $layout = isset($row->manifest->layout) ? (string)$row->manifest->layout : '';
        if ($layout === '' && !empty($row->folder)) {
            JLoader::register('OttomoHelperTemplate', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/template.php');
            $file = OttomoHelperTemplate::location($row->folder)->path.'/manifest.xml';
            if (is_file($file)) {
                $xml = @simplexml_load_file($file);
                $layout = $xml && isset($xml->layout) ? (string)$xml->layout : '';
            }
        }
        return preg_match('/^(1col|2col|3col|sections)$/', $layout) ? $layout : '';
    }

    /**
     * Convert a free-form manifest creationDate ("November 20th, 2026", "March, 2016", "2026-11-20")
     * to a short, translated form ("Nov 20, 2026", "Mar 2016"). Unparseable values are returned as-is.
     *
     * @param  string $date
     * @return string
     */
    public static function shortDate($date)
    {
        $date = trim((string) $date);
        $clean = trim(preg_replace('/[\s,]+/', ' ', preg_replace('/\b(\d{1,2})(?:st|nd|rd|th)\b/i', '$1', $date)));

        $patterns = array(
            array('/^\d{4}-\d{2}-\d{2}$/', array('Y-m-d'), 'M j, Y'),
            array('/^[A-Za-z]+ \d{1,2} \d{4}$/', array('F j Y', 'M j Y'), 'M j, Y'),
            array('/^\d{1,2} [A-Za-z]+ \d{4}$/', array('j F Y', 'j M Y'), 'M j, Y'),
            array('/^[A-Za-z]+ \d{4}$/', array('F Y', 'M Y'), 'M Y'),
        );

        foreach ($patterns as $pattern) {
            if (!preg_match($pattern[0], $clean)) {
                continue;
            }
            foreach ($pattern[1] as $format) {
                $parsed = DateTime::createFromFormat('!'.$format, $clean, new DateTimeZone('UTC'));
                $errors = DateTime::getLastErrors();
                if ($parsed && (!$errors || (!$errors['warning_count'] && !$errors['error_count']))) {
                    return JFactory::getDate($parsed->format('Y-m-d'), 'UTC')->format($pattern[2], false, true);
                }
            }
        }

        return $date;
    }

    public static function checkHash($newsletterId, $subscriberId, $hash)
    {
        if (!$newsletterId || !$subscriberId || !$hash) {
            return false;
        }
        $model = OttomoModel::getInstance('Subscribers');
        $model->setState('id', $subscriberId);
        $subscriber = $model->getRow();
        if (is_null($subscriber) || !$subscriber->id || !hash_equals((string)self::getHash($newsletterId, $subscriber->id, $subscriber->email), (string)$hash)) {
            return false;
        }
        return true;
    }

    /** Signature of an external link wrapped for click tracking (keeps newsletters.track from being an open redirect) */
    public static function linkSignature($url)
    {
        return substr(JApplicationHelper::getHash('ottomo-link:'.$url), 0, 20);
    }

    public static function getHash($newslsetterID, $subscriberID, $subscriberEmail)
    {
        return JApplicationHelper::getHash($newslsetterID.$subscriberID.$subscriberEmail);
    }


    /** Subscriber fields usable as {{field}} or {{field|fallback}}. */
    public static $personalFields = array('name', 'firstname', 'lastname', 'email', 'company', 'position', 'city', 'state', 'country', 'zip', 'address', 'phone', 'mobile');

    /** Placeholder pattern: system fields (id, hash) and personal fields, with an optional fallback. */
    const TOKEN_PATTERN = '/\{\{(subscriber_id|subscriber_hash|name|firstname|lastname|email|company|position|city|state|country|zip|address|phone|mobile)(?:\|([^{}|]*))?\}\}/i';

    /** Value of one subscriber field (name parts derived; a name that is only the email address counts as empty). */
    public static function fieldValue($recipient, $field)
    {
        $field = preg_replace('/^subscriber_/', '', strtolower($field)); // {{subscriber_id}}, {{subscriber_hash}}
        $name = isset($recipient->name) ? trim((string)$recipient->name) : '';
        $email = isset($recipient->email) ? trim((string)$recipient->email) : '';
        if ($name !== '' && strcasecmp($name, $email) === 0) {
            $name = ''; // the table stores the email as name when none was given
        }
        switch ($field) {
            case 'name':
                return $name;
            case 'firstname':
                $parts = preg_split('/\s+/u', $name, 2);
                return $name === '' ? '' : $parts[0];
            case 'lastname':
                $parts = preg_split('/\s+/u', $name, 2);
                return isset($parts[1]) ? $parts[1] : '';
            default:
                return isset($recipient->$field) ? trim((string)$recipient->$field) : '';
        }
    }

    /** Value for one full placeholder token (fallback applied). */
    public static function tokenValue($token, $recipient, $escape = true)
    {
        if (!preg_match(self::TOKEN_PATTERN, $token, $match)) {
            return $token;
        }
        $value = self::fieldValue($recipient, $match[1]);
        if ($value === '' && isset($match[2])) {
            $value = $match[2];
        }
        return $escape ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : $value;
    }

    /** Replace every subscriber placeholder of $text with the recipient's values (HTML-escaped unless $escape is false). */
    public static function personalise($text, $recipient, $escape = true)
    {
        return preg_replace_callback(self::TOKEN_PATTERN, function ($match) use ($recipient, $escape) {
            return OttomoHelperData::tokenValue($match[0], $recipient, $escape);
        }, (string)$text);
    }

    /** Distinct placeholder tokens found in $text (for services that substitute variables themselves). */
    public static function personalisationTokens($text)
    {
        preg_match_all(self::TOKEN_PATTERN, (string)$text, $matches);
        return array_values(array_unique($matches[0]));
    }

    /** RFC 8058 one-click unsubscribe URL of a recipient for a campaign (absolute). */
    public static function oneClickUnsubscribeUrl($newsletterId, $recipient)
    {
        $hash = isset($recipient->hash) ? $recipient->hash : self::getHash($newsletterId, $recipient->id, $recipient->email);
        $path = 'index.php?option=com_ottomo&task=subscribers.oneclick&newsletter='.(int)$newsletterId.'&id='.(int)$recipient->id.'&hash='.$hash;
        return rtrim(JURI::root(), '/').'/'.$path;
    }

    /**
     * Feedback-ID (Gmail Postmaster Tools: spam complaint rates per campaign): campaign:topic:newsletter:site,
     * the fields Gmail expects (CampaignID:CustomerID:MailTypeID:SenderID).
     */
    public static function feedbackId($newsletterId, $topics = array())
    {
        $topics = array_values(array_filter(array_map('intval', (array)$topics)));
        $site = preg_replace('/[^a-z0-9.-]/', '', strtolower((string)JUri::getInstance(JUri::root())->getHost()));
        return (int)$newsletterId.':'.($topics ? $topics[0] : 0).':newsletter:'.($site !== '' ? $site : 'ottomo');
    }

    /** The headers of a campaign email: Feedback-ID and List-Unsubscribe (campaign sends only, not test sends). */
    public static function campaignHeaders($newsletterId, $recipient, $topics = array())
    {
        if (empty($recipient->id) || !is_numeric($recipient->id)) {
            return array(); // a test send (no subscriber)
        }
        return array('Feedback-ID' => self::feedbackId($newsletterId, $topics)) + self::listUnsubscribeHeaders($newsletterId, $recipient);
    }

    /** List-Unsubscribe headers (RFC 2369 + RFC 8058) for a recipient, or an empty array when disabled. */
    public static function listUnsubscribeHeaders($newsletterId, $recipient)
    {
        JLoader::register('OttomoHelperSettings', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/settings.php');
        if (!OttomoHelperSettings::enabled('oneClickUnsubscribe') || empty($recipient->id) || !is_numeric($recipient->id)) {
            return array(); // disabled, or a test send (no subscriber)
        }
        return array(
            'List-Unsubscribe' => '<'.self::oneClickUnsubscribeUrl($newsletterId, $recipient).'>',
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        );
    }

    /**
     * For services that substitute variables themselves (batch sends): replace each placeholder of the HTML body,
     * the plain text and the subject with a service variable. HTML gets an escaped variable, text/subject a raw one.
     *
     * @param  string $html     HTML body (modified)
     * @param  string $text     plain text body (modified)
     * @param  string $subject  subject (modified)
     * @param  string $syntax   sprintf pattern of a variable, e.g. '%%recipient.%s%%' (Mailgun) or '{{params.%s}}' (Brevo)
     * @return array  variable name => array(token, escape) to feed serviceValues()
     */
    public static function serviceTemplate(&$html, &$text, &$subject, $syntax)
    {
        $variables = array();
        $index = 0;
        foreach (self::personalisationTokens($html.' '.$text.' '.$subject) as $token) {
            $htmlVar = 'ottomo'.$index;
            $textVar = 'ottomoT'.$index;
            $variables[$htmlVar] = array($token, true);
            $variables[$textVar] = array($token, false);
            $html = str_ireplace($token, sprintf($syntax, $htmlVar), $html);
            $text = str_ireplace($token, sprintf($syntax, $textVar), $text);
            $subject = str_ireplace($token, sprintf($syntax, $textVar), $subject);
            $index++;
        }
        return $variables;
    }

    /** Per-recipient values of the variables returned by serviceTemplate(). */
    public static function serviceValues($variables, $recipient)
    {
        $values = array();
        foreach ($variables as $name => $definition) {
            $values[$name] = self::tokenValue($definition[0], $recipient, $definition[1]);
        }
        return $values;
    }
}
