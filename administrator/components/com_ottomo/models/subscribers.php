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
use Joomla\CMS\Mail\MailHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/compat.php';

class OttomoModelSubscribers extends OttomoModel
{
    const STATUS_PENDING = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_UNSUBSCRIBED = 2;

    public function getList()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select($db->quoteName('subscriber').'.*');
        $serverType = method_exists($db, 'getServerType') ? $db->getServerType() : $db->name;
        if ($serverType === 'postgresql') {
            $query->select('STRING_AGG(CAST('.$db->quoteName('xref.topicID').' AS TEXT), '.$db->quote(',').') AS '.$db->quoteName('topics'));
        } else {
            $query->select('GROUP_CONCAT('.$db->quoteName('xref.topicID').' SEPARATOR ",") AS '.$db->quoteName('topics'));
        }
        $query->from($db->quoteName('#__ottomo_subscribers').' AS '.$db->quoteName('subscriber'));
        $query->leftJoin($db->quoteName('#__ottomo_subscribers_xref').' AS '.$db->quoteName('xref').' ON '.$db->quoteName('xref.subscriberID').' = '.$db->quoteName('subscriber.id'));
        $this->setQueryConditions($query);
        $query->group($db->quoteName('subscriber.id'));
        $db->setQuery($query, (int)$this->getState('limitstart'), (int)$this->getState('limit'));
        $rows = $db->loadObjectList();
        return $rows;
    }

    public function getListCount()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('COUNT(DISTINCT '.$db->quoteName('subscriber.id').')');
        $query->from($db->quoteName('#__ottomo_subscribers').' AS '.$db->quoteName('subscriber'));
        $query->leftJoin($db->quoteName('#__ottomo_subscribers_xref').' AS '.$db->quoteName('xref').' ON '.$db->quoteName('xref.subscriberID').' = '.$db->quoteName('subscriber.id'));
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
            if (MailHelper::isEmailAddress($search)) {
                $query->where($db->quoteName('subscriber.email').' = '.$db->quote($search));
            } else {
                $search = $db->quote('%'.$db->escape($search, true).'%');
                $query->where('( '.$db->quoteName('subscriber.name').' LIKE '.$search.' OR '.$db->quoteName('subscriber.email').' LIKE '.$search.' )');
            }
        }
        if ($this->getState('topic')) {
            if (is_array($this->getState('topic'))) {
                $query->where($db->quoteName('xref.topicID').' IN ('.implode(',', array_map('intval', $this->getState('topic'))).')');
            } else {
                $query->where($db->quoteName('xref.topicID').' = '.(int)$this->getState('topic'));
            }
        }
        if (is_array($this->getState('ids'))) {
            $ids = array_map('intval', $this->getState('ids'));
            $query->where($db->quoteName('subscriber.id').' IN ('.implode(',', count($ids) ? $ids : array(0)).')');
        }
        if (is_numeric($this->getState('status')) && $this->getState('status') != -1) {
            $query->where($db->quoteName('subscriber.status').' = '.(int)$this->getState('status'));
        }
        if ($this->getState('ordering') && $this->getState('orderingDir')) {
            $query->order($db->quoteName($this->getState('ordering')).' '.(strtoupper($this->getState('orderingDir')) === 'ASC' ? 'ASC' : 'DESC'));
        }

    }

    public function validate()
    {
        $db = $this->getDbo();
        $data = $this->getState('data');

        //Check that we are getting data from a module
        if (!isset($data['module']) || !$data['module']) {
            $this->setError(Text::_('COM_OTTOMO_AN_ERROR_OCCURED'));
            return false;
        }
        //Get module parameters
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName('#__modules'));
        $query->where($db->quoteName('published').' = 1');
        $query->where($db->quoteName('id').' = '.(int)$data['module']);
        $query->where($db->quoteName('module').' = '.$db->quote('mod_ottomo')); // the site signup module only
        $query->where($db->quoteName('client_id').' = 0');
        $db->setQuery($query, 0, 1);
        $module = $db->loadObject();
        if (is_null($module)) {
            $this->setError(Text::_('COM_OTTOMO_AN_ERROR_OCCURED'));
            return false;
        }
        $params = new Registry($module->params);

        //Check that at least one topic has been set
        if (!isset($data['topics'])) {
            $this->setError(Text::_('COM_OTTOMO_NO_TOPIC_SELECTED'));
            return false;
        }
        $data['topics'] = (array)$data['topics'];
        ArrayHelper::toInteger($data['topics']);
        // Visitors sign up to public topics only (never a private list or an unpublished topic, whatever is posted)
        if (!empty($data['topics'])) {
            $topics = OttomoModel::getInstance('Topics');
            $topics->setState('published', '1');
            $topics->setState('public', true);
            $topics->setState('id', $data['topics']);
            $data['topics'] = array_values(array_map(function ($topic) {
                return (int)$topic->id;
            }, $topics->getList()));
        }
        if (empty($data['topics'])) {
            $this->setError(Text::_('COM_OTTOMO_NO_TOPIC_SELECTED'));
            return false;
        }
        $this->setState('data', $data); // save() uses the checked topics

        //Check for valid email
        if (!MailHelper::isEmailAddress($data['email'])) {
            $this->setError(Text::_('COM_OTTOMO_PLEASE_ENTER_A_VALID_EMAIL_ADDRESS'));
            return false;
        }

        //Check that the email is not already used (someone who unsubscribed may sign up again: same record)
        $query = $db->getQuery(true);
        $query->select(array($db->quoteName('id'), $db->quoteName('status')));
        $query->from($db->quoteName('#__ottomo_subscribers'));
        $query->where($db->quoteName('email').' = '.$db->quote($data['email']));
        $db->setQuery($query, 0, 1);
        $existing = $db->loadObject();
        if ($existing && (int)$existing->status !== self::STATUS_UNSUBSCRIBED) {
            $this->setError(Text::_('COM_OTTOMO_THIS_EMAIL_IS_ALREADY_USED'));
            return false;
        }
        if ($existing) {
            $data['id'] = (int)$existing->id;
            $this->setState('data', $data);
        }

        //Check rest fields
        if ($params->get('subscriberName') == '2' && StringHelper::trim($data['name']) == '') {
            $this->setError(Text::_('COM_OTTOMO_NAME_IS_REQUIRED'));
            return false;
        }
        if ($params->get('subscriberBirthday') == '2' && StringHelper::trim($data['birthday']) == '') {
            $this->setError(Text::_('COM_OTTOMO_DATE_OF_BIRTH_IS_REQUIRED'));
            return false;
        }

        if ($params->get('subscriberAddress') == '2' && StringHelper::trim($data['address']) == '') {
            $this->setError(Text::_('COM_OTTOMO_ADDRESS_IS_REQUIRED'));
            return false;
        }

        if ($params->get('subscriberCity') == '2' && StringHelper::trim($data['city']) == '') {
            $this->setError(Text::_('COM_OTTOMO_CITY_IS_REQUIRED'));
            return false;
        }

        if ($params->get('subscriberState') == '2' && StringHelper::trim($data['state']) == '') {
            $this->setError(Text::_('COM_OTTOMO_STATE_PROVINCE_IS_REQUIRED'));
            return false;
        }

        if ($params->get('subscriberZip') == '2' && StringHelper::trim($data['zip']) == '') {
            $this->setError(Text::_('COM_OTTOMO_ZIP_CODE_IS_REQUIRED'));
            return false;
        }

        if ($params->get('subscriberCountry') == '2' && StringHelper::trim($data['country']) == '') {
            $this->setError(Text::_('COM_OTTOMO_COUNTRY_IS_REQUIRED'));
            return false;
        }

        if ($params->get('subscriberPhone') == '2' && StringHelper::trim($data['phone']) == '') {
            $this->setError(Text::_('COM_OTTOMO_TELEPHONE_FIXED_IS_REQUIRED'));
            return false;
        }

        if ($params->get('subscriberMobile') == '2' && StringHelper::trim($data['mobile']) == '') {
            $this->setError(Text::_('COM_OTTOMO_TELEPHONE_MOBILE_IS_REQUIRED'));
            return false;
        }

        if ($params->get('subscriberFax') == '2' && StringHelper::trim($data['fax']) == '') {
            $this->setError(Text::_('COM_OTTOMO_FAX_IS_REQUIRED'));
            return false;
        }

        if ($params->get('subscriberVat') == '2' && StringHelper::trim($data['vat']) == '') {
            $this->setError(Text::_('COM_OTTOMO_VAT_NO_IS_REQUIRED'));
            return false;
        }

        if ($params->get('subscriberTax') == '2' && StringHelper::trim($data['tax']) == '') {
            $this->setError(Text::_('COM_OTTOMO_TAX_AUTHORITY_IS_REQUIRED'));
            return false;
        }

        if ($params->get('subscriberCompany') == '2' && StringHelper::trim($data['company']) == '') {
            $this->setError(Text::_('COM_OTTOMO_COMPANY_NAME_IS_REQUIRED'));
            return false;
        }

        if ($params->get('subscriberPosition') == '2' && StringHelper::trim($data['position']) == '') {
            $this->setError(Text::_('COM_OTTOMO_POSITION_IN_COMPANY_IS_REQUIRED'));
            return false;
        }

        return true;
    }

    public function activate()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select($db->quoteName('id'));
        $query->from($db->quoteName('#__ottomo_subscribers'));
        $query->where($db->quoteName('status').' = 0');
        $query->where($db->quoteName('activation').' = '.$db->quote($this->getState('hash')));
        $db->setQuery($query);
        $id = (int)$db->loadResult();
        if (!$id) {
            $this->setError(Text::_('COM_OTTOMO_INVALID_ACTIVATION_TOKEN'));
            return false;
        }
        $query = $db->getQuery(true);
        $query->update($db->quoteName('#__ottomo_subscribers'));
        $query->set($db->quoteName('status').' = 1');
        $query->set($db->quoteName('activation').' = '.$db->quote(''));
        $query->where($db->quoteName('id').' = '.$id);
        $db->setQuery($query);
        $db->execute();
        return true;
    }

    /** How many subscribers have this status (all topics). */
    public function countByStatus($status)
    {
        $db = $this->getDbo();
        $db->setQuery($db->getQuery(true)->select('COUNT(*)')->from($db->quoteName('#__ottomo_subscribers'))->where($db->quoteName('status').' = '.(int)$status));
        return (int)$db->loadResult();
    }

    /**
     * Delete every unsubscribed address (status 2), with its topics, in chunks. Irreversible: Ottomo then no longer
     * knows they unsubscribed (an import could add them again).
     *
     * @return  int  How many were deleted
     */
    public function removeUnsubscribed()
    {
        $db = $this->getDbo();
        $table = $this->getTable('Subscribers', 'OttomoTable');
        $deleted = 0;
        do {
            $query = $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__ottomo_subscribers'))
                ->where($db->quoteName('status').' = '.self::STATUS_UNSUBSCRIBED)
                ->order($db->quoteName('id').' ASC');
            $db->setQuery($query, 0, 1000);
            $ids = array_map('intval', $db->loadColumn());
            if ($ids) {
                $table->delete($ids);
                $deleted += count($ids);
            }
        } while (count($ids) === 1000);
        return $deleted;
    }

    public function save()
    {
        $db = $this->getDbo();
        $data = $this->getState('data');
        $table = $this->getTable('Subscribers', 'OttomoTable');
        if (!isset($data['id'])) {
            $data['id'] = null;
        }
        $isNew = $data['id'] > 0 ? false : true;
        // New records default to active; existing ones keep their status unless it is explicitly given
        // (the admin form has no status field: saving it must not re-activate an unsubscribed address)
        if (!isset($data['status']) && $isNew) {
            $data['status'] = self::STATUS_ACTIVE;
        }
        // Suppression: an unsubscribed address stays unsubscribed whatever the backend (or an import) sends.
        // Only its owner may sign up again (site, double opt-in: validate() then status 0 with an activation link).
        if (!$isNew && !Factory::getApplication()->isClient('site')) {
            $query = $db->getQuery(true)
                ->select($db->quoteName('status'))
                ->from($db->quoteName('#__ottomo_subscribers'))
                ->where($db->quoteName('id').' = '.(int)$data['id']);
            $db->setQuery($query, 0, 1);
            if ((int)$db->loadResult() === self::STATUS_UNSUBSCRIBED) {
                $data['status'] = self::STATUS_UNSUBSCRIBED;
            }
        }
        if (!$table->save($data)) {
            $this->setError($table->getError());
            return false;
        }
        $this->setState('id', $table->id);

        //Drop all topics before adding the new
        $query = $db->getQuery(true);
        $query->delete($db->quoteName('#__ottomo_subscribers_xref'));
        $query->where($db->quoteName('subscriberID').' = '.(int)$table->id);
        $db->setQuery($query);
        $db->execute();

        $topics = isset($data['topics']) ? (array)$data['topics'] : array();
        $data = array();
        ArrayHelper::toInteger($topics);
        foreach ($topics as $topic) {
            if ($topic > 0) {
                $data[] = array($topic, (int)$table->id);
            }
        }
        if (count($data)) {

            $query = $db->getQuery(true);
            $query->insert($db->quoteName('#__ottomo_subscribers_xref'));
            $query->columns(array($db->quoteName('topicID'), $db->quoteName('subscriberID')));
            foreach ($data as $entry) {
                $query->values(implode(',', $entry));
            }
            $db->setQuery($query);
            $db->execute();
        }

        // Send activation email
        $mainframe = Factory::getApplication();
        if ($mainframe->isClient('site') && $table->activation && $table->status == self::STATUS_PENDING) {

            $params = ComponentHelper::getParams('com_ottomo');
            $uri = Uri::getInstance();
            $base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
            $activationLink = $base.Route::_('index.php?option=com_ottomo&task=subscribers.activate&hash='.$table->activation, false);
            JLoader::register('OttomoHelperSettings', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/settings.php');
            $sender = OttomoHelperSettings::sender();
            $this->sendActivationEmail($table, $activationLink, $sender->email, $sender->name);
        }

        return true;

    }

    /** Activation (double opt-in) email: HTML with a button, and the same text as the plain-text part */
    protected function sendActivationEmail($subscriber, $link, $senderEmail, $senderName)
    {
        JLoader::register('OttomoHelperSettings', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/settings.php');
        $siteName = OttomoHelperSettings::siteName();
        $name = trim((string)$subscriber->name);
        $greeting = ($name !== '' && strcasecmp($name, (string)$subscriber->email) !== 0) ? Text::sprintf('COM_OTTOMO_ACTIVATION_EMAIL_GREETING_NAME', $name) : Text::_('COM_OTTOMO_ACTIVATION_EMAIL_GREETING');
        $intro = Text::sprintf('COM_OTTOMO_ACTIVATION_EMAIL_INTRO', $siteName);
        $button = Text::_('COM_OTTOMO_ACTIVATION_EMAIL_BUTTON');
        $ignore = Text::_('COM_OTTOMO_ACTIVATION_EMAIL_IGNORE');
        $subject = Text::sprintf('COM_OTTOMO_ACTIVATION_EMAIL_SUBJECT', $siteName);

        // "Powered by Ottomo" at the very end (transactional emails only, never in campaign templates)
        $poweredUrl = 'https://www.joomlaworks.net/ottomo';
        $text = $greeting."\n\n".$intro."\n\n".$button.":\n".$link."\n\n".$ignore."\n\n-- \n".Text::sprintf('COM_OTTOMO_POWERED_BY', 'Ottomo').': '.$poweredUrl."\n";

        $e = function ($value) {
            return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
        };
        $font = 'font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Helvetica,Arial,sans-serif;';
        $html = '<!DOCTYPE html><html lang="'.$e(OttomoCompat::language()->getTag()).'"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>'.$e($subject).'</title></head>'
            .'<body style="margin:0;padding:0;background:#f4f4f5;">'
            .'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f4f4f5;"><tr><td align="center" style="padding:32px 12px;">'
            .'<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:520px;background:#ffffff;border:1px solid #e4e4e7;border-radius:12px;">'
            .'<tr><td style="padding:36px 36px 8px;'.$font.'font-size:20px;line-height:28px;font-weight:700;color:#18181b;">'.$e($greeting).'</td></tr>'
            .'<tr><td style="padding:0 36px 24px;'.$font.'font-size:16px;line-height:26px;color:#3f3f46;">'.$e($intro).'</td></tr>'
            .'<tr><td style="padding:0 36px 24px;"><table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr><td style="border-radius:8px;background:#18181b;">'
            .'<a href="'.$e($link).'" style="display:inline-block;padding:13px 26px;'.$font.'font-size:15px;font-weight:600;line-height:20px;color:#ffffff;text-decoration:none;border-radius:8px;">'.$e($button).'</a>'
            .'</td></tr></table></td></tr>'
            .'<tr><td style="padding:0 36px 32px;'.$font.'font-size:13px;line-height:20px;color:#71717a;">'.$e($ignore).'<br><a href="'.$e($link).'" style="color:#71717a;word-break:break-all;">'.$e($link).'</a></td></tr>'
            .'</table>'
            .'<p style="margin:24px 0 0;'.$font.'font-size:13px;line-height:24px;color:#a1a1aa;">'
            .'<a href="'.$poweredUrl.'" style="color:#a1a1aa;text-decoration:none;">'
            .Text::sprintf('COM_OTTOMO_POWERED_BY', '<img src="'.$e(rtrim(Uri::root(), '/').'/components/com_ottomo/static/images/powered-by-ottomo.png').'" width="132" height="33" alt="Ottomo" style="display:inline-block;width:132px;height:33px;border:0;vertical-align:-8px;margin-left:5px;">')
            .'</a></p>'
            .'</td></tr></table></body></html>';

        $mailer = OttomoCompat::mailer();
        $mailer->setSender(array($senderEmail, $senderName));
        $mailer->addRecipient($subscriber->email);
        $mailer->setSubject($subject);
        $mailer->isHtml(true);
        $mailer->setBody($html);
        $mailer->AltBody = $text; // multipart/alternative: clients without HTML show the text part
        return $mailer->Send();
    }

    public function enable()
    {
        $table = $this->getTable();
        $table->enable($this->getState('id'));
    }

    public function disable()
    {
        $table = $this->getTable();
        $table->disable($this->getState('id'));
    }

    public function unsubscribe()
    {
        $id = (int)$this->getState('id');
        $topics = $this->getState('topics');
        ArrayHelper::toInteger($topics);
        $db = $this->getDbo();
        if (count($topics)) {
            $query = $db->getQuery(true);
            $query->delete($db->quoteName('#__ottomo_subscribers_xref'));
            $query->where($db->quoteName('subscriberID').' = '.(int)$id);
            $query->where($db->quoteName('topicID').' IN ('.implode(',', $topics).')');
            $db->setQuery($query);
            $db->execute();
        }

        $query = $db->getQuery(true);
        $query->select('COUNT(*)');
        $query->from($db->quoteName('#__ottomo_subscribers_xref'));
        $query->where($db->quoteName('subscriberID').' = '.(int)$id);
        $db->setQuery($query);
        $result = $db->loadResult();
        // No topics left: keep the record as "Unsubscribed" (suppression), never delete it, so that a later import
        // or topic assignment cannot bring the address back. Deleting is reserved to an explicit account deletion.
        if (!$result) {
            $query = $db->getQuery(true);
            $query->update($db->quoteName('#__ottomo_subscribers'));
            $query->set($db->quoteName('status').' = '.self::STATUS_UNSUBSCRIBED);
            $query->where($db->quoteName('id').' = '.(int)$id);
            $db->setQuery($query);
            $db->execute();
        }

        return true;
    }

    public function getSubscriberTopics()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select($db->quoteName('topicID'));
        $query->from($db->quoteName('#__ottomo_subscribers_xref'));
        $query->where($db->quoteName('subscriberID').' = '.(int)$this->getState('id'));
        $db->setQuery($query);
        $rows = $db->loadColumn();
        return $rows;
    }

    public function getSubscriberByEmail()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName('#__ottomo_subscribers'));
        $query->where($db->quoteName('email').' = '.$db->quote($this->getState('email')));
        $db->setQuery($query, 0, 1);
        $row = $db->loadObject();
        return $row;
    }

}
