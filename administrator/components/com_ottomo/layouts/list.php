<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ; ?>
<div id="otm-admin-container" class="otm-listed-items otm-admin-list otm-admin-view-<?php echo $this->view; ?>">
	
	<script>
	Joomla.submitbutton = function(task) {
		'use strict';
		var view = document.querySelector('#otm-admin-container input[name="view"]').value;

		// Batch "Set topics" for the selected rows (SuperModal frame)
		if (task === 'batch.modal') {
			var ids = Array.prototype.map.call(document.querySelectorAll('#adminForm input[name="cid[]"]:checked'), function(el) {
				return 'cid[]=' + encodeURIComponent(el.value);
			});
			if (!ids.length) {
				alert(Joomla.JText._('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST', 'Please first make a selection from the list.'));
				return false;
			}
			Ottomo.frame('index.php?option=com_ottomo&view=batch&tmpl=component&type=' + view + '&' + ids.join('&'),
				<?php echo json_encode(JText::_('COM_OTTOMO_SET_TOPICS')); ?>, { reload: true });
			return false;
		}

		// Subscribers import / export (SuperModal frames)
		if (task === 'subscribers.import') {
			Ottomo.frame('index.php?option=com_ottomo&view=import&tmpl=component', <?php echo json_encode(JText::_('COM_OTTOMO_IMPORT')); ?>, { reload: true });
			return false;
		}
		// Export: the ticked rows; else the filtered/searched list (all its pages); else everyone. "Export all": everyone.
		if (task === 'subscribers.export' || task === 'subscribers.exportAll') {
			var url = 'index.php?option=com_ottomo&view=export&tmpl=component&scope=' + (task === 'subscribers.exportAll' ? 'all' : 'auto');
			if (task === 'subscribers.export') {
				Array.prototype.forEach.call(document.querySelectorAll('#adminForm input[name="cid[]"]:checked'), function(el) {
					url += '&cid[]=' + encodeURIComponent(el.value);
				});
			}
			Ottomo.frame(url, <?php echo json_encode(JText::_('COM_OTTOMO_EXPORTING_SUBSCRIBERS')); ?>);
			return false;
		}

		// Delete All Unsubscribed: irreversible, and Ottomo then no longer knows they opted out
		if (task === 'subscribers.removeUnsubscribed') {
			var unsubscribed = <?php echo isset($this->unsubscribedCount) ? (int)$this->unsubscribedCount : 0; ?>;
			if (!unsubscribed) {
				window.alert(<?php echo json_encode(JText::_('COM_OTTOMO_NO_UNSUBSCRIBED')); ?>);
				return false;
			}
			if (!window.confirm(<?php echo json_encode(JText::_('COM_OTTOMO_CONFIRM_DELETE_UNSUBSCRIBED')); ?>.replace('%s', unsubscribed.toLocaleString()))) {
				return false;
			}
			Joomla.submitform(task);
			return false;
		}

		// Deleting cannot be undone: say what happens to the related data
		if (/\.remove$/.test(task)) {
			var confirmations = <?php echo json_encode(array(
				'newsletters' => JText::_('COM_OTTOMO_CONFIRM_DELETE_NEWSLETTERS'),
				'subscribers' => JText::_('COM_OTTOMO_CONFIRM_DELETE_SUBSCRIBERS'),
				'topics' => JText::_('COM_OTTOMO_CONFIRM_DELETE_TOPICS'),
				'templates' => JText::_('COM_OTTOMO_CONFIRM_DELETE_TEMPLATES'),
			)); ?>;
			if (!window.confirm(confirmations[view] || <?php echo json_encode(JText::_('COM_OTTOMO_CONFIRM_DELETE')); ?>)) {
				return false;
			}
		}

		Joomla.submitform(task);
	};
	</script>
	<?php if ($this->view == 'subscribers'): ?>
	<script>
	// Visual cue: what "Export" will export (selected rows, the filtered list, or everyone)
	document.addEventListener('DOMContentLoaded', function() {
		'use strict';
		var button = document.querySelector('[data-task="subscribers.export"] span');
		if (!button) {
			return;
		}
		var filtered = <?php echo ($this->search !== '' || (int)$this->topic > 0 || (int)$this->status !== -1) ? 'true' : 'false'; ?>;
		var labels = <?php echo json_encode(array('all' => JText::_('COM_OTTOMO_EXPORT'), 'filtered' => JText::_('COM_OTTOMO_EXPORT_FILTERED'), 'selected' => JText::_('COM_OTTOMO_EXPORT_SELECTED_N'))); ?>;
		var update = function() {
			var selected = document.querySelectorAll('#adminForm input[name="cid[]"]:checked').length;
			button.textContent = selected ? labels.selected.replace('%d', selected) : (filtered ? labels.filtered : labels.all);
			button.parentNode.classList.toggle('is-scoped', !!selected || filtered);
		};
		document.addEventListener('change', function(event) {
			if (event.target.matches('input[name="cid[]"], input[name="checkall-toggle"]')) {
				window.setTimeout(update, 0);
			}
		});
		update();
	});
	</script>
	<?php endif; ?>
	
	
    <form action="index.php" method="post" id="adminForm" name="adminForm" enctype="multipart/form-data">
        <div class="otm-table-scroll">
                <table class="table table-striped">
                    <thead class="otm-items-header">
                        <tr>
                        	<th><?php echo JHtml::_('grid.checkall'); ?></th>
                            <?php foreach ($this->columns as $column): ?>
                            <th<?php echo $column->attributes; ?>><?php echo $column->heading; ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($this->rows as $i => $row):?>
                        <tr>
                        	<td><?php echo JHtml::_('grid.id', $i, $row->id); ?>
                            <?php foreach ($this->columns as $column): $property = $column->property; ?>
                            <td<?php echo $column->attributes; ?>><?php echo $row->$property; ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
        <input type="hidden" name="format" value="<?php echo $this->format; ?>" />
        <input type="hidden" name="total" value="<?php echo $this->total; ?>" />
        <?php echo JHTML::_('form.token'); ?>
    </form>
</div>