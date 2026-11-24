# Changelog

All notable changes to **Ottomo for Joomla** (`com_ottomo`) are documented in this file.

## [1.1] - 2026-11-24

### Added
- Campaign designer: text alignment (left, center, right) in the toolbar, as an inline `text-align` that email clients support.
- Campaign designer: an image bar when you click an image, with its width (the height follows), Fit to the column, alignment (left, center, right), alt text and a link (optionally in a new window).
- Campaign designer: a **Button** in the "+" menu, in the template's style (each template has an `extensions/button.php`; custom templates without one get a plain button). Its text is typed on the button; a button bar sets its link (optionally in a new window), alignment, full width, background and text colours and corners (square, rounded, pill). The templates' own buttons use the same bar, and classic Outlook gets a clickable button.
- Private topics: a checkbox in the topic form hides a topic on the site (signup forms, subscription preferences, and the archive for campaigns sent only to private topics), for lists you manage in the admin, such as test recipients. Campaigns can still be sent to them.
- Save & New for topics and subscribers: saves and opens an empty form.
- Send test: a hint under the address field (several addresses, comma separated), and every address is checked before anything is sent.
- The administrator menu shows Ottomo's version, release date and copyright, with links to the Ottomo and JoomlaWorks sites.

### Changed
- Images inserted from the Media Manager or with content (articles, K2 items) are fitted to their column: a `width` attribute for classic Outlook, `max-width:100%` and `height:auto` for every other client. Images wider than their column in existing campaigns are fitted when the designer opens.
- Sent emails keep every image of the content within its column (`max-width:100%`, `height:auto`), including campaigns edited in the HTML tab.
- Unpublished topics are retired: hidden on the site as before, and no longer offered for new campaigns, imports or batch changes. Updating from 1.0 turns unpublished topics into published private topics, so they work as before.
- The administrator toolbars use Ottomo's own language strings.
- Updates are announced by `update.xml` only (the manifest's update server); updating converts the old `collection.xml` update server of 1.0 sites.

### Fixed
- Send test: one click sent the test email more than once. Every dialog (preview, import, send) also loaded its page several times.
- Subscription preferences: saving them no longer removes the subscriber from topics the page doesn't show (private or unpublished ones).
- Signup forms only accept public topics, whatever is posted.
- Campaign designer: selecting the text of a link (e.g. a button) made the designer jump up and down, as the link bar pushed it down; the link and image bars now float over it.
- Campaign designer: copying and pasting inside the email kept only the text; images, links and formatting are now kept (anything pasted from outside is still plain text).

## [1.0] - 2026-11-23

First release, for Joomla 3.x UTD, 4, 5 and 6 (one package). See [README.md](README.md) for the features.
