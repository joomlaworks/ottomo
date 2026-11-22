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
<div class="otm-dashboard">

	<?php if (!count($this->latestNewsletters)): ?>
	<div class="otm-welcome">
		<h1><?php echo Text::_('COM_OTTOMO_WELCOME'); ?></h1>
		<div class="otm-welcomemsg"><?php echo Text::_('COM_OTTOMO_WELCOME_DESCRIPTION'); ?></div>
	</div>
	<?php endif; ?>

	<div id="otm-admin-container" class="otm-widget otm-widget--stats otm-admin-list otm-admin-view-<?php echo $this->view; ?>">
	    <div class="otm-widget-content">
	    	<h3 class="otm-title--inverse"><?php echo Text::_('COM_OTTOMO_DESCRIPTION'); ?></h3>
		    <?php if (count($this->latestNewsletters)): ?>
		    <div class="otm-dashboard-chart">
				<?php echo $this->loadTemplate('chart'); ?>
		    </div>
			<?php else: ?>
			<div class="otm-nocampaigns">
				<h1><?php echo Text::_('COM_OTTOMO_NO_CAMPAIGNS'); ?></h1>
				<a class="otm-btn otm-btn--icon otm-btn--plus" href="index.php?option=com_ottomo&amp;view=newsletters&amp;task=edit">
					<?php echo OttomoHelperHTML::icon('plus-lg'); ?>
					<span><?php echo Text::_('COM_OTTOMO_NEW_CAMPAIGN'); ?></span>
				</a>
			</div>
		    <?php endif; ?>
		</div>
	</div>

	<div class="otm-dashboard-side">
		<div class="otm-widget otm-widget--inverse otm-widget--totals">
			<div class="otm-widget-content">
				<div class="otm-totalblock">
					<span class="sum"><?php echo $this->numOfNewsletters; ?></span>
					<span class="lbl"><?php echo Text::_('COM_OTTOMO_NEWSLETTERS'); ?></span>
				</div>
				<div class="otm-totalblock">
					<span class="sum"><?php echo $this->numOfSubscribers; ?></span>
					<span class="lbl"><?php echo Text::_('COM_OTTOMO_SUBSCRIBERS'); ?></span>
				</div>
			</div>
		</div>

		<div class="otm-widget otm-widget--campaign" data-otm-tabs>
			<div class="otm-widget-content">
				<h3><?php echo Text::_('COM_OTTOMO_LATEST_NEWSLETTERS'); ?></h3>
				<div class="otm-tabs" role="tablist" aria-label="<?php echo htmlspecialchars(Text::_('COM_OTTOMO_LATEST_NEWSLETTERS'), ENT_QUOTES, 'UTF-8'); ?>">
					<button type="button" role="tab" id="otm-tab-sent" aria-controls="otm-panel-sent" aria-selected="true"><?php echo Text::_('COM_OTTOMO_RECENTLY_SENT'); ?> <span class="otm-tabs__count"><?php echo count($this->recentSent); ?></span></button>
					<button type="button" role="tab" id="otm-tab-drafts" aria-controls="otm-panel-drafts" aria-selected="false" tabindex="-1"><?php echo Text::_('COM_OTTOMO_DRAFTS'); ?> <span class="otm-tabs__count"><?php echo count($this->drafts); ?></span></button>
				</div>

				<div role="tabpanel" id="otm-panel-sent" aria-labelledby="otm-tab-sent" data-role="recent-sent">
					<?php if (count($this->recentSent)): ?>
					<ul class="otm-campaigns">
						<?php foreach ($this->recentSent as $newsletter): ?>
						<li>
							<a class="otm-campaigns__title" href="index.php?option=com_ottomo&amp;view=newsletters&amp;task=edit&amp;id=<?php echo (int)$newsletter->id; ?>"><?php echo htmlspecialchars($newsletter->subject, ENT_QUOTES, 'UTF-8'); ?></a>
							<span class="otm-campaigns__meta">
								<?php echo OttomoHelperHTML::date($newsletter->sent); ?>
								<?php if ($newsletter->stats->recipients): ?>
								<span class="otm-badge" data-role="recipients" title="<?php echo htmlspecialchars(Text::_('COM_OTTOMO_RECIPIENTS'), ENT_QUOTES, 'UTF-8'); ?>"><?php echo OttomoHelperHTML::icon('people'); ?> <strong><?php echo number_format($newsletter->stats->recipients); ?></strong></span>
								<span class="otm-badge" title="<?php echo htmlspecialchars(Text::plural('COM_OTTOMO_N_SUBSCRIBERS', $newsletter->stats->opens), ENT_QUOTES, 'UTF-8'); ?>"><?php echo Text::_('COM_OTTOMO_OPENS'); ?> <strong><?php echo rtrim(rtrim(number_format($newsletter->stats->openRate, 1), '0'), '.'); ?>%</strong></span>
								<span class="otm-badge" title="<?php echo htmlspecialchars(Text::plural('COM_OTTOMO_N_SUBSCRIBERS', $newsletter->stats->clicks), ENT_QUOTES, 'UTF-8'); ?>"><?php echo Text::_('COM_OTTOMO_CLICKS'); ?> <strong><?php echo rtrim(rtrim(number_format($newsletter->stats->clickRate, 1), '0'), '.'); ?>%</strong></span>
								<?php endif; ?>
							</span>
						</li>
						<?php endforeach; ?>
					</ul>
					<?php else: ?>
					<div class="otm-nocontent"><?php echo Text::_('COM_OTTOMO_NO_SENT_CAMPAIGNS'); ?></div>
					<?php endif; ?>
				</div>

				<div role="tabpanel" id="otm-panel-drafts" aria-labelledby="otm-tab-drafts" data-role="drafts" hidden>
					<?php if (count($this->drafts)): ?>
					<ul class="otm-campaigns">
						<?php foreach ($this->drafts as $newsletter): ?>
						<li>
							<a class="otm-campaigns__title" href="index.php?option=com_ottomo&amp;view=newsletters&amp;task=edit&amp;id=<?php echo (int)$newsletter->id; ?>"><?php echo htmlspecialchars($newsletter->subject, ENT_QUOTES, 'UTF-8'); ?></a>
							<span class="otm-campaigns__meta"><?php echo OttomoHelperHTML::pill(Text::_('COM_OTTOMO_STATUS_DRAFT'), 'neutral'); ?> <a href="index.php?option=com_ottomo&amp;view=newsletters&amp;task=edit&amp;id=<?php echo (int)$newsletter->id; ?>"><?php echo Text::_('COM_OTTOMO_CONTINUE_EDITING'); ?></a></span>
						</li>
						<?php endforeach; ?>
					</ul>
					<?php else: ?>
					<div class="otm-nocontent"><?php echo Text::_('COM_OTTOMO_NO_DRAFTS'); ?></div>
					<?php endif; ?>
				</div>

				<div class="otm-widgetbuttons">
					<a class="otm-btn otm-btn--generic" href="index.php?option=com_ottomo&amp;view=newsletters"><?php echo Text::_('COM_OTTOMO_MANAGE_NEWSLETTERS'); ?></a>
				</div>
			</div>
		</div>
	</div>

</div>