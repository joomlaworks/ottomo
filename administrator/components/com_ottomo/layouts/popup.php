<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

// Send campaign: rendered inside a SuperModal frame (see js/ottomo.js -> Ottomo.frames.send)
defined('_JEXEC') or die ;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
?>
<div class="otm-frame" data-frame="send">
    <div class="otm-frame__head">
        <h2 class="otm-frame__title"><?php echo htmlspecialchars($this->row->subject, ENT_QUOTES, 'UTF-8'); ?></h2>
        <p class="otm-frame__lead"><?php JLoader::register('OttomoHelperSettings', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/settings.php'); $sender = OttomoHelperSettings::sender($this->row); echo htmlspecialchars($sender->name.' <'.$sender->email.'>', ENT_QUOTES, 'UTF-8'); ?></p>
    </div>

    <?php if ($this->error): ?>
    <div class="otm-frame__notice otm-frame__notice--error" role="alert"><?php echo $this->error; ?></div>
    <div class="otm-frame__actions">
        <button type="button" class="otm-btn otm-btn--generic" data-frame-close><?php echo Text::_('COM_OTTOMO_CLOSE'); ?></button>
    </div>
    <?php else: ?>
    <div data-state="ready">
        <p class="otm-frame__notice"><?php echo $this->resume ? Text::sprintf('COM_OTTOMO_RESUME_TO_N', number_format((int)$this->total)) : Text::plural('COM_OTTOMO_SEND_TO_N_RECIPIENTS', $this->total); ?></p>
        <div class="otm-frame__actions">
            <button type="button" class="otm-btn otm-btn--generic" data-frame-close><?php echo Text::_('COM_OTTOMO_CANCEL'); ?></button>
            <button type="button" class="otm-btn otm-btn--save" data-action="start"><?php echo OttomoHelperHTML::icon('envelope'); ?> <span><?php echo Text::_($this->resume ? 'COM_OTTOMO_RESUME_SENDING' : 'COM_OTTOMO_SEND'); ?></span></button>
        </div>
    </div>

    <div data-state="running" hidden>
        <p class="otm-frame__notice"><?php echo Text::_('COM_OTTOMO_SENDING_KEEP_OPEN'); ?></p>
        <div class="otm-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="otm-progress__bar"></div></div>
        <p class="otm-frame__meta"><span data-role="done">0</span> / <?php echo (int)$this->total; ?></p>
    </div>

    <div data-state="finished" hidden>
        <div class="otm-frame__notice otm-frame__notice--success" role="status" data-role="summary"></div>
        <div class="otm-frame__actions">
            <button type="button" class="otm-btn otm-btn--save" data-frame-close><?php echo Text::_('COM_OTTOMO_CLOSE'); ?></button>
        </div>
    </div>

    <div data-state="failed" hidden>
        <div class="otm-frame__notice otm-frame__notice--error" role="alert" data-role="error"></div>
        <p class="otm-frame__meta"><?php echo Text::_('COM_OTTOMO_SEND_RESUME_HINT'); ?></p>
        <div class="otm-frame__actions">
            <button type="button" class="otm-btn otm-btn--generic" data-frame-close><?php echo Text::_('COM_OTTOMO_CLOSE'); ?></button>
            <button type="button" class="otm-btn otm-btn--save" data-action="resume"><?php echo OttomoHelperHTML::icon('envelope'); ?> <span><?php echo Text::_('COM_OTTOMO_RESUME_SENDING'); ?></span></button>
        </div>
    </div>

    <form action="index.php" method="post" name="adminForm" id="adminForm" hidden>
        <input type="hidden" name="id" value="<?php echo (int)$this->row->id; ?>" />
        <input type="hidden" name="option" value="com_ottomo" />
        <input type="hidden" name="task" value="newsletters.send" />
        <input type="hidden" name="total" value="<?php echo (int)$this->total; ?>" />
        <input type="hidden" name="after" value="0" />
        <input type="hidden" name="format" value="json" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
    <?php endif; ?>
</div>
