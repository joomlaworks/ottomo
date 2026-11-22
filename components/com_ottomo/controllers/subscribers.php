<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/compat.php';

class OttomoControllerSubscribers extends OttomoController
{
    public function activate()
    {
        $application = Factory::getApplication();
        $model = OttomoModel::getInstance('Subscribers');
        $model->setState('hash', $this->input->getString('hash'));
        if ($model->activate()) {
            $application->enqueueMessage(Text::_('COM_OTTOMO_YOUR_ACCOUNT_HAS_BEEN_ACTIVATED'));
        } else {
            $application->enqueueMessage($model->getError(), 'error');
        }
        return $this;
    }

    /**
     * The email's unsubscribe link ({{unsubscribe}}): GET only asks, the confirmation's POST unsubscribes, so that
     * link scanners and prefetchers opening the URL cannot unsubscribe anyone. The subscriber hash authenticates.
     */
    public function unsubscribe()
    {
        $id = $this->input->getInt('id');
        $hash = $this->input->getCmd('hash');
        $newsletterId = $this->input->getInt('newsletter');
        $topics = explode('|', (string)$this->input->getString('topics', ''));

        if (!OttomoHelperData::checkHash($newsletterId, $id, $hash)) {
            throw new Exception(Text::_('COM_OTTOMO_INVALID_HASH'), 400);
        }

        if ($this->input->getMethod() !== 'POST') {
            echo self::confirmation(false);
            return $this;
        }

        $model = OttomoModel::getInstance('Subscribers');
        $model->setState('id', $id);
        $model->setState('topics', $topics);
        if ($model->unsubscribe()) {
            echo self::page('<p>'.Text::_('COM_OTTOMO_YOU_HAVE_UNSUBSCRIBED').'</p>');
        } else {
            echo self::page('<p>'.htmlspecialchars((string)$model->getError(), ENT_QUOTES, 'UTF-8').'</p>');
        }
        return $this;
    }

    /** The unsubscribe pages (link and one-click): a centred box with a title */
    protected static function page($body)
    {
        $title = htmlspecialchars(Text::_('COM_OTTOMO_ONECLICK_TITLE'), ENT_QUOTES, 'UTF-8');
        $box = 'max-width:32rem;margin:3rem auto;padding:2rem;border:1px solid #e4e4e7;border-radius:.75rem;text-align:center;font-family:system-ui,sans-serif;';
        return '<div class="ottomo-oneclick" style="'.$box.'"><h1 style="margin:0 0 .75rem;font-size:1.5rem;">'.$title.'</h1>'.$body.'</div>';
    }

    /** "Stop receiving these emails?" and a button posting back to the same address */
    protected static function confirmation($oneClick)
    {
        $action = htmlspecialchars(Uri::getInstance()->toString(), ENT_QUOTES, 'UTF-8');
        return self::page('<p>'.Text::_('COM_OTTOMO_ONECLICK_CONFIRM').'</p>'
            .'<form method="post" action="'.$action.'">'.($oneClick ? '<input type="hidden" name="List-Unsubscribe" value="One-Click" />' : '')
            .'<button type="submit" style="padding:.625rem 1.25rem;border:0;border-radius:.5rem;background:#18181b;color:#fff;font:inherit;cursor:pointer;">'.Text::_('COM_OTTOMO_ONECLICK_BUTTON').'</button></form>');
    }

