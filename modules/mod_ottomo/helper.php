<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

use Joomla\CMS\Captcha\Captcha;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/compat.php';

/**
 * Data for the sub-templates (tmpl/<name>/default.php, overridable in templates/<template>/html/mod_ottomo/<name>/).
 * Sub-templates only deal with markup: the fields, topics and spam protection come from here.
 */
class modOttomoHelper
{
    const DEFAULT_LAYOUT = 'compact';

    /** Profile fields: param => (input name, label, input type, autocomplete) — in display order */
    protected static $profileFields = array(
        'subscriberBirthday' => array('birthday', 'MOD_OTTOMO_DATE_OF_BIRTH', 'date', 'bday'),
        'subscriberAddress' => array('address', 'MOD_OTTOMO_ADDRESS', 'textarea', 'street-address'),
        'subscriberCity' => array('city', 'MOD_OTTOMO_CITY', 'text', 'address-level2'),
        'subscriberState' => array('state', 'MOD_OTTOMO_STATE_PROVINCE', 'text', 'address-level1'),
        'subscriberZip' => array('zip', 'MOD_OTTOMO_ZIP_CODE', 'text', 'postal-code'),
        'subscriberCountry' => array('country', 'MOD_OTTOMO_COUNTRY', 'text', 'country-name'),
        'subscriberPhone' => array('phone', 'MOD_OTTOMO_TELEPHONE_FIXED', 'tel', 'tel'),
        'subscriberMobile' => array('mobile', 'MOD_OTTOMO_TELEPHONE_MOBILE', 'tel', 'tel'),
        'subscriberFax' => array('fax', 'MOD_OTTOMO_FAX', 'tel', 'off'),
        'subscriberCompany' => array('company', 'MOD_OTTOMO_COMPANY_NAME', 'text', 'organization'),
        'subscriberPosition' => array('position', 'MOD_OTTOMO_POSITION_IN_COMPANY', 'text', 'organization-title'),
        'subscriberVat' => array('vat', 'MOD_OTTOMO_VAT_NO', 'text', 'off'),
        'subscriberTax' => array('tax', 'MOD_OTTOMO_TAX_AUTHORITY', 'text', 'off'),
    );

    /** The sub-template to use: the selected one if it exists (module or template override), else "compact" */
    public static function getLayout($params)
    {
        $layout = preg_replace('/[^a-z0-9_-]/i', '', (string)$params->get('template', self::DEFAULT_LAYOUT));
        $template = Factory::getApplication()->getTemplate();
        foreach (array(JPATH_THEMES.'/'.$template.'/html/mod_ottomo/', JPATH_SITE.'/modules/mod_ottomo/tmpl/') as $base) {
            if ($layout !== '' && is_file($base.$layout.'/default.php')) {
                return $layout;
            }
        }
        return self::DEFAULT_LAYOUT;
    }

    /**
     * The essential fields: email and (if enabled) name.
     * Each field: name, id, label, type, required, autocomplete.
     */
    public static function getEssentialFields($params, $module)
    {
        $fields = array(self::field($module, 'email', 'MOD_OTTOMO_EMAIL', 'email', true, 'email'));
        if ($params->get('subscriberName')) {
            $fields[] = self::field($module, 'name', 'MOD_OTTOMO_NAME', 'text', $params->get('subscriberName') == '2', 'name');
        }
        return $fields;
    }

    /** The optional profile fields enabled in the module */
    public static function getProfileFields($params, $module)
    {
        $fields = array();
        foreach (self::$profileFields as $param => $definition) {
            if ($params->get($param)) {
                $fields[] = self::field($module, $definition[0], $definition[1], $definition[2], $params->get($param) == '2', $definition[3]);
            }
        }
        return $fields;
    }

    protected static function field($module, $name, $label, $type, $required, $autocomplete)
    {
        $field = new stdClass();
        $field->name = $name;
        $field->id = 'otm-module-'.(int)$module->id.'-'.$name;
        $field->label = Text::_($label);
        $field->type = $type;
        $field->required = (bool)$required;
        $field->autocomplete = $autocomplete;
        return $field;
    }

    /** Renders one field's input (escaped) */
    public static function input($field, $class = '')
    {
        $attributes = 'name="'.$field->name.'" id="'.$field->id.'" autocomplete="'.$field->autocomplete.'"'
            .($class !== '' ? ' class="'.htmlspecialchars($class, ENT_QUOTES, 'UTF-8').'"' : '')
            .($field->required ? ' required aria-required="true"' : '');
        if ($field->type === 'textarea') {
            return '<textarea '.$attributes.' rows="3"></textarea>';
        }
        return '<input type="'.$field->type.'" '.$attributes.' value=""'.($field->type === 'email' ? ' inputmode="email" spellcheck="false"' : '').' />';
    }

