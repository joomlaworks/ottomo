<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;
?>
<div id="otm-admin-container" class="otm-tmpleditor otm-admin-list otm-admin-view-<?php echo $this->view; ?>">

    <div class="otm-tmpleditor-details otm-generic-form">
        <h2><?php echo JText::_('COM_OTTOMO_TEMPLATE_DETAILS'); ?></h2>
        <form action="index.php" method="post" name="adminForm" id="adminForm" data-otm-required="name" data-otm-required-message="<?php echo htmlspecialchars(JText::_('COM_OTTOMO_TEMPLATE_MUST_HAVE_A_NAME'), ENT_QUOTES, 'UTF-8'); ?>">
            <div class="otm-setting-single">
                <label for="name"><?php echo JText::_('COM_OTTOMO_NAME'); ?></label>
                <input type="text" name="name" id="name" value="<?php echo $this->row->name; ?>" />
            </div>
            <input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
            <input type="hidden" name="folder" value="<?php echo $this->row->folder; ?>" />
            <input type="hidden" name="manifest" value="<?php echo $this->row->manifest; ?>" />
            <input type="hidden" name="option" value="<?php echo $this->option; ?>" />
            <input type="hidden" name="view" value="<?php echo $this->view; ?>" />
            <input type="hidden" name="task" value="<?php echo $this->task; ?>" />
            <input type="hidden" name="tmpl" value="<?php echo $this->tmpl; ?>" />
            <?php echo JHTML::_('form.token'); ?>
        </form>
    </div>

    <div class="otm-tmpleditor-workspace">
        <div class="otm-tmpleditor-files">
            <h3><?php echo JText::_('COM_OTTOMO_SELECT_A_FILE_TO_EDIT'); ?></h3>
            <ul class="otm-tmpleditor-menu">
                <?php foreach ($this->files as $file): ?>
                <li><a href="<?php echo $file->link; ?>"><?php echo $file->path; ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="otm-tmpleditor-pane otm-tmpleditor-empty">
            <p><?php echo JText::_('COM_OTTOMO_SELECT_A_FILE_TO_EDIT'); ?></p>
        </div>
    </div>
</div>
