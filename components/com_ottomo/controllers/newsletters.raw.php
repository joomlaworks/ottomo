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
use Joomla\CMS\Uri\Uri;

require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/compat.php';

class OttomoControllerNewsletters extends OttomoController
{
    public function track()
    {
        $id = $this->input->getInt('id');
        $subscriberID = $this->input->getInt('subscriber');
        $type = $this->input->getInt('type');
        $hash = $this->input->getString('hash');
        $allowed = array(2, 3);
        if (!in_array($type, $allowed)) {
            throw new Exception(Text::_('COM_OTTOMO_INVALID_TYPE'), 400);
            return $this;
        }

        if (!OttomoHelperData::checkHash($id, $subscriberID, $hash)) {
            return $this;
        }

        $model = OttomoModel::getInstance('Newsletters');
        $model->setState('id', $id);
        $model->setState('subscriber', $subscriberID);
        $model->setState('type', $type);
        // URL-safe base64 (helpers/template.php: strtr(base64_encode($url), '+/=', '-_,'))
        $redirect = preg_replace('/[^A-Za-z0-9\-_,+\/=]/', '', (string)$this->input->getString('redirect'));
        $redirect = (string)base64_decode(strtr($redirect, '-_,', '+/='), true);
        // Internal links, or external ones Ottomo signed when it wrapped them: anything else goes to the home page
        $internal = $redirect !== '' && Uri::isInternal($redirect);
        $signed = !$internal && preg_match('#^https?://#i', $redirect) && function_exists('hash_equals')
            && hash_equals(OttomoHelperData::linkSignature($redirect), (string)$this->input->getCmd('sig'));
        $redirect = $internal || $signed ? $redirect : '';
        $model->setState('link', $type == 2 ? $redirect : '');
        if (!$model->track() && $model->getState('errorType') != 'duplicate') {
            echo $model->getError();
            return $this;
        }
        if ($model->getState('type') == 3) {
            Factory::getApplication()->setHeader('Content-disposition', 'inline; filename="open.gif"', true);
            $document = OttomoCompat::document();
            $document->setMimeEncoding('image/gif');
            readfile(JPATH_ADMINISTRATOR.'/components/com_ottomo/static/images/open.gif');
            return $this;
        }
        if ($redirect === '') {
            $this->setRedirect(Uri::root());
        } else {
            $this->setRedirect($redirect);
        }
    }

}
