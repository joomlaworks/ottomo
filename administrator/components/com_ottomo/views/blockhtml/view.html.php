<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

/**
 * The HTML of one designer block, in a code editor (Ace), shown in a SuperModal frame: the block's "Edit HTML" button.
 * The frame reads the block from the designer page and writes it back (Ottomo.blockHtml in static/js/ottomo.js).
 */
class OttomoViewBlockhtml extends OttomoView
{
    public function display($tpl = null)
    {
        OttomoCompat::document()->addScript('https://cdnjs.cloudflare.com/ajax/libs/ace/1.45.2/ace.js');
        $this->setLayout('blockhtml');
        parent::display($tpl);
    }
}
