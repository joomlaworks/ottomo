<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ; ?>
<?php
/*
 * TEMPLATE STARTER (for developers)
 * ---------------------------------
 * Copy this folder, rename it (and the folder="" attribute and <name> of manifest.xml), then style it.
 * Keep the email-safe basics: tables for layout, inline styles, a 600px wide container, images with width/height.
 *
 * Files
 *   default.php           this layout (rendered by Joomla's document renderer: JText and PHP work here)
 *   manifest.xml          name, description, <layout> (1col, 2col or sections) and the list of files to install
 *   sampleData.ini        content shown in the designer for empty positions (key = position name, single line,
 *                         double-quoted, HTML with single quotes; {{base}} is this template's URL)
 *   extensions/<x>.php    how a content plugin's item is rendered here (articles.php: Joomla articles, k2.php: K2
 *                         items; $row holds the item). Images and custom HTML need no renderer.
 *   images/               your images, referenced as {{base}}images/...
 *   preview.webp          a full-height screenshot (about 650px wide), thumbnail.webp (500 x 355); WebP is
 *                         preferred, .avif, .jpg, .jpeg and .png work too (list them in manifest.xml)
 *
 * Positions (the editable areas of the designer)
 *   {{position:NAME:text}}   a single editable text area (headlines, intros)
 *   {{position:NAME:html}}   a stack of blocks: the user adds articles, K2 items, images or HTML, and drags
 *                            them around (within the position and between positions)
 *   Position names: letters, digits and underscores, unique in the template.
 *
 * Placeholders (replaced when the email is shown or sent; use them in the layout, not inside positions; only the
 * personalisation placeholders below also work inside positions)
 *   {{base}}                                URL of this template's folder (with a trailing slash)
 *   {{site}}                                URL of the site's home page
 *   {{sitename}}                            the site's name (Settings > Compliance, else Global Configuration)
 *   {{why}}                                 why the reader gets the email (Settings > Compliance, or the stock line)
 *   {{subscribe}}                           the signup page (Settings > Signup forms; default: the home page)
 *   {{date}}                                the date of the email (YYYY-MM-DD HH:MM:SS)
 *   {{subject}}                             the campaign's subject
 *   {{campaign_id}}                         the campaign's ID
 *   {{view_in_browser}}                     the campaign in a browser ("View in browser")
 *   {{topic_ids}}                           the campaign's topic IDs (1|2), for links of your own
 *   {{topics}}                              the campaign's topic names (Daily Briefing, Weekly Digest)
 *   {{preferences}}                         the subscriber's preferences page
 *   {{unsubscribe}}                         unsubscribe from the campaign's topics
 *   {{subscriber_id}}, {{subscriber_hash}}  the subscriber's ID and security hash (used by the links above)
 *
 * Personalisation (also in sampleData.ini, positions, the subject and the plain text version)
 *   {{FIELD}} or {{FIELD|fallback}}
 *   FIELD: name, firstname, lastname, email, company, position (the job title), city, state, country, zip,
 *          address, phone, mobile
 *   e.g. Hi {{firstname|there}}  ->  Hi Maria  (or "Hi there" when the first name is unknown)
 *
 * Dark mode and classic Outlook (see TEMPLATE-GUIDE.md)
 *   The second <style> block below is the dark version: dm-page (outer table), dm-card (container), dm-muted
 *   (secondary text), dm-strong (headlines), dm-invert (dark monochrome images); change its colours to yours.
 *   The <!--[if mso]> block is for classic Outlook for Windows (fonts, high-DPI); keep it. Ottomo adds bgcolor
 *   attributes and Outlook button padding (mso-padding-alt, class otm-button) when it renders the email.
 *
 * Every email should keep: the "View in browser" link, why the recipient gets the email, the preferences and
 * unsubscribe links, and the signup link for forwarded emails (all in the footer below).
 * Note: these comments are PHP, so they never reach the email (placeholders in HTML comments would be replaced).
 */
