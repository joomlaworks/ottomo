<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/compat.php';

class OttomoViewArchive extends OttomoView
{
    public function display($tpl = null)
    {
        $application = Factory::getApplication();
        $this->limit = OttomoCompat::input()->getInt('limit');
        $this->limitstart = OttomoCompat::input()->getInt('limitstart');

        // Apply maximum limit
        if ($this->limit > 100) {
            $this->limit = 100;
        }

        // Get rows
        $model = OttomoModel::getInstance('Newsletters');
        $model->setState('limit', $this->limit);
        $model->setState('limitstart', $this->limitstart);
        $model->setState('ordering', 'sent');
        $model->setState('orderingDir', 'DESC');
        $model->setState('published', 1);
        $this->rows = $model->getList();

        // Get total
        $this->total = $model->getListCount();

        // Prepare the document
        $application = Factory::getApplication();
        $this->params = $application->getParams('com_ottomo');
        $document = OttomoCompat::document();
        $menu = $application->getMenu();
        $active = $menu->getActive();
        if ($active) {
            $this->params->def('page_heading', $this->params->get('page_title', $active->title));
        } else {
            $this->params->def('page_heading', Text::_('COM_OTTOMO_ARCHIVE'));
        }
        $title = $this->params->get('page_title', '');
        if (empty($title)) {
            $title = $application->get('sitename');
        } elseif ($application->get('sitename_pagetitles', 0) == 1) {
            $title = Text::sprintf('JPAGETITLE', $application->get('sitename'), $title);
        } elseif ($application->get('sitename_pagetitles', 0) == 2) {
            $title = Text::sprintf('JPAGETITLE', $title, $application->get('sitename'));
        }
        $document->setTitle($title);
        if ($this->params->get('menu-meta_description')) {
            $document->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('menu-meta_keywords')) {
            $document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->params->get('robots')) {
            $document->setMetadata('robots', $this->params->get('robots'));
        }

        // Set the layout
        $this->setLayout('archive');

        // Display
        parent::display($tpl);
    }

}
