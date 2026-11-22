<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

// Export subscribers: rendered inside a SuperModal frame (see js/ottomo.js -> Ottomo.frames.export)
defined('_JEXEC') or die ;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
$token = Session::getFormToken();
?>
<div class="otm-frame" data-frame="export">
    <div data-state="running">
        <p class="otm-frame__notice" data-role="export-scope" data-scope="<?php echo htmlspecialchars($this->scope, ENT_QUOTES, 'UTF-8'); ?>"><?php echo Text::plural('COM_OTTOMO_EXPORTING_N_SUBSCRIBERS_'.strtoupper($this->scope), $this->counter); ?></p>
        <div class="otm-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="otm-progress__bar"></div></div>
        <p class="otm-frame__meta"><span data-role="done">0</span> / <?php echo (int)$this->counter; ?></p>
    </div>

    <div data-state="finished" hidden>
        <div class="otm-frame__notice otm-frame__notice--success" role="status"><?php echo Text::_('COM_OTTOMO_EXPORT_READY'); ?></div>
        <div class="otm-frame__actions">
            <button type="button" class="otm-btn otm-btn--generic" data-frame-close><?php echo Text::_('COM_OTTOMO_CLOSE'); ?></button>
            <a class="otm-btn otm-btn--save" data-role="download" href="index.php?option=com_ottomo&amp;task=export.download&amp;format=raw&amp;<?php echo $token; ?>=1"><?php echo OttomoHelperHTML::icon('download'); ?> <span><?php echo Text::_('COM_OTTOMO_DOWNLOAD'); ?></span></a>
        </div>
    </div>

    <div data-state="failed" hidden>
        <div class="otm-frame__notice otm-frame__notice--error" role="alert" data-role="error"></div>
        <div class="otm-frame__actions">
            <button type="button" class="otm-btn otm-btn--generic" data-frame-close><?php echo Text::_('COM_OTTOMO_CLOSE'); ?></button>
        </div>
    </div>

    <form action="index.php" method="post" name="adminForm" id="adminForm" hidden>
        <input type="hidden" name="option" value="com_ottomo" />
        <input type="hidden" name="task" value="export.run" />
        <input type="hidden" name="total" value="<?php echo (int)$this->counter; ?>" />
        <input type="hidden" name="batch" value="<?php echo (int)$this->limit; ?>" /><?php // not "limit": Joomla would store it as the list page size ?>
        <input type="hidden" name="limitstart" value="0" />
        <input type="hidden" name="format" value="json" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
