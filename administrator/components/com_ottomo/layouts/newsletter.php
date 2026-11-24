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
use Joomla\CMS\Session\Session;
use Joomla\String\StringHelper;
?>
<div id="otm-admin-container" class="otm-admin-list otm-admin-view <?php echo StringHelper::ucfirst($this->view); ?>">
	<script>
	Joomla.submitbutton = function(task) {
		'use strict';
		var form = document.getElementById('adminForm');
		var id = (form.elements.id.value || '').trim();
		var token = <?php echo json_encode(Session::getFormToken()); ?>;

		if (task === 'newsletters.popup' || task === 'newsletters.preview' || task === 'newsletters.sample') {
			if (!id) {
				alert(<?php echo json_encode(Text::_('COM_OTTOMO_NEWSLETTER_MUST_BE_SAVED_FIRST')); ?>);
				return false;
			}
			if (Ottomo.newsletterModified) {
				alert(<?php echo json_encode(Text::_('COM_OTTOMO_NEWSLETTER_HAS_CHANGED_PLEASE_SAVE_BEFORE_SENDING')); ?>);
				return false;
			}
			var base = 'index.php?option=com_ottomo&tmpl=component&id=' + encodeURIComponent(id) + '&' + token + '=1';

			// Send test: the frame posts nothing, the emails travel in the URL
			if (task === 'newsletters.sample') {
				var emails = (form.elements.emails.value || '').trim();
				var phpMail = form.elements.testUsePhpMail && form.elements.testUsePhpMail.checked ? '&testUsePhpMail=1' : '';
				Ottomo.frame(base + '&task=newsletters.sample&emails=' + encodeURIComponent(emails) + phpMail,
					<?php echo json_encode(Text::_('COM_OTTOMO_SEND_TEST_TITLE')); ?>);
				if (emails && Ottomo.preflight) {
					Ottomo.preflight.markTestSent();
				}
				return false;
			}

			// Send the campaign: the frame shows the recipients and asks for the final confirmation
			if (task === 'newsletters.popup') {
				if (Ottomo.preflight && !Ottomo.preflight.ok()) {
					alert(<?php echo json_encode(Text::_('COM_OTTOMO_PREFLIGHT_BLOCKED')); ?>);
					return false;
				}
				Ottomo.frame(base + '&task=newsletters.popup', <?php echo json_encode(Text::_('COM_OTTOMO_SEND_CAMPAIGN')); ?>, { reload: true });
				return false;
			}
		} else if (task === 'newsletters.save' || task === 'newsletters.apply') {
			if (!document.getElementById('subject').value.trim()) {
				alert(<?php echo json_encode(Text::_('COM_OTTOMO_NEWSLETTER_MUST_HAVE_A_SUBJECT')); ?>);
				return false;
			}
			Ottomo.updateData();
		}
		Joomla.submitform(task);
	};
	</script>
    <div id="otm-newsletter-form" class="otm-nsl-form">
        <?php if ($this->row->published && $this->remaining): ?>
        <div class="otm-msg otm-msg--warning otm-sent-banner" role="status" data-role="send-interrupted">
            <span><?php echo Text::sprintf('COM_OTTOMO_SEND_INTERRUPTED', number_format((int)$this->remaining)); ?></span>
            <button type="button" class="otm-btn otm-btn--save" data-role="send-now" onclick="Joomla.submitbutton('newsletters.popup'); return false;">
                <?php echo OttomoHelperHTML::icon('envelope'); ?>
                <span><?php echo Text::_('COM_OTTOMO_RESUME_SENDING'); ?></span>
            </button>
        </div>
        <?php elseif ($this->row->published): ?>
        <div class="otm-msg otm-msg--info otm-sent-banner" role="status">
            <span><?php echo Text::_('COM_OTTOMO_ALREADY_SENT'); ?></span>
            <a class="otm-btn otm-btn--generic" data-role="duplicate" href="<?php echo Route::_('index.php?option=com_ottomo&task=newsletters.duplicate&id='.(int)$this->row->id.'&'.Session::getFormToken().'=1'); ?>">
                <?php echo OttomoHelperHTML::icon('files'); ?>
                <span><?php echo Text::_('COM_OTTOMO_DUPLICATE_AS_DRAFT'); ?></span>
            </a>
        </div>
        <?php endif; ?>
        <?php if (!$this->row->id): ?>
        <div class="otm-msg otm-msg--info otm-msg--center" role="status">
            <?php echo Text::_('COM_OTTOMO_CAMPAIGN_NOT_SAVED'); ?>
        </div>
        <?php endif; ?>
        <form action="index.php" method="post" name="adminForm" id="adminForm">
        	<div class="otm-nsltabs">
                <ul data-role="tabs-navigation" id="nsl--tabs">
                    <li>
                        <a href="#step1" class="active">
                            <span class="num">1</span>
                            <span class="txt"><?php echo Text::_('COM_OTTOMO_CAMPAIGN_DETAILS_AND_RECIPIENTS'); ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="#step2">
                            <span class="num">2</span>
                            <span class="txt"><?php echo Text::_('COM_OTTOMO_NEWSLETTER_DESIGN'); ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="#step3">
                            <span class="num">3</span>
                            <span class="txt"><?php echo Text::_('COM_OTTOMO_NEWSLETTER_CONTENT'); ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="#step4">
                            <span class="num">4</span>
                            <span class="txt"><?php echo Text::_('COM_OTTOMO_STEP_SEND'); ?></span>
                        </a>
                    </li>
                </ul>
            </div>

            <div data-role="tabs-content" class="tab-pane padding active" id="step1">
                <?php JLoader::register('OttomoHelperTemplate', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/template.php'); JLoader::register('OttomoHelperSettings', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/settings.php'); $defaultSender = OttomoHelperSettings::sender(); ?>

                <div class="otm-step1">
                <div class="otm-fluidform">
                    <div class="otm-fixedform otm-form-grid">
                        <h3 class="otm-formblock--wide"><?php echo Text::_('COM_OTTOMO_EDIT_CAMPAIGN_DETAILS'); ?></h3>

                        <div class="otm-formblock otm-formblock--wide">
                            <div class="otm-label-row">
                                <label for="subject"><?php echo Text::_('COM_OTTOMO_SUBJECT'); ?></label>
                                <button type="button" class="otm-link-action" data-insert-token="{{firstname|<?php echo htmlspecialchars(Text::_('COM_OTTOMO_PERSONALISE_FALLBACK'), ENT_QUOTES, 'UTF-8'); ?>}}" data-insert-target="subject" title="<?php echo htmlspecialchars(Text::_('COM_OTTOMO_PERSONALISE_INSERT_FIRSTNAME_DESC'), ENT_QUOTES, 'UTF-8'); ?>"><?php echo Text::_('COM_OTTOMO_PERSONALISE_INSERT_FIRSTNAME'); ?></button>
                            </div>
                            <input type="text" name="subject" id="subject" value="<?php echo $this->row->subject; ?>" />
                        </div>

                        <div class="otm-formblock otm-formblock--wide">
                            <label for="preheader"><?php echo Text::_('COM_OTTOMO_PREHEADER'); ?></label>
                            <input type="text" name="preheader" id="preheader" maxlength="150" value="<?php echo isset($this->row->preheader) ? $this->row->preheader : ''; ?>" aria-describedby="preheaderHint" />
                            <p class="otm-field-hint" id="preheaderHint"><?php echo Text::_('COM_OTTOMO_PREHEADER_HINT'); ?> <span data-role="preheader-count" aria-live="polite"></span></p>
                        </div>

                        <div class="otm-formblock">
                            <label for="senderName"><?php echo Text::_('COM_OTTOMO_FROM_NAME'); ?> <span class="otm-optional"><?php echo Text::_('COM_OTTOMO_OPTIONAL'); ?></span></label>
                            <input type="text" name="senderName" id="senderName" value="<?php echo $this->row->senderName; ?>" placeholder="<?php echo htmlspecialchars($defaultSender->name, ENT_QUOTES, 'UTF-8'); ?>" aria-describedby="senderHint" />
                        </div>

                        <div class="otm-formblock">
                            <label for="senderEmail"><?php echo Text::_('COM_OTTOMO_FROM_EMAIL_ADDRESS'); ?> <span class="otm-optional"><?php echo Text::_('COM_OTTOMO_OPTIONAL'); ?></span></label>
                            <input type="email" name="senderEmail" id="senderEmail" value="<?php echo $this->row->senderEmail; ?>" placeholder="<?php echo htmlspecialchars($defaultSender->email, ENT_QUOTES, 'UTF-8'); ?>" aria-describedby="senderHint" />
                        </div>
                        <p class="otm-field-hint otm-formblock--wide" id="senderHint"><?php echo Text::_('COM_OTTOMO_SENDER_HINT'); ?></p>

                    </div>
                </div>

                <div class="otm-fluidform">
                    <div class="otm-fixedform otm-recipients">
                        <div class="otm-formblock otm-topics">
                            <!-- <?php echo $this->published; ?> -->

                            <h3><?php echo Text::_('COM_OTTOMO_RECIPIENTS'); ?></h3>

                            <div class="otm-topics otm-topics--upper">
                                <input type="checkbox" id="otm-addall" name="otm-addall" />
                                <label for="otm-addall"><?php echo Text::_('COM_OTTOMO_CHECK_ALL'); ?></label>

                                <a href="#" id="otm-reset" class="otm-reset"><?php echo Text::_('COM_OTTOMO_RESET'); ?></a>
                            </div>

                            <?php echo $this->topics; ?>

                        </div>

                        <div class="otm-formblock otm-recipients__total">
                            <span class="otm-tolalreciep" id="otm-total-recipients">0</span>
                            <span class="otm-tolalreciep--lbl">
                                <?php echo Text::_('COM_OTTOMO_UNIQUE_RECIPIENTS_SELECTED'); ?>
                            </span>
                        </div>
                    </div>
                </div>
                </div>

                <div class="otm-fixedform otm-step-nav">
                    <div class="otm-nextstep otm-nextstep--next">
                        <span class="msg"><?php echo Text::_('COM_OTTOMO_PICK_A_DESIGN_FOR_YOUR_NEWSLETTER'); ?></span>

                        <button class="otm-btn otm-btn--generic otm-btn--iconright" data-role="tabs-next">
                            <span><?php echo Text::_('COM_OTTOMO_NEXT'); ?></span>
                            <?php echo OttomoHelperHTML::icon('chevron-right', 'icon'); ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Design -->
            <div data-role="tabs-content" class="tab-pane" id="step2">

                <div class="otm-fluidform">
                    <ul class="otm-template-cards otm-grid-items" role="list">
                        <?php foreach ($this->templates as $template): $selected = $template->id == $this->row->template; ?>
                        <li class="otm-grid-item otm-template-card<?php echo $selected ? ' active otm-grid-item--selected' : ''; ?>" data-role="template" data-template="<?php echo (int)$template->id; ?>" tabindex="0" aria-pressed="<?php echo $selected ? 'true' : 'false'; ?>">
                            <div class="otm-grid-item-wrap">
                                <div class="otm-template-card__media">
                                    <?php if ($template->cardImage): ?>
                                    <div class="otm-grid-item-bg<?php echo $template->cardImageFull ? '' : ' otm-grid-item-bg--thumb'; ?>" style="background-image: url(<?php echo htmlspecialchars($template->cardImage, ENT_QUOTES, 'UTF-8'); ?>);"></div>
                                    <?php else: ?>
                                    <div class="otm-grid-item-bg otm-grid-item-bg--empty"></div>
                                    <?php endif; ?>
                                    <span class="otm-template-card__selected"><?php echo OttomoHelperHTML::icon('check-circle-fill'); ?><?php echo Text::_('COM_OTTOMO_SELECTED'); ?></span>
                                    <a class="otm-template-card__preview template-preview-image" href="<?php echo htmlspecialchars($template->preview, ENT_QUOTES, 'UTF-8'); ?>" title="<?php echo htmlspecialchars($template->name, ENT_QUOTES, 'UTF-8'); ?>"><?php echo OttomoHelperHTML::icon('eye'); ?><span><?php echo Text::_('COM_OTTOMO_PREVIEW'); ?></span></a>
                                </div>
                                <div class="otm-grid-text-wrap otm-template-card__body">
                                    <h3><?php echo $template->name; ?></h3>
                                    <?php if ($template->layoutLabel !== ''): ?>
                                    <span class="otm-template-card__layout"><?php echo OttomoHelperHTML::icon('layers'); ?><?php echo htmlspecialchars($template->layoutLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="otm-fixedform otm-step-nav">

                    <div class="otm-nextstep">
                        <button class="otm-btn otm-btn--generic otm-btn--iconleft" data-role="tabs-previous">
                            <?php echo OttomoHelperHTML::icon('chevron-left', 'icon'); ?>
                            <span><?php echo Text::_('COM_OTTOMO_PREVIOUS'); ?></span>
                        </button>
                        <span class="msg"><?php echo Text::_('COM_OTTOMO_CAMPAIGN_DETAILS'); ?></span>
                    </div>

                    <div class="otm-nextstep otm-nextstep--next">
                        <span class="msg"><?php echo Text::_('COM_OTTOMO_ADD_SOME_CONTENT'); ?></span>
                        <button class="otm-btn otm-btn--generic otm-btn--iconright" data-role="tabs-next">
                            <span><?php echo Text::_('COM_OTTOMO_NEXT'); ?></span>
                            <?php echo OttomoHelperHTML::icon('chevron-right', 'icon'); ?>
                        </button>
                    </div>

                    <input type="hidden" name="template" value="<?php echo htmlspecialchars((string)$this->row->template, ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
            </div>

            <!-- Add Content -->
            <div data-role="tabs-content" class="tab-pane" id="step3">

                <div class="otm-editor-head">
                    <ul class="nav nav-tabs" id="otm-editor-tabs" role="tablist">
                      <li class="active"><a href="#visual" role="tab"><?php echo Text::_('COM_OTTOMO_VISUAL'); ?></a></li>
                      <li><a href="#html" role="tab"><?php echo Text::_('COM_OTTOMO_HTML'); ?></a></li>
                      <li><a href="#text" role="tab"><?php echo Text::_('COM_OTTOMO_PLAIN_TEXT'); ?></a></li>
                    </ul>

                    <div class="otm-editor-head__actions">
                    <div class="otm-device-toggle" role="group" aria-label="<?php echo htmlspecialchars(Text::_('COM_OTTOMO_EDITOR_DEVICE'), ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="button" data-device="desktop" aria-pressed="true" title="<?php echo htmlspecialchars(Text::_('COM_OTTOMO_EDITOR_DESKTOP'), ENT_QUOTES, 'UTF-8'); ?>"><?php echo OttomoHelperHTML::icon('display'); ?><span class="visually-hidden"><?php echo Text::_('COM_OTTOMO_EDITOR_DESKTOP'); ?></span></button>
                        <button type="button" data-device="mobile" aria-pressed="false" title="<?php echo htmlspecialchars(Text::_('COM_OTTOMO_EDITOR_MOBILE'), ENT_QUOTES, 'UTF-8'); ?>"><?php echo OttomoHelperHTML::icon('phone'); ?><span class="visually-hidden"><?php echo Text::_('COM_OTTOMO_EDITOR_MOBILE'); ?></span></button>
                    </div>
                    <?php if ($this->row->id): ?>
                    <button type="button" class="otm-btn otm-btn--generic" data-role="preview-button" data-id="<?php echo $this->row->id; ?>"<?php echo !empty($this->row->published) ? ' data-public-url="'.OttomoHelperTemplate::publicLink($this->row->id).'"' : ''; ?>>
                        <?php echo OttomoHelperHTML::icon('eye'); ?>
                        <span><?php echo Text::_('COM_OTTOMO_PREVIEW'); ?></span>
                    </button>
                    <?php else: ?>
                    <button type="button" class="otm-btn otm-btn--generic" disabled="disabled" title="<?php echo htmlspecialchars(Text::_('COM_OTTOMO_CAMPAIGN_NOT_SAVED'), ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo OttomoHelperHTML::icon('eye'); ?>
                        <span><?php echo Text::_('COM_OTTOMO_PREVIEW'); ?></span>
                    </button>
                    <?php endif; ?>
                    </div>
                </div>

                <div class="otm-editor-pane tab-content">
                    <div class="tab-pane active" id="visual">
                        <div id="otm-editor-buttons">
                            <?php $t = function ($key) { return htmlspecialchars(Text::_('COM_OTTOMO_EDITOR_'.$key), ENT_QUOTES, 'UTF-8'); }; ?>
                            <div class="otm-editor-toolbar is-inactive" role="toolbar" aria-label="<?php echo $t('TOOLBAR'); ?>" data-editor-toolbar>
                                <div class="otm-editor-toolbar__group">
                                    <button type="button" data-command="bold" aria-pressed="false" title="<?php echo $t('BOLD'); ?>" aria-label="<?php echo $t('BOLD'); ?>"><?php echo OttomoHelperHTML::icon('type-bold'); ?></button>
                                    <button type="button" data-command="italic" aria-pressed="false" title="<?php echo $t('ITALIC'); ?>" aria-label="<?php echo $t('ITALIC'); ?>"><?php echo OttomoHelperHTML::icon('type-italic'); ?></button>
                                    <button type="button" data-command="underline" aria-pressed="false" title="<?php echo $t('UNDERLINE'); ?>" aria-label="<?php echo $t('UNDERLINE'); ?>"><?php echo OttomoHelperHTML::icon('type-underline'); ?></button>
                                </div>
                                <div class="otm-editor-toolbar__group">
                                    <select data-command="formatBlock" aria-label="<?php echo $t('TEXT_STYLE'); ?>" title="<?php echo $t('TEXT_STYLE'); ?>">
                                        <option value="p"><?php echo $t('PARAGRAPH'); ?></option>
                                        <option value="h1"><?php echo $t('HEADING_1'); ?></option>
                                        <option value="h2"><?php echo $t('HEADING_2'); ?></option>
                                        <option value="h3"><?php echo $t('HEADING_3'); ?></option>
                                        <option value="h4"><?php echo $t('HEADING_4'); ?></option>
                                        <option value="pre"><?php echo $t('PREFORMATTED'); ?></option>
                                    </select>
                                    <?php
                                    // Fonts every major email client renders (web, desktop, mobile); the default is the template's own font
                                    $emailFonts = array(
                                        'Arial' => 'Arial, Helvetica, sans-serif',
                                        'Verdana' => 'Verdana, Geneva, sans-serif',
                                        'Tahoma' => 'Tahoma, Geneva, sans-serif',
                                        'Trebuchet MS' => "'Trebuchet MS', Helvetica, sans-serif",
                                        'Georgia' => "Georgia, 'Times New Roman', serif",
                                        'Times New Roman' => "'Times New Roman', Times, serif",
                                        'Courier New' => "'Courier New', Courier, monospace",
                                    );
                                    ?>
                                    <select data-command="fontName" aria-label="<?php echo $t('FONT'); ?>" title="<?php echo $t('FONT'); ?>">
                                        <option value=""><?php echo $t('FONT_TEMPLATE'); ?></option>
                                        <?php foreach ($emailFonts as $fontLabel => $fontStack): ?>
                                        <option value="<?php echo htmlspecialchars($fontStack, ENT_QUOTES, 'UTF-8'); ?>" style="font-family:<?php echo htmlspecialchars($fontStack, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($fontLabel, ENT_QUOTES, 'UTF-8'); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="otm-editor-toolbar__group">
                                    <button type="button" data-command="justifyLeft" aria-pressed="false" title="<?php echo $t('ALIGN_LEFT'); ?>" aria-label="<?php echo $t('ALIGN_LEFT'); ?>"><?php echo OttomoHelperHTML::icon('text-left'); ?></button>
                                    <button type="button" data-command="justifyCenter" aria-pressed="false" title="<?php echo $t('ALIGN_CENTER'); ?>" aria-label="<?php echo $t('ALIGN_CENTER'); ?>"><?php echo OttomoHelperHTML::icon('text-center'); ?></button>
                                    <button type="button" data-command="justifyRight" aria-pressed="false" title="<?php echo $t('ALIGN_RIGHT'); ?>" aria-label="<?php echo $t('ALIGN_RIGHT'); ?>"><?php echo OttomoHelperHTML::icon('text-right'); ?></button>
                                </div>
                                <div class="otm-editor-toolbar__group">
                                    <button type="button" data-command="insertUnorderedList" aria-pressed="false" title="<?php echo $t('BULLET_LIST'); ?>" aria-label="<?php echo $t('BULLET_LIST'); ?>"><?php echo OttomoHelperHTML::icon('list-ul'); ?></button>
                                    <button type="button" data-command="insertOrderedList" aria-pressed="false" title="<?php echo $t('NUMBERED_LIST'); ?>" aria-label="<?php echo $t('NUMBERED_LIST'); ?>"><?php echo OttomoHelperHTML::icon('list-ol'); ?></button>
                                </div>
                                <div class="otm-editor-toolbar__group">
                                    <button type="button" data-command="link" aria-pressed="false" title="<?php echo $t('LINK'); ?>" aria-label="<?php echo $t('LINK'); ?>"><?php echo OttomoHelperHTML::icon('link-45deg'); ?></button>
                                    <button type="button" data-command="removeFormat" title="<?php echo $t('CLEAR_FORMATTING'); ?>" aria-label="<?php echo $t('CLEAR_FORMATTING'); ?>"><?php echo OttomoHelperHTML::icon('eraser'); ?></button>
                                </div>
                                <div class="otm-editor-toolbar__group">
                                    <?php $fallback = htmlspecialchars(Text::_('COM_OTTOMO_PERSONALISE_FALLBACK'), ENT_QUOTES, 'UTF-8'); ?>
                                    <select data-command="insertField" aria-label="<?php echo $t('PERSONALISE'); ?>" title="<?php echo $t('PERSONALISE'); ?>">
                                        <option value=""><?php echo $t('PERSONALISE'); ?>…</option>
                                        <option value="{{firstname|<?php echo $fallback; ?>}}"><?php echo $t('FIELD_FIRSTNAME'); ?></option>
                                        <option value="{{name|<?php echo $fallback; ?>}}"><?php echo $t('FIELD_NAME'); ?></option>
                                        <option value="{{lastname}}"><?php echo $t('FIELD_LASTNAME'); ?></option>
                                        <option value="{{email}}"><?php echo $t('FIELD_EMAIL'); ?></option>
                                        <option value="{{company}}"><?php echo $t('FIELD_COMPANY'); ?></option>
                                    </select>
                                </div>
                                <span class="otm-editor-toolbar__hint"><?php echo $t('HINT'); ?></span>
                            </div>
                            <div class="otm-editor-link" data-editor-link hidden>
                                <label class="otm-editor-link__field">
                                    <?php echo OttomoHelperHTML::icon('link-45deg'); ?>
                                    <span class="visually-hidden"><?php echo $t('LINK_URL'); ?></span>
                                    <input type="url" data-link-href placeholder="https://" autocomplete="off" spellcheck="false" />
                                </label>
                                <button type="button" data-link-target aria-pressed="false"><?php echo $t('LINK_NEW_WINDOW'); ?></button>
                                <a data-link-follow href="#" target="_blank" rel="noopener noreferrer" title="<?php echo $t('LINK_OPEN'); ?>" aria-label="<?php echo $t('LINK_OPEN'); ?>"><?php echo OttomoHelperHTML::icon('box-arrow-up-right'); ?></a>
                                <button type="button" data-link-remove title="<?php echo $t('LINK_REMOVE'); ?>" aria-label="<?php echo $t('LINK_REMOVE'); ?>"><?php echo OttomoHelperHTML::icon('x-lg'); ?></button>
                            </div>
                            <div class="otm-editor-image" role="group" aria-label="<?php echo $t('IMAGE'); ?>" data-editor-image hidden>
                                <label class="otm-editor-image__width">
                                    <?php echo OttomoHelperHTML::icon('image'); ?>
                                    <span><?php echo $t('IMAGE_WIDTH'); ?></span>
                                    <input type="number" data-image-width min="16" max="2000" step="1" inputmode="numeric" />
                                    <span aria-hidden="true">px</span>
                                </label>
                                <button type="button" data-image-fit title="<?php echo $t('IMAGE_FIT_TITLE'); ?>"><?php echo OttomoHelperHTML::icon('arrows-angle-contract'); ?><span><?php echo $t('IMAGE_FIT'); ?></span></button>
                                <span class="otm-editor-image__align" role="group" aria-label="<?php echo $t('IMAGE_ALIGN'); ?>">
                                    <button type="button" data-image-align="left" aria-pressed="false" title="<?php echo $t('ALIGN_LEFT'); ?>" aria-label="<?php echo $t('ALIGN_LEFT'); ?>"><?php echo OttomoHelperHTML::icon('text-left'); ?></button>
                                    <button type="button" data-image-align="center" aria-pressed="false" title="<?php echo $t('ALIGN_CENTER'); ?>" aria-label="<?php echo $t('ALIGN_CENTER'); ?>"><?php echo OttomoHelperHTML::icon('text-center'); ?></button>
                                    <button type="button" data-image-align="right" aria-pressed="false" title="<?php echo $t('ALIGN_RIGHT'); ?>" aria-label="<?php echo $t('ALIGN_RIGHT'); ?>"><?php echo OttomoHelperHTML::icon('text-right'); ?></button>
                                </span>
                                <label class="otm-editor-link__field">
                                    <span><?php echo $t('IMAGE_ALT'); ?></span>
                                    <input type="text" data-image-alt placeholder="<?php echo $t('IMAGE_ALT_PLACEHOLDER'); ?>" autocomplete="off" />
                                </label>
                                <label class="otm-editor-link__field">
                                    <?php echo OttomoHelperHTML::icon('link-45deg'); ?>
                                    <span class="visually-hidden"><?php echo $t('IMAGE_LINK'); ?></span>
                                    <input type="url" data-image-href placeholder="https://" autocomplete="off" spellcheck="false" />
                                </label>
                                <button type="button" data-image-target aria-pressed="false"><?php echo $t('LINK_NEW_WINDOW'); ?></button>
                            </div>
                            <div class="otm-editor-button" role="group" aria-label="<?php echo $t('BUTTON'); ?>" data-editor-button hidden>
                                <label class="otm-editor-link__field">
                                    <?php echo OttomoHelperHTML::icon('cursor'); ?>
                                    <span class="visually-hidden"><?php echo $t('BUTTON_LINK'); ?></span>
                                    <input type="url" data-button-href placeholder="https://" autocomplete="off" spellcheck="false" />
                                </label>
                                <button type="button" data-button-target aria-pressed="false"><?php echo $t('LINK_NEW_WINDOW'); ?></button>
                                <span class="otm-editor-image__align" role="group" aria-label="<?php echo $t('BUTTON_ALIGN'); ?>">
                                    <button type="button" data-button-align="left" aria-pressed="false" title="<?php echo $t('ALIGN_LEFT'); ?>" aria-label="<?php echo $t('ALIGN_LEFT'); ?>"><?php echo OttomoHelperHTML::icon('text-left'); ?></button>
                                    <button type="button" data-button-align="center" aria-pressed="false" title="<?php echo $t('ALIGN_CENTER'); ?>" aria-label="<?php echo $t('ALIGN_CENTER'); ?>"><?php echo OttomoHelperHTML::icon('text-center'); ?></button>
                                    <button type="button" data-button-align="right" aria-pressed="false" title="<?php echo $t('ALIGN_RIGHT'); ?>" aria-label="<?php echo $t('ALIGN_RIGHT'); ?>"><?php echo OttomoHelperHTML::icon('text-right'); ?></button>
                                </span>
                                <button type="button" data-button-full aria-pressed="false"><?php echo OttomoHelperHTML::icon('arrows'); ?><span><?php echo $t('BUTTON_FULL_WIDTH'); ?></span></button>
                                <label class="otm-editor-button__color"><span><?php echo $t('BUTTON_BACKGROUND'); ?></span><input type="color" data-button-background /></label>
                                <label class="otm-editor-button__color"><span><?php echo $t('BUTTON_TEXT_COLOR'); ?></span><input type="color" data-button-color /></label>
                                <span class="otm-editor-image__align" role="group" aria-label="<?php echo $t('BUTTON_CORNERS'); ?>">
                                    <button type="button" data-button-corners="0px" aria-pressed="false"><?php echo $t('CORNERS_SQUARE'); ?></button>
                                    <button type="button" data-button-corners="8px" aria-pressed="false"><?php echo $t('CORNERS_ROUNDED'); ?></button>
                                    <button type="button" data-button-corners="999px" aria-pressed="false"><?php echo $t('CORNERS_PILL'); ?></button>
                                </span>
                            </div>
                        </div>

                        <iframe id="otm-designer-frame" name="otm-designer-frame" src="<?php echo Route::_('index.php?option=com_ottomo&view=designer&id='.$this->row->id.'&template='.$this->row->template.'&format=raw')?>"></iframe>

                    </div>
                    <div class="tab-pane" id="html">
                    	<span class="otm-msg"><?php echo Text::_('COM_OTTOMO_SNAPSHOT_WARNING'); ?></span>

                        <div id="otm-source-editor"><?php echo $this->row->snapshot; ?></div>
                    </div>

                    <div class="tab-pane" id="text">
                        <div class="otm-fixedform">
                            <textarea name="plain" id="plain" rows="5" cols="20"><?php echo $this->row->plain; ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="otm-fixedform otm-step-nav">

                    <div class="otm-nextstep">
                        <button class="otm-btn otm-btn--generic otm-btn--iconleft" data-role="tabs-previous">
                            <?php echo OttomoHelperHTML::icon('chevron-left', 'icon'); ?>
                            <span><?php echo Text::_('COM_OTTOMO_PREVIOUS'); ?></span>
                        </button>

                        <span class="msg"><?php echo Text::_('COM_OTTOMO_NEWSLETTER_DESIGN'); ?></span>
                    </div>


                    <div class="otm-nextstep otm-nextstep--next">
                        <span class="msg"><?php echo Text::_('COM_OTTOMO_SEND_CAMPAIGN'); ?></span>
                        <button class="otm-btn otm-btn--generic otm-btn--iconright" data-role="tabs-next">
                            <span><?php echo Text::_('COM_OTTOMO_NEXT'); ?></span>
                            <?php echo OttomoHelperHTML::icon('chevron-right', 'icon'); ?>
                        </button>
                    </div>
                </div>

            </div>

            <div data-role="tabs-content" class="tab-pane padding" id="step4">

                <div class="otm-step-row"><?php // the summary, and the results of a sent campaign next to it ?>
                <div class="otm-fluidform">
                    <div class="otm-fixedform otm-summary">
                        <div class="otm-summary__head">
                        <h3><?php echo Text::_('COM_OTTOMO_CAMPAIGN_SUMMARY'); ?></h3>
						<?php if ($this->row->id): ?>
                        <button class="otm-btn otm-btn--accent otm-btn--iconleft" data-role="preview-button" data-id="<?php echo $this->row->id; ?>"<?php echo !empty($this->row->published) ? ' data-public-url="'.OttomoHelperTemplate::publicLink($this->row->id).'"' : ''; ?>>
                            <?php echo OttomoHelperHTML::icon('eye', 'icon'); ?>
                            <span><?php echo Text::_('COM_OTTOMO_PREVIEW'); ?></span>
                        </button>
                        <?php else: ?>
                        <button class="otm-btn otm-btn--accent otm-btn--iconleft disabled" disabled="disabled">
                            <?php echo OttomoHelperHTML::icon('eye', 'icon'); ?>
                            <span><?php echo Text::_('COM_OTTOMO_PREVIEW'); ?></span>
                        </button>
                        <?php endif; ?>
                        </div>
                        <div class="otm-summary__body">
                        <div class="otm-summarywrap">

                            <div class="otm-summaryblock">
                                <label class="purple"><?php echo Text::_('COM_OTTOMO_SUBJECT'); ?></label>
                                <div class="otm-summarytxt" data-role="summary-subject"></div>
                            </div>

                            <div class="otm-summaryblock">
                                <label class="purple"><?php echo Text::_('COM_OTTOMO_FROM'); ?></label>
                                <div class="otm-summarytxt">
                                    <span class="otm-sender" data-role="summary-sender-name"><?php echo $this->row->senderName; ?></span>
                                    <span class="otm-sendermail" data-role="summary-sender-email"><?php echo $this->row->senderEmail; ?></span>
                                </div>
                            </div>

                            <div class="otm-summaryblock">
                                <label class="purple"><?php echo Text::_('COM_OTTOMO_RECIPIENTS'); ?> <span data-role="summary-recipients-counter"></span></label>
                               <ul data-role="summary-recipients" class="otm-summary-recipients"></ul>
                            </div>
                        </div>
                        <div class="otm-summarywrap otm-summarywrap--template">
                            <label class="purple"><?php echo Text::_('COM_OTTOMO_TEMPLATE'); ?></label>
                            <div data-role="summary-template" class="otm-grid-item"></div>
                        </div>
                        </div>
                    </div>
                </div>
                <?php if ($this->report): $report = $this->report; ?>
                <section class="otm-fluidform otm-report" data-role="campaign-report" aria-labelledby="otm-report-title">
                    <h3 id="otm-report-title"><?php echo Text::_('COM_OTTOMO_CAMPAIGN_RESULTS'); ?> <small><?php echo Text::_('COM_OTTOMO_SENT_ON'); ?> <?php echo OttomoHelperHTML::date($this->row->sent); ?></small></h3>
                    <div class="otm-report__figures">
                        <div class="otm-report__figure"><span class="otm-report__label"><?php echo Text::_('COM_OTTOMO_RECIPIENTS'); ?></span><strong data-role="report-recipients"><?php echo number_format($report->recipients); ?></strong></div>
                        <div class="otm-report__figure"><span class="otm-report__label"><?php echo Text::_('COM_OTTOMO_OPEN_RATE'); ?></span><strong data-role="report-open-rate"><?php echo OttomoHelperHTML::rate($report->openRate); ?></strong><span class="otm-muted"><?php echo Text::plural('COM_OTTOMO_N_SUBSCRIBERS', $report->opens); ?></span></div>
                        <div class="otm-report__figure"><span class="otm-report__label"><?php echo Text::_('COM_OTTOMO_CLICK_RATE'); ?></span><strong data-role="report-click-rate"><?php echo OttomoHelperHTML::rate($report->clickRate); ?></strong><span class="otm-muted"><?php echo Text::plural('COM_OTTOMO_N_SUBSCRIBERS', $report->clicks); ?></span></div>
                    </div>
                    <h4><?php echo Text::_('COM_OTTOMO_TOP_LINKS'); ?></h4>
                    <?php if (count($report->links)): ?>
                    <ol class="otm-report__links" data-role="report-links">
                        <?php foreach ($report->links as $link): ?>
                        <li><a href="<?php echo htmlspecialchars($link->link, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer"><?php echo htmlspecialchars($link->link, ENT_QUOTES, 'UTF-8'); ?></a><span class="otm-num"><?php echo Text::plural('COM_OTTOMO_N_CLICKS', (int)$link->clicks); ?></span></li>
                        <?php endforeach; ?>
                    </ol>
                    <?php else: ?>
                    <p class="otm-muted"><?php echo Text::_('COM_OTTOMO_NO_LINK_CLICKS'); ?></p>
                    <?php endif; ?>
                </section>
                <?php endif; ?>
                </div>

                <?php if (!$this->row->published): ?>
                <?php JLoader::register('OttomoHelperSettings', JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/settings.php'); ?>
                <div class="otm-step-row"><?php // the pre-flight checklist next to the test send ?>
                <div class="otm-fluidform">
                    <div class="otm-fixedform">
                        <section class="otm-preflight" data-preflight data-required="<?php echo OttomoHelperSettings::enabled('preflightRequired') ? '1' : '0'; ?>" data-footer="<?php echo OttomoHelperSettings::enabled('complianceFooter') ? '1' : '0'; ?>" aria-labelledby="otm-preflight-title">
                            <h3 id="otm-preflight-title"><?php echo Text::_('COM_OTTOMO_PREFLIGHT_TITLE'); ?></h3>
                            <ul class="otm-preflight__list">
                                <li data-check="subject" data-blocking="1"><?php echo Text::_('COM_OTTOMO_PREFLIGHT_SUBJECT'); ?></li>
                                <li data-check="sender" data-blocking="1"><?php echo Text::_('COM_OTTOMO_PREFLIGHT_SENDER'); ?></li>
                                <li data-check="recipients" data-blocking="1"><?php echo Text::_('COM_OTTOMO_PREFLIGHT_RECIPIENTS'); ?></li>
                                <li data-check="saved" data-blocking="1"><?php echo Text::_('COM_OTTOMO_PREFLIGHT_SAVED'); ?></li>
                                <li data-check="unsubscribe" data-blocking="1"><?php echo Text::_('COM_OTTOMO_PREFLIGHT_UNSUBSCRIBE'); ?></li>
                                <li data-check="size" data-blocking="0"><span><?php echo Text::_('COM_OTTOMO_PREFLIGHT_SIZE'); ?><small class="otm-preflight__detail" data-role="size-detail"></small></span></li>
                                <li data-check="auth" data-blocking="0"><span><?php echo Text::_('COM_OTTOMO_PREFLIGHT_AUTH'); ?><small class="otm-preflight__detail" data-role="auth-detail"></small></span></li>
                                <li data-check="preheader" data-blocking="0"><?php echo Text::_('COM_OTTOMO_PREFLIGHT_PREHEADER'); ?></li>
                                <li data-check="test" data-blocking="0"><?php echo Text::_('COM_OTTOMO_PREFLIGHT_TEST'); ?></li>
                            </ul>
                            <p class="otm-preflight__summary" data-role="preflight-summary" aria-live="polite"></p>
                        </section>
                    </div>
                </div>

                <div class="otm-fluidform">
                    <div class="otm-fixedform otm-sendtest">

                        <h3><?php echo Text::_('COM_OTTOMO_SEND_TEST_TITLE'); ?></h3>
                        <div class="otm-sendtest__row">
                            <input type="text" name="emails" inputmode="email" autocomplete="off" spellcheck="false" aria-label="<?php echo htmlspecialchars(Text::_('COM_OTTOMO_SEND_TEST'), ENT_QUOTES, 'UTF-8'); ?>" aria-describedby="otm-sendtest-hint" />
                            <?php if ($this->row->id): ?>
                            <button class="otm-btn otm-btn--dark" onclick="Joomla.submitbutton('newsletters.sample'); return false;">
                                <span><?php echo Text::_('COM_OTTOMO_SEND_TEST'); ?></span>
                            </button>
                            <?php else: ?>
                            <button class="otm-btn otm-btn--dark disabled" disabled="disabled">
                                <span><?php echo Text::_('COM_OTTOMO_SEND_TEST'); ?></span>
                            </button>
                            <?php endif; ?>
                        </div>
                        <p class="otm-sendtest__hint" id="otm-sendtest-hint"><?php echo OttomoHelperHTML::icon('info-circle'); ?><span><?php echo Text::_('COM_OTTOMO_SEND_TEST_TEXT'); ?></span></p>
                        <label class="otm-check otm-sendtest__option">
                            <input type="checkbox" name="testUsePhpMail" value="1" />
                            <span><?php echo Text::_('COM_OTTOMO_SEND_TEST_USE_PHP_MAIL'); ?></span>
                        </label>

                    </div>
                </div>
                </div>

                <div class="otm-fluidform">
                    <div class="otm-fixedform otm-sendnsl">
                        <div class="otm-sendnsl__text">
                            <h3><?php echo Text::_('COM_OTTOMO_SEND_NEWSLETTER'); ?></h3>
                            <p><strong><?php echo Text::_('COM_OTTOMO_SEND_NEWSLETTER_TEXT'); ?></strong></p>
                        </div>
                        <?php if ($this->row->id): ?>
                        <button class="otm-btn otm-btn--save" data-role="send-now" onclick="Joomla.submitbutton('newsletters.popup'); return false;">
                            <?php echo Text::_('COM_OTTOMO_SEND'); ?>
                        </button>
                        <?php else: ?>
                        <button class="otm-btn otm-btn--save disabled" disabled="disabled">
                            <?php echo Text::_('COM_OTTOMO_SEND'); ?>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="otm-fluidform otm-fluidform--last">
                    <div class="otm-fixedform otm-step-nav">
                        <div class="otm-nextstep">
                            <button class="otm-btn otm-btn--generic otm-btn--iconleft" data-role="tabs-previous">
                                <?php echo OttomoHelperHTML::icon('chevron-left', 'icon'); ?>
                                <span><?php echo Text::_('COM_OTTOMO_PREVIOUS'); ?></span>
                            </button>

                            <span class="msg"><?php echo Text::_('COM_OTTOMO_NEWSLETTER_CONTENT'); ?></span>
                        </div>
                    </div>
                </div>

            </div>

            <?php foreach ($this->row->data as $field):?>
                <input type="hidden" value="<?php echo $field->content; ?>" class="otm-position" id="<?php echo $field->position; ?>" name="ottomoPosition_<?php echo $field->position; ?>">
            <?php endforeach; ?>

			<input type="hidden" name="snapshot" value="<?php echo htmlspecialchars((string)$this->row->snapshot, ENT_QUOTES, 'UTF-8'); ?>" />
            <input type="hidden" name="id" value="<?php echo $this->row->id; ?>" />
            <input type="hidden" name="option" value="<?php echo $this->option; ?>" />
            <input type="hidden" name="view" value="<?php echo $this->view; ?>" />
            <input type="hidden" name="task" value="<?php echo $this->task; ?>" />
            <input type="hidden" name="tmpl" value="<?php echo $this->tmpl; ?>" />
            <?php echo HTMLHelper::_('form.token'); ?>
        </form>
    </div>
</div>