?>
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
	/* Resets for email clients; everything else is inline. */
	body { margin: 0; padding: 0; width: 100% !important; background: #ffffff; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
	table { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
	img { border: 0; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; max-width: 100%; height: auto; }
	p { margin: 0 0 16px; }
	a { color: #000000; }
	/* Phones: full-width container, stacked columns */
	@media only screen and (max-width: 620px) {
		.container { width: 100% !important; }
		.stack { display: block !important; width: 100% !important; padding: 0 0 16px !important; }
	}
</style>
<style type="text/css">
	/* Dark mode (Apple Mail, iOS Mail, Outlook for Mac, Thunderbird…): surfaces, then any text, background or
	   border inside, so content with inline colours stays readable; buttons keep their colours */
	:root { color-scheme: light dark; supported-color-schemes: light dark; }
	@media (prefers-color-scheme: dark) {
		body, .dm-page { background: #09090b !important; }
		.dm-card { background: #18181b !important; }
		.dm-page [style*="border"] { border-color: #3f3f46 !important; }
		.dm-page [style*="background"]:not(.otm-button):not([class*="dm-"]) { background-color: #27272a !important; }
		.dm-page td, .dm-page th, .dm-page p, .dm-page li, .dm-page div, .dm-page span, .dm-page strong, .dm-page em, .dm-page b, .dm-page i { color: #d4d4d8 !important; }
		.dm-page h1, .dm-page h2, .dm-page h3, .dm-page h4, .dm-page .dm-strong, .dm-page .dm-strong * { color: #fafafa !important; }
		.dm-page a:not(.otm-button__link) { color: #fafafa !important; }
		.dm-page .dm-muted, .dm-page .dm-muted *, .dm-page .dm-muted a { color: #a1a1aa !important; }
		.dm-page .otm-button__link * { color: inherit !important; }
		.dm-page .otm-button[style*="#000000"] { background: #fafafa !important; }
		.dm-page .otm-button[style*="#000000"] .otm-button__link { color: #18181b !important; }
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
<body style="margin: 0; padding: 0; background: #ffffff;">
<table class="dm-page" role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td align="center" style="padding: 16px;">
			<table role="presentation" class="container" width="600" cellpadding="0" cellspacing="0" border="0" style="width: 600px; font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px; color: #000000;">

				<!-- Top: view in browser -->
				<tr>
					<td class="dm-muted" align="right" style="padding: 0 0 16px; font-size: 12px; line-height: 18px; color: #666666;">
						<a href="{{view_in_browser}}" style="color: #666666;"><?php echo JText::_('COM_OTTOMO_TMPL_VIEW_IN_BROWSER'); ?></a>
					</td>
				</tr>

				<!-- Header: a text position -->
				<tr>
					<td style="padding: 16px 0; border-bottom: 1px solid #dddddd; font-size: 20px; line-height: 28px; font-weight: bold;">{{position:header:text}}</td>
				</tr>

				<!-- Title and introduction: text positions -->
				<tr>
					<td style="padding: 24px 0 8px; font-size: 28px; line-height: 34px; font-weight: bold;">{{position:title:text}}</td>
				</tr>
				<tr>
					<td style="padding: 0 0 24px;">{{position:intro:text}}</td>
				</tr>

				<!-- Main content: an html position (articles, K2 items, images, HTML) -->
				<tr>
					<td style="padding: 0 0 8px;">{{position:content:html}}</td>
				</tr>

				<!-- Two columns: two html positions, stacked on phones -->
				<tr>
					<td style="padding: 0 0 24px;">
						<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
							<tr>
								<td class="stack" width="50%" valign="top" style="width: 50%; padding-right: 12px;">{{position:left:html}}</td>
								<td class="stack" width="50%" valign="top" style="width: 50%; padding-left: 12px;">{{position:right:html}}</td>
							</tr>
						</table>
					</td>
				</tr>

				<!-- Call to action: an html position -->
				<tr>
					<td style="padding: 0 0 32px;">{{position:cta:html}}</td>
				</tr>

				<!-- Footer: why, preferences, unsubscribe, view in browser, subscribe -->
				<tr>
					<td class="dm-muted" style="padding: 16px 0; border-top: 1px solid #dddddd; font-size: 12px; line-height: 18px; color: #666666;">
						<p style="margin: 0 0 8px;">{{position:footer:text}}</p>
						<p style="margin: 0 0 8px;">{{why}} <?php echo JText::sprintf('COM_OTTOMO_TMPL_SENT_TO', '{{email}}'); ?></p>
						<p style="margin: 0 0 8px;">
							<a href="{{preferences}}" style="color: #666666;"><?php echo JText::_('COM_OTTOMO_TMPL_UPDATE_YOUR_PREFERENCES'); ?></a> |
							<a href="{{unsubscribe}}" style="color: #666666;"><?php echo JText::_('COM_OTTOMO_TMPL_UNSUBSCRIBE'); ?></a> |
							<a href="{{view_in_browser}}" style="color: #666666;"><?php echo JText::_('COM_OTTOMO_TMPL_VIEW_IN_BROWSER'); ?></a>
						</p>
						<p style="margin: 0;"><?php echo JText::_('COM_OTTOMO_TMPL_FORWARDED'); ?> <a href="{{subscribe}}" style="color: #000000;"><?php echo JText::_('COM_OTTOMO_TMPL_SUBSCRIBE'); ?></a></p>
					</td>
				</tr>

			</table>
		</td>
	</tr>
</table>
</body>
</html>
