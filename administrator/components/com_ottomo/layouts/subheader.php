<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die; ?>

<?php
// Templates list view contains its own subheader with package upload input
if (!($this->view == 'templates' && $this->task != 'edit')): ?>
<div class="otm-subheader">

    <h3 class="otm-title">
        <span class="purple"><?php echo $this->title; ?></span>
    </h3>

    <?php
    // Add email campaign button on dashboard
    if ($this->view == 'dashboard'): ?>
    <a class="otm-btn otm-btn--icon otm-btn--plus otm-btn--header" href="index.php?option=com_ottomo&amp;view=newsletters&amp;task=edit">
        <?php echo OttomoHelperHTML::icon('plus-lg'); ?>
        <span><?php echo JText::_('COM_OTTOMO_NEW_CAMPAIGN'); ?></span>
    </a>
    <a class="otm-btn otm-btn--generic otm-btn--header otm-header-secondary" href="index.php?option=com_ottomo&amp;view=import&amp;tmpl=component" data-frame-open data-frame-reload data-frame-title="<?php echo htmlspecialchars(JText::_('COM_OTTOMO_IMPORT'), ENT_QUOTES, 'UTF-8'); ?>">
        <?php echo OttomoHelperHTML::icon('upload'); ?>
        <span><?php echo JText::_('COM_OTTOMO_IMPORT_SUBSCRIBERS'); ?></span>
    </a>
    <?php if (!empty($this->health)): ?>
    <?php $health = $this->health; $label = array('ok' => 'COM_OTTOMO_HEALTH_OK', 'warning' => 'COM_OTTOMO_HEALTH_WARNING', 'error' => 'COM_OTTOMO_HEALTH_ERROR'); ?>
    <a class="otm-health otm-health--<?php echo $health->level; ?>" href="index.php?option=com_ottomo&amp;view=settings&amp;task=edit" title="<?php echo htmlspecialchars($health->message, ENT_QUOTES, 'UTF-8'); ?>" data-health="<?php echo $health->level; ?>">
        <span class="otm-health__dot" aria-hidden="true"></span>
        <span><?php echo JText::_($label[$health->level]); ?></span>
        <span class="visually-hidden">: <?php echo htmlspecialchars($health->message, ENT_QUOTES, 'UTF-8'); ?></span>
    </a>
    <?php endif; ?>
    <?php endif; ?>

    <?php
    // List view action buttons (New, Import, Export)
    $subItems = $this->subheaderToolbar->getItems();
    if (($this->task !== 'edit' && $this->task !== 'add') && count($subItems)): ?>
    <div class="otm-add-area--header">
        <?php foreach ($subItems as $button): ?>
        <?php echo $this->subheaderToolbar->renderButton($button); ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php
    // Edit view action buttons (Save & Close, Save, Close)
    $headerItems = $this->headerToolbar->getItems();
    if (($this->task === 'edit' || $this->task === 'add') && count($headerItems)): ?>
    <div class="otm-add-area otm-add-area--edit">
        <?php foreach ($headerItems as $button): ?>
        <?php echo $this->headerToolbar->renderButton($button); ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (count($this->filters)): ?>
    <div class="otm-top-filters">
        <div id="otm-admin-filters" class="otm-filters">
            <?php foreach ($this->filters as $filter) : ?>
            <div class="otm-filter">
                <?php if ($filter->label): ?>
                <label><?php echo $filter->label; ?></label>
                <?php endif; ?>
                <?php echo $filter->content; ?>
            </div>
            <?php endforeach; ?>
            <?php if ($this->filtersActive()): ?>
            <a class="otm-filters__reset" href="<?php echo $this->resetLink(); ?>" data-role="reset-filters"><?php echo OttomoHelperHTML::icon('x-lg'); ?><span><?php echo JText::_('COM_OTTOMO_RESET_FILTERS'); ?></span></a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>
