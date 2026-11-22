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
use Joomla\String\StringHelper;
?>
<div id="otm-admin-container" class="otm-subscrform otm-admin-list otm-admin-view-<?php echo $this->view; ?>">
    <form action="index.php" method="post" name="adminForm" id="adminForm" class="otm-subscriber-form" data-otm-required="email" data-otm-required-message="<?php echo htmlspecialchars(Text::_('COM_OTTOMO_SUBSCRIBER_MUST_HAVE_AN_EMAIL_ADDRESS'), ENT_QUOTES, 'UTF-8'); ?>">
        <div class="otm-subscriber">
            <div class="otm-subscrside">
                <div class="otm-setting-field">
                    <div class="otm-setting-single">
                        <?php
                        // Local initials avatar (no Gravatar: it sent a hash of the subscriber's email to a third party)
                        $source = trim((string)$this->row->name) !== '' ? trim((string)$this->row->name) : trim((string)$this->row->email);
                        $initials = '';
                        foreach (array_slice(preg_split('/[\s@._-]+/u', $source, -1, PREG_SPLIT_NO_EMPTY), 0, 2) as $word) {
                            $initials .= StringHelper::strtoupper(StringHelper::substr($word, 0, 1));
                        }
                        ?>
                        <div class="otm-avatar" aria-hidden="true"><?php echo $initials !== '' ? htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') : OttomoHelperHTML::icon('people'); ?></div>
                    </div>

                    <h3><?php echo Text::_('COM_OTTOMO_SUBSCRIBED_TO'); ?></h3>
                    <div class="otm-setting-single">
                        
                        <div class="otm-topics otm-topics--upper">
                            <input type="checkbox" id="otm-addall" name="otm-addall" />
                            <label for="otm-addall"><?php echo Text::_('COM_OTTOMO_CHECK_ALL'); ?></label>

                            <a href="#" id="otm-reset" class="otm-reset"><?php echo Text::_('COM_OTTOMO_RESET'); ?></a>
                        </div>

                        <label><?php echo Text::_('COM_OTTOMO_TOPICS'); ?></label>

                        <div class="otm-topics">
                            <?php echo $this->topics; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="otm-subscriber-details">
                <div class="otm-setting-field">
                    <h3><?php echo Text::_('COM_OTTOMO_SUBSCRIBER_DETAILS'); ?></h3>

                    <div class="otm-setting-single">    
                        <label for="name"><?php echo Text::_('COM_OTTOMO_NAME'); ?></label>
                        <div class="full--input">
                            <input type="text" name="name" id="name" value="<?php echo $this->row->name; ?>" />
                        </div>
                    </div>
                    
                    <div class="otm-setting-single">
                        <label for="email"><?php echo Text::_('COM_OTTOMO_EMAIL'); ?></label>
                        <div class="full--input">
                            <input type="text" name="email" id="email" value="<?php echo $this->row->email; ?>" />
                        </div>
                    </div>
                    
                    <div class="otm-setting-single">
                        <label><?php echo Text::_('COM_OTTOMO_DATE_OF_BIRTH'); ?></label>
                        <?php $birthday = preg_match('/^\d{4}-\d{2}-\d{2}/', (string)$this->row->birthday) && strpos((string)$this->row->birthday, '0000') !== 0 ? substr($this->row->birthday, 0, 10) : ''; ?>
                        <input type="date" name="birthday" id="birthday" value="<?php echo htmlspecialchars($birthday, ENT_QUOTES, 'UTF-8'); ?>" />
                    </div>

                    <div class="otm-setting-single otm-field--wide">
                        <label for="address"><?php echo Text::_('COM_OTTOMO_ADDRESS'); ?></label>
                        <textarea rows="3" cols="25" name="address" id="address"><?php echo $this->row->address; ?></textarea>
                    </div>
                    
                    <div class="otm-setting-single">
                        <label><?php echo Text::_('COM_OTTOMO_CITY'); ?></label>
                        <input type="text" name="city" value="<?php echo $this->row->city; ?>" />
                    </div>
                    
                    <div class="otm-setting-single">
                        <label><?php echo Text::_('COM_OTTOMO_STATE_PROVINCE'); ?></label>
                        <input type="text" name="state" value="<?php echo $this->row->state; ?>" />
                    </div>
                    
                    <div class="otm-setting-single">
                        <label><?php echo Text::_('COM_OTTOMO_ZIP_CODE'); ?></label>
                        <input type="text" name="zip" value="<?php echo $this->row->zip; ?>" />
                    </div>
                    
                    <div class="otm-setting-single">
                        <label><?php echo Text::_('COM_OTTOMO_COUNTRY'); ?></label>
                        <input type="text" name="country" value="<?php echo $this->row->country; ?>" />
                    </div>
                    
                    <div class="otm-setting-single">
                        <label><?php echo Text::_('COM_OTTOMO_TELEPHONE_FIXED'); ?></label>
                        <input type="text" name="phone" value="<?php echo $this->row->phone; ?>" />
                    </div>
                    
                    <div class="otm-setting-single">
                        <label><?php echo Text::_('COM_OTTOMO_TELEPHONE_MOBILE'); ?></label>
                        <input type="text" name="mobile" value="<?php echo $this->row->mobile; ?>" />
                    </div>
                    
                    <div class="otm-setting-single">
                        <label><?php echo Text::_('COM_OTTOMO_FAX'); ?></label>
                        <input type="text" name="fax" value="<?php echo $this->row->fax; ?>" />
                    </div>
                    
                    <div class="otm-setting-single">
                        <label><?php echo Text::_('COM_OTTOMO_VAT_NO'); ?></label>
                        <input type="text" name="vat" value="<?php echo $this->row->vat; ?>" />
                    </div>
                    
                    <div class="otm-setting-single">
                        <label><?php echo Text::_('COM_OTTOMO_TAX_AUTHORITY'); ?></label>
                        <input type="text" name="tax" value="<?php echo $this->row->tax; ?>" />
                    </div>
                    
                    <div class="otm-setting-single">
                        <label><?php echo Text::_('COM_OTTOMO_COMPANY_NAME'); ?></label>
                        <input type="text" name="company" value="<?php echo $this->row->company; ?>" />
                    </div>

                    <div class="otm-setting-single">
                        <label><?php echo Text::_('COM_OTTOMO_POSITION_IN_COMPANY'); ?></label>
                        <input type="text" name="position" value="<?php echo $this->row->position; ?>" />        
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
        <input type="hidden" name="option" value="<?php echo $this->option; ?>" />
        <input type="hidden" name="view" value="<?php echo $this->view; ?>" />
        <input type="hidden" name="task" value="<?php echo $this->task; ?>" />
        <input type="hidden" name="tmpl" value="<?php echo $this->tmpl; ?>" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>