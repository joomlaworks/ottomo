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
<div id="otm-container" class="otm-view-<?php echo $this->view; ?> <?php echo $this->params->get('pageclass_sfx'); ?>">
    
    <?php if ($this->params->get('show_page_heading', 1)) : ?>
    <div class="page-header">
	    <h1>
	        <?php echo $this->escape($this->params->get('page_heading')); ?>
	    </h1>
	</div>
    <?php endif; ?>
    
    <div class="table-responsive-wrap">
        <div class="table-responsive">
            <table class="table table-striped">  
                
                <?php if (count($this->rows)): ?>
                <thead>
                    <th><?php echo Text::_('COM_OTTOMO_SENT'); ?></th>
                    <th><?php echo Text::_('COM_OTTOMO_SUBJECT'); ?></th>
                </thead>

                <tbody>
                    <?php foreach ($this->rows as $row): ?>
                    <tr>
                        <td><span><?php echo $row->sent ; ?></span></td>
                        <td><h4><a href="<?php echo $row->link; ?>"><?php echo $row->subject; ?></a></h4></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <?php if (isset($this->pagination)): ?>
    <div class="pagination">
        <p class="counter"> <?php echo $this->pagination->getPagesCounter(); ?> </p>
        <?php echo $this->pagination->getPagesLinks(); ?>
    </div>
    <?php endif; ?>

</div>