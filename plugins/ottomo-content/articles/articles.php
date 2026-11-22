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
use Joomla\CMS\Uri\Uri;

require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/compat.php';

class plgOttomocontentArticles extends CMSPlugin
{
    /** Whether the content source (com_content, K2...) is on the site. */
    protected $isInstalled = false;

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
        $view->search = $mainframe->getUserStateFromRequest($view->prefix.'.search', 'search', '', 'string');
        $view->filterDefaults = array('catid' => 0);
        $view->search = strtolower($view->search);

        // Get rows
        $model = OttomoCompat::componentModel('com_content', 'Articles', 'Administrator', 'ContentModel');
        $model->setState('list.limit', $view->limit);
        $model->setState('list.start', $view->limitstart);
        $model->setState('list.ordering', $view->ordering);
        $model->setState('list.direction', $view->orderingDir);
        if ($view->published != -1) {
            $model->setState('filter.published', $view->published);
        }
        if ($view->catid) {
            $model->setState('filter.category_id', array($view->catid));
        }
        if ($view->search) {
            $model->setState('filter.seach', $view->search);
        }
        $rows = $model->getItems();
        $view->rows = $rows;

        // Get total
        $total = $model->getTotal();
        $view->total = $total;

        // Add columns
        $view->addColumn('COM_OTTOMO_TITLE', 'title', 'a.title');
        $view->addColumn('COM_OTTOMO_STATE', 'state', 'a.state', 'class="otm-text-center"');
        $view->addColumn('COM_OTTOMO_CATEGORY', 'category_title', 'category_title');
        $view->addColumn('COM_OTTOMO_AUTHOR', 'author_name', 'author_name');
        $view->addColumn('COM_OTTOMO_ID', 'id', 'id', 'class="otm-text-center"');

        // Add filters
        $searchFilter = OttomoHelperHTML::search($view->search);
        $view->addFilter($searchFilter);

        $stateFilter = OttomoHelperHTML::state($view->published);
        $view->addFilter($stateFilter, Text::_('COM_OTTOMO_STATE'));

        $options = HTMLHelper::_('category.options', 'com_content');

        $categoryFilter = HTMLHelper::_('select.genericlist', $options, 'catid', '', 'value', 'text', $view->catid);
        $view->addFilter($categoryFilter, Text::_('COM_OTTOMO_CATEGORY'));

    }

    public function onOttomoExtensionRowContent($id)
    {
        if (!$this->allowed()) {
            return null;
        }
        $params = ComponentHelper::getParams('com_content');
        $model = OttomoCompat::componentModel('com_content', 'Article', 'Site', 'ContentModel');
        $route = OttomoCompat::contentRoute();
        $model->setState('article.id', $id);
        $model->setState('params', $params);
        $article = $model->getItem();
        // Only what this user may see on the site (view access levels of the article and its category)
        $levels = OttomoCompat::user()->getAuthorisedViewLevels();
        if (!is_object($article) || empty($article->id) || !in_array((int)$article->access, $levels) || (isset($article->category_access) && !in_array((int)$article->category_access, $levels))) {
            return null;
        }
        $article->linkTitle = $article->title;
        $article->safeTitle = htmlspecialchars($article->title, ENT_QUOTES, 'UTF-8');
        $article->slug = $article->alias ? ($article->id.':'.$article->alias) : $article->id;
        $article->catslug = $article->category_alias ? ($article->catid.':'.$article->category_alias) : $article->catid;
        $article->parent_slug = $article->category_alias ? ($article->parent_id.':'.$article->parent_alias) : $article->parent_id;
        $article->link = $route::getArticleRoute($article->slug, $article->catslug);
        $article->category = new stdClass();
        $article->category->name = $article->category_title;
        $article->category->link = $route::getCategoryRoute($article->catslug);
        $article->date = null;
        $article->images = json_decode($article->images);
        $article->introImage = null;
        $article->introImageCaption = null;
        $article->fullImage = null;
        $article->fullImageCaption = null;
        if ($article->created_by_alias) {
            $article->author = $article->created_by_alias;
        }
        if (!$this->params->get('contentTitle')) {
            $article->title = null;
        }
        if (!$this->params->get('contentCategory')) {
            $article->category = null;
        }
        if ($this->params->get('contentDate')) {
            $article->date = $article->created;
        }
        if (!$this->params->get('contentAuthor')) {
            $article->author = null;
        }
        if (!$this->params->get('contentIntrotext')) {
            $article->introtext = null;
        }
        if (!$this->params->get('contentFulltext')) {
            $article->fulltext = null;
        }
        if (is_object($article->images)) {
            if ($this->params->get('contentIntroImage') && $article->images->image_intro) {
                $article->introImage = Uri::root().$article->images->image_intro;
                $article->introImageCaption = $article->images->image_intro_caption;
            }
            if ($this->params->get('contentFullImage') && $article->images->image_fulltext) {
                $article->fullImage = Uri::root().$article->images->image_fulltext;
                $article->fullImageCaption = $article->images->image_fulltext_caption;
            }
        }
        return $article;
    }

    public function onOttomoExtensionRowLink($id)
    {
        $article = $this->onOttomoExtensionRowContent($id);
        if (!$article) {
            return '';
        }
        return '<a href="'.htmlspecialchars($article->link, ENT_QUOTES, 'UTF-8').'">'.$article->safeTitle.'</a>';
    }

    public function onOttomoExtensionEditorButton()
    {
        if (!$this->isInstalled || !$this->allowed()) {
            return false;
        }
        $button = new stdClass();
        $button->extension = $this->_name;
        $button->label = Text::_('PLG_OTTOMO_CONTENT_ARTICLES_LABEL');
        return $button;
    }

    protected function isInstalled()
    {
        return true;
    }

    /** Articles are picked by those who manage them (com_content's own "Access Administration Interface") */
    protected function allowed()
    {
        return (bool)OttomoCompat::user()->authorise('core.manage', 'com_content');
    }

}
