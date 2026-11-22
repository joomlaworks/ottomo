<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ; ?>

<div id="otm-admin-container" class="otm-admin-list otm-admin-view-<?php echo $this->view; ?>">

<form action="index.php" method="post" id="adminForm" name="adminForm" enctype="multipart/form-data" data-otm-confirm-task="templates.remove" data-otm-confirm-message="<?php echo htmlspecialchars(JText::_('COM_OTTOMO_CONFIRM_DELETE_TEMPLATES'), ENT_QUOTES, 'UTF-8'); ?>">
    <div class="otm-subheader">
       
        <h3 class="otm-title">
            <span class="purple"><?php echo $this->title; ?></span>
        </h3>
        
        <?php if (!$this->task == 'edit' && OttomoHelperHTML::canManageTemplates()): ?>
    	<div class="otm-add-area--header otm-upload">
            <input class="visually-hidden" name="package" id="otm-template-package" type="file" accept=".zip,application/zip" data-role="template-package" />
            <label class="otm-btn otm-btn--save otm-btn--icon" for="otm-template-package" tabindex="0" role="button">
                <?php echo OttomoHelperHTML::icon('upload'); ?>
                <span><?php echo JText::_('COM_OTTOMO_UPLOAD_PACKAGE_FILE'); ?></span>
            </label>
            <span class="otm-upload__hint" data-role="template-package-name"><?php echo JText::_('COM_OTTOMO_UPLOAD_PACKAGE_HINT'); ?></span>
        </div>
        <?php endif; ?>
    </div>
    
        <div class="otm-templates">
            <ul class="otm-template-cards otm-grid-items" role="list">
                <?php foreach ($this->rows as $i => $row): ?>
                <li class="otm-grid-item otm-template-card otm-template-card--manage">
                    <div class="otm-grid-item-wrap">
                        <div class="otm-template-card__media">
                            <?php if ($row->cardImage): ?>
                            <div class="otm-grid-item-bg<?php echo $row->cardImageFull ? '' : ' otm-grid-item-bg--thumb'; ?>" style="background-image: url(<?php echo htmlspecialchars($row->cardImage, ENT_QUOTES, 'UTF-8'); ?>);"></div>
                            <?php else: ?>
                            <div class="otm-grid-item-bg otm-grid-item-bg--empty"></div>
                            <?php endif; ?>
                            <?php if ($row->custom): ?>
                            <span class="otm-template-card__source" title="<?php echo htmlspecialchars(JText::_('COM_OTTOMO_TEMPLATE_SITE_TEMPLATE_DESC'), ENT_QUOTES, 'UTF-8'); ?>"><?php echo OttomoHelperHTML::icon('folder'); ?><?php echo JText::_('COM_OTTOMO_TEMPLATE_SITE_TEMPLATE'); ?></span>
                            <?php endif; ?>
                            <div class="otm-template-card__overlay">
                                <a class="otm-template-card__action template-preview-image" href="<?php echo htmlspecialchars($row->preview, ENT_QUOTES, 'UTF-8'); ?>" title="<?php echo $row->name; ?>">
                                    <?php echo OttomoHelperHTML::icon('eye'); ?><span><?php echo JText::_('COM_OTTOMO_PREVIEW'); ?></span>
                                </a>
                                <a class="otm-template-card__action otm-template-card__action--primary" data-role="use-template" href="<?php echo JRoute::_('index.php?option=com_ottomo&view=newsletters&task=edit&template='.(int)$row->id); ?>">
                                    <?php echo OttomoHelperHTML::icon('send'); ?><span><?php echo JText::_('COM_OTTOMO_USE_TEMPLATE'); ?></span>
                                </a>
                            </div>
                        </div>
                        <div class="otm-grid-text-wrap otm-template-card__body">
                            <h3><?php echo $row->name; ?></h3>
                            <?php if ($row->layoutLabel !== ''): ?>
                            <span class="otm-template-card__layout"><?php echo OttomoHelperHTML::icon('layers'); ?><?php echo htmlspecialchars($row->layoutLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php endif; ?>
                            <span class="otm-template-card__meta" title="<?php echo $row->date; ?>"><?php echo implode(' · ', array_filter(array($row->version ? 'v'.$row->version : '', $row->shortDate, $row->author))); ?></span>
                            <?php if (OttomoHelperHTML::canManageTemplates()): ?>
                            <div class="otm-template-card__tools">
                                <a class="otm-icon-btn" href="<?php echo $row->editLink; ?>"><?php echo OttomoHelperHTML::icon('pencil'); ?><span><?php echo JText::_('COM_OTTOMO_EDIT'); ?></span></a>
                                <a class="otm-icon-btn" href="<?php echo $row->downloadLink; ?>"><?php echo OttomoHelperHTML::icon('download'); ?><span><?php echo JText::_('COM_OTTOMO_DOWNLOAD'); ?></span></a>
                                <button type="button" class="otm-icon-btn" data-template-task="templates.copy" data-id="<?php echo (int)$row->id; ?>"><?php echo OttomoHelperHTML::icon('files'); ?><span><?php echo JText::_('COM_OTTOMO_DUPLICATE'); ?></span></button>
                                <?php if (!$row->custom): ?>
                                <button type="button" class="otm-icon-btn otm-template-card__delete" data-template-task="templates.remove" data-id="<?php echo (int)$row->id; ?>"><?php echo OttomoHelperHTML::icon('trash'); ?><span><?php echo JText::_('COM_OTTOMO_DELETE'); ?></span></button>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <?php $this->renderFooter(); ?>
                
        <input type="hidden" name="option" value="<?php echo $this->option; ?>" />
        <input type="hidden" name="view" value="<?php echo $this->view; ?>" />
        <input type="hidden" name="task" value="<?php echo $this->task; ?>" />
        <input type="hidden" name="extension" value="<?php echo $this->extension; ?>" />
        <input type="hidden" name="filter_order" value="<?php echo $this->ordering; ?>" />
        <input type="hidden" name="filter_order_Dir" value="<?php echo $this->orderingDir; ?>" />
        <input type="hidden" name="tmpl" value="<?php echo $this->tmpl; ?>" />
        <input type="hidden" name="editor" value="<?php echo $this->editor; ?>" />
        <input type="hidden" name="template" value="<?php echo $this->template; ?>" />
        <input type="hidden" name="boxchecked" value="0" />
        <input type="hidden" name="cid[]" value="" data-role="template-id" disabled />
        <input type="hidden" name="format" value="<?php echo $this->format; ?>" />
        <input type="hidden" name="total" value="<?php echo $this->total; ?>" />
        <?php echo JHTML::_('form.token'); ?>
    </form>
</div>