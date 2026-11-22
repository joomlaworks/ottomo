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
use Joomla\Filesystem\File;

require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/compat.php';

class OttomoControllerImport extends OttomoController
{
    public function upload()
    {
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
        $response = new stdClass();
        $response->file = '';
        $response->error = '';
        $file = $this->input->files->get('csv');

        if ($file['error'] > 0) {
            $response->error = Text::_('COM_OTTOMO_AN_ERROR_OCCURED');
            echo json_encode($response);
            return $this;
        }
        if (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) != 'csv') {
            $response->error = Text::_('COM_OTTOMO_THIS_IS_NOT_A_CSV_FILE');
            echo json_encode($response);
            return $this;
        }
        // Unguessable name, kept server-side: import.run only reads the file stored in the session
        $destinationFile = 'ottomo-import-'.bin2hex(random_bytes(16)).'.csv';
        if (File::upload($file['tmp_name'], JPATH_SITE.'/tmp/'.$destinationFile)) {
            OttomoCompat::session()->set('com_ottomo.import.file', $destinationFile);
            OttomoCompat::session()->clear('com_ottomo.import.nameColumn');
            $response->file = 1; // truthy flag for the UI; the name itself stays on the server
        } else {
            $response->error = Text::_('COM_OTTOMO_AN_ERROR_OCCURED');
        }
        echo json_encode($response);
        return $this;


    }
}
