<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ; ?>
<div class="otm-app otm-app--<?php echo ($this->tmpl == 'component') ? 'frame' : 'page'; ?><?php echo OttomoCompat::isModern() ? ' otm-app--atum' : ''; ?> view-<?php echo $this->view; ?>" id="otm">

	<?php if ($this->regions->header): ?>
		
	<?php echo $this->regions->header; ?>

	<?php endif; ?>

	<div class="otm-component">
		
		<?php if ($this->regions->subheader): ?>

		<?php echo $this->regions->subheader; ?>

		<?php endif; ?>

		<?php if ($this->regions->toolbar): ?>

		<?php echo $this->regions->toolbar; ?>

		<?php endif; ?>

		<?php echo $this->regions->main; ?>		

	</div>

</div>