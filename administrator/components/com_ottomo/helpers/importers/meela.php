<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

JLoader::register('OttomoImporterKunena', __DIR__.'/kunena.php');

/** Meela's forum members: Meela continues Kunena 5.2 with the same element (com_kunena) and tables. */
class OttomoImporterMeela extends OttomoImporterKunena
{
    public function name()
    {
        return 'meela';
    }

    public function label()
    {
        return 'Meela';
    }

    public function isAvailable()
    {
        return $this->componentEnabled('com_kunena', self::MEELA_AUTHOR) && $this->tablesExist(array('#__kunena_users'));
    }
}
