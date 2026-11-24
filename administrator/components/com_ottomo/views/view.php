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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\String\StringHelper;

require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/compat.php';

#[\AllowDynamicProperties]
class OttomoView extends HtmlView
{
    protected $regions = null;

    /** A list's sorting until the user picks a column: column and direction (views override it) */
    protected $defaultOrdering = array('id', 'desc');

    protected $columns = array();

    protected $filters = array();

    public function __construct($config = array())
    {

        // Parent construct
        parent::__construct($config);

        // Load Helpers
        JLoader::register('OttomoHelperData', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/data.php');
        JLoader::register('OttomoHelperHTML', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/html.php');

        // Add common variables to the view
        $application = Factory::getApplication();

        $this->option = OttomoCompat::input()->getCmd('option');
        $this->view = OttomoCompat::input()->getCmd('view', 'dashboard');
        $this->task = OttomoCompat::input()->getCmd('task');
        $this->format = OttomoCompat::input()->getCmd('format', 'html');
        $this->extension = OttomoCompat::input()->getCmd('extension');
        $this->prefix = $this->option.'.'.$this->view;
        if ($this->extension) {
            $this->prefix .= '.'.$this->extension;
        }
        $this->limit = $application->getUserStateFromRequest('global.list.limit', 'limit', $application->get('list_limit'), 'int');
        $this->limitstart = $application->getUserStateFromRequest($this->prefix.'.limitstart', 'limitstart', 0, 'int');
        $this->ordering = $application->getUserStateFromRequest($this->prefix.'.filter_order', 'filter_order', $this->defaultOrdering[0], 'cmd');
        $this->orderingDir = $application->getUserStateFromRequest($this->prefix.'.filter_order_Dir', 'filter_order_Dir', $this->defaultOrdering[1], 'word');
        $this->published = $application->getUserStateFromRequest($this->prefix.'.published', 'published', -1, 'int');
        $this->status = $application->getUserStateFromRequest($this->prefix.'.status', 'status', -1, 'int');
        $this->topic = $application->getUserStateFromRequest($this->prefix.'.topic', 'topic', 0, 'int');
        $this->search = $application->getUserStateFromRequest($this->prefix.'.search', 'search', '', 'string');
        $this->params = ComponentHelper::getParams($this->option);
        $this->tmpl = OttomoCompat::input()->getCmd('tmpl', 'index');
        $this->template = OttomoCompat::input()->getCmd('template');
        $this->editor = OttomoCompat::input()->getCmd('editor');
        $this->file = OttomoCompat::input()->get('file');

        // Add custom template path
        $this->addTemplatePath(JPATH_BASE.'/components/com_ottomo/layouts');
        $this->addTemplatePath(JPATH_BASE.'/templates/'.$application->getTemplate().'/com_ottomo/layouts');

    }

    public function display($tpl = null)
    {
        // Get application
        $application = Factory::getApplication();

        // Add styles
        $this->addStyles();

        // Add scripts
        $this->addScripts();

        // Prepare data
        if (isset($this->rows)) {
            OttomoHelperData::prepare($this->rows, $this->view, $this->task);
            $this->numOfColumns = count($this->columns);
            $this->numOfRows = count($this->rows);
        }
        if (isset($this->row)) {
            OutputFilter::objectHTMLSafe($this->row);
        }

        // Add pagination
        $this->addPagination();

        // Extra tasks only for administration views
        if ($application->isClient('administrator') && $this->option == 'com_ottomo') {

            // Keep alive the session
            HTMLHelper::_('behavior.keepalive');

            // Auto-set the layout in case it has not be set by the child view or by the URL
            if (OttomoCompat::input()->getCmd('layout') == '' && $this->getLayout() == 'default') {
                $layout = (isset($this->rows)) ? 'list' : 'form';
                $this->setLayout($layout);
            }

            // Add title
            $this->addTitle();

            // Add buttons
            $this->addButtons();

            // Render
            $this->render($tpl);
        } else {
            parent::display($tpl);
        }

    }

    public function setLayout($name)
    {
        $layout = OttomoCompat::input()->getCmd('layout', $name);
        parent::setLayout($layout);
    }

    protected function render($tpl)
    {
        // Init regions object
        $regions = new stdClass();

        if ($this->tmpl == 'component') {
            $regions->header = '';
            $regions->subheader = '';
            $regions->toolbar = '';
        } else {
            // Header
            ob_start();
            include JPATH_COMPONENT_ADMINISTRATOR.'/layouts/header.php';
            $regions->header = ob_get_clean();

            // Subheader
            ob_start();
            include JPATH_COMPONENT_ADMINISTRATOR.'/layouts/subheader.php';
            $regions->subheader = ob_get_clean();

            // Toolbar
            ob_start();
            include JPATH_COMPONENT_ADMINISTRATOR.'/layouts/toolbar.php';
            $regions->toolbar = ob_get_clean();
        }

        // Main
        ob_start();
        parent::display($tpl);
        $regions->main = ob_get_clean();

        // Assing regions
        $this->regions = $regions;

        // Render master layout
        include JPATH_COMPONENT_ADMINISTRATOR.'/layouts/master.php';
    }

    protected function renderFooter()
    {
        include JPATH_COMPONENT_ADMINISTRATOR.'/layouts/footer.php';
    }

    protected function addStyles()
    {
        $application = Factory::getApplication();
        $document = OttomoCompat::document();
        if ($document->getType() == 'html') {
            if ($application->isClient('administrator')) {
                // One stylesheet for the backend (the statistics widget adds css/ottomo-graphs.css, shared with the control panel module)
                $document->addStyleSheet(OttomoHelperHTML::asset(Uri::base(true).'/components/com_ottomo/static/css/ottomo.css'));
                $document->addStyleSheet(OttomoHelperHTML::asset(Uri::base(true).'/components/com_ottomo/static/js/vendor/supermodal/supermodal.min.css'));
            } elseif (is_file(JPATH_COMPONENT.'/static/css/style.css')) {
                $document->addStyleSheet(OttomoHelperHTML::asset(Uri::base(true).'/components/com_ottomo/static/css/style.css'));
            }
        }
    }

    protected function addScripts()
    {
        $application = Factory::getApplication();
        $document = OttomoCompat::document();
        if ($document->getType() == 'html') {
            if ($application->isClient('administrator')) {
                $document->addScript(OttomoHelperHTML::asset(Uri::base(true).'/components/com_ottomo/static/js/vendor/supermodal/supermodal.min.js'));
                HTMLHelper::_('behavior.core');
                Text::script('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST');
                foreach (array('EDITOR_INSERT_IMAGE', 'SENT_TO_N', 'SEND_FAILED_N', 'CSV_UPLOADING', 'CSV_READY', 'BATCH_SELECT_TOPICS', 'REQUEST_FAILED', 'EDITOR_DEVICE', 'EDITOR_DESKTOP', 'EDITOR_MOBILE', 'EDITOR_SCHEME', 'EDITOR_LIGHT', 'EDITOR_DARK', 'OPEN_IN_NEW_TAB', 'PREFLIGHT_SIZE_NOW', 'PREFLIGHT_SIZE_OVER', 'PREFLIGHT_CHECKING') as $key) {
                    Text::script('COM_OTTOMO_'.$key); // used by the frames and Ottomo.preview() (js/ottomo.js)
                }
            }
            if ($application->isClient('administrator')) {
                // One script for the backend (vanilla JS): lists, frames, the campaign wizard, the designer, statistics
                $document->addScript(OttomoHelperHTML::asset(Uri::base(true).'/components/com_ottomo/static/js/ottomo.js'));
            }
        }
    }

    protected function addTitle()
    {
        if (!OttomoCompat::isAdmin()) {
            return false;
        }
        $title = 'COM_OTTOMO_'.StringHelper::strtoupper($this->view);
        if ($this->task == 'edit' && isset($this->row)) {
            $title .= $this->row->id ? '_EDIT' : '_ADD';
        }
        $this->title = Text::_($title);
        ToolbarHelper::title($this->title, 'ottomo');
    }

    protected function addButtons()
    {
        require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/classes/toolbar.php';

        $headerToolbar = new OttomoToolbar();
        $subheaderToolbar = new OttomoToolbar();
        $batchToolbar = new OttomoToolbar();

        if ($this->task == 'edit') {
            $headerToolbar->appendButton('OttomoStandard', 'save', 'COM_OTTOMO_TOOLBAR_SAVE_AND_CLOSE', $this->view.'.save', false);
            $headerToolbar->appendButton('OttomoStandard', 'apply', 'COM_OTTOMO_TOOLBAR_SAVE', $this->view.'.apply', false);
            // Topics and subscribers are often added one after the other: save, then a new empty form
            if (in_array($this->view, array('topics', 'subscribers'), true)) {
                $headerToolbar->appendButton('OttomoStandard', 'save-new', 'COM_OTTOMO_TOOLBAR_SAVE_AND_NEW', $this->view.'.save2new', false);
            }
            $headerToolbar->appendButton('OttomoStandard', 'cancel', 'COM_OTTOMO_CLOSE', $this->view.'.cancel', false);
        } else {
            if ($this->view == 'topics') {
                $batchToolbar->appendButton('OttomoBatch', 'check-circle-o', 'COM_OTTOMO_TOOLBAR_PUBLISH', $this->view.'.publish', true);
                $batchToolbar->appendButton('OttomoBatch', 'circle-o', 'COM_OTTOMO_TOOLBAR_UNPUBLISH', $this->view.'.unpublish', true);
            }

            if ($this->view == 'newsletters') {
                $batchToolbar->appendButton('OttomoBatch', 'copy', 'COM_OTTOMO_COPY', $this->view.'.copy', true);
            }

            if ($this->view == 'subscribers') {
                $batchToolbar->appendButton('OttomoBatch', 'check-circle-o', 'COM_OTTOMO_ENABLE', $this->view.'.enable', true);
                $batchToolbar->appendButton('OttomoBatch', 'circle-o', 'COM_OTTOMO_DISABLE', $this->view.'.disable', true);
            }

            if ($this->view == 'newsletters' || $this->view == 'topics' || $this->view == 'subscribers') {
                $subheaderToolbar->appendButton('OttomoStandard', 'plus', 'COM_OTTOMO_TOOLBAR_NEW', $this->view.'.add', false);
            }

            if ($this->view == 'newsletters' || $this->view == 'topics' || $this->view == 'subscribers') {
                $batchToolbar->appendButton('OttomoBatch', 'trash-o', 'COM_OTTOMO_DELETE', $this->view.'.remove', true);
            }

            if ($this->view == 'newsletters' || $this->view == 'subscribers') {
                $batchToolbar->appendButton('OttomoBatch', 'batch', 'COM_OTTOMO_BATCH', 'batch.modal');
            }

            if ($this->view == 'subscribers' && $this->task != 'import') {
                $subheaderToolbar->appendButton('OttomoStandard', 'upload', 'COM_OTTOMO_IMPORT', $this->view.'.import', false);
                $subheaderToolbar->appendButton('OttomoStandard', 'download', 'COM_OTTOMO_EXPORT', $this->view.'.export', false);
                $subheaderToolbar->appendButton('OttomoStandard', 'export-all', 'COM_OTTOMO_EXPORT_ALL', $this->view.'.exportAll', false);
                $subheaderToolbar->appendButton('OttomoStandard', 'trash-unsubscribed', 'COM_OTTOMO_DELETE_ALL_UNSUBSCRIBED', $this->view.'.removeUnsubscribed', false);
            }

        }

        $this->headerToolbar = $headerToolbar;
        $this->subheaderToolbar = $subheaderToolbar;
        $this->batchToolbar = $batchToolbar;
    }

    protected function addPagination()
    {
        if (isset($this->total)) {
            $this->pagination = new Pagination($this->total, $this->limitstart, $this->limit);
        }
    }

    public function addColumn($name, $property, $ordering = null, $attributes = '')
    {
        $column = new stdClass();
        $column->heading = is_null($ordering) ? Text::_($name) : HTMLHelper::_('grid.sort', $name, $ordering, $this->orderingDir, $this->ordering);
        $column->property = $property;
        if (!empty($attributes)) {
            $attributes = ' '.$attributes;
        }
        $column->attributes = $attributes;
        $this->columns[$property] = $column;
    }

    /** Filters of a view (or of a content plugin's picker) beyond the common ones: request name => default value */
    public $filterDefaults = array();

    /** Every filter with its default value: what "Reset all" restores */
    protected function allFilterDefaults()
    {
        return array_merge(array('search' => '', 'published' => -1, 'status' => -1, 'topic' => 0, 'filter_template' => 0), $this->filterDefaults);
    }

    /** A search or a filter is applied (the list is not what it is by default) */
    public function filtersActive()
    {
        $properties = array('filter_template' => 'templateFilter');
        foreach ($this->allFilterDefaults() as $name => $default) {
            $property = isset($properties[$name]) ? $properties[$name] : $name;
            if (isset($this->$property) && (string)$this->$property !== (string)$default) {
                return true;
            }
        }
        return false;
    }

    /** This list as if no search or filter had been applied: sorting and page size are kept, so are a picker's own parameters */
    public function resetLink()
    {
        $uri = Uri::getInstance();
        $query = $uri->getQuery(true);
        unset($query['task']);
        foreach ($this->allFilterDefaults() as $name => $default) {
            $query[$name] = $default;
        }
        $query['limitstart'] = 0;
        return 'index.php?'.htmlspecialchars(http_build_query($query), ENT_QUOTES, 'UTF-8');
    }

    public function addFilter($content, $label = null)
    {
        $filter = new stdClass();
        $filter->label = $label;
        $filter->content = $content;
        $this->filters[] = $filter;
    }

}
