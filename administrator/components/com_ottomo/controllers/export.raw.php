<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

class OttomoControllerExport extends OttomoController
{
    /**
     * Stream this session's export file as a download, then delete it.
     * The file never needs to be reachable by URL.
     */
    public function download()
    {
        JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));
        $session = JFactory::getSession();
        $name = (string)$session->get('export.file', '', 'com_ottomo');
        $file = preg_match('/^ottomo-export-[a-f0-9]{32}\.csv$/', $name) ? JPATH_SITE.'/tmp/'.$name : null;
        if (!$file || !is_file($file)) {
            throw new Exception(JText::_('COM_OTTOMO_EXPORT_FILE_NOT_FOUND'), 404);
        }
        $session->clear('export.file', 'com_ottomo');

        $application = JFactory::getApplication();
        $application->clearHeaders();
        $application->setHeader('Content-Type', 'text/csv; charset=utf-8', true);
        $application->setHeader('Content-Disposition', 'attachment; filename="subscribers-'.JFactory::getDate()->format('Y-m-d').'.csv"', true);
        $application->setHeader('Content-Length', (string)filesize($file), true);
        $application->setHeader('Cache-Control', 'no-store', true);
        $application->sendHeaders();
        readfile($file);
        @unlink($file);
        $application->close();
    }
}
