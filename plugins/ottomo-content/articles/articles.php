<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

class plgOttomocontentArticles extends JPlugin
{
    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        JFactory::getLanguage()->load('com_ottomo', JPATH_ADMINISTRATOR.'/components/com_ottomo'); // Ottomo's backend language file
        $this->isInstalled = $this->isInstalled();
        $settings = JComponentHelper::getParams('com_ottomo');
        $this->params->merge($settings);
    }

    public function ottomoExtensionView(&$view)
    {
        if (!$this->allowed()) {
            $view->rows = array();
            $view->total = 0;
            return;
        }

        // Assign extension specific variables
        $mainframe = JFactory::getApplication();
        $view->catid = $mainframe->getUserStateFromRequest($view->prefix.'.catid', 'catid', 0, 'int');
        $view->search = $mainframe->getUserStateFromRequest($view->prefix.'.search', 'search', '', 'string');
        $view->filterDefaults = array('catid' => 0);
        $view->search = strtolower($view->search);

        // Get rows
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_content/models');
        $model = JModelLegacy::getInstance('Articles', 'ContentModel', array('ignore_request' => true));
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
        $view->assignRef('rows', $rows);

        // Get total
        $total = $model->getTotal();
        $view->assignRef('total', $total);

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
        $view->addFilter($stateFilter, JText::_('COM_OTTOMO_STATE'));

        $options = JHtml::_('category.options', 'com_content');

        $categoryFilter = JHtml::_('select.genericlist', $options, 'catid', '', 'value', 'text', $view->catid);
        $view->addFilter($categoryFilter, JText::_('COM_OTTOMO_CATEGORY'));

    }

    public function ottomoExtensionRowContent($id)
    {
        if (!$this->allowed()) {
            return null;
        }
        JModelLegacy::addIncludePath(JPATH_SITE.'/components/com_content/models');
        JLoader::register('ContentHelperRoute', JPATH_SITE.'/components/com_content/helpers/route.php');
        $params = JComponentHelper::getParams('com_content');
        $model = JModelLegacy::getInstance('Article', 'ContentModel', array('ignore_request' => true));
        $model->setState('article.id', $id);
        $model->setState('params', $params);
        $article = $model->getItem();
        // Only what this user may see on the site (view access levels of the article and its category)
        $levels = JFactory::getUser()->getAuthorisedViewLevels();
        if (!is_object($article) || empty($article->id) || !in_array((int)$article->access, $levels) || (isset($article->category_access) && !in_array((int)$article->category_access, $levels))) {
            return null;
        }
        $article->linkTitle = $article->title;
        $article->safeTitle = htmlspecialchars($article->title, ENT_QUOTES, 'UTF-8');
        $article->slug = $article->alias ? ($article->id.':'.$article->alias) : $article->id;
        $article->catslug = $article->category_alias ? ($article->catid.':'.$article->category_alias) : $article->catid;
        $article->parent_slug = $article->category_alias ? ($article->parent_id.':'.$article->parent_alias) : $article->parent_id;
        $article->link = ContentHelperRoute::getArticleRoute($article->slug, $article->catslug);
        $article->category = new stdClass();
        $article->category->name = $article->category_title;
        $article->category->link = ContentHelperRoute::getCategoryRoute($article->catslug);
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
                $article->introImage = JURI::root().$article->images->image_intro;
                $article->introImageCaption = $article->images->image_intro_caption;
            }
            if ($this->params->get('contentFullImage') && $article->images->image_fulltext) {
                $article->fullImage = JURI::root().$article->images->image_fulltext;
                $article->fullImageCaption = $article->images->image_fulltext_caption;
            }
        }
        return $article;
    }

    public function ottomoExtensionRowLink($id)
    {
        $article = $this->ottomoExtensionRowContent($id);
        if (!$article) {
            return '';
        }
        return '<a href="'.htmlspecialchars($article->link, ENT_QUOTES, 'UTF-8').'">'.$article->safeTitle.'</a>';
    }

    public function ottomoExtensionEditorButton()
    {
        if (!$this->isInstalled || !$this->allowed()) {
            return false;
        }
        $button = new stdClass();
        $button->extension = $this->_name;
        $button->label = JText::_('PLG_OTTOMO_CONTENT_ARTICLES_LABEL');
        return $button;
    }

    protected function isInstalled()
    {
        return true;
    }

    /** Articles are picked by those who manage them (com_content's own "Access Administration Interface") */
    protected function allowed()
    {
        return (bool)JFactory::getUser()->authorise('core.manage', 'com_content');
    }

}
