<p align="center"><img src="administrator/components/com_ottomo/static/images/logo/ottomo-logo-light.svg" alt="Ottomo" width="320"></p>

# Ottomo for Joomla

**Ottomo** is a free, open source email newsletter and campaign manager for **Joomla 3.x UTD, 4, 5 and 6** (one package for all), by [JoomlaWorks](https://www.joomlaworks.net), the makers of K2.

Design a campaign visually, pick who gets it by topic, send it through your own server or a mail service, and see who opened and clicked: all inside your Joomla administrator, with your subscribers' data on your own site.

- **For marketers**: a four-step campaign wizard, a drag-and-drop designer with six responsive templates, personalisation, a pre-flight checklist, open and click rates per campaign, and signup forms that look like your site.
- **For site owners**: your audience stays in your database. Import the users you already have in Joomla, AcyMailing, Kunena, Meela, VirtueMart or HikaShop in a few clicks, and send through PHP mail, SMTP or one of six mail services.
- **For developers**: plain PHP and vanilla JavaScript, no build step, no jQuery; a documented template format, plugin events for content and mail services, and import adapters you can extend. GNU/GPL.

---

<p align="center"><img src="docs/screenshots/v1.1/02 - Ottomo Dashboard.png" alt="Ottomo Dashboard" width="100%" height="auto" /></p>

## Highlights

- **Visual designer**: edit text in place, add Joomla articles, K2 items, images and HTML blocks, drag them between positions, check desktop and mobile previews.
- **Six email templates** (Minimal 1 and 2 columns, Announcement, Studio, Linen and a documented Starter), and your own in your site template's folder.
- **Sends at scale, safely**: batches of any size, resumable after an interruption, never twice to the same subscriber.
- **Deliverability and compliance built in**: double opt-in, one-click unsubscribe (Gmail/Yahoo bulk sender rules), unsubscribed addresses kept as suppressed, a compliance footer with your postal address.
- **Ready for Gmail, Yahoo and Outlook**: SPF, DKIM and DMARC checked before you send, emails kept under Gmail's clipping size, footers Gmail won't fold away, and a Feedback-ID for Gmail Postmaster Tools.
- **Dark mode and classic Outlook**: every template has a dark version for Apple Mail, iOS Mail and other dark-mode clients, and renders properly in Outlook for Windows.
- **Tracking without third parties**: opens, unique clicks per link and campaign rates, stored on your site.
- **Six mail services** out of the box: Amazon SES, Brevo, Mailgun, Mailjet, SendGrid and SendPulse, plus PHP mail, Sendmail and SMTP.
- **One-click imports** from Joomla, AcyMailing, Kunena, Meela, VirtueMart and HikaShop, and CSV.
- **Command line** for cron jobs and large lists: send or resume a campaign, import subscribers, purge unsubscribed addresses, check the sending settings.
- **51 languages**, light and dark schemes, tablet and phone layouts.
- **Joomla 3.x UTD, 4, 5 and 6** from one package, the same look in every administrator template (Prothero and Atum).

---

<p align="center"><img src="docs/screenshots/v1.1/03 - Ottomo Email Campaigns.png" alt="Ottomo Email Campaigns" width="100%" height="auto" /></p>

## Features

### Campaigns

- **Four-step wizard**: details and recipients, design, content, send. Completed steps get a tick, and every step can be revisited.
- **Recipients by topic**: tick one or more topics; the wizard shows how many active subscribers the campaign will reach, counting each person once.
- **Sender**: a campaign can have its own sender name and address, or use the default sender of Settings (shown as a hint, and followed when it changes).
- **Preview text** (the line inboxes show after the subject), with a character counter.
- **Personalisation**: `{{firstname|there}}`, name, last name, email, company, city, country and other profile fields, in the subject, the content and the plain-text version, with a fallback for missing values. One click adds the first name to the subject; a menu in the designer adds any field.
- **Plain-text version** alongside the HTML email.
- **Preview** on desktop and mobile widths, in light and dark mode, and in a new tab exactly as readers see it in their browser.
- **Test send** to one or more addresses before the real thing (comma separated; every address is checked first).
- **Pre-flight checklist**: subject, sender, recipients, saved changes and an unsubscribe link must pass before sending (also checked on the server); preview text, a test email, a size Gmail won't clip and an authenticated sender domain are recommended.
- **Sending in batches** from the browser, with a progress bar. Each batch goes to the subscribers who haven't received the campaign yet, so subscribers joining or leaving meanwhile are never skipped nor sent to twice, and people who sign up later don't receive an old campaign.
- **Resume sending**: if the connection drops or the tab is closed, the campaign shows how many subscribers are still waiting and resumes with them only. A single address a mail service rejects is counted as failed and the send goes on; a batch the mail server or service refuses entirely stops the send with its error message.
- **Results**: recipients, unique open and click rates and the most clicked links, on the campaign itself, in the campaigns list and on the dashboard.
- **Duplicate** any campaign as a new draft, e.g. to resend an improved version.
- **Public archive**: a menu item listing your sent campaigns, each viewable in the browser.

### Designer

- **In-place editing**: bold, italic, underline, headings, lists, alignment (left, center, right), links (optionally in a new window), clear formatting and plain-text paste, with an email-safe font menu.
- **Content blocks**: Joomla articles, K2 items, images from the Media Manager and custom HTML, added with a "+" menu in any position; each template decides how articles and K2 items look.
- **Image settings**: click an image to set its width (the height follows), fit it to its column, align it, and give it alt text and a link. Images inserted from the Media Manager or with content fit their column on their own, with a `width` for classic Outlook and `max-width` for every other client, so a large photo never widens the email.
- **Drag and drop** blocks within and between positions, or move them with the keyboard.
- **HTML tab** with a code editor (Ace) for full control, kept in sync with the visual editor, and a **Plain text** tab.
- **Desktop / mobile** preview widths while editing.

### Email templates

- **Six built-in templates**, 600px wide, responsive (columns stack on phones), with sample content:
  - **Minimal – 1 Column** and **Minimal – 2 Columns**: clean everyday newsletters.
  - **Announcement**: one message, one call to action.
  - **Studio**: editorial, with a full-width image, a serif headline, an uneven image-and-text row, a large quote, three cards and a dark closing band.
  - **Linen**: warm neutrals, a rounded hero, a personal greeting, product, quote and event cards.
  - **Template Starter (for developers)**: plain and fully documented, using every placeholder; the base for your own.
- **Dark mode**: each template has its own dark palette, used by Apple Mail, iOS Mail, Outlook for Mac, Thunderbird and other clients when the reader's device is dark. Content added in the designer stays readable (its text, backgrounds and borders follow the palette), buttons keep their colours, and dark logos and social icons are inverted.
- **Classic Outlook for Windows**: Office settings for sharp rendering on high-DPI screens, fonts Windows has instead of Outlook's Times New Roman fallback, exact line heights, background colours Outlook understands and buttons that keep their padding, added automatically to your own content too.
- Every template includes a **View in browser** link, **why** the reader gets the email, **preferences** and **unsubscribe** links, and a **Subscribe** link for forwarded emails.
- **Templates page**: cards with full-height previews and the layout (1, 2, 3 columns, sections); Preview and *Use template*; Edit (a code editor for the template's files), Download (as an installable package), Duplicate and Delete. A template used by a campaign is never deleted.
- **Your own templates**: install a package, or drop a folder into your site template's `html/ottomo/<folder>/`, where it is picked up automatically (and overrides a built-in template of the same name). The template format, placeholders and email HTML tips are documented in [TEMPLATE-GUIDE.md](TEMPLATE-GUIDE.md).
- **Placeholders** in double braces, so they never clash with Joomla plugin tags: `{{view_in_browser}}`, `{{unsubscribe}}`, `{{preferences}}`, `{{subscribe}}`, `{{why}}`, `{{sitename}}`, `{{subject}}`, `{{topics}}`, `{{date}}`, `{{firstname|there}}` and more; positions are `{{position:content:html}}`.

### Subscribers and topics

- **Topics** are your mailing lists: a subscriber can be in several; each topic shows its active, pending and unsubscribed subscribers and its campaigns.
- **Private topics** stay off the site (signup forms, subscription preferences, and the archive for campaigns sent only to them) but work everywhere in the administrator: lists you manage yourself, such as test recipients. Unpublished topics are retired: kept for their subscribers, no longer offered for new campaigns or imports.
- **Subscriber profiles**: name, email, and optional company, position, address, city, state, ZIP, country, phones, fax, VAT and tax numbers and date of birth.
- **Statuses**: Subscribed, Pending (awaiting confirmation) and Unsubscribed, shown as pills that toggle with a click.
- **Suppression**: an unsubscribed address is kept, never re-added by an import or an administrator, and only its owner can sign up again (with double opt-in).
- **Delete All Unsubscribed**, in one click after a confirmation (it can't be undone: those addresses are no longer suppressed), also from the command line.
- **Lists** with search, filters (status, topic, template…), sorting, *Reset all*, and batch topic assignment; **Save & New** on the topic and subscriber forms for adding several in a row.
- **Import**: paste addresses, upload a CSV (a header row and a name column are recognised; a sample file is provided), or import from your site (see [Importers](#importers)). Imports run in batches with a progress bar and report how many addresses were added, already present, or kept as unsubscribed.
- **Export** what you see: the ticked subscribers, else the filtered list, else everyone, as a CSV with a header and all profile columns. Files are created under unguessable names and downloaded through an authenticated task that deletes them.

### Signup and self-service (frontend)

- **Signup module** with two layouts: **Compact** for sidebars, and **Section**, a full-width band with topics as chips. Choose the topics offered (or let visitors pick), the profile fields shown and which are required, and an intro text (e.g. a link to your privacy policy).
- **Looks like your site**: the module's CSS has zero specificity, so your template's fonts, inputs and buttons apply; tune colours and radius with CSS custom properties, or override the layouts in your template.
- **Spam protection**: Joomla's captcha plugins (the site's default or a chosen one) and a honeypot field.
- **Double opt-in**: an activation email in HTML and plain text, with a confirmation button and a small “Powered by Ottomo” line at its end (never in your campaigns).
- **Preferences page** for subscribers (linked from every email): update the profile, choose topics, unsubscribe or delete the account.
- **One-click unsubscribe** from the email itself, with a confirmation so link scanners can't unsubscribe anyone.

### Deliverability and compliance

- **One-click unsubscribe headers** (RFC 8058 `List-Unsubscribe` and `List-Unsubscribe-Post`), as Gmail and Yahoo require of bulk senders.
- **Compliance footer**: your postal address in every email, and an unsubscribe link when a design has none.
- **Why this email**: your own line (e.g. "You get this email as a member of our forum") instead of the default one.
- **Site name in emails**, independent of Joomla's site name.
- **Sender authentication check**: the sender's domain is checked for SPF, DKIM and DMARC, which Gmail, Yahoo and Outlook require of bulk senders, in the pre-flight checklist and in Settings > Test connection; addresses at free mailbox providers (Gmail, Yahoo, Outlook.com…), which can't send bulk email through other servers, are flagged.
- **No Gmail clipping**: sent emails are minified (comments and extra whitespace removed, Outlook's conditional code kept), and the pre-flight checklist shows the email's size against Gmail's 102 KB limit, beyond which Gmail hides the end of an email, unsubscribe link included.
- **Footers stay visible in Gmail**: every campaign ends with a unique hidden line, so Gmail doesn't fold the footer (why this email, preferences, unsubscribe) away as content repeated from earlier emails.
- **Feedback-ID header** on every campaign email, so Gmail Postmaster Tools reports spam complaints per campaign.
- **Test connection** for the sending settings, and a sending status indicator on the dashboard.

### Tracking and statistics

- **Opens** (a tracking pixel), **clicks** on every link (external links too, signed so the tracker can't be used as an open redirect), stored once per subscriber per campaign, and per link for clicks.
- **Dashboard**: sent, opened and clicked over a day, week, month or custom range, per topic; recently sent campaigns with their rates; drafts; totals.
- **Control panel module**: the same statistics widget on Joomla's control panel, with quick links.
- **Mail service statistics**: Amazon SES, Brevo, Mailgun, Mailjet, SendGrid and SendPulse report their own figures to the dashboard.

### Administration

- **Dashboard, Email Campaigns, Topics, Subscribers, Templates and Settings**, in a modern app layout that follows the administrator template's light and dark schemes, with tablet and phone layouts.
- **Settings**, grouped: Sending (mailer, sender, reply-to, batch size), Mail services (credentials and Test connection), Compliance, Signup forms (captcha, honeypot, signup page), Campaigns (pre-flight checklist), Dashboard, Content retrieval (which article fields the designer inserts) and Permissions.
- **Permissions**: who may use Ottomo, change its settings, and manage email templates (Super Users only by default).
- **Third-party software** used by Ottomo (Ace, Bootstrap Icons, SuperModal.js, Unsplash photos), with versions and licenses, listed in Settings.
- **Accessible**: keyboard-friendly controls, ARIA tabs, focus states, and dialogs that work with screen readers.
- **51 languages**: English plus Albanian, Arabic, Belarusian, Bengali, Bosnian, Bulgarian, Chinese (Simplified), Croatian, Czech, Danish, Dutch, Estonian, Finnish, French, German, Greek, Hausa, Hindi, Hungarian, Icelandic, Indonesian, Irish, Italian, Japanese, Korean, Latvian, Lithuanian, Macedonian, Maltese, Marathi, Montenegrin, Norwegian (Bokmål), Persian, Polish, Portuguese (Brazil), Romanian, Russian, Serbian, Slovak, Slovene, Spanish, Swahili, Swedish, Tamil, Telugu, Thai, Turkish, Ukrainian, Urdu and Vietnamese.

### Mail services

| Mailer | API | Personalisation | One-click unsubscribe | Statistics |
|---|---|---|---|---|
| PHP mail, Sendmail, SMTP | Joomla's mailer (SMTP keeps one connection per batch; unauthenticated relays supported) | Per recipient | ✓ | Ottomo's own |
| Amazon SES | SES v2, zero-dependency AWS Signature v4; paced to the account's send rate, throttled emails retried | Per recipient | ✓ | ✓ |
| Brevo | Transactional Email API v3, `messageVersions` | Per recipient | ✓ (Brevo's) | ✓ |
| Mailgun | Messages API v3, US and EU regions, `recipient-variables` | Per recipient | ✓ (Mailgun's) | ✓ |
| Mailjet | Send API v3.1, per-message results | Per recipient | ✓ | ✓ |
| SendGrid | Web API v3 | Substitutions | ✓ | ✓ |
| SendPulse | OAuth2 + SMTP REST API, throttled emails retried | Per recipient | — | ✓ |

Mail services are plugins (`ottomo-providers`): no vendored SDKs, one small HTTP client each.

### Importers

Import the people already on your site, in batches that keep any site within PHP's time limits (50,000 forum members import in about four minutes). Each source shows how many addresses it holds; sources that aren't installed are shown disabled.

- **AcyMailing** (version 5 or newer): subscribed users, confirmed when AcyMailing requires confirmation; those who left every list are imported as unsubscribed, so they stay suppressed.
- **HikaShop**: customers with at least one order, registered or guest, with their billing address, company, phones and VAT number.
- **Joomla Users**: accounts that are activated and not blocked.
- **Kunena** (5.x) and **Meela**: forum members who can sign in and aren't banned.
- **VirtueMart**: registered customers and guest buyers, with their billing address, company and phones.

### Integrations

- **Joomla articles** and **K2 items** (Joomla 3.x UTD) in the designer, with the fields you choose (title, category, date, author, intro and full text, images and captions), respecting view access levels.
- **Joomla's Media Manager** for images, **Joomla's captcha plugins** for signup forms, **Joomla's ACL** for permissions, and **Joomla's mailer** for PHP mail, Sendmail and SMTP.
- **Meela** (the continuation of Kunena 5.2 for Joomla 3.x UTD) and **Kunena**, **AcyMailing**, **VirtueMart** and **HikaShop** as import sources (each where it runs).

### For developers

- **Command line** (`php cli/joomla.php list ottomo`, on Joomla 3.x UTD, 4, 5 and 6): `ottomo:campaign:list`, `ottomo:campaign:send <id>` (sends or resumes a campaign in batches: ideal for cron jobs and large lists), `ottomo:subscriber:import <source> --topic=<ids>`, `ottomo:subscriber:purge-unsubscribed` and `ottomo:check` (sending settings and the sender's SPF, DKIM and DMARC). All support `--format=json`; those that change something support `--dry-run`. On Joomla 3.x UTD they're offered to AI assistants by its MCP server too.
- **No build step**: plain PHP, CSS and vanilla JavaScript; no jQuery, MooTools or Bootstrap JS; CSS Grid/Flexbox; Bootstrap Icons as inline SVGs; [SuperModal.js](https://fevangelou.github.io/supermodal.js/) for dialogs.
- **Email templates**: a folder with `manifest.xml`, `default.php`, `sampleData.ini`, renderers for articles and K2 items, and preview images (WebP, AVIF, JPEG or PNG); installable as packages (`type="ottomotemplate"`) from the Templates page. See [TEMPLATE-GUIDE.md](TEMPLATE-GUIDE.md).
- **Content plugins** (`ottomo-content`): add your own content source to the designer's "+" menu through the `onOttomoExtensionEditorButton`, `onOttomoExtensionView`, `onOttomoExtensionRowContent` and `onOttomoExtensionRowLink` events.
- **Mail service plugins** (`ottomo-providers`): implement `onOttomoSend()`, `onOttomoVerify()` and `onOttomoStatistics()`, and optionally `onOttomoBatchSize()`; `onOttomoSend()` returns the recipients that failed.
- **Admin JavaScript**: one vanilla script (`static/js/ottomo.js`), one request helper (`Ottomo.request`) for every server call, and simple forms declare their toolbar rules with `data-otm-*` attributes instead of inline scripts.
- **Import adapters**: one small class per source (`helpers/importers/`), with keyset paging; add a source by adding a file.
- **Overrides**: email templates and signup module layouts from your site template.
- **One codebase for Joomla 3.x UTD, 4, 5 and 6**: namespaced core classes, one small compatibility class for what differs, classic (non-namespaced) extension classes everywhere.
- **Databases**: MySQL/MariaDB, PostgreSQL, and SQLite through Joomla 3.x UTD's driver.
- **Security**: CSRF tokens on every action, a central permission check for every controller route, escaped output, signed click tracking, hashed subscriber links.

---

## Requirements

- **Joomla 3.x UTD** (https://github.com/joomlaworks/joomla-3.x), **Joomla 4**, **Joomla 5** or **Joomla 6** (tested on 3.18, 4.4, 5.4 and 6.2)
- **PHP** 7.1 – 8.5 (within what your Joomla version supports: 7.2.5+ on Joomla 4, 8.1+ on Joomla 5, 8.3+ on Joomla 6)
- **Database**: MySQL / MariaDB, PostgreSQL, or SQLite (Joomla 3.x UTD's SQLite driver)
- **K2** items in the designer and **Meela** imports: Joomla 3.x UTD (where those extensions run)

## Installation

Install the package in **Extensions > Install**. It installs the component, the signup module, the control panel module, the mail service plugins, the content plugins and the command-line plugin, and registers the email templates. Then open **Components > Ottomo > Settings** to set your sender and mailer, add the signup module to a position, and create your first topic and campaign.

---

## Changelog

A short overview of each release. Every change is described in detail in [CHANGELOG.md](CHANGELOG.md).

## Version 1.1 - released November 24th, 2026
Summary of changes:
- **Text alignment** in the designer: left, center and right, as inline styles every email client understands
- **Image settings** in the designer: click an image for its width, Fit to the column, alignment, alt text and link. Images from the Media Manager (or in articles and K2 items) fit their column on their own, classic Outlook included, so a 1920px photo no longer widens the email
- **Private topics**: hidden on the site but usable for campaigns, for lists you manage yourself such as test recipients; unpublished topics are retired. Updating from 1.0 turns unpublished topics into private ones, so nothing changes for them
- **Save & New** for topics and subscribers, for adding several in a row
- **Test emails** to several addresses at once, comma separated, with a hint under the field; a typo stops the send before anything goes out
- **Version and copyright** at the foot of Ottomo's menu
- **Fixes:** one click on Send test sent the email twice (every dialog also loaded its page more than once); saving subscription preferences removed the subscriber from topics the page doesn't show; signup forms accepted hidden topics when posted directly; selecting a link's text in the designer made it jump; copying icons or images inside the email pasted only their text

[Release notes and download](https://github.com/joomlaworks/ottomo/releases/tag/v1.1)

## Version 1.0 - released November 23rd, 2026
First release, for Joomla 3.x UTD, 4, 5 and 6 from one package. [Release notes and download](https://github.com/joomlaworks/ottomo/releases/tag/v1.0)

## License
Ottomo is a Joomla extension developed by JoomlaWorks, released under the GNU General Public License. See [LICENSE.txt](LICENSE.txt).

Copyright (c) 2006 - 2026 JoomlaWorks Ltd.
