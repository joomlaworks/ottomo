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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\String\StringHelper;


class JFormFieldProviders extends FormField
{
    public $type = 'providers';

    public function getInput()
    {
        PluginHelper::importPlugin('ottomo-providers');
        $providers = PluginHelper::getPlugin('ottomo-providers');
        // Grouped: built in (PHP Mail), the mail services by name, your own server (Custom SMTP, Sendmail)
        $services = array();
        foreach ($providers as $provider) {
            $services[$provider->name] = Text::_('PLG_OTTOMO_'.StringHelper::strtoupper($provider->name).'_LABEL');
        }
        uasort($services, 'strcasecmp');
        $groups = array(
            'COM_OTTOMO_MAILER_GROUP_BUILTIN' => array('mail' => Text::_('COM_OTTOMO_PHP_MAIL')),
            'COM_OTTOMO_MAILER_GROUP_SERVICES' => $services,
            'COM_OTTOMO_MAILER_GROUP_SERVER' => array('smtp' => Text::_('COM_OTTOMO_SMTP'), 'sendmail' => Text::_('COM_OTTOMO_SENDMAIL')),
        );
        $e = function ($value) {
            return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
        };
        $html = '<select name="'.$e($this->name).'" id="'.$e($this->id).'">';
        foreach ($groups as $label => $options) {
            if (!count($options)) {
                continue;
            }
            $html .= '<optgroup label="'.$e(Text::_($label)).'">';
            foreach ($options as $value => $text) {
                $html .= '<option value="'.$e($value).'"'.((string)$value === (string)$this->value ? ' selected="selected"' : '').'>'.$e($text).'</option>';
            }
            $html .= '</optgroup>';
        }
        return $html.'</select>';
    }

}
