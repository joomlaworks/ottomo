<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

#[\AllowDynamicProperties]
class OttomoController extends JControllerLegacy
{
    protected $default_view = 'dashboard';

    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->view = $this->input->getCmd('view', $this->default_view);
        $this->task = $this->input->getCmd('task');
    }

    /**
     * Permissions, checked for every task of every controller (base, dotted, .json, .raw): the inherited tasks
     * (save, copy, remove...) work on the model named by "view", so both the controller and the view are checked.
     * Settings: core.admin. Templates and their files: ottomo.templates (the templates list itself is open).
     */
    public function execute($task)
    {
        $controller = strtolower(substr(get_class($this), strlen('OttomoController')));
        $contexts = array_filter(array($controller, strtolower((string)$this->view)));
        $name = strtolower((string)$task);
        $user = JFactory::getUser();
        $allowed = true;
        if (in_array('settings', $contexts, true) && !$user->authorise('core.admin', 'com_ottomo')) {
            $allowed = false;
        }
        if (in_array('files', $contexts, true) || (in_array('templates', $contexts, true) && $name !== '' && $name !== 'display')) {
            JLoader::register('OttomoHelperHTML', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/html.php');
            if (!OttomoHelperHTML::canManageTemplates()) {
                $allowed = false;
            }
        }
        if (!$allowed) {
            $application = JFactory::getApplication();
            if ($this->input->getWord('format', 'html') !== 'html') {
                throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
            }
            $application->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
            $application->redirect(JRoute::_(in_array('settings', $contexts, true) ? 'index.php?option=com_ottomo' : 'index.php?option=com_ottomo&view=templates', false));
        }
        return parent::execute($task);
    }

    public function add()
    {
        $this->setRedirect('index.php?option=com_ottomo&view='.$this->view.'&task=edit');
    }

    public function edit()
    {
        $document = JFactory::getDocument();
        $viewType = $document->getType();
        $viewName = $this->view;
        switch ($viewName) {
            case 'newsletters':
                $layout = 'newsletter';
                break;
            case 'topics':
                $layout = 'topic';
                break;
            case 'subscribers':
                $layout = 'subscriber';
                break;
            case 'templates':
                $layout = 'template';
                break;
            case 'settings':
                $layout = 'settings';
                break;
            case 'files':
                $layout = 'file';
                break;
        }
        $viewLayout = $this->input->getCmd('layout', $layout);
        $view = $this->getView($viewName, $viewType, '', array('base_path' => $this->basePath, 'layout' => $viewLayout));
        if ($model = $this->getModel()) {
            $view->setModel($model, true);
        }
        $view->assignRef('document', $document);
        $view->edit();
        return $this;
    }

    public function apply()
    {
        $this->save();
    }

    public function save()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        $model = $this->getModel();
        $model->setState('data', $this->input->post->getArray());
        if ($model->save()) {
            $message = JText::_('COM_OTTOMO_SAVED');
            $type = 'message';
        } else {
            $message = htmlspecialchars((string)$model->getError(), ENT_QUOTES, 'UTF-8');
            $type = 'error';
        }
        if ($this->task == 'save') {
            $url = 'index.php?option=com_ottomo&view='.$this->view;
        } else {
            $url = 'index.php?option=com_ottomo&view='.$this->view.'&task=edit&&id='.$model->getState('id');
        }
        $this->setRedirect($url, $message, $type);
    }

    public function cancel()
    {
        $this->setRedirect('index.php?option=com_ottomo&view='.$this->view);
    }

    public function publish()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        $model = $this->getModel();
        $model->setState('id', $this->input->getVar('cid'));
        $model->publish();
        $this->setRedirect('index.php?option=com_ottomo&view='.$this->view, JText::_('COM_OTTOMO_'.JString::strtoupper($this->view).'_PUBLISHED'));
    }

    public function unpublish()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        $model = $this->getModel();
        $model->setState('id', $this->input->getVar('cid'));
        $model->unpublish();
        $this->setRedirect('index.php?option=com_ottomo&view='.$this->view, JText::_('COM_OTTOMO_'.JString::strtoupper($this->view).'_UNPUBLISHED'));

    }

    public function copy()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        $model = $this->getModel();
        $model->setState('id', $this->input->getVar('cid'));
        $model->copy();
        $this->setRedirect('index.php?option=com_ottomo&view='.$this->view, JText::_('COM_OTTOMO_'.JString::strtoupper($this->view).'_COPIED'));
    }

    public function remove()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        $model = $this->getModel();
        $model->setState('id', $this->input->getVar('cid'));
        $model->remove();
        $this->setRedirect('index.php?option=com_ottomo&view='.$this->view, JText::_('COM_OTTOMO_'.JString::strtoupper($this->view).'_DELETED'));
    }

    public function batch()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        $model = $this->getModel();
        $model->setState('data', $this->input->getArray());
        $model->batch();
        $this->setRedirect('index.php?option=com_ottomo&view='.$this->view);
    }

    public function getModel($name = '', $prefix = '', $config = array())
    {
        return parent::getModel($this->view);
    }

}
