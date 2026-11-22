<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

class OttomoControllerImport extends OttomoController
{
    public function upload()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        $response = new stdClass();
        $response->file = '';
        $response->error = '';
        $file = $this->input->files->get('csv');

        if ($file['error'] > 0) {
            $response->error = JText::_('COM_OTTOMO_AN_ERROR_OCCURED');
            echo json_encode($response);
            return $this;
        }
        if (JFile::getExt($file['name']) != 'csv') {
            $response->error = JText::_('COM_OTTOMO_THIS_IS_NOT_A_CSV_FILE');
            echo json_encode($response);
            return $this;
        }
        // Unguessable name, kept server-side: import.run only reads the file stored in the session
        $destinationFile = 'ottomo-import-'.bin2hex(random_bytes(16)).'.csv';
        if (JFile::upload($file['tmp_name'], JPATH_SITE.'/tmp/'.$destinationFile)) {
            JFactory::getSession()->set('import.file', $destinationFile, 'com_ottomo');
            JFactory::getSession()->clear('import.nameColumn', 'com_ottomo');
            $response->file = 1; // truthy flag for the UI; the name itself stays on the server
        } else {
            $response->error = JText::_('COM_OTTOMO_AN_ERROR_OCCURED');
        }
        echo json_encode($response);
        return $this;


    }
}
