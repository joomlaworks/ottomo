<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Captcha\Captcha;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\User\UserHelper;

require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/compat.php';

class OttomoControllerSubscribers extends OttomoController
{
    public function subscribe()
    {
        if (!Session::checkToken()) {
            return $this->respond(false, Text::_('JINVALID_TOKEN'));
        }
        JLoader::register('OttomoHelperSettings', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/settings.php');

        // Honeypot (Settings > Signup forms): a filled-in hidden field is a bot. Answer like a success, save nothing.
        if (OttomoHelperSettings::enabled('honeypot') && trim((string)$this->input->post->getString('ottomo_website', '')) !== '') {
            return $this->respond(true, Text::_('COM_OTTOMO_THANKS_FOR_SUBSCRIBING'));
        }

        // Captcha (Settings > Signup forms; default: the site's captcha)
        $plugin = OttomoHelperSettings::captcha();
        if ($plugin !== '') {
            $captcha = Captcha::getInstance($plugin, array('namespace' => 'mod_ottomo_'.$this->input->post->getInt('module')));
            $passed = false;
            $error = '';
            try {
                $passed = $captcha && $captcha->checkAnswer($this->input->post->getString('ottomo_captcha', ''));
                if (!$passed && $captcha) {
                    $error = $captcha->getError();
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
            if (!$passed) {
                return $this->respond(false, $error ? (is_object($error) ? $error->getMessage() : $error) : Text::_('COM_OTTOMO_CAPTCHA_FAILED'));
            }
        }

        $model = OttomoModel::getInstance('Subscribers');
        $data = $this->input->post->getArray();
        $data['activation'] = ApplicationHelper::getHash(UserHelper::genRandomPassword());
        $data['status'] = 0;
        $data['id'] = null;
        $model->setState('data', $data);
        if (!$model->validate() || !$model->save()) {
            return $this->respond(false, $model->getError());
        }
        return $this->respond(true, Text::_('COM_OTTOMO_THANKS_FOR_SUBSCRIBING'));
    }

    /** The answer read by mod_ottomo's script: {"success": bool, "message": "..."} */
    protected function respond($success, $message)
    {
        OttomoCompat::document()->setMimeEncoding('application/json');
        echo json_encode(array('success' => (bool)$success, 'message' => strip_tags((string)$message)));
        return $this;
    }

}
