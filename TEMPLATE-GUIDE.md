# Building your own Ottomo email template

An Ottomo email template is a folder with a PHP layout, a manifest, sample content and a few images. Campaigns are designed on top of it in the Ottomo designer, and every email is rendered from it when it is sent or viewed in a browser.

The quickest start: copy the **Template Starter (for developers)** (`components/com_ottomo/templates/starter/`). It is plain on purpose, uses every placeholder and both kinds of positions, and explains all of this in a comment at the top of its `default.php`.

## Where templates live

Ottomo finds templates in two places. The first one found wins.

| Location | For | How it gets there |
|---|---|---|
| `templates/<your site template>/html/ottomo/<folder>/` | Your own templates, kept with your site template (and in its version control) | Create the folder. The **Templates** page picks it up the next time it is opened. |
| `components/com_ottomo/templates/<folder>/` | The built-in templates, uploaded packages and duplicates | Ottomo's installer, **Templates > Upload your own template** (a `.zip`), or **Duplicate** |

- `<your site template>` is the site's **default** template (System > Site Templates, the starred style).
- A folder in your site template with the **same name as a built-in template** overrides it: campaigns that use the built-in template are rendered from your copy.
- A template in your site template shows a **Site template** badge on the Templates page. It can be edited, downloaded and duplicated there, but not deleted: remove its folder from your site template instead. (A template used by a campaign is never deleted, wherever it is: campaigns are rendered from their template every time.)
- Managing templates (upload, edit, download, duplicate, delete) needs the **Manage email templates** permission (Settings > Permissions; by default Super Users only). Other backend users pick a template when they create a campaign.

Example: a template called "JoomlaWorks" in a site template named `hammond`:

```
templates/hammond/html/ottomo/joomlaworks/
├── manifest.xml
├── default.php
├── sampleData.ini
├── preview.webp
├── thumbnail.webp
├── extensions/
│   ├── articles.php
│   ├── button.php
│   └── k2.php
└── images/
    └── logo.png
```

The folder name is the template's id in the database: use letters, digits, `-`, `_` or `.`, and do not rename it once campaigns use it.

## Files

### `manifest.xml` (required)

```xml
<?xml version="1.0" encoding="utf-8"?>
<extension version="3.3" type="ottomotemplate" method="upgrade" folder="joomlaworks">
	<name>JoomlaWorks</name>
	<creationDate>November 23rd, 2026</creationDate>
	<author>JoomlaWorks</author>
	<authorEmail>please-use-the-contact-form@joomlaworks.net</authorEmail>
	<authorUrl>https://www.joomlaworks.net</authorUrl>
	<copyright>Copyright (c) 2026 JoomlaWorks Ltd. All rights reserved.</copyright>
	<license>GNU/GPL license: https://www.gnu.org/copyleft/gpl.html</license>
	<layout>2col</layout>
	<version>1.0.0</version>
	<description>One line about the design: shown on the Templates page.</description>
	<files>
		<folder>extensions</folder>
		<folder>images</folder>
		<filename>default.php</filename>
		<filename>manifest.xml</filename>
		<filename>sampleData.ini</filename>
		<filename>preview.webp</filename>
		<filename>thumbnail.webp</filename>
	</files>
</extension>
```

- `folder` must match the folder name.
- `<layout>` is the label on the template cards: `1col`, `2col`, `3col` or `sections`.
- `<files>` matters for packages: the installer copies **only** the files and folders listed here. A template in your site template does not need it, but keep it complete so the template can be downloaded and installed elsewhere.

### `default.php` (required)

