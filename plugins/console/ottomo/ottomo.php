<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;

/*
 * Ottomo's commands (helpers/cli/commands.php), loaded only when the console runs, so the plugin is harmless where
 * there is none (stock Joomla 3). Joomla 3.x UTD asks for them with onGetConsoleCommands; Joomla 4+ consoles take
 * them when the application starts (application.before_execute: a subscriber, which Joomla 3 doesn't know).
 */
if (interface_exists('Joomla\\Event\\SubscriberInterface') && class_exists('Joomla\\Console\\Command\\AbstractCommand')) {
    class PlgConsoleOttomo extends CMSPlugin implements \Joomla\Event\SubscriberInterface
    {
        public static function getSubscribedEvents(): array
        {
            return array(\Joomla\Application\ApplicationEvents::BEFORE_EXECUTE => 'registerCommands');
        }

        public function registerCommands($event)
        {
            $application = $event->getApplication();
            if (!method_exists($application, 'addCommand')) {
                return;
            }
            foreach (self::commands() as $command) {
                $application->addCommand($command);
            }
        }

        public static function commands()
        {
            $file = JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/cli/commands.php';
            if (!is_file($file)) {
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
} else {
    class PlgConsoleOttomo extends CMSPlugin
    {
        /**
         * Joomla 3.x UTD's command line.
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
}
