<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;

class OttomoTableTemplates extends Table
{
    public function __construct($db)
    {
        parent::__construct('#__ottomo_templates', 'id', $db);
    }

    public function check()
    {
        if (trim($this->name) == '') {
            $this->setError(Text::_('COM_OTTOMO_TEMPLATE_MUST_HAVE_A_NAME'));
            return false;
        }
        if (!self::validFolder($this->folder)) {
            $this->setError(Text::_('COM_OTTOMO_TEMPLATE_MUST_HAVE_A_FOLDER'));
            return false;
        }
        return true;
    }

    /** A template folder is one plain directory name (letters, digits, "_", "-", "."): never a path */
    public static function validFolder($folder)
    {
        return is_string($folder) && (bool)preg_match('/^[A-Za-z0-9][A-Za-z0-9_.-]*$/', $folder);
    }

}