The email itself: a full HTML document, included by Ottomo as plain PHP. For strings use `Text::_()` with `use Joomla\CMS\Language\Text;` after the `defined('_JEXEC')` line (it works on Joomla 3.x UTD, 4, 5 and 6; `JText` doesn't exist on Joomla 6). `<jdoc:include type="head" />` is where Ottomo puts the title and base URL.

```php
<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<jdoc:include type="head" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style type="text/css">
	/* resets and @media rules for phones; everything else inline */
</style>
</head>
<body>
	...
	<td>{{position:title:text}}</td>
	...
	<td>{{position:content:html}}</td>
	...
	<a href="{{unsubscribe}}"><?php echo Text::_('COM_OTTOMO_TMPL_UNSUBSCRIBE'); ?></a>
</body>
</html>
```

Keep `<jdoc:include type="head" />` in the `<head>`: the designer adds its editing styles through it (they never reach the email).

### `sampleData.ini` (recommended)

What the designer shows in each position until the campaign has its own content: one line per position, the key is the position name.

```ini
title="Your headline"
intro="Hi {{firstname|there}}, a short introduction."
content="<h2 style='margin: 0 0 8px;'>An html position</h2><p>Blocks of content go here.</p>"
logo="<img src='{{base}}images/logo.png' width='120' height='32' alt='Your brand' style='display: block; border: 0;' />"
```

- One value per line, in double quotes; HTML inside uses **single** quotes.
- `{{base}}` (this template's folder URL) and `{{site}}` are filled in when the sample is loaded.

### `extensions/articles.php` and `extensions/k2.php` (recommended)

How a Joomla article or a K2 item looks when it is added to an html position (the **+** button in the designer). The item is in `$row`. Without these files, inserted articles and K2 items render as nothing, so copy them from the starter and restyle them.

| `$row` field | Joomla articles | K2 items |
|---|---|---|
| `title`, `safeTitle` (HTML-escaped), `link` | ✓ | ✓ |
| `introtext`, `fulltext` | ✓ | ✓ |
| `date` | ✓ | ✓ |
| `category->name`, `category->link` | ✓ | ✓ |
| `author` | a string | an object: `name`, `link` |
| images | `introImage`, `introImageCaption`, `fullImage`, `fullImageCaption` | `image`, `image_caption`, `image_credits` |
| `tags` (each `name`, `link`), `extra_fields` (each `name`, `value`) | | ✓ |

Fields switched off in the plugin's settings are empty (`null`): test before you print them. Wrap the block in `<div contenteditable="true">…</div>` so it can be edited in the designer.

Images inserted with the **Images** plugin and custom HTML need no renderer.

### `extensions/button.php` (recommended)

A button (call to action) added with the **+** menu's **Button**, in your template's style. `$row->text` and `$row->link` are already HTML-escaped. Keep this shape, which the designer's button bar edits (alignment, full width, background and text colours, corners) and which Ottomo makes clickable in classic Outlook when sending:

```html
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td align="left">
  <table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
    <td style="border-radius: 8px; background: #18181b;">
      <a href="<?php echo $row->link; ?>" style="display: inline-block; padding: 14px 28px; color: #ffffff; text-decoration: none; border-radius: 8px;"><?php echo $row->text; ?></a>
    </td>
  </tr></table>
</td></tr></table>
```

The outer cell's `align` places the button; the inner cell carries the background (a hex colour) and the corners; the link carries the padding and the text colour. Without this file, Ottomo inserts a plain dark button. The designer wraps the button in an editable block itself, so don't add `contenteditable`. Buttons already in your sample content use the same bar when they have this inner shape (a link alone in a cell with a background).

### `images/`

Reference them as `{{base}}images/<file>` so the URLs are absolute in the email.

### `preview.webp` and `thumbnail.webp` (recommended)

- `preview`: a full-height screenshot of the template with its sample content, about 650px wide (the Preview button of the Templates page and the campaign wizard).
- `thumbnail`: 500 × 355 (the cards).
- **WebP is preferred**; `.avif`, `.jpg`, `.jpeg` and `.png` work too (first found, in that order). List the file you ship in `manifest.xml`.
- Without a preview image, Preview shows the template itself with its sample content; without a thumbnail, the card shows a placeholder.

## Positions

Positions are the editable areas of the designer.

| Placeholder | In the designer |
|---|---|
| `{{position:NAME:text}}` | One editable text area: headlines, introductions, a signature |
| `{{position:NAME:html}}` | A stack of blocks: articles, K2 items, images or HTML, added with **+** and dragged around (within the position and to other positions) |

- `NAME`: letters, digits and underscores, unique within the template.
- Put a position in a `<td>` (or a `<div>`) with the styles its content should inherit (font, size, colour, padding).
- A campaign stores its content per position name: two templates with the same position names can swap without losing content.

## Placeholders

Ottomo's placeholders use double braces, so they never clash with the `{…}` tags of Joomla plugins (`{loadposition …}`, `{emailcloak=off}`) in inserted articles. Replaced in the layout when an email is shown or sent:

| Placeholder | Value |
|---|---|
| `{{base}}` | URL of this template's folder (trailing slash) |
| `{{site}}` | URL of the site's home page |
| `{{sitename}}` | The site's name (Settings > Compliance > Site name in emails, else Global Configuration) |
| `{{why}}` | Why the reader gets the email (Settings > Compliance, or the stock line) |
| `{{subscribe}}` | The signup page (Settings > Signup forms; default: the home page) |
| `{{date}}` | The date of the email (`YYYY-MM-DD HH:MM:SS`) |
| `{{subject}}` | The campaign's subject |
| `{{campaign_id}}` | The campaign's ID |
| `{{view_in_browser}}` | The campaign in a browser (*View in browser*) |
| `{{topic_ids}}` | The campaign's topic IDs (`1\|2`), for links of your own |
| `{{topics}}` | The campaign's topic names (`Daily Briefing, Weekly Digest`) |
| `{{preferences}}` | The subscriber's preferences page |
| `{{unsubscribe}}` | Unsubscribe from the campaign's topics |
| `{{subscriber_id}}`, `{{subscriber_hash}}` | The subscriber's ID and security hash (used by the links above) |

**Personalisation**: `{{FIELD}}` or `{{FIELD|fallback}}`, with `FIELD` one of `name`, `firstname`, `lastname`, `email`, `company`, `position`, `city`, `state`, `country`, `zip`, `address`, `phone`, `mobile`. (`{{position}}` is the job title; `{{position:NAME:TYPE}}` is a template position.) Example: `Hi {{firstname|there}}` gives "Hi Maria", or "Hi there" when the first name is unknown. Values are HTML-escaped.

Use the placeholders in the **layout**. Inside position content (what editors type in the designer) only personalisation and `{{campaign_id}}` are replaced.

Placeholders inside HTML comments are replaced too, so document them in PHP comments (`<?php /* … */ ?>`), which never reach the email.

## What every template must keep

Readers, mailbox providers and the law expect these; the built-in templates have them in their footer:

- a **View in browser** link: `{{view_in_browser}}`;
- **why** the reader gets the email: `{{why}}` (Settings > Compliance > Why this email, or the stock line “You are receiving this email because you subscribed to … at …”);
- **Update your preferences** (`{{preferences}}`) and **Unsubscribe** (`{{unsubscribe}}`);
- for forwarded emails, a **Subscribe** link: `{{subscribe}}`.

The strings are in the frontend language file (`COM_OTTOMO_TMPL_VIEW_IN_BROWSER`, `COM_OTTOMO_TMPL_WHY` (behind `{{why}}`), `COM_OTTOMO_TMPL_UPDATE_YOUR_PREFERENCES`, `COM_OTTOMO_TMPL_UNSUBSCRIBE`, `COM_OTTOMO_TMPL_FORWARDED`, `COM_OTTOMO_TMPL_SUBSCRIBE`, `COM_OTTOMO_TMPL_READ_MORE`, `COM_OTTOMO_TMPL_SENT_TO`). Ottomo also adds your postal address and an unsubscribe link when the template has none (Settings > Compliance).

## Writing HTML for email

Email clients are not browsers. What works everywhere:

- **Tables for layout** (`role="presentation"`, `cellpadding="0" cellspacing="0" border="0"`), a container about **600px** wide.
- **Inline styles** on every element. Keep the `<style>` block for resets and `@media` rules (phones: full-width container, stacked columns).
- Web-safe fonts with fallbacks (`Helvetica, Arial, sans-serif`, `Georgia, 'Times New Roman', serif`).
- Images with `width`, `height`, `alt` and `display: block`; absolute URLs (`{{base}}…`).
- **Buttons** as a table cell with a background colour around a padded `inline-block` link, not `<button>`. Ottomo makes them work in classic Outlook for you (see below).
- No JavaScript, forms, video, `position`, flexbox or grid.

### Dark mode

Apple Mail, iOS Mail, Outlook for Mac, Thunderbird and others show emails dark when the reader's device is dark, if the email says it supports it. The built-in templates do; copy their approach:

- `<meta name="color-scheme" content="light dark">` and `<meta name="supported-color-schemes" content="light dark">` (or `light` only, if you don't design a dark version: those clients then keep it light).
- A **separate** `<style>` block with `:root { color-scheme: light dark; }` and a `@media (prefers-color-scheme: dark)` rule (separate, so a client that rejects it keeps your other styles), with these classes in the layout:
  - `dm-page` on the outer table (the page background), `dm-card` on the content container;
  - `dm-muted` on secondary text cells (view in browser, footer, eyebrows), `dm-strong` on headlines;
  - `dm-invert` on dark, monochrome images on a transparent background (logos, icons), which are inverted.
- Give `.dm-page` descendants a light text colour, light inline backgrounds and borders a dark one (`[style*="background"]`, `[style*="border"]`): content added in the designer keeps its inline colours, and stays readable this way. Leave `.otm-button` cells and `.otm-button__link` links alone (they keep their colours), or change your template's own button colour only, e.g. `.otm-button[style*="#18181b"]`.
- **Preview** has a Light / Dark switch; the designer always shows the light version.

Gmail's apps and Outlook.com apply their own dark mode (they recolour the email), whatever the template says.

### Classic Outlook for Windows

Outlook 2016–2021 and Microsoft 365's classic Outlook render email with Word. The built-in templates include, and yours should too:

- `xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office"` on `<html>`, and in `<head>` an `<!--[if mso]>` block with `<o:OfficeDocumentSettings>` (`<o:AllowPNG/>`, `<o:PixelsPerInch>96</o:PixelsPerInch>`: no scaling on high-DPI screens) and a style block that sets fonts Windows has (`'Segoe UI', Arial`, and Georgia on `.serif` cells), since Outlook uses only the first font of a list (`-apple-system` → Times New Roman), plus `mso-line-height-rule: exactly`.
- Widths as `width` attributes on tables, cells and images (Outlook ignores `max-width`).

Ottomo adds the rest when it shows or sends an email: inline background colours are copied to `bgcolor` attributes, and buttons get their link's padding as `mso-padding-alt` on the cell (Outlook ignores padding on links), plus the `otm-button` / `otm-button__link` classes. Rounded corners stay square in Outlook.

## Check it

1. Open **Email Campaigns > New**, pick your template in step 2, and look at it in the designer (step 3): every position should show its sample content; try **+** in an html position with an article and a K2 item.
2. Use **Preview** (Desktop and Mobile, Light and Dark) and **Open in new tab**.
3. **Send test** (step 4) to a few real mailboxes (Gmail, Outlook, Apple Mail), on a phone too.

## Share it

On the Templates page, **Download** packs any template into a `.zip` that **Upload your own template** installs on another site (into `components/com_ottomo/templates/`). For a package, check that `manifest.xml` lists every file.
