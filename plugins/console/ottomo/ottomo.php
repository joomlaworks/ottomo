<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

class PlgConsoleOttomo extends JPlugin
{
    /**
     * Ottomo's commands (Joomla 3.x UTD's command line). The classes are loaded only here, so the plugin is
     * harmless where the command line doesn't exist (stock Joomla 3).
     *
     * @param   object  $app  The console application
     * @return  array
     */
    public function onGetConsoleCommands($app)
    {
        $file = JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/cli/commands.php';
        if (!class_exists('Joomla\\CMS\\Console\\AbstractCommand') || !is_file($file)) {
            return array();
        }
        require_once $file;

        return array(
            new OttomoCampaignListCommand(),
            new OttomoCampaignSendCommand(),
            new OttomoSubscriberImportCommand(),
            new OttomoSubscriberPurgeCommand(),
            new OttomoCheckCommand(),
        );
    }
}
