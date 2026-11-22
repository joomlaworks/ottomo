<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\Folder;
use Joomla\String\StringHelper;

require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/compat.php';

JLoader::register('OttomoHelperHTML', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/html.php');
JLoader::register('OttomoHelperData', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/data.php');

class OttomoHelperTemplate
{
    private static $base = null;
    private static $directory = null;
    private static $tracking = null;
    private static $trackedNewsletter = 0;
    private static $folder = null;
    private static $mode = null;
    private static $sampleData = null;
    private static $sampleDataIndex = array();

    public static function render($newsletter, $mode = 'view', $tracking = true, $subscriber = false)
    {
        // Set tracking flag
        self::$tracking = $tracking;

        // Import filesystem classes

        // Load language file
        $siteLanguage = ComponentHelper::getParams('com_languages')->get('site');
        $language = OttomoCompat::language();
        $language->load('com_ottomo', JPATH_SITE.'/components/com_ottomo', $siteLanguage);

        // Get date
        $date = Factory::getDate();

        // Determine paths
        self::setPaths($newsletter->template);

        // Sample data
        self::loadSampleData();

        // Set the mode
        self::$mode = $mode;

        // The head: title, base and, in the designer, its editing styles (they never reach the email)
        $head = '<title>'.htmlspecialchars((string)$newsletter->subject, ENT_QUOTES, 'UTF-8').'</title>'."\n"
            .'<base href="'.htmlspecialchars(self::$base, ENT_QUOTES, 'UTF-8').'" />'."\n"
            .'<meta name="generator" content="Ottomo" />';
        if (self::$mode == 'edit') {
            JLoader::register('OttomoHelperHTML', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/html.php');
            $head .= "\n".'<link href="'.htmlspecialchars(OttomoHelperHTML::asset(Uri::base(true).'/components/com_ottomo/static/css/designer.css'), ENT_QUOTES, 'UTF-8').'" rel="stylesheet" />';
        }

        // The template's default.php (no Joomla document: the same on Joomla 3 and 4+, and nothing of the admin page
        // leaks into the email); <jdoc:include type="head" /> is where the head goes
        $buffer = self::includeFile(self::$directory.'/'.self::$folder.'/default.php');
        $buffer = preg_replace('#<jdoc:include\s+type="head"\s*/?>#i', $head, $buffer);

        // Set the search and replacements arrays. Common replacements first.
        $patterns = array();
        $replacements = array();

        // Base URL
        $patterns[] = '{{base}}';
        $replacements[] = self::$base;

        // Site URL
        $patterns[] = '{{site}}';
        $replacements[] = Uri::root();

        // Date
        $patterns[] = '{{date}}';
        $replacements[] = $date->toSql();

        // Newsletter subject
        $patterns[] = '{{subject}}';
        $replacements[] = $newsletter->subject;

        // Newsletter link
        $patterns[] = '{{view_in_browser}}';
        $campaignLink = 'index.php?option=com_ottomo&view=newsletters&id='.$newsletter->id;
        if ($mode == 'send' || ($mode == 'view' && $subscriber)) {
            $campaignLink .= '&subscriber={{subscriber_id}}&hash={{subscriber_hash}}';
        }
        $replacements[] = self::route($campaignLink);

        // Subscriber edit link
        $patterns[] = '{{preferences}}';
        $replacements[] = $mode == 'view' && !$subscriber ? self::route($campaignLink).'#' : self::route('index.php?option=com_ottomo&view=subscribers&id={{subscriber_id}}&newsletter={{campaign_id}}&hash={{subscriber_hash}}');

        // Subscriber unsubscribe link
        $patterns[] = '{{unsubscribe}}';
        $replacements[] = $mode == 'view' && !$subscriber ? self::route($campaignLink).'#' : self::route('index.php?option=com_ottomo&task=subscribers.unsubscribe&id={{subscriber_id}}&newsletter={{campaign_id}}&hash={{subscriber_hash}}&topics={{topic_ids}}');

        // Topic names ("Daily Briefing, Weekly Digest"): for the "why you received this email" line
        // Why the reader gets the email: Settings > Compliance (own message) or the stock line; it may hold placeholders
        $patterns[] = '{{why}}';
        $replacements[] = self::why();

        $patterns[] = '{{topics}}';
        $replacements[] = self::topicNames(is_array($newsletter->topics) ? $newsletter->topics : array());

        // Site name, and the signup page (Settings > Signup forms; default: the home page) for forwarded emails
        $patterns[] = '{{sitename}}';
        JLoader::register('OttomoHelperSettings', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/settings.php');
        $replacements[] = htmlspecialchars(OttomoHelperSettings::siteName(), ENT_QUOTES, 'UTF-8');
        $patterns[] = '{{subscribe}}';
        JLoader::register('OttomoHelperSettings', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/settings.php');
        $subscribePage = trim((string)OttomoHelperSettings::get('subscribePage'));
        $replacements[] = htmlspecialchars($subscribePage !== '' ? $subscribePage : Uri::root(), ENT_QUOTES, 'UTF-8');

        // Newsletter topics
        $patterns[] = '{{topic_ids}}';
        $replacements[] = is_array($newsletter->topics) ? implode('|', $newsletter->topics) : 0;

        // Edit mode
        if (self::$mode == 'edit') {
            // Get extension buttons
            PluginHelper::importPlugin('ottomo-content');
            $buttons = OttomoCompat::trigger('onOttomoExtensionEditorButton');
            $buttons = array_filter($buttons);

            // Labels of the block controls
            $moveLabel = htmlspecialchars(Text::_('COM_OTTOMO_EDITOR_MOVE'), ENT_QUOTES, 'UTF-8');
            $removeLabel = htmlspecialchars(Text::_('COM_OTTOMO_EDITOR_REMOVE_BLOCK'), ENT_QUOTES, 'UTF-8');

            // Match all the positions
            preg_match_all('#\{\{position:([a-z0-9_]+):(text|html)\}\}#i', $buffer, $matches);

            // Build the pattern/replacement variables for each position
            foreach ($matches[0] as $key => $pattern) {
                // Get position name and type
                $name = $matches[1][$key];
                $type = strtolower($matches[2][$key]);

                // Position replacement pattern
                $patterns[] = '{{position:'.$name.':'.$type.'}}';

                // Position type text
                if ($type == 'text') {
                    $replacements[] = '<div id="'.$name.'" contenteditable="true" class="otm-text-data">'.self::getPositionValue($name, $newsletter->data).'</div>';
                }
                // Position type HTML
                elseif ($type == 'html') {

                    // Get position value
                    $value = self::getPositionValue($name, $newsletter->data);

                    // Add extra class if the value is from sample data
                    $class = 'otm-position';
                    if (!empty(self::$sampleDataIndex[$name])) {
                        $class .= ' otm-sample-data';
                    }

                    $replacement = '<div class="'.$class.'"><ul class="otm-extension-menu extension-count'.count($buttons).'"><li><span class="otm-nsl-btn"><a class="otm-extension-menu-add-button">'.OttomoHelperHTML::icon('plus-circle').'<span class="visually-hidden">'.Text::_('COM_OTTOMO_ADD').'</span></a></span><ul>';

                    // Add the custom content button
                    $replacement .= '<li><a class="otm-custom-content-button" data-target="'.$name.'" href="#"> <img src="'.Uri::root(true).'/administrator/components/com_ottomo/static/images/html.svg" width="32" height="32" alt="" /> <span class="link">HTML</span></a></li>';

                    // Add the extension buttons to position
                    if (count($buttons)) {
                        foreach ($buttons as $button) {
                            $class = 'otm-modal-'.strtolower($button->extension);
                            $link = isset($button->link) ? $button->link.'&fieldid='.$name : Uri::base(true).'/index.php?option=com_ottomo&view=extension&extension='.$button->extension.'&editor='.$name.'&template='.$newsletter->template.'&tmpl=component';
                            $replacement .= '<li><a class="otm-extension-button otm-modal '.$class.'" href="'.$link.'"> <img src="'.Uri::root(true).'/plugins/ottomo-content/'.$button->extension.'/icon.svg" width="32" height="32" alt="" /> <span class="link">'.$button->label.'</span></a></li>';
                        }
                    }
                    $replacement .= '</ul></li></ul><div id="'.$name.'" class="otm-extensions-data otm-sortable">';

                    // Render position value back to extension rows
                    if ($value) {
                        $rows = explode('{extension}', $value);
                        foreach ($rows as $row) {
                            $replacement .= '<div class="text-right otm-sortable-item">
                            <div class="text-left otm-position-content">'.$row.'</div>
                        <button type="button" class="otm-nsl-btn otm-nsl-btn--move otm-sortable-handle" title="'.$moveLabel.'" aria-label="'.$moveLabel.'">'.OttomoHelperHTML::icon('arrows-move').'</button>
                        <button type="button" class="otm-nsl-btn otm-nsl-btn--remove otm-sortable-remove-button" title="'.$removeLabel.'" aria-label="'.$removeLabel.'">'.OttomoHelperHTML::icon('dash-circle').'</button>
                        </div>';
                        }
                    }
                    $replacement .= '</div></div>';
                    $replacements[] = $replacement;
                } else {
                    $replacements[] = '';
                }
            }
        }
        // View mode and send mode
        else {

            // Check for snapshot first
            if ($newsletter->snapshot) {
                $doc = new DOMDocument();
                @$doc->loadHTML($newsletter->snapshot);
                $headLinks = $doc->getElementsByTagName('link');
                while ($headLinks->length > 0) {
                    $headLink = $headLinks->item(0);
                    $headLink->parentNode->removeChild($headLink);
                }

                $xpath = new DomXPath($doc);

                $extensionMenus = $xpath->query("//*[contains(@class, 'otm-extension-menu')]");
                foreach ($extensionMenus as $extensionMenu) {
                    $extensionMenu->parentNode->removeChild($extensionMenu);
                }

                // Editable regions (designer, and legacy Aloha markup in older snapshots)
                foreach ($xpath->query('//*[@contenteditable]') as $editable) {
                    $editable->removeAttribute('contenteditable');
                }
                foreach ($xpath->query('//*[@class]') as $element) {
                    $classes = preg_split('/\s+/', trim($element->getAttribute('class')));
                    $classes = array_diff($classes, array('aloha-editable', 'aloha-active', 'otm-editing', 'otm-text-data'));
                    if (count($classes)) {
                        $element->setAttribute('class', implode(' ', $classes));
                    } else {
                        $element->removeAttribute('class');
                    }
                }

                $alohaElements = $xpath->query("//*[contains(@class, 'aloha-caret')]");
                foreach ($alohaElements as $alohaElement) {
                    $alohaElement->parentNode->removeChild($alohaElement);
                }

                //$doc->formatOutput = true;
                $buffer = $doc->saveHTML();
                $buffer = preg_replace_callback('#href="index.php\?([^"]*)#m', array('ottomoHelperTemplate', 'routeCallback'), $buffer);
            } else {
                // Place content in positions
                $contents = array();

                // Perform the routing for the extensions content
                foreach ($newsletter->data as $entry) {
                    $entry->content = self::stripEditorMarkup($entry->content);
                    $entry->content = preg_replace_callback('#href="index.php\?([^"]*)#m', array('ottomoHelperTemplate', 'routeCallback'), $entry->content);
                    $contents[$entry->position] = $entry->content;
                }

                // Match all the positions
                preg_match_all('#\{\{position:([a-z0-9_]+):(text|html)\}\}#i', $buffer, $matches);

                // For each position build the replacement variable
                foreach ($matches[0] as $key => $pattern) {
                    // Get position name and type
                    $name = $matches[1][$key];
                    $type = strtolower($matches[2][$key]);

                    // Build the pattern
                    $patterns[] = '{{position:'.$name.':'.$type.'}}';

                    // Build the replacement
                    if (isset($contents[$name])) {
                        $replacements[] = $contents[$name];
                    } else {
                        $replacements[] = '';
                    }
                }

                // Remove the content editable attribute
                $patterns[] = 'contenteditable="true"';
                $replacements[] = '';

                // Remove the extension rows seperator
                $patterns[] = '{extension}';
                $replacements[] = '';
            }

            // Preheader (inbox preview text) and compliance footer
            $buffer = self::addPreheader($buffer, isset($newsletter->preheader) ? $newsletter->preheader : '');
            $buffer = self::addComplianceFooter($buffer);

            // In send mode add the open tracking image
            if (self::$mode == 'send' && self::$tracking) {
                $trackingImage = '<img style="width: 1px; height: 1px; border: none;" src="'.self::route('index.php?option=com_ottomo&task=newsletters.track&id={{campaign_id}}&subscriber={{subscriber_id}}&type=3&hash={{subscriber_hash}}&format=raw').'" alt="'.Text::_('COM_OTTOMO_TRACKING_IMAGE').'" />';
                $buffer = StringHelper::str_ireplace('</body>', $trackingImage.'</body>', $buffer);
            }
        }

        // Newsletter id. Needs to be last becasue it's already used in above replacements
        $patterns[] = '{{campaign_id}}';
        $replacements[] = $newsletter->id;

        // Check for subscriber provided in the URL when we are in view mode
        if ($mode == 'view' && $subscriber) {
            $patterns[] = '{{subscriber_id}}';
            $replacements[] = (int)$subscriber->id;
            $patterns[] = '{{subscriber_hash}}';
            $replacements[] = preg_replace('/[^A-Za-z0-9]/', '', (string)$subscriber->hash); // it ends up in href attributes
        }

        // Perform all the replacements
        foreach ($patterns as $key => $pattern) {
            $buffer = StringHelper::str_ireplace($pattern, (string)$replacements[$key], $buffer);
        }

        // Click tracking for external links (template and content alike), now that every placeholder is filled
        $buffer = self::trackExternalLinks($buffer, (int)$newsletter->id);

        // Classic Outlook: background colours as bgcolor attributes, and padded buttons (it ignores the padding of links)
        if ($mode != 'edit') {
            $buffer = self::outlook($buffer);
        }

        // Sent emails: smaller (Gmail clips messages over 102 KB), and with a unique ending (Gmail folds content
        // repeated from earlier emails, such as the footer, behind a "..." button)
        if ($mode == 'send') {
            $buffer = self::uniqueEnding(self::minify($buffer), (int)$newsletter->id);
        }

        // Return the buffer
        return $buffer;
    }

    /** A template file's output (included in its own scope). */
    protected static function includeFile($file)
    {
        if (!is_file($file)) {
            return '';
        }
        ob_start();
        include $file;
        return (string)ob_get_clean();
    }

    /** Gmail clips emails whose HTML is larger than this (bytes); the pre-flight checklist warns above it. */
    const GMAIL_CLIP_BYTES = 102400;

    /**
     * Smaller HTML for sending: HTML comments removed (Outlook's conditional comments kept), runs of whitespace
     * collapsed (to one line break when they contained one, so lines stay short); <pre>, <textarea>, <style> and
     * <script> blocks untouched (old-style CSS hides inside <!-- -->).
     */
    public static function minify($html)
    {
        $parts = preg_split('#(<(pre|textarea|style|script)\b[^>]*>.*?</\2>)#is', (string)$html, -1, PREG_SPLIT_DELIM_CAPTURE);
        $out = '';
        for ($i = 0, $n = count($parts); $i < $n; $i++) {
            if ($i % 3 === 1) {
                $out .= $parts[$i]; // a <pre> or <textarea> block, as it is
                continue;
            }
            if ($i % 3 === 2) {
                continue; // the tag name captured inside the block
            }
            $chunk = preg_replace('#<!--(?!\[if|<!|\s*\[endif)(?:(?!-->).)*-->#s', '', $parts[$i]);
            $chunk = preg_replace_callback('#\s{2,}|\n\s*|\t#', function ($match) {
                return strpos($match[0], "\n") !== false ? "\n" : ' ';
            }, $chunk);
            $out .= $chunk;
        }
        return trim($out);
    }

    /**
     * Classic Outlook for Windows (Word's HTML engine): cells and tables with an inline background colour get it as a
     * bgcolor attribute too; a button (a cell with a background around a padded inline-block link) gets the link's
     * padding as mso-padding-alt on the cell, and the otm-button / otm-button__link classes (dark mode keeps their colours).
     */
    public static function outlook($html)
    {
        $html = preg_replace_callback('#<(td|th|table)\b[^>]*>#i', function ($match) {
            $tag = $match[0];
            if (stripos($tag, 'bgcolor=') !== false || !preg_match('#\sstyle=("|\')(.*?)\1#is', $tag, $style)) {
                return $tag;
            }
            if (preg_match('#background(?:-color)?\s*:\s*(\#[0-9a-f]{3}(?:[0-9a-f]{3})?)\b#i', $style[2], $color)) {
                $tag = preg_replace('#^<(\w+)#', '<$1 bgcolor="'.strtolower($color[1]).'"', $tag);
            }
            return $tag;
        }, (string)$html);

        return preg_replace_callback('#(<td\b[^>]*>)(\s*)(<a\b[^>]*>)#i', function ($match) {
            list(, $cell, $space, $link) = $match;
            if (!preg_match('#\sstyle=("|\')(.*?)\1#is', $cell, $cellStyle) || stripos($cellStyle[2], 'background') === false) {
                return $match[0];
            }
            if (!preg_match('#\sstyle=("|\')(.*?)\1#is', $link, $linkStyle) || stripos($linkStyle[2], 'inline-block') === false
                || !preg_match('#(?:^|;)\s*padding\s*:\s*([^;]+)#i', $linkStyle[2], $padding)) {
                return $match[0];
            }
            if (stripos($cellStyle[2], 'mso-padding-alt') === false) {
                $value = rtrim(trim($cellStyle[2]), ';').'; mso-padding-alt: '.trim($padding[1]).';';
                $cell = str_replace($cellStyle[0], ' style='.$cellStyle[1].$value.$cellStyle[1], $cell);
            }
            return self::addClass($cell, 'otm-button').$space.self::addClass($link, 'otm-button__link');
        }, $html);
    }

    /** Adds a class to an opening tag. */
    protected static function addClass($tag, $class)
    {
        if (preg_match('#\sclass=("|\')(.*?)\1#is', $tag, $match)) {
            if (in_array($class, preg_split('#\s+#', $match[2]))) {
                return $tag;
            }
            return str_replace($match[0], ' class='.$match[1].trim($match[2].' '.$class).$match[1], $tag);
        }
        return preg_replace('#^<(\w+)#', '<$1 class="'.$class.'"', $tag);
    }

    /** A hidden line unique to the campaign and the moment it is sent, just before </body>. */
    public static function uniqueEnding($html, $newsletterId)
    {
        $marker = '<div style="display:none;max-height:0;overflow:hidden;font-size:1px;line-height:1px;color:transparent;mso-hide:all;" aria-hidden="true">'
            .(int)$newsletterId.'.'.Factory::getDate()->format('YmdHis').'</div>';
        $count = 0;
        $html = preg_replace('#</body>#i', $marker.'</body>', $html, 1, $count);
        return $count ? $html : $html.$marker;
    }

    public static function loadSampleData()
    {
        if (is_file(self::$directory.'/'.self::$folder.'/sampleData.ini')) {
            self::$sampleData = parse_ini_file(self::$directory.'/'.self::$folder.'/sampleData.ini');
        }
    }

    public static function getPositionValue($name, $data)
    {
        $value = null;
        self::$sampleDataIndex[$name] = false;
        foreach ($data as $entry) {
            if ($entry->position == $name) {
                $value = $entry->content;
            }
        }
        if (is_null($value)) {
            if (is_array(self::$sampleData) && isset(self::$sampleData[$name])) {
                $value = '<div contenteditable="true">'.self::$sampleData[$name].'</div>';
                // Resolved here: positions are filled in after the placeholders, and sample content may be saved as is
                $value = str_replace(array('{{base}}', '{{site}}'), array(self::$base, Uri::root()), $value);
                self::$sampleDataIndex[$name] = true;
            }
        }

        return $value;
    }

    public static function renderExtension($extension, $row, $template)
    {
        // Determine paths
        self::setPaths($template);

        // Render
        $buffer = '';
        $path = self::$directory.'/'.self::$folder.'/extensions';
        $layout = StringHelper::strtolower($extension).'.php';
        if (is_file($path.'/'.$layout)) {
            ob_start();
            include $path.'/'.$layout;
            $buffer = ob_get_clean();
        }

        return $buffer;
    }

    /** The "why you get this email" line: Settings > Compliance > Why this email (escaped, line breaks kept), or the stock one */
    public static function why()
    {
        JLoader::register('OttomoHelperSettings', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/settings.php');
        $own = trim((string)OttomoHelperSettings::get('whyMessage'));
        if ($own !== '') {
            return nl2br(htmlspecialchars($own, ENT_QUOTES, 'UTF-8'));
        }
        OttomoCompat::language()->load('com_ottomo', JPATH_SITE.'/components/com_ottomo');
        return Text::sprintf('COM_OTTOMO_TMPL_WHY', '{{topics}}', '{{sitename}}');
    }

    /** The site's default template (client 0), e.g. "cassiopeia"; '' when none */
    public static function siteTemplate()
    {
        static $template = null;
        if ($template === null) {
            $db = OttomoCompat::db();
            $query = $db->getQuery(true)
                ->select($db->quoteName('s.template'))
                ->from($db->quoteName('#__template_styles', 's'))
                ->leftJoin($db->quoteName('#__extensions', 'e').' ON '.$db->quoteName('e.element').' = '.$db->quoteName('s.template').' AND '.$db->quoteName('e.type').' = '.$db->quote('template').' AND '.$db->quoteName('e.client_id').' = 0')
                ->where($db->quoteName('s.client_id').' = 0')
                ->where($db->quoteName('s.home').' = '.$db->quote('1'))
                ->where($db->quoteName('e.enabled').' = 1');
            $template = basename((string)$db->setQuery($query, 0, 1)->loadResult());
        }
        return $template;
    }

    /**
     * Where an email template's files are: the site template's html/ottomo/<folder>/ (custom templates, and overrides of
     * the built-in ones) first, then components/com_ottomo/templates/<folder>/.
     * Returns {path: absolute folder, rel: path from the site root (for URLs), custom: in the site template}.
     */
    public static function location($folder)
    {
        $folder = basename((string)$folder);
        $site = self::siteTemplate();
        if ($folder !== '' && $site !== '' && is_file(JPATH_SITE.'/templates/'.$site.'/html/ottomo/'.$folder.'/default.php')) {
            $rel = 'templates/'.$site.'/html/ottomo/'.$folder;
            return (object)array('path' => JPATH_SITE.'/'.$rel, 'rel' => $rel, 'custom' => true);
        }
        $rel = 'components/com_ottomo/templates/'.$folder;
        return (object)array('path' => JPATH_SITE.'/'.$rel, 'rel' => $rel, 'custom' => false);
    }

    /** A template's preview or thumbnail image, relative to the site root: preview.webp (preferred), .avif, .jpg, .jpeg or .png; '' if none */
    public static function image($location, $name)
    {
        foreach (array('webp', 'avif', 'jpg', 'jpeg', 'png') as $extension) {
            if (is_file($location->path.'/'.$name.'.'.$extension)) {
                return $location->rel.'/'.$name.'.'.$extension;
            }
        }
        return '';
    }

    public static function setPaths($templateId)
    {
        $model = BaseDatabaseModel::getInstance('Templates', 'OttomoModel');
        $template = $model->getTable();
        $template->load($templateId);
        if (!$template->id) {
            throw new Exception(Text::_('COM_OTTOMO_TEMPLATE_NOT_FOUND'), 404);
        }
        self::$folder = $template->folder;
        $location = self::location($template->folder);
        if (is_file($location->path.'/default.php')) {
            self::$directory = dirname($location->path);
            self::$base = Uri::root().$location->rel.'/';
        } else {
            throw new Exception(Text::_('COM_OTTOMO_TEMPLATE_NOT_FOUND'), 404);
        }
    }

    /** The campaign's public page on the site ("View in browser"), HTML-escaped; sent (published) campaigns only. */
    public static function publicLink($id)
    {
        return self::route('index.php?option=com_ottomo&view=newsletters&id='.(int)$id);
    }

    public static function route($url)
    {
        $uri = OttomoCompat::siteRouter()->build($url);
        $result = $uri->toString();
        $result = str_replace(Uri::base(true).'/', Uri::root(false), $result);
        $result = preg_replace('/\s/u', '%20', $result);
        $result = htmlspecialchars($result);

        return $result;
    }

    /**
     * Send mode with tracking: external http(s) links go through newsletters.track too (type 2), with a signature
     * (OttomoHelperData::linkSignature) so the tracker only redirects off-site to links Ottomo wrote.
     * Placeholders ({{unsubscribe}} etc.) are filled in later and are never wrapped.
     */
    /** The names of the campaign's topics, escaped; "our newsletter" when it has none */
    protected static function topicNames($ids)
    {
        $ids = array_values(array_filter(array_map('intval', (array)$ids)));
        $names = array();
        if (count($ids)) {
            $db = OttomoCompat::db();
            $query = $db->getQuery(true)->select($db->quoteName('name'))->from($db->quoteName('#__ottomo_topics'))
                ->where($db->quoteName('id').' IN ('.implode(',', $ids).')')->order($db->quoteName('name'));
            $names = (array)$db->setQuery($query)->loadColumn();
        }
        $text = count($names) ? implode(', ', $names) : Text::_('COM_OTTOMO_TMPL_OUR_NEWSLETTER');
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    public static function trackExternalLinks($html, $newsletterId = 0)
    {
        if (self::$mode !== 'send' || !self::$tracking) {
            return $html;
        }
        self::$trackedNewsletter = (int)$newsletterId;
        return preg_replace_callback('#href="(https?://[^"{}]+)"#i', array('ottomoHelperTemplate', 'externalLinkCallback'), $html);
    }

    public static function externalLinkCallback($matches)
    {
        $url = html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
        if (stripos($url, 'newsletters.track') !== false || stripos($url, 'option=com_ottomo') !== false || stripos($url, '/component/ottomo/') !== false) {
            return $matches[0]; // Ottomo's own links (tracking, unsubscribe, preferences)
        }
        $redirect = strtr(base64_encode($url), '+/=', '-_,');
        return 'href="'.self::route('index.php?option=com_ottomo&task=newsletters.track&id='.self::$trackedNewsletter.'&subscriber={{subscriber_id}}&type=2&hash={{subscriber_hash}}&redirect='.$redirect.'&sig='.OttomoHelperData::linkSignature($url).'&format=raw').'"';
    }

    public static function routeCallback($matches)
    {
        $url = $matches[1];
        $route = self::route('index.php?'.$url);
        $route = StringHelper::str_ireplace('&amp;', '&', $route);
        if (self::$tracking) {
            $route = strtr(base64_encode($route), '+/=', '-_,');
            $link = self::route('index.php?option=com_ottomo&task=newsletters.track&id={{campaign_id}}&subscriber={{subscriber_id}}&type=2&hash={{subscriber_hash}}&redirect='.$route.'&format=raw');

            return 'href="'.$link;
        } else {
            return 'href="'.$route;
        }
    }

    public static function getFiles($template)
    {
        $source = self::location($template->folder)->path;
        $files = array();
        if ($template->folder && is_dir($source)) {
            $list = Folder::files($source, '.php', true, true);
            foreach ($list as $entry) {
                $row = new stdClass();
                $row->name = basename($entry);
                $row->path = str_replace($source.'/', '', $entry);
                $row->link = Route::_('index.php?option=com_ottomo&view=files&task=edit&template='.$template->id.'&file='.base64_encode($row->path));
                $files[] = $row;
            }

            return $files;
        } else {
            $application = Factory::getApplication();
            $application->enqueueMessage(Text::_('COM_OTTOMO_AN_ERROR_OCCURED'), 'error');
            $application->redirect('index.php?option=com_ottomo&view=templates');
        }
    }

    public static function getFile($file, $template)
    {
        $response = new stdClass();
        $response->path = base64_decode($file);
        $response->path = trim($response->path);
        $response->path = str_replace('..', '', $response->path);
        $response->contents = '';
        $source = self::location($template->folder)->path;
        if ($response->path) {
            if (!is_file($source.'/'.$response->path)) {
                $application = Factory::getApplication();
                $application->enqueueMessage(Text::_('COM_OTTOMO_AN_ERROR_OCCURED'), 'error');
                $application->redirect('index.php?option=com_ottomo&view=templates');
            }
            $response->contents = htmlspecialchars(file_get_contents($source.'/'.$response->path));
        }

        return $response;
    }

    /**
     * Remove designer-only markup (editable regions) from stored content before it is viewed or sent.
     */
    public static function stripEditorMarkup($html)
    {
        // Only inside tags: the word may legitimately appear in the text
        return preg_replace_callback('/<[a-z][^>]*>/i', function ($tag) {
            return preg_replace('/\s+contenteditable(?:\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+))?(?=[\s\/>])/i', '', $tag[0]);
        }, (string)$html);
    }

    /** Hidden preview text right after <body>, padded so that clients don't pull body text into the preview. */
    public static function addPreheader($buffer, $preheader)
    {
        $preheader = trim((string)$preheader);
        if ($preheader === '') {
            return $buffer;
        }
        $html = '<div class="otm-preheader" style="display:none;font-size:1px;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;mso-hide:all;">'
            .htmlspecialchars($preheader, ENT_QUOTES, 'UTF-8').str_repeat('&#847;&zwnj;&nbsp;', 40).'</div>';
        $count = 0;
        $buffer = preg_replace('/<body\b[^>]*>/i', '$0'.$html, $buffer, 1, $count);
        return $count ? $buffer : $html.$buffer;
    }

    /**
     * Compliance footer (Settings > Compliance): the postal address, and an unsubscribe link when the email has none.
     * Its {{unsubscribe}} placeholder is replaced with the other placeholders.
     */
    public static function addComplianceFooter($buffer)
    {
        JLoader::register('OttomoHelperSettings', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/settings.php');
        if (!OttomoHelperSettings::enabled('complianceFooter')) {
            return $buffer;
        }
        $address = trim((string)OttomoHelperSettings::get('companyAddress'));
        $hasUnsubscribe = stripos($buffer, '{{unsubscribe}}') !== false || stripos($buffer, 'subscribers.unsubscribe') !== false;
        if ($address === '' && $hasUnsubscribe) {
            return $buffer;
        }
        $style = 'color:#71717a;font-family:Arial,Helvetica,sans-serif;font-size:12px;line-height:1.5;';
        $html = '<div class="otm-footer" style="padding:24px 16px;text-align:center;'.$style.'">';
        if ($address !== '') {
            $html .= '<div>'.nl2br(htmlspecialchars($address, ENT_QUOTES, 'UTF-8')).'</div>';
        }
        if (!$hasUnsubscribe) {
            $html .= '<div style="margin-top:6px;"><a href="{{unsubscribe}}" style="'.$style.'text-decoration:underline;">'.Text::_('COM_OTTOMO_FOOTER_UNSUBSCRIBE').'</a></div>';
        }
        $html .= '</div>';
        $count = 0;
        $buffer = preg_replace('#</body>#i', $html.'</body>', $buffer, 1, $count);
        return $count ? $buffer : $buffer.$html;
    }
}
