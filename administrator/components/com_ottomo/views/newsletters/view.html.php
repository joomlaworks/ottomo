<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\MailHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;

require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/compat.php';

class OttomoViewNewsletters extends OttomoView
{
    public function display($tpl = null)
    {
        // Get rows
        $model = OttomoModel::getInstance('Newsletters');
        $model->setState('limit', $this->limit);
        $model->setState('limitstart', $this->limitstart);
        $model->setState('ordering', $this->ordering);
        $model->setState('orderingDir', $this->orderingDir);
        $model->setState('published', $this->published);
        $model->setState('topic', $this->topic);
        $model->setState('search', $this->search);
        $this->templateFilter = Factory::getApplication()->getUserStateFromRequest($this->prefix.'.filter_template', 'filter_template', 0, 'int');
        $model->setState('filterTemplate', $this->templateFilter);
        $this->rows = $model->getList();

        // Get total
        $this->total = $model->getListCount();

        // Per-campaign figures (recipients, unique opens and clicks)
        $stats = $model->getCampaignStats(array_map(function ($row) {
            return $row->id;
        }, $this->rows));
        foreach ($this->rows as $row) {
            $row->stats = $stats[(int)$row->id]; // rendered by OttomoHelperData::prepare()
        }

        // Add columns
        $this->addColumn('COM_OTTOMO_CAMPAIGN', 'link', 'newsletter.subject');
        $this->addColumn('COM_OTTOMO_STATUS', 'statusPill', 'newsletter.published');
        $this->addColumn('COM_OTTOMO_SENT_DATE', 'sentDate', 'newsletter.sent');
        $this->addColumn('COM_OTTOMO_RECIPIENTS', 'recipients', null, 'class="otm-text-end"');
        $this->addColumn('COM_OTTOMO_OPEN_RATE', 'openRate', null);
        $this->addColumn('COM_OTTOMO_CLICK_RATE', 'clickRate', null);
        $this->addColumn('COM_OTTOMO_TOPICS', 'topics', null, 'class="otm-listedtopics"');
        $this->addColumn('COM_OTTOMO_TEMPLATE', 'templateName', 'template.name');
        $this->addColumn('COM_OTTOMO_ACTIONS', 'actions', null, 'class="otm-text-end"');

        // Add filters
        $searchFilter = OttomoHelperHTML::search($this->search);
        $this->addFilter($searchFilter);

        $stateFilter = OttomoHelperHTML::state($this->published, 'published', '', true, 'COM_OTTOMO_SENT', 'COM_OTTOMO_NOT_SENT');
        $this->addFilter($stateFilter, Text::_('COM_OTTOMO_STATE'));

        $topicFilter = OttomoHelperHTML::topics('list', $this->topic);
        $this->addFilter($topicFilter, Text::_('COM_OTTOMO_TOPIC'));
        $this->addFilter(OttomoHelperHTML::templates($this->templateFilter), Text::_('COM_OTTOMO_TEMPLATE'));

        // Add batch operations fields
        $this->batchTopics = OttomoHelperHTML::topics('list', null, 'topics[]', 'multiple="multiple" size="10"', false);

        // Display
        parent::display($tpl);

    }