    /**
     * The topics shown to the visitor and how they choose them:
     * mode '' = no choice (hidden inputs), 'single' = one (radios), 'multiple' = several (checkboxes)
     */
    public static function getTopics($params)
    {
        $topicIds = $params->get('topics');
        require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/models/model.php';
        $model = OttomoModel::getInstance('Topics');
        $model->setState('published', 1);
        $model->setState('public', true); // never private topics, even if the module was set up with one
        if (is_array($topicIds) && count($topicIds)) {
            $model->setState('id', $topicIds);
        }
        $model->setState('ordering', 'name');
        $model->setState('orderingDir', 'ASC');

        $result = new stdClass();
        $result->items = (array)$model->getList();
        $result->mode = in_array($params->get('userSelect'), array('single', 'multiple'), true) && count($result->items) > 1 ? $params->get('userSelect') : '';
        return $result;
    }

    /** Hidden topic inputs (no choice, or a single topic) */
    public static function hiddenTopics($topics)
    {
        $html = '';
        foreach ($topics->items as $topic) {
            $html .= '<input type="hidden" name="topics[]" value="'.(int)$topic->id.'" />';
        }
        return $html;
    }

    /** The topic choices: label > input + text, the same markup for radios and checkboxes */
    public static function topicChoices($topics, $module, $class = 'otm-topic')
    {
        $type = $topics->mode === 'single' ? 'radio' : 'checkbox';
        $html = '';
        foreach ($topics->items as $index => $topic) {
            $id = 'otm-module-'.(int)$module->id.'-topic-'.(int)$topic->id;
            $html .= '<label class="'.$class.'" for="'.$id.'">'
                .'<input type="'.$type.'" name="topics[]" id="'.$id.'" value="'.(int)$topic->id.'"'.($type === 'radio' && $index === 0 ? ' checked' : '').' />'
                .'<span>'.htmlspecialchars($topic->name, ENT_QUOTES, 'UTF-8').'</span>'
                .'</label>';
        }
        return $html;
    }

    /** The intro text (WYSIWYG, filtered as safe HTML on save) */
    public static function pretext($params)
    {
        return trim((string)$params->get('pretext', ''));
    }

    /** The hidden fields every form needs */
    public static function hiddenFields($module)
    {
        return '<input type="hidden" name="module" value="'.(int)$module->id.'" />'
            .'<input type="hidden" name="option" value="com_ottomo" />'
            .'<input type="hidden" name="task" value="subscribers.subscribe" />'
            .'<input type="hidden" name="format" value="raw" />'
            .HTMLHelper::_('form.token');
    }

    public static function loadAssets($layout)
    {
        $document = OttomoCompat::document();
        JLoader::register('OttomoHelperHTML', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/html.php');
        $override = '/templates/'.Factory::getApplication()->getTemplate().'/html/mod_ottomo/'.$layout.'/css/style.css';
        $stylesheet = is_file(JPATH_SITE.$override) ? $override : '/modules/mod_ottomo/tmpl/'.$layout.'/css/style.css';
        if (is_file(JPATH_SITE.$stylesheet)) {
            $document->addStyleSheet(OttomoHelperHTML::asset(Uri::root(true).$stylesheet));
        }
        $document->addScript(OttomoHelperHTML::asset(Uri::root(true).'/modules/mod_ottomo/includes/js/script.js'), array(), array('defer' => true));
    }

    /** Honeypot (Ottomo Settings > Signup forms): invisible to people, keyboard and screen-reader users */
    public static function honeypot($module)
    {
        JLoader::register('OttomoHelperSettings', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/settings.php');
        if (!OttomoHelperSettings::enabled('honeypot')) {
            return '';
        }
        return '<div class="otm-module-hp" aria-hidden="true" style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden;">'
            .'<label for="otm-module-'.(int)$module->id.'-website">'.Text::_('MOD_OTTOMO_HONEYPOT_LABEL').'</label>'
            .'<input type="text" name="ottomo_website" id="otm-module-'.(int)$module->id.'-website" value="" tabindex="-1" autocomplete="off" />'
            .'</div>';
    }

    /** The captcha widget (Ottomo Settings > Signup forms; default: the site's captcha), or '' */
    public static function captcha($module)
    {
        JLoader::register('OttomoHelperSettings', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/settings.php');
        $plugin = OttomoHelperSettings::captcha();
        if ($plugin === '') {
            return '';
        }
        $captcha = Captcha::getInstance($plugin, array('namespace' => 'mod_ottomo_'.(int)$module->id));
        return $captcha ? (string)$captcha->display('ottomo_captcha', 'otm-module-'.(int)$module->id.'-captcha', 'otm-captcha') : '';
    }
}
