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

JLoader::register('OttomoHelperImport', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/import.php');

/**
 * The import runs in steps, one request each (the page sends the answer back as the next request):
 * pasted addresses → the selected sources (helpers/importers/, one after the other) → the uploaded CSV file.
 * Each step handles $step addresses, so a site of any size imports within PHP's time limit.
 */
class OttomoControllerImport extends OttomoController
{
    private $response = null;
    private $request = null;
    private $step = 300;
    private $topics = array();

    public function __construct($config = array())
    {
        parent::__construct($config);

        $request = json_decode((string)$this->input->get('request', '', 'raw'));
        if (!is_object($request)) {
            $request = new stdClass();
            $request->task = 'importFromText';
        }
        $defaults = array('limitstart' => 0, 'source' => 0, 'cursor' => '', 'total' => -1, 'done' => 0, 'offset' => 0, 'added' => 0, 'existing' => 0, 'unsubscribed' => 0);
        foreach ($defaults as $key => $value) {
            if (!isset($request->$key)) {
                $request->$key = $value;
            }
        }
        $this->request = $request;

        // The answer carries the request's state on to the next step
        $this->response = clone $request;
        $this->response->status = 'RUNNING';
        $this->response->errors = array();

        $topics = $this->input->get('topics', array(), 'array');
        $this->topics = array_values(array_filter(array_map('intval', (array)$topics)));
    }

    public function run()
    {
        if (!Session::checkToken()) {
            $this->response->errors[] = Text::_('JINVALID_TOKEN');
            $this->response->status = 'COMPLETED';
            return $this->render();
        }

        // The step comes from the client: only the import steps may run
        if (in_array($this->request->task, array('importFromText', 'importFromSources', 'importFromCsv'), true)) {
            call_user_func(array($this, $this->request->task));
        }

        return $this->render();
    }

    /** Pasted addresses, one per line */
    private function importFromText()
    {
        $emails = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string)$this->input->getString('subscribers'))), 'strlen'));
        $limitstart = (int)$this->request->limitstart;
        $size = count($emails);
        if ($limitstart >= $size) {
            return $this->next('importFromSources');
        }
        $records = array();
        foreach (array_slice($emails, $limitstart, $this->step) as $email) {
            $records[] = (object)array('email' => $email);
        }
        $this->importRecords($records);
        $this->response->limitstart = $limitstart + $this->step;
        $this->response->progress = min($this->response->limitstart / $size, 1);
        $this->response->label = Text::_('COM_OTTOMO_IMPORTING_FROM_TEXT');
    }

    /** The selected sources, one after the other, in batches */
    private function importFromSources()
    {
        $sources = $this->sources();
        $index = (int)$this->request->source;
        if (!isset($sources[$index])) {
            return $this->next('importFromCsv');
        }
        $adapter = $sources[$index];
        if ((int)$this->request->total < 0) {
            $this->response->total = $adapter->total();
            $this->response->done = 0;
        }
        list($records, $cursor) = $adapter->batch($this->request->cursor === '' ? null : $this->request->cursor, $this->step);
        $this->importRecords($records);
        $this->response->done = (int)$this->response->done + count($records);
        $this->response->label = Text::sprintf('COM_OTTOMO_IMPORTING_FROM_SOURCE', $adapter->label());
        $total = max((int)$this->response->total, 1);
        if ($cursor === null) {
            // This source is done: the next one starts with the next request
            $this->response->source = $index + 1;
            $this->response->cursor = '';
            $this->response->total = -1;
            $this->response->progress = 1;
        } else {
            $this->response->cursor = $cursor;
            $this->response->progress = min($this->response->done / $total, 0.99);
        }
    }

    /** The uploaded CSV file: email in the first column, the "name" column when there's a header row */
    private function importFromCsv()
    {
        $session = OttomoCompat::session();
        $file = (string)$session->get('com_ottomo.import.file', '');
        $file = preg_match('/^ottomo-import-[a-f0-9]{32}\.csv$/', $file) ? $file : '';
        $path = $file ? JPATH_SITE.'/tmp/'.$file : '';

        if (!$file) {
            return $this->complete();
        }
        if (!is_file($path) || !($handle = fopen($path, 'r'))) {
            $this->response->errors[] = Text::_('COM_OTTOMO_COULD_NOT_READ_CSV_FILE');
            $session->clear('com_ottomo.import.file');
            $session->clear('com_ottomo.import.nameColumn');
            return $this->complete();
        }

        $offset = (int)$this->request->offset;
        $first = $offset === 0;
        // The "name" column, found in the header row by the first step (-1: none)
        $nameColumn = $first ? -1 : (int)$session->get('com_ottomo.import.nameColumn', -1);
        if (!$first) {
            fseek($handle, $offset);
        }
        $records = array();
        while (count($records) < $this->step && ($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            if ($first) {
                $first = false;
                $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string)$row[0]); // UTF-8 BOM
                // A header row (e.g. an Ottomo export): skip it, and read the "name" column if there is one
                if (strtolower(trim($row[0])) === 'email') {
                    $found = array_search('name', array_map('strtolower', array_map('trim', $row)), true);
                    $nameColumn = $found === false ? -1 : (int)$found;
                    $session->set('com_ottomo.import.nameColumn', $nameColumn);
                    continue;
                }
            }
            if (!isset($row[0]) || trim((string)$row[0]) === '') {
                continue;
            }
            $record = (object)array('email' => trim((string)$row[0]));
            if ($nameColumn >= 0 && isset($row[$nameColumn]) && trim((string)$row[$nameColumn]) !== '') {
                $record->name = trim((string)$row[$nameColumn]);
            }
            $records[] = $record;
        }
        $position = ftell($handle);
        $eof = feof($handle);
        fclose($handle);

        $this->importRecords($records);
        $this->response->label = Text::_('COM_OTTOMO_IMPORTING_FROM_CSV');
        if ($eof || $position === false) {
            File::delete($path);
            $session->clear('com_ottomo.import.file');
            $session->clear('com_ottomo.import.nameColumn');
            return $this->complete();
        }
        $this->response->offset = $position;
        $size = filesize($path);
        $this->response->progress = $size ? min($position / $size, 0.99) : 0;
    }

    /** The installed adapters of the sources ticked on the page, in the page's order */
    private function sources()
    {
        $ticked = $this->input->get('sources', array(), 'array');
        $sources = array();
        foreach (OttomoHelperImport::adapters() as $name => $adapter) {
            if (in_array($name, $ticked, true) && $adapter->isAvailable()) {
                $sources[] = $adapter;
            }
        }
        return $sources;
    }

    private function importRecords(array $records)
    {
        $counts = array('added' => (int)$this->response->added, 'existing' => (int)$this->response->existing, 'unsubscribed' => (int)$this->response->unsubscribed);
        $this->response->errors = array_merge($this->response->errors, OttomoHelperImport::save($records, $this->topics, $counts));
        foreach ($counts as $key => $value) {
            $this->response->$key = $value;
        }
    }

    private function next($task)
    {
        $this->response->task = $task;
        $this->response->progress = 0;
        $this->response->label = '';
    }

    private function complete()
    {
        $this->response->status = 'COMPLETED';
        $this->response->progress = 1;
        $this->response->label = Text::_('COM_OTTOMO_IMPORT_COMPLETED').' · '.OttomoHelperImport::summary(array(
            'added' => $this->response->added,
            'existing' => $this->response->existing,
            'unsubscribed' => $this->response->unsubscribed,
        ));
    }

    private function render()
    {
        echo json_encode($this->response);
        return $this;
    }
}
