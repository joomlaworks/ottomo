<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Mail\MailHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;

require_once JPATH_ADMINISTRATOR.'/components/com_ottomo/helpers/compat.php';


/** What every Ottomo command needs: Ottomo's classes, models and language. */
/*
 * The console of Joomla 3.x UTD (Joomla\CMS\Console) or of Joomla 4+ (Joomla\Console, see modern.php): each command
 * defines its arguments and options in define() and runs in handle($io).
 */
if (class_exists('Joomla\\CMS\\Console\\AbstractCommand')) {
    abstract class OttomoCommandBase extends \Joomla\CMS\Console\AbstractCommand
    {
        protected function configure()
        {
            $this->define();
        }

        protected function doExecute(\Joomla\CMS\Console\CommandIO $io)
        {
            return $this->handle($io);
        }

        protected function define()
        {
        }

        abstract protected function handle($io);
    }
} else {
    require_once __DIR__.'/modern.php';
}

abstract class OttomoCommand extends OttomoCommandBase
{
    protected function bootstrap()
    {
        $base = JPATH_ADMINISTRATOR.'/components/com_ottomo';
        JLoader::register('OttomoModel', $base.'/models/model.php');
        JLoader::register('OttomoHelperData', $base.'/helpers/data.php');
        JLoader::register('OttomoHelperHTML', $base.'/helpers/html.php');
        JLoader::register('OttomoHelperImport', $base.'/helpers/import.php');
        JLoader::register('OttomoHelperSettings', $base.'/helpers/settings.php');
        JLoader::register('OttomoHelperTemplate', $base.'/helpers/template.php');
        BaseDatabaseModel::addIncludePath($base.'/models');
        Table::addIncludePath($base.'/tables');
        OttomoCompat::language()->load('com_ottomo', $base);
        OttomoCompat::language()->load('com_ottomo', JPATH_SITE.'/components/com_ottomo');
    }

    /** IDs from a comma-separated option or repeated values ("1,2" or array("1", "2")). */
    protected function ids($value)
    {
        $ids = array();
        foreach ((array)$value as $part) {
            foreach (explode(',', (string)$part) as $id) {
                if (ctype_digit(trim($id))) {
                    $ids[] = (int)trim($id);
                }
            }
        }
        return array_values(array_unique(array_filter($ids)));
    }
}

/** Email campaigns with their status, sent date and the subscribers still waiting for them: it only reads. */
class OttomoCampaignListCommand extends OttomoCommand
{
    protected $name = 'ottomo:campaign:list';
    protected $description = 'List Ottomo\'s email campaigns';
    protected $help = 'Lists the email campaigns: ID, subject, status (draft or sent), sent date, recipients so far and '
        .'subscribers still waiting (an interrupted send). With --status, only drafts or only sent campaigns.';
    protected $readOnly = true;

    protected function define()
    {
        $this->addOption('status', null, self::OPTION_REQUIRED, 'Only "draft" or "sent" campaigns');
    }

    protected function handle($io)
    {
        $this->bootstrap();
        $io->title('Email Campaigns');
        $status = $io->getOption('status');
        if ($status !== null && !in_array($status, array('draft', 'sent'), true)) {
            $io->error('--status must be "draft" or "sent".');
            return self::INVALID;
        }
        $db = OttomoCompat::db();
        $query = $db->getQuery(true)
            ->select($db->quoteName(array('id', 'subject', 'published', 'sent')))
            ->from($db->quoteName('#__ottomo_email_campaigns'))
            ->order($db->quoteName('id').' DESC');
        if ($status !== null) {
            $query->where($db->quoteName('published').' = '.($status === 'sent' ? 1 : 0));
        }
        $rows = $db->setQuery($query)->loadObjectList();
        $model = OttomoModel::getInstance('Newsletters');
        $stats = $rows ? $model->getCampaignStats(array_map(function ($row) {
            return (int)$row->id;
        }, $rows)) : array();
        $items = array();
        foreach ($rows as $row) {
            $items[] = array(
                'id' => (int)$row->id,
                'subject' => (string)$row->subject,
                'status' => (int)$row->published ? 'sent' : 'draft',
                'sent' => (int)$row->published ? (string)$row->sent : '',
                'recipients' => isset($stats[(int)$row->id]) ? (int)$stats[(int)$row->id]->recipients : 0,
                'waiting' => (int)$row->published ? $model->getRemaining((int)$row->id) : 0,
            );
        }
        $io->table(array('id' => 'ID', 'subject' => 'Subject', 'status' => 'Status', 'sent' => 'Sent', 'recipients' => 'Recipients', 'waiting' => 'Waiting'), $items, 'campaigns');
        return self::SUCCESS;
    }
}

