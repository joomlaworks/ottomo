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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\String\StringHelper;

require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/compat.php';

#[\AllowDynamicProperties]
class plgOttomocontentK2 extends CMSPlugin
{
    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        OttomoCompat::language()->load('com_ottomo', JPATH_ADMINISTRATOR.'/components/com_ottomo'); // Ottomo's backend language file
        $this->isInstalled = $this->isInstalled();
        $settings = ComponentHelper::getParams('com_ottomo');
        $this->params->merge($settings);
    }

    public function onOttomoExtensionView($view)
    {
        if (!$this->allowed()) {
            $view->rows = array();
            $view->total = 0;
            return;
        }

        // Assign extension specific variables
        $mainframe = Factory::getApplication();
        $view->catid = $mainframe->getUserStateFromRequest($view->prefix.'.catid', 'catid', 0, 'int');
        $view->featured = $mainframe->getUserStateFromRequest($view->prefix.'.featured', 'featured', -1, 'int');
        $view->trash = $mainframe->getUserStateFromRequest($view->prefix.'.trash', 'trash', -1, 'int');
        $view->search = $mainframe->getUserStateFromRequest($view->prefix.'.search', 'search', '', 'string');
        $view->filterDefaults = array('catid' => 0, 'featured' => -1, 'trash' => -1);
        $view->search = StringHelper::strtolower($view->search);

        // Get rows
        BaseDatabaseModel::addIncludePath(dirname(__FILE__).'/models');
        $model = BaseDatabaseModel::getInstance('K2', 'OttomoModel');
        $model->setState('limit', $view->limit);
        $model->setState('limitstart', $view->limitstart);
        $model->setState('ordering', $view->ordering);
        $model->setState('orderingDir', $view->orderingDir);
        $model->setState('published', $view->published);
        $model->setState('trash', $view->trash);
        $model->setState('featured', $view->featured);
        $model->setState('catid', $view->catid);
        $model->setState('search', $view->search);
        $rows = $model->getList();
        $view->rows = $rows;

        // Get total
        $total = $model->getListCount();
        $view->total = $total;

        // Add columns
        $view->addColumn('COM_OTTOMO_TITLE', 'title', 'i.title');
        $view->addColumn('COM_OTTOMO_STATE', 'published', 'i.published', 'class="otm-text-center"');
        $view->addColumn('COM_OTTOMO_CATEGORY', 'category', 'category');
        $view->addColumn('COM_OTTOMO_AUTHOR', 'author', 'author');
        $view->addColumn('COM_OTTOMO_CREATED', 'created', 'i.created');
        $view->addColumn('COM_OTTOMO_ID', 'id', 'id', 'class="otm-text-center"');

        // Add filters
        $searchFilter = OttomoHelperHTML::search($view->search);
        $view->addFilter($searchFilter);

        $stateFilter = OttomoHelperHTML::state($view->published);
        $view->addFilter($stateFilter, Text::_('COM_OTTOMO_STATE'));

        $categories = $model->getCategories();
        $option = HTMLHelper::_('select.option', 0, Text::_('COM_OTTOMO_ANY'));
        array_unshift($categories, $option);
        $categoryFilter = HTMLHelper::_('select.genericlist', $categories, 'catid', '', 'value', 'text', $view->catid);
        $view->addFilter($categoryFilter, Text::_('COM_OTTOMO_CATEGORY'));

        $options = array();
        $options[] = HTMLHelper::_('select.option', '-1', Text::_('COM_OTTOMO_ANY'));
        $options[] = HTMLHelper::_('select.option', '1', Text::_('COM_OTTOMO_FEATURED'));
        $options[] = HTMLHelper::_('select.option', '0', Text::_('COM_OTTOMO_NOT_FEATURED'));
        $featuredFilter = HTMLHelper::_('select.genericlist', $options, 'featured', '', 'value', 'text', $view->featured);
        $view->addFilter($featuredFilter, Text::_('COM_OTTOMO_FEATURED_STATE'));

        $options = array();
        $options[] = HTMLHelper::_('select.option', '0', Text::_('COM_OTTOMO_CURRENT'));
        $options[] = HTMLHelper::_('select.option', '1', Text::_('COM_OTTOMO_TRASHED'));
        $trashFilter = HTMLHelper::_('select.genericlist', $options, 'trash', '', 'value', 'text', $view->trash);
        $view->addFilter($trashFilter, Text::_('COM_OTTOMO_STATUS'));

    }

    public function onOttomoExtensionRowContent($id)
    {
        if (!$this->allowed()) {
            return null;
        }
        BaseDatabaseModel::addIncludePath(dirname(__FILE__).'/models');
        $model = BaseDatabaseModel::getInstance('K2', 'OttomoModel');
        Table::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');
        $item = Table::getInstance('K2Item', 'Table');
        $item->load($id);
        if (!$item->id || !in_array((int)$item->access, OttomoCompat::user()->getAuthorisedViewLevels())) {
            return null;
        }
        $item->image = null;
        $item->category = $model->getItemCategory($item->catid);
        $item->catalias = $item->category ? $item->category->alias : '';
        $item->safeTitle = htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8');
        $item->author = null;
        $item->date = null;
        $item->tags = array();
        $item->extra_fields = array();
        if ($this->params->get('k2Image') && $this->params->get('k2ImageSize') && is_file(JPATH_SITE.'/media/k2/items/cache/'.md5("Image".$item->id).'_'.$this->params->get('k2ImageSize').'.jpg')) {
            $item->image = Uri::root().'media/k2/items/cache/'.md5("Image".$item->id).'_'.$this->params->get('k2ImageSize').'.jpg';
        }
        if (!$this->params->get('k2Title')) {
            $item->title = null;
        }
        if (!$this->params->get('k2Introtext')) {
            $item->introtext = null;
        }
        if (!$this->params->get('k2Fulltext')) {
            $item->fulltext = null;
        }
        if (!$this->params->get('k2Category')) {
            $item->category = null;
        }
        if (!$this->params->get('k2ImageCaption')) {
            $item->image_caption = null;
        }
        if (!$this->params->get('k2ImageCredits')) {
            $item->image_credits = null;
        }
        if ($this->params->get('k2Tags')) {
            $item->tags = $model->getItemTags($item->id);
        }
        if ($this->params->get('k2ExtraFields')) {
            $item->extra_fields = $model->getItemExtraFields($item->extra_fields);
        }
        if ($this->params->get('k2Author')) {
            $item->author = $model->getItemAuthor($item->created_by, $item->created_by_alias);
        }
        if ($this->params->get('k2Date')) {
            $item->date = $item->created;
        }
        JLoader::register('OttomoHelperK2', dirname(__FILE__).'/helpers/helper.php');
        $item->link = OttomoHelperK2::getItemRoute($item->id.':'.$item->alias, $item->catid.':'.$item->catalias);
        return $item;
    }

    public function onOttomoExtensionRowLink($id)
    {
        if (!$this->allowed()) {
            return '';
        }
        Table::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_k2/tables');
        JLoader::register('K2HelperRoute', JPATH_SITE.'/components/com_k2/helpers/route.php');
        $item = Table::getInstance('K2Item', 'Table');
        $item->load($id);
        if (!$item->id || !in_array((int)$item->access, OttomoCompat::user()->getAuthorisedViewLevels())) {
            return '';
        }
        $db = OttomoCompat::db();
        $db->setQuery("SELECT alias FROM #__k2_categories WHERE id = ".(int)$item->catid);
        $alias = $db->loadResult();
        JLoader::register('OttomoHelperK2', dirname(__FILE__).'/helpers/helper.php');
        $link = OttomoHelperK2::getItemRoute($item->id.':'.$item->alias, $item->catid.':'.$alias);
        return '<a href="'.htmlspecialchars($link, ENT_QUOTES, 'UTF-8').'">'.htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8').'</a>';
    }

    public function onOttomoExtensionEditorButton()
    {
        if (!$this->isInstalled || !$this->allowed()) {
            return false;
        }
        $button = new stdClass();
        $button->extension = $this->_name;
        $button->label = Text::_('PLG_OTTOMO_CONTENT_K2_LABEL');
        return $button;
    }

    protected function isInstalled()
    {
        return is_file(JPATH_SITE.'/components/com_k2/k2.php');
    }

    /** K2 items are picked by those who manage them (com_k2's "Access Administration Interface") */
    protected function allowed()
    {
        return (bool)OttomoCompat::user()->authorise('core.manage', 'com_k2');
    }

}
