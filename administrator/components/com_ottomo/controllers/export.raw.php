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
use Joomla\CMS\Session\Session;

require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/compat.php';

class OttomoControllerExport extends OttomoController
{
    /**
     * Stream this session's export file as a download, then delete it.
     * The file never needs to be reachable by URL.
     */
    public function download()
    {
        Session::checkToken('get') or jexit(Text::_('JINVALID_TOKEN'));
        $session = OttomoCompat::session();
        $name = (string)$session->get('com_ottomo.export.file', '');
        $file = preg_match('/^ottomo-export-[a-f0-9]{32}\.csv$/', $name) ? JPATH_SITE.'/tmp/'.$name : null;
        if (!$file || !is_file($file)) {
            throw new Exception(Text::_('COM_OTTOMO_EXPORT_FILE_NOT_FOUND'), 404);
        }
        $session->clear('com_ottomo.export.file');

        $application = Factory::getApplication();
        $application->clearHeaders();
        $application->setHeader('Content-Type', 'text/csv; charset=utf-8', true);
        $application->setHeader('Content-Disposition', 'attachment; filename="subscribers-'.Factory::getDate()->format('Y-m-d').'.csv"', true);
        $application->setHeader('Content-Length', (string)filesize($file), true);
        $application->setHeader('Cache-Control', 'no-store', true);
        $application->sendHeaders();
        readfile($file);
        @unlink($file);
        $application->close();
    }
}
