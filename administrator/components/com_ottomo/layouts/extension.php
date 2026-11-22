<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ; ?>

<div class="otm-subheader">
    <h3 class="otm-title">
        <?php echo $this->title; ?> 
    </h3>
</div>

<div id="otm-admin-container" class="otm-admin-list otm-admin-view-<?php echo $this->view; ?>">
    <form action="index.php" method="post" id="adminForm" name="adminForm" enctype="multipart/form-data">
    	
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
    	
    	
        <table class="table table-striped">
            
            <thead class="otm-items-header">
                <tr>
                    <?php foreach ($this->columns as $column): ?>
                    <th><?php echo $column->heading; ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            
            


            <tbody>
                <?php foreach ($this->rows as $i => $row):?>
                <tr>
                    <?php foreach ($this->columns as $column): $property = $column->property; ?>
                    <td<?php echo $column->attributes; ?>><?php echo $row->$property; ?></td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
         <?php if ($this->view == 'templates'): ?>
        <fieldset>
            <legend><?php echo JText::_('COM_OTTOMO_UPLOAD_PACKAGE_FILE'); ?></legend>
            <label><?php echo JText::_('COM_OTTOMO_PACKAGE_FILE'); ?>:</label>
            <input name="package" type="file" size="60" />
            <input type="button" value="<?php echo JText::_('COM_OTTOMO_UPLOAD_FILE_AND_INSTALL'); ?>" onclick="Joomla.submitbutton('templates.install'); return false;" />
        </fieldset>
        <?php endif; ?>
        
        <?php if ($this->view == 'newsletters' || $this->view == 'subscribers'): ?>
        <?php echo $this->loadTemplate('batch'); ?>
        <?php endif; ?>
        
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
        <input type="hidden" name="format" value="<?php echo $this->format; ?>" />
        <input type="hidden" name="total" value="<?php echo $this->total; ?>" />
        <?php echo JHTML::_('form.token'); ?>
    </form>
</div>