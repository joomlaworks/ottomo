<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/compat.php';

class OttomoControllerSubscribers extends OttomoController
{
    public function enable()
    {
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
        $model = $this->getModel();
        $model->setState('id', $this->input->get('cid', array(), 'array'));
        $model->enable();
        $this->setRedirect('index.php?option=com_ottomo&view=subscribers', Text::_('COM_OTTOMO_SUBSCRIBERS_ENABLED').self::suppressedNote($this->input->get('cid', array(), 'array')));
    }

    public function disable()
    {
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
        $model = $this->getModel();
        $model->setState('id', $this->input->get('cid', array(), 'array'));
        $model->disable();
        $this->setRedirect('index.php?option=com_ottomo&view=subscribers', Text::_('COM_OTTOMO_SUBSCRIBERS_DISABLED').self::suppressedNote($this->input->get('cid', array(), 'array')));
    }

    /** Delete All Unsubscribed (confirmed in the page first: it cannot be undone). */
    public function removeUnsubscribed()
    {
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
        $deleted = $this->getModel()->removeUnsubscribed();
        $this->setRedirect('index.php?option=com_ottomo&view=subscribers', $deleted ? Text::sprintf('COM_OTTOMO_DELETED_UNSUBSCRIBED', number_format($deleted)) : Text::_('COM_OTTOMO_NO_UNSUBSCRIBED'));
    }

    /** " N unsubscribed addresses were left unchanged." when the selection contained some. */
    protected static function suppressedNote($ids)
    {
        $ids = array_filter(array_map('intval', (array)$ids));
        if (!count($ids)) {
            return '';
        }
        $db = OttomoCompat::db();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__ottomo_subscribers'))
            ->where($db->quoteName('id').' IN ('.implode(',', $ids).')')
            ->where($db->quoteName('status').' = 2');
        $db->setQuery($query);
        $count = (int)$db->loadResult();
        return $count ? ' '.Text::plural('COM_OTTOMO_N_UNSUBSCRIBED_UNCHANGED', $count) : '';
    }

}
