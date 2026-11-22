<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ; ?>
<?php $items = $this->batchToolbar->getItems();
if (count($items)) : ?>
<div class="otm-toolbar" id="otm-toolbar">
	<ul class="otm-toolbar-actions">
		<?php foreach ($this->batchToolbar->getItems() as $button): ?>
		<li><?php echo $this->batchToolbar->renderButton($button); ?></li>
		<?php endforeach; ?>
	</ul>
</div>
<?php endif; ?>