<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

// The HTML of one designer block: rendered inside a SuperModal frame (see js/ottomo.js -> Ottomo.frames.blockhtml)
defined('_JEXEC') or die ;

use Joomla\CMS\Language\Text;
?>
<div class="otm-frame otm-frame--code" data-frame="blockhtml">
    <div class="otm-blockhtml__editor" id="otm-block-source" data-role="source"></div>
    <div class="otm-frame__actions">
        <p class="otm-field__hint otm-blockhtml__hint"><?php echo Text::_('COM_OTTOMO_EDITOR_HTML_HINT'); ?></p>
        <button type="button" class="otm-btn otm-btn--generic" data-frame-close><?php echo Text::_('COM_OTTOMO_CANCEL'); ?></button>
        <button type="button" class="otm-btn otm-btn--save" data-role="apply"><?php echo OttomoHelperHTML::icon('check-circle'); ?><span><?php echo Text::_('COM_OTTOMO_EDITOR_HTML_APPLY'); ?></span></button>
    </div>
</div>