/**
 * Sends a campaign (or resumes an interrupted send) in batches, until every subscriber of its topics has it: the
 * same batches as the Send dialog, so a closed terminal or a stopped cron job resumes where it stopped.
 */
class OttomoCampaignSendCommand extends OttomoCommand
{
    protected $name = 'ottomo:campaign:send';
    protected $description = 'Send an email campaign (or resume it) to the subscribers of its topics';
    protected $help = 'Sends the campaign in batches to the active subscribers of its topics who haven\'t received it '
        .'yet, with the mailer of Settings > Sending, until none is left: a send interrupted in the browser or here '
        .'resumes with the rest, never twice. Emails contain links to the site: set $live_site in configuration.php '
        .'or pass --live-site=https://www.example.com. --batch overrides the batch size of the settings.';
    protected $dryRun = true;
    protected $superUser = true;

    protected function define()
    {
        $this->addArgument('id', self::ARGUMENT_REQUIRED, 'The campaign ID');
        $this->addOption('batch', null, self::OPTION_REQUIRED, 'Recipients per batch (default: Settings > Sending)');
    }

    protected function handle($io)
    {
        $this->bootstrap();
        $io->title('Send Email Campaign');
        $id = (string)$io->getArgument('id');
        $model = OttomoModel::getInstance('Newsletters');
        $model->setState('id', (int)$id);
        $row = ctype_digit($id) ? $model->getRow() : null;
        if (!$row || empty($row->id)) {
            $io->error(sprintf('Campaign %s not found.', $id));
            return self::NOT_FOUND;
        }
        $batch = $io->getOption('batch');
        if ($batch !== null && (!ctype_digit((string)$batch) || (int)$batch < 1)) {
            $io->error('--batch must be a positive number.');
            return self::INVALID;
        }

        // The same blocking checks as the Send step
        $sender = OttomoHelperSettings::sender($row);
        $problems = array();
        if (trim((string)$row->subject) === '') {
            $problems[] = 'The campaign has no subject.';
        }
        if (!MailHelper::isEmailAddress((string)$sender->email)) {
            $problems[] = 'There is no valid sender address (the campaign\'s or Settings > Sending).';
        }
        if (!array_filter((array)$model->getNewsletterTopics())) {
            $problems[] = 'The campaign has no topics.';
        }
        if (trim((string)OttomoCompat::config('live_site')) === '') {
            $problems[] = 'The site\'s address is unknown, so the emails\' links would be wrong: set $live_site in configuration.php or pass --live-site.';
        }
        if ($problems) {
            foreach ($problems as $problem) {
                $io->error($problem);
            }
            return self::INVALID;
        }

        $remaining = $model->getRemaining((int)$row->id);
        $io->setData('id', (int)$row->id);
        $io->setData('waiting', $remaining);
        if (!$remaining) {
            $io->success(sprintf('Campaign %d "%s" has no subscribers waiting for it.', $row->id, $row->subject));
            return self::SUCCESS;
        }
        if ($io->isDryRun()) {
            $io->plan(sprintf('Send campaign %d "%s" to %d subscribers', $row->id, $row->subject, $remaining), array('action' => 'send', 'id' => (int)$row->id, 'recipients' => $remaining));
            return self::SUCCESS;
        }

        $limit = $batch !== null ? (int)$batch : max(1, (int)ComponentHelper::getParams('com_ottomo')->get('limit', 50));
        $io->text(sprintf('Sending campaign %d "%s" to %d subscribers, from %s (%s), links to %s ...', $row->id, $row->subject, $remaining, $sender->name, $sender->email, Uri::root()));
        $after = 0;
        $sent = 0;
        $failed = 0;
        $started = microtime(true);
        do {
            $model->setState('id', (int)$row->id);
            $model->setState('limit', $limit);
            $model->setState('after', $after);
            $processed = $model->send();
            if ($processed === false) {
                $io->error($model->getError());
                $io->setData('sent', $sent);
                $io->setData('failed', $failed);
                $io->text('Run the command again to resume: those already reached are skipped.');
                return self::FAILURE;
            }
            $failed += count((array)$model->getState('failed', array()));
            $sent += (int)$processed;
            $after = (int)$model->getState('lastID');
            $io->text(sprintf('  %d / %d', min($sent, $remaining), $remaining));
        } while ($processed > 0 && $processed >= (int)$model->getState('batchSize', $limit));

        $io->setData('sent', $sent - $failed);
        $io->setData('failed', $failed);
        $io->setData('seconds', round(microtime(true) - $started, 1));
        $io->success(sprintf('Campaign %d sent: %d emails%s in %.0f s.', $row->id, $sent - $failed, $failed ? sprintf(', %d not delivered (run again to retry them)', $failed) : '', microtime(true) - $started));
        return $failed ? self::FAILURE : self::SUCCESS;
    }
}

