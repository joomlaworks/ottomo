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
	body { margin: 0; padding: 0; width: 100% !important; background: #efedea; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
	table { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
	img { border: 0; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; max-width: 100%; height: auto; }
	p { margin: 0 0 16px; }
	a { color: #1c1917; }
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
		body, .dm-page { background: #0c0a09 !important; }
		.dm-card { background: #1c1917 !important; }
		.dm-page [style*="border"] { border-color: #44403c !important; }
		.dm-page [style*="background"]:not(.otm-button):not([class*="dm-"]) { background-color: #292524 !important; }
		.dm-page td, .dm-page th, .dm-page p, .dm-page li, .dm-page div, .dm-page span, .dm-page strong, .dm-page em, .dm-page b, .dm-page i { color: #d6d3d1 !important; }
		.dm-page h1, .dm-page h2, .dm-page h3, .dm-page h4, .dm-page .dm-strong, .dm-page .dm-strong * { color: #fafaf9 !important; }
		.dm-page a:not(.otm-button__link) { color: #fafaf9 !important; }
		.dm-page .dm-muted, .dm-page .dm-muted *, .dm-page .dm-muted a { color: #a8a29e !important; }
		.dm-page .otm-button__link * { color: inherit !important; }
		.dm-page .otm-button[style*="#1c1917"] { background: #fafaf9 !important; }
		.dm-page .otm-button[style*="#1c1917"] .otm-button__link { color: #1c1917 !important; }
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
<body style="margin: 0; padding: 0; background: #efedea;">
<table class="dm-page" role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background: #efedea;">
	<tr>
		<td align="center" style="padding: 20px 12px 16px;">
			<table role="presentation" class="container" width="600" cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td class="dm-muted" align="right" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif; font-size: 12px; line-height: 18px; color: #78716c;">
						<a href="{{view_in_browser}}" style="color: #78716c; text-decoration: underline;"><?php echo JText::_('COM_OTTOMO_TMPL_VIEW_IN_BROWSER'); ?></a>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td align="center" style="padding: 0 12px;">
			<table role="presentation" class="container dm-card" width="600" cellpadding="0" cellspacing="0" border="0" style="width: 600px; background: #ffffff; border-radius: 0;">
				<tr>
					<td class="px" style="padding: 28px 40px;">
						<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
							<tr>
								<td class="nav-logo" valign="middle">{{position:logo:html}}</td>
								<td class="nav" valign="middle" align="right" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif; font-size: 13px; line-height: 18px; color: #44403c;">{{position:nav:html}}</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td class="full" style="padding: 0;">{{position:hero:html}}</td>
				</tr>
				<tr>
					<td class="px dm-muted" style="padding: 36px 40px 10px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif; font-size: 12px; line-height: 18px; letter-spacing: 0.14em; text-transform: uppercase; color: #78716c;">{{position:eyebrow:text}}</td>
				</tr>
				<tr>
					<td class="px h1 dm-strong serif" style="padding: 0 40px 14px; font-family: Georgia, 'Times New Roman', Times, serif; font-size: 40px; line-height: 46px; letter-spacing: -0.01em; color: #1c1917;">{{position:title:text}}</td>
				</tr>
				<tr>
					<td class="px" style="padding: 0 40px 24px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif; font-size: 17px; line-height: 28px; color: #44403c;">{{position:intro:text}}</td>
				</tr>
				<tr>
					<td class="px" style="padding: 0 40px 44px;">{{position:cta:html}}</td>
				</tr>
				<tr>
					<td class="px" style="padding: 0 40px 44px;">
						<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
							<tr>
								<td class="stack" width="216" valign="top" style="width: 216px;">{{position:feature_image:html}}</td>
								<td class="stack-gap" width="28" style="width: 28px; font-size: 0; line-height: 0;">&nbsp;</td>
								<td class="stack" width="276" valign="middle" style="width: 276px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif; font-size: 16px; line-height: 26px; color: #44403c;">{{position:feature:html}}</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td class="px quote serif" align="center" style="padding: 48px 56px; background: #f5f5f4; font-family: Georgia, 'Times New Roman', Times, serif; font-size: 28px; line-height: 40px; text-align: center; color: #1c1917;">{{position:quote:html}}</td>
				</tr>
				<tr>
					<td class="px dm-muted" style="padding: 44px 40px 8px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif; font-size: 12px; line-height: 18px; letter-spacing: 0.14em; text-transform: uppercase; color: #78716c;">{{position:cards_title:text}}</td>
				</tr>
				<tr>
					<td class="px" style="padding: 8px 40px 44px;">
						<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
							<tr>
								<td class="stack" width="160" valign="top" style="width: 160px;">{{position:card1:html}}</td>
								<td class="stack-gap" width="20" style="width: 20px; font-size: 0; line-height: 0;">&nbsp;</td>
								<td class="stack" width="160" valign="top" style="width: 160px;">{{position:card2:html}}</td>
								<td class="stack-gap" width="20" style="width: 20px; font-size: 0; line-height: 0;">&nbsp;</td>
								<td class="stack" width="160" valign="top" style="width: 160px;">{{position:card3:html}}</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td class="px" align="center" style="padding: 44px 40px; background: #1c1917; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif; font-size: 16px; line-height: 26px; color: #e7e5e4;">{{position:closing:html}}</td>
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
					<td align="center" class="px dm-muted" style="padding: 0 40px 36px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif; font-size: 12px; line-height: 19px; color: #78716c;">
						<p style="margin: 0 0 10px;">{{why}}</p>
						<p style="margin: 0 0 10px;">
							<a href="{{preferences}}" style="color: #78716c; text-decoration: underline;"><?php echo JText::_('COM_OTTOMO_TMPL_UPDATE_YOUR_PREFERENCES'); ?></a>
							&nbsp;&middot;&nbsp;
							<a href="{{unsubscribe}}" style="color: #78716c; text-decoration: underline;"><?php echo JText::_('COM_OTTOMO_TMPL_UNSUBSCRIBE'); ?></a>
							&nbsp;&middot;&nbsp;
							<a href="{{view_in_browser}}" style="color: #78716c; text-decoration: underline;"><?php echo JText::_('COM_OTTOMO_TMPL_VIEW_IN_BROWSER'); ?></a>
						</p>
						<p style="margin: 0;"><?php echo JText::_('COM_OTTOMO_TMPL_FORWARDED'); ?> <a href="{{subscribe}}" style="color: #1c1917; font-weight: 600; text-decoration: underline;"><?php echo JText::_('COM_OTTOMO_TMPL_SUBSCRIBE'); ?></a></p>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</body>
</html>