    public function edit($tpl = null)
    {
        // Load scripts
        $document = OttomoCompat::document();
        $document->addScript('https://cdnjs.cloudflare.com/ajax/libs/ace/1.45.2/ace.js');
        foreach (array('PREFLIGHT_BLOCKED', 'PREFLIGHT_WARNINGS', 'PREFLIGHT_READY', 'PREHEADER_COUNT', 'STEP_COMPLETE') as $key) {
            Text::script('COM_OTTOMO_'.$key);
        }
        Text::script('COM_OTTOMO_EDITOR_MOVE');
        Text::script('COM_OTTOMO_EDITOR_REMOVE_BLOCK');
        $document->addScriptDeclaration('Ottomo.adminPath = "'.Uri::base().'"; Ottomo.sitePath = "'.Uri::root().'";');

        // Get row
        $model = OttomoModel::getInstance('Newsletters');
        $model->setState('id', OttomoCompat::input()->getInt('id'));
        $this->row = $model->getRow();
        // "Use template" (Templates page): a new campaign starts with that template chosen
        $template = OttomoCompat::input()->getInt('template');
        if (!$this->row->id && $template > 0) {
            $this->row->template = $template;
        }

        // A snapshot saved with images shrunk to 16px (1.1's first release): the template's images get their width back
        if (!empty($this->row->snapshot) && strpos((string)$this->row->snapshot, 'width="16"') !== false && $this->row->template) {
            JLoader::register('OttomoHelperTemplate', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/template.php');
            $this->row->snapshot = OttomoHelperTemplate::restoreShrunkSnapshot($this->row->snapshot, $this->row->template);
        }

        // Get newsletter data
        $this->row->data = $model->getNewsletterData();
        foreach ($this->row->data as $data) {
            OutputFilter::objectHTMLSafe($data);
        }

        // Get newsletter topics
        $this->row->topics = $model->getNewsletterTopics();

        // Results of a sent campaign (Send step)
        $this->report = null;
        $this->remaining = 0;
        if ((int)$this->row->published === 1 && $this->row->id) {
            $stats = $model->getCampaignStats(array($this->row->id));
            $this->report = $stats[(int)$this->row->id];
            $this->report->links = $model->getTopLinks($this->row->id, 5);
            // An interrupted send: those still waiting for it (Resume sending)
            $this->remaining = $model->getRemaining((int)$this->row->id);
        }

        // Get templates
        $model = OttomoModel::getInstance('Templates');
        $model->setState('ordering', 'id');
        $model->setState('orderingDir', 'DESC');
        $this->templates = $model->getList();
        OttomoHelperData::prepare($this->templates, 'templates');

        // Get form fields
        $this->topics = OttomoHelperHTML::topics('checkboxes', $this->row->topics, 'topics[]', '', false, true);

        // Display
        parent::display($tpl);
    }

    public function popup($tpl = null)
    {
        // Rendered inside a SuperModal frame: always show the layout, with the reason when sending is not possible
        $this->setLayout('popup');
        $this->error = '';
        $this->total = 0;
        $params = ComponentHelper::getParams('com_ottomo');
        $this->limit = (int)$params->get('limit', 50) ?: 50;

        // Get row
        $model = OttomoModel::getInstance('Newsletters');
        $model->setState('id', OttomoCompat::input()->getInt('id'));
        $row = $model->getRow();
        $row->topics = array_filter((array)$model->getNewsletterTopics());
        $this->row = $row;

        // Verify that we can send in case a provider has been selected
        if (!in_array($params->get('mailer', 'mail'), array('mail', 'sendmail', 'smtp'))) {
            PluginHelper::importPlugin('ottomo-providers', $params->get('mailer', 'mail'));
            $result = OttomoCompat::trigger('onOttomoVerify');
            if (isset($result[0]) && $result[0] === false) {
                $this->error = Text::_('COM_OTTOMO_COULD_NOT_INITIATE_SERVICE');
            }
        }

        // Count the recipients
        // Those still waiting for it: everyone in its topics, or after an interrupted send, those it hasn't reached
        $this->resume = (bool)$model->sendingStarted((int)$row->id);
        if (!$this->error && count($row->topics)) {
            $this->total = $model->getRemaining((int)$row->id);
        }
        if (!$this->error && !$this->total) {
            $this->error = Text::_('COM_OTTOMO_THERE_ARE_NO_SUBSCRIBERS_FOR_THE_SPECIFIC_NEWSLETTER');
        }

        // Pre-flight checks (Settings > Campaigns): the same blocking checks as the Send step, on the server
        JLoader::register('OttomoHelperSettings', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/settings.php');
        if (!$this->error && OttomoHelperSettings::enabled('preflightRequired')) {
            $failed = array();
            if (trim((string)$row->subject) === '') {
                $failed[] = Text::_('COM_OTTOMO_PREFLIGHT_SUBJECT');
            }
            $sender = OttomoHelperSettings::sender($row);
            if ($sender->name === '' || !MailHelper::isEmailAddress($sender->email)) {
                $failed[] = Text::_('COM_OTTOMO_PREFLIGHT_SENDER');
            }
            if (!OttomoHelperSettings::enabled('complianceFooter')) {
                JLoader::register('OttomoHelperTemplate', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/template.php');
                $row->data = $model->getNewsletterData();
                $html = OttomoHelperTemplate::render($row, 'send', false);
                if (stripos($html, 'subscribers.unsubscribe') === false) {
                    $failed[] = Text::_('COM_OTTOMO_PREFLIGHT_UNSUBSCRIBE');
                }
            }
            if (count($failed)) {
                $this->error = Text::_('COM_OTTOMO_PREFLIGHT_BLOCKED').' '.implode(' · ', $failed);
            }
        }

        parent::display($tpl);
    }

    public function sample($tpl = null)
    {
        $this->setLayout('sample');
        parent::display($tpl);
    }

}
