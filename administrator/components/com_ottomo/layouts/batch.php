<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

// Batch "Set topics": rendered inside a SuperModal frame (see js/ottomo.js -> Ottomo.frames.batch)
defined('_JEXEC') or die ;
?>
<div class="otm-frame" data-frame="batch">
    <?php if (!count($this->ids)): ?>
    <div class="otm-frame__notice otm-frame__notice--error" role="alert"><?php echo JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'); ?></div>
    <div class="otm-frame__actions">
        <button type="button" class="otm-btn otm-btn--generic" data-frame-close><?php echo JText::_('COM_OTTOMO_CLOSE'); ?></button>
    </div>
    <?php else: ?>
    <form action="index.php" method="post" id="adminForm" name="adminForm">
        <div class="otm-frame__head">
            <span class="otm-frame__icon" aria-hidden="true"><?php echo OttomoHelperHTML::icon('tags'); ?></span>
            <div>
                <h2 class="otm-frame__title"><?php echo JText::_('COM_OTTOMO_SET_TOPICS'); ?></h2>
                <p class="otm-frame__lead"><?php echo JText::plural('COM_OTTOMO_BATCH_SET_TOPICS_FOR_N', count($this->ids)); ?></p>
            </div>
        </div>
        <fieldset class="otm-choice-list otm-import__topics">
            <legend class="visually-hidden"><?php echo JText::_('COM_OTTOMO_TOPICS'); ?></legend>
            <?php echo $this->topics; ?>
        </fieldset>
        <div class="otm-frame__notice otm-frame__notice--error" role="alert" data-role="error" hidden></div>
        <div class="otm-frame__actions">
            <button type="button" class="otm-btn otm-btn--generic" data-frame-close><?php echo JText::_('COM_OTTOMO_CANCEL'); ?></button>
            <button type="submit" class="otm-btn otm-btn--save"><?php echo OttomoHelperHTML::icon('check-circle'); ?><span><?php echo JText::_('COM_OTTOMO_SET_TOPICS_BUTTON'); ?></span></button>
        </div>
        <?php foreach ($this->ids as $id): ?>
        <input type="hidden" name="cid[]" value="<?php echo (int)$id; ?>" />
        <?php endforeach; ?>
        <input type="hidden" name="option" value="com_ottomo" />
        <input type="hidden" name="view" value="<?php echo $this->type; ?>" />
        <input type="hidden" name="task" value="<?php echo $this->type; ?>.batch" />
        <?php echo JHTML::_('form.token'); ?>
    </form>
    <?php endif; ?>
</div>
