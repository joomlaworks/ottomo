<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ; ?>
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<jdoc:include type="head" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="x-apple-disable-message-reformatting" />
<meta name="color-scheme" content="light dark" />
<meta name="supported-color-schemes" content="light dark" />
<style type="text/css">
	body { margin: 0; padding: 0; width: 100% !important; background: #f3eee6; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
	table { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
	img { border: 0; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; max-width: 100%; height: auto; }
	p { margin: 0 0 16px; }
	a { color: #3b342c; }
	h1, h2, h3, h4 { margin: 0 0 12px; }
	ul, ol { margin: 0 0 16px; padding: 0 0 0 20px; }
	@media only screen and (max-width: 620px) {
		.container { width: 100% !important; border-radius: 0 !important; }
		.px { padding-left: 24px !important; padding-right: 24px !important; }
		.stack { display: block !important; width: 100% !important; padding-left: 0 !important; padding-right: 0 !important; }
		.stack-gap { display: block !important; width: 100% !important; height: 24px !important; }
		.h1 { font-size: 30px !important; line-height: 36px !important; }
		.quote { font-size: 22px !important; line-height: 32px !important; }
		.full img { width: 100% !important; }
		.stack img { width: 100% !important; max-width: 100% !important; }
		.nav-logo { display: block !important; width: 100% !important; }
		.nav { display: block !important; width: 100% !important; padding-top: 12px !important; text-align: left !important; }
	}
</style>
<style type="text/css">
	/* Dark mode (Apple Mail, iOS Mail, Outlook for Mac, Thunderbird…): surfaces, then any text, background or
	   border inside, so content with inline colours stays readable; buttons keep their colours */
	:root { color-scheme: light dark; supported-color-schemes: light dark; }
	@media (prefers-color-scheme: dark) {
		body, .dm-page { background: #1a1714 !important; }
		.dm-card { background: #24201c !important; }
		.dm-page [style*="border"] { border-color: #403830 !important; }
		.dm-page [style*="background"]:not(.otm-button):not([class*="dm-"]) { background-color: #2f2a24 !important; }
		.dm-page td, .dm-page th, .dm-page p, .dm-page li, .dm-page div, .dm-page span, .dm-page strong, .dm-page em, .dm-page b, .dm-page i { color: #e0d8ce !important; }
		.dm-page h1, .dm-page h2, .dm-page h3, .dm-page h4, .dm-page .dm-strong, .dm-page .dm-strong * { color: #f7f1ea !important; }
		.dm-page a:not(.otm-button__link) { color: #f0b48f !important; }
		.dm-page .dm-muted, .dm-page .dm-muted *, .dm-page .dm-muted a { color: #a39889 !important; }
		.dm-page .otm-button__link * { color: inherit !important; }
		.dm-page .dm-invert, .dm-page img[src*="images/logo.png"], .dm-page img[src*="images/social-"] { filter: invert(1) hue-rotate(180deg); }
	}
</style>
<!--[if mso]>
<noscript><xml><o:OfficeDocumentSettings><o:AllowPNG/><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript>
<style type="text/css">
	/* Classic Outlook for Windows: it reads only the first font of a list (and falls back to Times New Roman) */
	body, table, td, th, div, p, a, span, li, h1, h2, h3, h4, strong, em, b, i { font-family: 'Segoe UI', Arial, sans-serif !important; mso-line-height-rule: exactly; }
	.serif, .serif p, .serif a, .serif span, .serif strong, .serif em { font-family: Georgia, 'Times New Roman', serif !important; }
</style>
<![endif]-->
</head>
<body style="margin: 0; padding: 0; background: #f3eee6;">
<table class="dm-page" role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background: #f3eee6;">
	<tr>
		<td align="center" style="padding: 20px 12px 16px;">
			<table role="presentation" class="container" width="600" cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td class="dm-muted" align="right" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif; font-size: 12px; line-height: 18px; color: #8a7f72;">
						<a href="{{view_in_browser}}" style="color: #8a7f72; text-decoration: underline;"><?php echo JText::_('COM_OTTOMO_TMPL_VIEW_IN_BROWSER'); ?></a>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td align="center" style="padding: 0 12px;">
			<table role="presentation" class="container dm-card" width="600" cellpadding="0" cellspacing="0" border="0" style="width: 600px; background: #fffdf9; border-radius: 18px; border: 1px solid #e8e0d4;">
				<tr>
					<td class="px" align="center" style="padding: 36px 40px 28px;">{{position:logo:html}}</td>
				</tr>
				<tr>
					<td class="px full" style="padding: 0 24px;">{{position:hero:html}}</td>
				</tr>
				<tr>
					<td class="px dm-muted" style="padding: 36px 40px 6px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif; font-size: 15px; line-height: 22px; color: #8a7f72;">{{position:greeting:text}}</td>
				</tr>
				<tr>
					<td class="px h1 dm-strong serif" style="padding: 0 40px 12px; font-family: Georgia, 'Times New Roman', Times, serif; font-size: 36px; line-height: 42px; color: #3b342c;">{{position:title:text}}</td>
				</tr>
				<tr>
					<td class="px" style="padding: 0 40px 24px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif; font-size: 17px; line-height: 28px; color: #5b5248;">{{position:intro:text}}</td>
				</tr>
				<tr>
					<td class="px" style="padding: 0 40px 40px;">{{position:cta:html}}</td>
				</tr>
				<tr>
					<td class="px" style="padding: 0 40px 40px;">
						<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
							<tr>
								<td class="stack" width="252" valign="top" style="width: 252px;">{{position:card1:html}}</td>
								<td class="stack-gap" width="16" style="width: 16px; font-size: 0; line-height: 0;">&nbsp;</td>
								<td class="stack" width="252" valign="top" style="width: 252px;">{{position:card2:html}}</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td class="px" style="padding: 0 40px 40px;">{{position:quote:html}}</td>
				</tr>
				<tr>
					<td class="px full" style="padding: 0 24px 24px;">{{position:event:html}}</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td align="center" style="padding: 0 12px 32px;">
			<table role="presentation" class="container" width="600" cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td align="center" class="px" style="padding: 32px 40px 12px;">{{position:social:html}}</td>
				</tr>
				<tr>
					<td align="center" class="px dm-muted" style="padding: 0 40px 36px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif; font-size: 12px; line-height: 19px; color: #8a7f72;">
						<p style="margin: 0 0 10px;">{{why}}</p>
						<p style="margin: 0 0 10px;">
							<a href="{{preferences}}" style="color: #8a7f72; text-decoration: underline;"><?php echo JText::_('COM_OTTOMO_TMPL_UPDATE_YOUR_PREFERENCES'); ?></a>
							&nbsp;&middot;&nbsp;
							<a href="{{unsubscribe}}" style="color: #8a7f72; text-decoration: underline;"><?php echo JText::_('COM_OTTOMO_TMPL_UNSUBSCRIBE'); ?></a>
							&nbsp;&middot;&nbsp;
							<a href="{{view_in_browser}}" style="color: #8a7f72; text-decoration: underline;"><?php echo JText::_('COM_OTTOMO_TMPL_VIEW_IN_BROWSER'); ?></a>
						</p>
						<p style="margin: 0;"><?php echo JText::_('COM_OTTOMO_TMPL_FORWARDED'); ?> <a href="{{subscribe}}" style="color: #3b342c; font-weight: 600; text-decoration: underline;"><?php echo JText::_('COM_OTTOMO_TMPL_SUBSCRIBE'); ?></a></p>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</body>
</html>
