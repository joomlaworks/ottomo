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
use Joomla\CMS\Router\Route;

require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/compat.php';

class OttomoViewFiles extends OttomoView
{
    public function edit($tpl = null)
    {
        if (!OttomoHelperHTML::canManageTemplates()) {
            Factory::getApplication()->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
            Factory::getApplication()->redirect(Route::_('index.php?option=com_ottomo&view=templates', false));
        }
        // Get application
        $application = Factory::getApplication();

        // Get row
        $model = OttomoModel::getInstance('Templates');
        $model->setState('id', OttomoCompat::input()->getInt('template'));
        $this->row = $model->getRow();

        // Get files
        require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/template.php';
        $this->files = OttomoHelperTemplate::getFiles($this->row);
        $this->file = OttomoHelperTemplate::getFile(OttomoCompat::input()->get('file'), $this->row);

        // Set layout
        $this->setLayout('file');

        // Head data
        $document = OttomoCompat::document();
        $document->addScript('https://cdnjs.cloudflare.com/ajax/libs/ace/1.45.2/ace.js');
        $document->addScriptDeclaration("document.addEventListener('DOMContentLoaded', function() { Ottomo.sourceEditor = ace.edit('otm-source-editor'); Ottomo.sourceEditor.setTheme('ace/theme/monokai'); Ottomo.sourceEditor.getSession().setMode('ace/mode/php'); Ottomo.sourceEditor.setOptions({ fontSize: '15px', showPrintMargin: false }); });");

        // Display
        parent::display($tpl);
    }

}
