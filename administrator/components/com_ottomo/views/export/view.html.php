<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

JLoader::register('OttomoHelperExport', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/export.php');

class OttomoViewExport extends OttomoView
{
    public function display($tpl = null)
    {
        // What to export: the ticked rows, else the Subscribers list as filtered/searched (all its pages),
        // else everyone ("Export all": scope=all). Kept in the session for export.run.
        $application = JFactory::getApplication();
        $ids = array_values(array_filter(array_map('intval', (array)$application->input->get('cid', array(), 'array'))));
        $scope = array('mode' => 'all');
        if ($application->input->getCmd('scope') !== 'all') {
            if (count($ids)) {
                $scope = array('mode' => 'selected', 'ids' => $ids);
            } else {
                $filters = array(
                    'search' => (string)$application->getUserState('com_ottomo.subscribers.search', ''),
                    'topic' => (int)$application->getUserState('com_ottomo.subscribers.topic', 0),
                    'status' => (int)$application->getUserState('com_ottomo.subscribers.status', -1),
                );
                if ($filters['search'] !== '' || $filters['topic'] > 0 || $filters['status'] !== -1) {
                    $scope = array('mode' => 'filtered') + $filters;
                }
            }
        }
        JFactory::getSession()->set('export.scope', $scope, 'com_ottomo');
        $this->scope = $scope['mode'];

        // Get the total rows
        $model = OttomoModel::getInstance('Subscribers');
        OttomoHelperExport::apply($model, $scope);
        $this->counter = $model->getListCount();

        // Check that we have subscribers
        if (!$this->counter) {
            JError::raiseNotice('', JText::_('COM_OTTOMO_THERE_ARE_NO_SUBSCRIBERS_TO_EXPORT'));
            return;
        }

        // We need a custom layout
        $this->setLayout('export');

        // Override the limit
        $limit = (int)($this->counter / 4);
        if ($limit < 1) {
            $limit = 1;
        }
        if ($limit > 500) {
            $limit = 500;
        }
        $this->limit = $limit;

        // Unguessable file name, kept server-side: export.run and export.download only accept this one.
        // A new export replaces this session's previous, never downloaded one.
        $session = JFactory::getSession();
        $previous = (string)$session->get('export.file', '', 'com_ottomo');
        if (preg_match('/^ottomo-export-[a-f0-9]{32}\.csv$/', $previous) && is_file(JPATH_SITE.'/tmp/'.$previous)) {
            @unlink(JPATH_SITE.'/tmp/'.$previous);
        }
        $this->filename = 'ottomo-export-'.bin2hex(random_bytes(16)).'.csv';
        $session->set('export.file', $this->filename, 'com_ottomo');

        // Remove exports left behind by interrupted runs (older than one hour)
        foreach ((array)glob(JPATH_SITE.'/tmp/ottomo-export-*.csv') as $stale) {
            if ($stale && filemtime($stale) < time() - 3600) {
                @unlink($stale);
            }
        }

        // Display
        parent::display($tpl);
    }

}
