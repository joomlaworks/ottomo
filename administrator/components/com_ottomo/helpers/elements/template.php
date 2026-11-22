<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Filesystem\Folder;

require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/compat.php';


class JFormFieldTemplate extends FormField
{
    public $type = 'template';

    public function getInput()
    {
        $moduleTemplatesPath = JPATH_SITE.'/modules/mod_ottomo/tmpl';
        $moduleTemplatesFolders = Folder::folders($moduleTemplatesPath);
        $db = OttomoCompat::db();
        $query = "SELECT template FROM #__template_styles WHERE client_id = 0 AND home = 1";
        $db->setQuery($query);
        $template = $db->loadResult();
        $templatePath = JPATH_SITE.'/templates/'.$template.'/html/mod_ottomo';
        if (is_dir($templatePath)) {
            $templateFolders = Folder::folders($templatePath);
            $folders = @array_merge($templateFolders, $moduleTemplatesFolders);
            $folders = @array_unique($folders);
        } else {
            $folders = $moduleTemplatesFolders;
        }
        sort($folders);
        $options = array();
        foreach ($folders as $folder) {
            $options[] = HTMLHelper::_('select.option', $folder, ucfirst(str_replace(array('-', '_'), ' ', $folder))); // "section" -> "Section"
        }
        return HTMLHelper::_('select.genericlist', $options, $this->name, '', 'value', 'text', $this->value);
    }

}
