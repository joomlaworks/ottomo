<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
?>
<div id="otm-container" class="otm-view-<?php echo $this->view; ?>">
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('otm-form');
        var emailInput = document.getElementById('otm-email');
        var saveBtn = document.getElementById('otm-save-button');
        var deleteBtn = document.getElementById('otm-delete-button');
        var taskInput = form ? form.querySelector('input[name="task"]') : null;

        function handleAction(actionTask, requireConfirm) {
            if (!form || !emailInput || !taskInput) {
                return;
            }
            if (emailInput.value.trim() === '') {
                alert('<?php echo Text::_('COM_OTTOMO_PLEASE_TYPE_YOUR_EMAIL', true); ?>');
                emailInput.focus();
                return;
            }
            if (requireConfirm && !confirm('<?php echo Text::_('COM_OTTOMO_ARE_YOU_SURE', true); ?>')) {
                return;
            }
            taskInput.value = actionTask;
            form.submit();
        }

        if (saveBtn) {
            saveBtn.addEventListener('click', function (event) {
                event.preventDefault();
                handleAction('subscribers.save', false);
            });
        }
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function (event) {
                event.preventDefault();
                handleAction('subscribers.delete', true);
            });
        }
    });
    </script>
    <form action="<?php echo Route::_('index.php'); ?>" method="post" id="otm-form">

        <div class="page-header">
            <h1>
                <?php echo Text::_('COM_OTTOMO_MANAGE_YOUR_SUBSCRIPTION'); ?>
            </h1>
        </div>
    
        <div class="controls">
            <div class="input-prepend">
                <label class="lbltitle" for="otm-email"><?php echo Text::_('COM_OTTOMO_EMAIL'); ?></label>
                <input type="text" name="email" id="otm-email" value="" />
            </div>
        </div>

        <h4><?php echo Text::_('COM_OTTOMO_TOPICS')?></h4>
        
        <?php foreach ($this->topics as $topic):?>
       <div class="controls checkbox">
            <div class="input-prepend">
                <input type="checkbox" name="topics[]" id="otm-topic-<?php echo $topic->id;?>" value="<?php echo $topic->id;?>" <?php if (in_array($topic->id, $this->row->topics)) {
                    echo 'checked="checked"';
                } ?> />
                <label for="otm-topic-<?php echo (int)$topic->id;?>"><?php echo htmlspecialchars((string)$topic->name, ENT_QUOTES, 'UTF-8'); ?></label>
            </div>
        </div>
        <?php endforeach; ?>

        <input type="button" class="btn btn-mid btn-primary" name="save" value="<?php echo Text::_('COM_OTTOMO_SAVE')?>" id="otm-save-button" />
        <input type="button" class="btn btn-mid btn-danger" name="delete" value="<?php echo Text::_('COM_OTTOMO_DELETE_ACCOUNT')?>" id="otm-delete-button" />
        
        <input type="hidden" name="id" value="<?php echo (int)$this->row->id; ?>" />
        <input type="hidden" name="option" value="<?php echo $this->option; ?>" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="newsletter" value="<?php echo (int)$this->newsletterId; ?>" />
        <input type="hidden" name="hash" value="<?php echo htmlspecialchars((string)$this->hash, ENT_QUOTES, 'UTF-8'); ?>" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>