/** Imports the users of a source on the site (Joomla, AcyMailing, Kunena, Meela, VirtueMart, HikaShop) as subscribers. */
class OttomoSubscriberImportCommand extends OttomoCommand
{
    protected $name = 'ottomo:subscriber:import';
    protected $description = 'Import subscribers from Joomla, AcyMailing, HikaShop, Kunena, Meela or VirtueMart';
    protected $help = 'Imports the users of a source on this site (joomla, acymailing, hikashop, kunena, meela, virtuemart) '
        .'into the topics given with --topic, in batches, like the Import dialog: addresses already in Ottomo are left as '
        .'they are, and AcyMailing users who left every list are kept as unsubscribed. Without an argument, lists the sources.';
    protected $dryRun = true;
    protected $superUser = true;

    protected function define()
    {
        $this->addArgument('source', self::ARGUMENT_OPTIONAL, 'The source: joomla, acymailing, hikashop, kunena, meela or virtuemart');
        $this->addOption('topic', null, self::OPTION_REQUIRED, 'Topic IDs, comma-separated');
    }

    protected function handle($io)
    {
        $this->bootstrap();
        $io->title('Import Subscribers');
        $name = (string)$io->getArgument('source');
        $adapters = OttomoHelperImport::adapters();
        if ($name === '') {
            $items = array();
            foreach ($adapters as $key => $adapter) {
                $available = $adapter->isAvailable();
                $items[] = array('source' => $key, 'name' => strip_tags($adapter->label()), 'installed' => $available ? 'yes' : 'no', 'addresses' => $available ? $adapter->total() : 0);
            }
            $io->table(array('source' => 'Source', 'name' => 'Name', 'installed' => 'Installed', 'addresses' => 'Addresses'), $items, 'sources');
            return self::SUCCESS;
        }
        if (!isset($adapters[$name])) {
            $io->error(sprintf('Unknown source "%s". Sources: %s.', $name, implode(', ', array_keys($adapters))));
            return self::INVALID;
        }
        $adapter = $adapters[$name];
        if (!$adapter->isAvailable()) {
            $io->error(sprintf('%s is not installed on this site.', strip_tags($adapter->label())));
            return self::NOT_FOUND;
        }
        $topics = $this->ids($io->getOption('topic'));
        if (!$topics) {
            $io->error('Give the topics to add them to: --topic=1 or --topic=1,2.');
            return self::INVALID;
        }
        $db = OttomoCompat::db();
        $found = $db->setQuery($db->getQuery(true)->select($db->quoteName('id'))->from($db->quoteName('#__ottomo_topics'))->where($db->quoteName('id').' IN ('.implode(',', $topics).')'))->loadColumn();
        if (count($found) !== count($topics)) {
            $io->error(sprintf('Topic(s) not found: %s.', implode(', ', array_diff($topics, array_map('intval', $found)))));
            return self::NOT_FOUND;
        }

        $total = $adapter->total();
        if ($io->isDryRun()) {
            $io->plan(sprintf('Import up to %d addresses from %s into topic(s) %s', $total, strip_tags($adapter->label()), implode(', ', $topics)), array('action' => 'import', 'source' => $name, 'topics' => $topics, 'addresses' => $total));
            return self::SUCCESS;
        }
        $io->text(sprintf('Importing %d addresses from %s...', $total, strip_tags($adapter->label())));
        $counts = array('added' => 0, 'existing' => 0, 'unsubscribed' => 0);
        $errors = array();
        $cursor = null;
        $done = 0;
        do {
            list($records, $cursor) = $adapter->batch($cursor, 500);
            $errors = array_merge($errors, OttomoHelperImport::save($records, $topics, $counts));
            $done += count($records);
            $io->text(sprintf('  %d / %d', min($done, $total), $total));
        } while ($cursor !== null);

        foreach (array_slice($errors, 0, 20) as $error) {
            $io->warning($error);
        }
        foreach ($counts as $key => $value) {
            $io->setData($key, (int)$value);
        }
        $io->setData('errors', count($errors));
        $io->success(sprintf('Added: %d. Already in Ottomo: %d. Kept as unsubscribed: %d.%s', $counts['added'], $counts['existing'], $counts['unsubscribed'], $errors ? ' Not imported: '.count($errors).'.' : ''));
        return self::SUCCESS;
    }
}

