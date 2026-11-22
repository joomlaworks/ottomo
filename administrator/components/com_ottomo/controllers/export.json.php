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
     * Append one batch of subscribers to the export file chosen by the export view (stored in the session).
     */
    public function run()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        $file = self::file();
        if (!$file) {
            jexit(JText::_('JINVALID_TOKEN'));
        }
        $limitstart = $this->input->getInt('limitstart');
        JLoader::register('OttomoHelperExport', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/export.php');
        $model = OttomoModel::getInstance('Subscribers');
        OttomoHelperExport::apply($model, (array)JFactory::getSession()->get('export.scope', array('mode' => 'all'), 'com_ottomo'));
        $model->setState('ordering', 'subscriber.id');
        $model->setState('orderingDir', 'asc');
        $model->setState('limit', max(1, $this->input->getInt('batch', 100)));
        $model->setState('limitstart', $limitstart);
        $rows = $model->getList();

        // Topic names, once per batch
        $topicNames = array();
        $topics = OttomoModel::getInstance('Topics');
        $topics->setState('published', -1);
        foreach ((array)$topics->getList() as $topic) {
            $topicNames[(int)$topic->id] = $topic->name;
        }
        $statuses = array(0 => 'pending', 1 => 'subscribed', 2 => 'unsubscribed');
        $columns = OttomoHelperExport::columns();

        $output = fopen($file, $limitstart === 0 ? 'w' : 'a');
        if ($limitstart === 0) {
            fwrite($output, "\xEF\xBB\xBF"); // UTF-8 BOM: spreadsheet apps read the accents right
            fputcsv($output, $columns);
        }
        foreach ($rows as $row) {
            $line = array();
            foreach ($columns as $column) {
                switch ($column) {
                    case 'status':
                        $line[] = isset($statuses[(int)$row->status]) ? $statuses[(int)$row->status] : '';
                        break;
                    case 'topics':
                        $names = array();
                        foreach (array_filter(explode(',', (string)$row->topics)) as $topicId) {
                            if (isset($topicNames[(int)$topicId])) {
                                $names[] = $topicNames[(int)$topicId];
                            }
                        }
                        $line[] = implode('; ', $names);
                        break;
                    default:
                        $value = isset($row->$column) ? (string)$row->$column : '';
                        // Neutralise spreadsheet formulas (CSV injection)
                        $line[] = preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
                }
            }
            fputcsv($output, $line);
        }
        fclose($output);
        $response = new stdClass();
        $response->counter = count($rows);
        echo json_encode($response);
        return $this;
    }

    /**
     * Absolute path of this session's export file, or null.
     */
    public static function file()
    {
        $name = (string)JFactory::getSession()->get('export.file', '', 'com_ottomo');
        if (!preg_match('/^ottomo-export-[a-f0-9]{32}\.csv$/', $name)) {
            return null;
        }
        return JPATH_SITE.'/tmp/'.$name;
    }
}
