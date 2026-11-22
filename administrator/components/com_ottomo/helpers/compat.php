<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

if (!class_exists('OttomoCompat', false)) {
    /**
     * The one place that knows how Joomla 3.x UTD and Joomla 4/5/6 differ (existence checks, not version checks).
     */
    class OttomoCompat
    {
        public static function app()
        {
            return Factory::getApplication();
        }

        /** The request: Joomla 6 deprecates $app->input (getInput()); Joomla 3 has no getInput(). */
        public static function input()
        {
            $app = Factory::getApplication();
            return method_exists($app, 'getInput') ? $app->getInput() : $app->input;
        }

        public static function document()
        {
            return Factory::getApplication()->getDocument();
        }

        public static function language()
        {
            $app = Factory::getApplication();
            return method_exists($app, 'getLanguage') ? $app->getLanguage() : Factory::getLanguage();
        }

        public static function session()
        {
            $app = Factory::getApplication();
            return method_exists($app, 'getSession') ? $app->getSession() : Factory::getSession();
        }

        /** Global configuration value. */
        public static function config($name, $default = null)
        {
            return Factory::getApplication()->get($name, $default);
        }

        public static function db()
        {
            if (method_exists('Joomla\CMS\Factory', 'getContainer') && interface_exists('Joomla\Database\DatabaseInterface')) {
                try {
                    return Factory::getContainer()->get('Joomla\Database\DatabaseInterface');
                } catch (\Throwable $e) {
                    // below
                }
            }
            return Factory::getDbo();
        }

        /** The current user, or the one with this id. */
        public static function user($id = null)
        {
            if ($id === null) {
                $app = Factory::getApplication();
                $user = method_exists($app, 'getIdentity') ? $app->getIdentity() : null;
                return $user ? $user : Factory::getUser();
            }
            if (method_exists('Joomla\CMS\Factory', 'getContainer') && interface_exists('Joomla\CMS\User\UserFactoryInterface')) {
                try {
                    return Factory::getContainer()->get('Joomla\CMS\User\UserFactoryInterface')->loadUserById((int)$id);
                } catch (\Throwable $e) {
                    // below
                }
            }
            return Factory::getUser((int)$id);
        }

        /** A new mailer (Joomla's mail settings applied). */
        public static function mailer()
        {
            if (method_exists('Joomla\CMS\Factory', 'getContainer') && interface_exists('Joomla\CMS\Mail\MailerFactoryInterface')) {
                try {
                    return Factory::getContainer()->get('Joomla\CMS\Mail\MailerFactoryInterface')->createMailer();
                } catch (\Throwable $e) {
                    // below
                }
            }
            return Factory::getMailer();
        }

        /**
         * Triggers a plugin event and returns the plugins' answers (an array). Joomla 4+ calls only the on* methods of
         * classic plugins, and passes the arguments by value.
         */
        public static function trigger($event, array $args = array())
        {
            $results = Factory::getApplication()->triggerEvent($event, $args);
            return is_array($results) ? $results : array();
        }

        /** The site's router (to build frontend links from the backend or the command line). */
        public static function siteRouter()
        {
            if (method_exists('Joomla\CMS\Factory', 'getContainer') && class_exists('Joomla\CMS\Router\SiteRouter')) {
                $container = Factory::getContainer();
                if ($container->has('Joomla\CMS\Router\SiteRouter')) {
                    return $container->get('Joomla\CMS\Router\SiteRouter');
                }
            }
            return \Joomla\CMS\Application\CMSApplication::getInstance('site')->getRouter();
        }

        /** The admin page's toolbar: Joomla 4+ keeps it in the document, Joomla 3 has Toolbar::getInstance(). */
        public static function toolbar($name = 'toolbar')
        {
            $document = Factory::getApplication()->getDocument();
            if (method_exists($document, 'getToolbar')) {
                return $document->getToolbar($name);
            }
            return \Joomla\CMS\Toolbar\Toolbar::getInstance($name);
        }

        public static function isAdmin()
        {
            return Factory::getApplication()->isClient('administrator');
        }

        public static function isSite()
        {
            return Factory::getApplication()->isClient('site');
        }

        /** Joomla 4 or later: Atum/Cassiopeia, Bootstrap 5, no K2, no SQLite. */
        public static function isModern()
        {
            return version_compare(JVERSION, '4.0', 'ge');
        }
    }
}