/** Deletes every unsubscribed address (Subscribers > Delete All Unsubscribed). */
class OttomoSubscriberPurgeCommand extends OttomoCommand
{
    protected $name = 'ottomo:subscriber:purge-unsubscribed';
    protected $description = 'Delete every unsubscribed address';
    protected $help = 'Deletes the subscribers whose status is Unsubscribed, with their topics. It can\'t be undone, '
        .'and Ottomo then no longer knows they unsubscribed: an import could add them again.';
    protected $dryRun = true;
    protected $superUser = true;

    protected function handle($io)
    {
        $this->bootstrap();
        $io->title('Delete Unsubscribed Addresses');
        $model = OttomoModel::getInstance('Subscribers');
        $count = $model->countByStatus(2);
        $io->setData('unsubscribed', $count);
        if (!$count) {
            $io->success('There are no unsubscribed addresses.');
            return self::SUCCESS;
        }
        if ($io->isDryRun()) {
            $io->plan(sprintf('Delete %d unsubscribed addresses', $count), array('action' => 'delete', 'unsubscribed' => $count));
            return self::SUCCESS;
        }
        if ($io->isInteractive() && !$io->confirm(sprintf('Delete %d unsubscribed addresses? This can\'t be undone.', $count), false)) {
            $io->warning('Nothing was deleted.');
            return self::SUCCESS;
        }
        $deleted = $model->removeUnsubscribed();
        $io->setData('deleted', $deleted);
        $io->success(sprintf('Unsubscribed addresses deleted: %d.', $deleted));
        return self::SUCCESS;
    }
}

/** The sending settings and the sender's domain, as Settings > Test connection checks them: it only reads. */
class OttomoCheckCommand extends OttomoCommand
{
    protected $name = 'ottomo:check';
    protected $description = 'Check Ottomo\'s sending settings and the sender\'s domain (SPF, DKIM, DMARC)';
    protected $help = 'Connects with the mailer of Settings > Sending (SMTP server, Sendmail or the mail service\'s API) '
        .'without sending anything, and checks the DNS records of the default sender\'s domain that Gmail, Yahoo and '
        .'Outlook require of bulk senders. With --sender, checks that address\'s domain instead.';
    protected $readOnly = true;

    protected function define()
    {
        $this->addOption('sender', null, self::OPTION_REQUIRED, 'A sender address to check instead of the default one');
    }

    protected function handle($io)
    {
        $this->bootstrap();
        $io->title('Ottomo Check');
        $connection = OttomoHelperSettings::checkConnection(false);
        $email = $io->getOption('sender') !== null ? (string)$io->getOption('sender') : OttomoHelperSettings::sender()->email;
        $auth = OttomoHelperSettings::senderAuthentication($email);
        $items = array(array('check' => 'Connection ('.$connection->mailer.')', 'level' => $connection->level, 'result' => strip_tags($connection->message)));
        foreach ($auth->checks as $check) {
            $items[] = array('check' => 'Sender '.$email, 'level' => $check->level, 'result' => strip_tags($check->message));
        }
        $io->table(array('check' => 'Check', 'level' => 'Level', 'result' => 'Result'), $items, 'checks');
        $worst = in_array('error', array($connection->level, $auth->level), true) ? 'error' : (in_array('warning', array($connection->level, $auth->level), true) ? 'warning' : 'ok');
        $io->setData('level', $worst);
        if ($worst === 'error') {
            $io->error('Some checks failed.');
            return self::FAILURE;
        }
        $worst === 'warning' ? $io->warning('Ready, with warnings.') : $io->success('Ready to send.');
        return self::SUCCESS;
    }
}