    public function save()
    {
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        $id = $this->input->getInt('id');
        $hash = $this->input->getString('hash');
        $newsletterId = $this->input->getInt('newsletter');

        if (!OttomoHelperData::checkHash($newsletterId, $id, $hash)) {
            throw new Exception(Text::_('COM_OTTOMO_INVALID_HASH'), 400);
        }

        $model = OttomoModel::getInstance('Subscribers');
        $model->setState('id', $id);
        $row = $model->getRow();
        if (!$row->id || !$row->status) {
            return $this->setRedirect(Route::_('index.php?option=com_ottomo&view=subscribers&id='.$id.'&hash='.$hash.'&newsletter='.$newsletterId, false), Text::_('COM_OTTOMO_INVALID_SUBSCRIBER'), 'error');
        }
        if ($row->email != $this->input->getString('email')) {
            return $this->setRedirect(Route::_('index.php?option=com_ottomo&view=subscribers&id='.$id.'&hash='.$hash.'&newsletter='.$newsletterId, false), Text::_('COM_OTTOMO_INVALID_EMAIL'), 'error');
        }
        $data = get_object_vars($row);
        $data['topics'] = $this->input->get('topics', array(), 'array');
        $model->setState('data', $data);
        if ($model->save()) {
            $message = Text::_('COM_OTTOMO_YOUR_PREFERENCES_HAVE_BEEN_UPDATED');
            $type = 'message';
        } else {
            $message = $model->getError();
            $type = 'error';
        }
        return $this->setRedirect(Route::_('index.php?option=com_ottomo&view=subscribers&id='.$id.'&hash='.$hash.'&newsletter='.$newsletterId, false), $message, $type);
    }

    public function delete()
    {
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        $id = $this->input->getInt('id');
        $hash = $this->input->getString('hash');
        $newsletterId = $this->input->getInt('newsletter');

        if (!OttomoHelperData::checkHash($newsletterId, $id, $hash)) {
            throw new Exception(Text::_('COM_OTTOMO_INVALID_HASH'), 400);
        }

        $model = OttomoModel::getInstance('Subscribers');
        $model->setState('id', $id);
        $row = $model->getRow();
        if (!$row->id || $row->email != $this->input->getString('email')) {
            return $this->setRedirect(Route::_('index.php?option=com_ottomo&view=subscribers&id='.$id.'&hash='.$hash.'&newsletter='.$newsletterId, false), Text::_('COM_OTTOMO_INVALID_EMAIL'), 'error');
        }
        if ($model->remove()) {
            return $this->setRedirect(Uri::root(true), Text::_('COM_OTTOMO_YOUR_ACCOUNT_HAS_BEEN_DELETED'));
        } else {
            return $this->setRedirect(Route::_('index.php?option=com_ottomo&view=subscribers&id='.$id.'&hash='.$hash.'&newsletter='.$newsletterId, false), $model->getError(), 'error');
        }
    }


    /**
     * One-click unsubscribe (RFC 8058), the target of the List-Unsubscribe header.
     * POST unsubscribes immediately (mail clients' one-click); GET only shows a confirmation, so that link scanners
     * opening the URL cannot unsubscribe anyone. No CSRF token by design: the subscriber hash authenticates.
     */
    public function oneclick()
    {
        $application = Factory::getApplication();
        $input = OttomoCompat::input();
        $id = $input->getInt('id');
        $newsletterId = $input->getInt('newsletter');
        $hash = $input->getCmd('hash');

        if (!OttomoHelperData::checkHash($newsletterId, $id, $hash)) {
            throw new Exception(Text::_('COM_OTTOMO_INVALID_HASH'), 400);
        }

        if ($input->getMethod() !== 'POST') {
            echo self::confirmation(true);
            return $this;
        }

        // The campaign's topics; a campaign without topics unsubscribes from everything
        $newsletters = OttomoModel::getInstance('Newsletters');
        $newsletters->setState('id', $newsletterId);
        $topics = array_filter(array_map('intval', (array)$newsletters->getNewsletterTopics()));
        if (!count($topics)) {
            $db = OttomoCompat::db();
            $db->setQuery($db->getQuery(true)->select($db->quoteName('topicID'))->from($db->quoteName('#__ottomo_subscribers_xref'))->where($db->quoteName('subscriberID').' = '.(int)$id));
            $topics = array_map('intval', (array)$db->loadColumn());
        }

        $model = OttomoModel::getInstance('Subscribers');
        $model->setState('id', $id);
        $model->setState('topics', $topics);
        $model->unsubscribe();

        echo self::page('<p>'.Text::_('COM_OTTOMO_YOU_HAVE_UNSUBSCRIBED').'</p>');
        return $this;
    }
}
