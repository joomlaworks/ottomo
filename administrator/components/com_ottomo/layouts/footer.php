<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

use Joomla\CMS\Language\Text;
?>
<?php if (isset($this->pagination)): ?>

<div class="otm-pagination">
	
	<div class="otm-limit">
		<label><?php echo Text::_('COM_OTTOMO_DISPLAY'); ?></label>
		<?php echo $this->pagination->getLimitBox(); ?>
	</div>
	
	<?php if ($this->pagination->pagesTotal > 1): ?>
	<ul>
		<li class="otm-showvsmall"><a href="#" onclick="document.adminForm.limitstart.value = 0; Joomla.submitform(); return false;"><?php echo Text::_('COM_OTTOMO_START'); ?></a></li>
		
		<?php if (($this->pagination->pagesCurrent - 1) > 0) : ?>
		<li class="otm-showvsmall"><a href="#" onclick="document.adminForm.limitstart.value = <?php echo((($this->pagination->pagesCurrent - 1) * $this->pagination->limit) - $this->pagination->limit); ?>; Joomla.submitform(); return false;"><?php echo Text::_('COM_OTTOMO_PREVIOUS'); ?></a></li>
		<?php endif; ?>
		
		<?php for ($i = $this->pagination->pagesStart; $i <= $this->pagination->pagesStop; $i++) : ?>
		<li><a <?php if ($i == $this->pagination->pagesCurrent): ?> class="active" <?php endif; ?> href="#" onclick="document.adminForm.limitstart.value = <?php echo(($i * $this->pagination->limit) - $this->pagination->limit); ?>; Joomla.submitform(); return false;"><?php echo $i; ?></a></li>
		<?php endfor; ?>
		
		<?php if (($this->pagination->pagesCurrent + 1) <= $this->pagination->pagesTotal) : ?>
		<li class="otm-showvsmall"><a href="#" onclick="document.adminForm.limitstart.value = <?php echo($this->pagination->pagesCurrent  * $this->pagination->limit); ?>; Joomla.submitform(); return false;"><?php echo Text::_('COM_OTTOMO_NEXT'); ?></a></li>
		<?php endif; ?>
		
		<li class="otm-showvsmall"><a href="#" onclick="document.adminForm.limitstart.value = <?php echo ($this->pagination->pagesTotal  * $this->pagination->limit) - $this->pagination->limit; ?>; Joomla.submitform(); return false;"><?php echo Text::_('COM_OTTOMO_END'); ?></a></li>
	</ul>
	<?php endif; ?>
	
	<div class="otm-total">
		<?php if ($this->pagination->pagesTotal): ?>
		<span class="otm-total-block">
			<?php echo Text::_('COM_OTTOMO_PAGE'); ?> <b><?php echo $this->pagination->pagesCurrent; ?></b>
		</span>
		<span class="otm-total-block">
			<?php echo Text::_('COM_OTTOMO_OF'); ?> <b><?php echo $this->pagination->pagesTotal; ?></b>
		</span>
		<?php endif; ?>
		<?php if (isset($this->total)): ?>
		<span class="otm-total-block">
			<strong class="purple"><?php echo $this->total; ?></strong> <?php echo $this->title; ?>
		</span>
		<?php endif; ?>
	</div>

	<input type="hidden" name="limitstart" value="<?php echo (int)$this->pagination->limitstart; ?>" />

</div>
<?php endif; ?>