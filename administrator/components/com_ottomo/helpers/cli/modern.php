<?php
/**
 * @version    1.x
 * @package    Ottomo
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2026 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

use Joomla\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Joomla 4+: Ottomo's commands on Joomla's console (Symfony Console). The commands themselves (commands.php) are
 * written for Joomla 3.x UTD's console: define() adds their arguments and options, handle($io) runs them with an
 * $io that offers the same methods as UTD's CommandIO (OttomoCommandIO below).
 */
abstract class OttomoCommandBase extends AbstractCommand
{
    const ARGUMENT_REQUIRED = 1;
    const ARGUMENT_OPTIONAL = 2;
    const ARGUMENT_ARRAY = 4;
    const OPTION_NONE = 1;
    const OPTION_REQUIRED = 2;
    const OPTION_OPTIONAL = 4;
    const SUCCESS = 0;
    const FAILURE = 1;
    const INVALID = 2;
    const NOT_FOUND = 3;
    const REFUSED = 4;

    protected function configure(): void
    {
        $this->setName($this->name);
        $this->setDescription($this->description);
        $this->setHelp($this->help);
        $this->addOption('format', null, InputOption::VALUE_REQUIRED, 'Output format: text or json', 'text');
        if (!empty($this->dryRun)) {
            $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show what would be done, change nothing');
        }
        $this->define();
    }

    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $io = new OttomoCommandIO($this->name, $input, $output);
        $code = (int)$this->handle($io);
        $io->finish($code);
        return $code;
    }

    /** Arguments and options of the command. */
    protected function define()
    {
    }

    abstract protected function handle($io);
}

/** The methods of Joomla 3.x UTD's CommandIO that Ottomo's commands use, on Symfony's input and output. */
class OttomoCommandIO
{
    protected $command;
    protected $input;
    protected $output;
    protected $style;
    protected $json;
    protected $data = array();
    protected $messages = array();
    protected $errorMessage = null;
    protected static $errorCodes = array(1 => 'failed', 2 => 'invalid', 3 => 'not_found', 4 => 'refused');

    public function __construct($command, InputInterface $input, OutputInterface $output)
    {
        $this->command = $command;
        $this->input = $input;
        $this->output = $output;
        $this->style = new SymfonyStyle($input, $output);
        $this->json = $input->hasOption('format') && $input->getOption('format') === 'json';
    }

    public function getArgument($name)
    {
        return $this->input->hasArgument($name) ? $this->input->getArgument($name) : null;
    }

    public function getOption($name)
    {
        return $this->input->hasOption($name) ? $this->input->getOption($name) : null;
    }

    public function isJson()
    {
        return $this->json;
    }

    public function isInteractive()
    {
        return !$this->json && $this->input->isInteractive();
    }

    public function isDryRun()
    {
        return $this->input->hasOption('dry-run') && $this->input->getOption('dry-run');
    }

    public function plan($text, array $item = array())
    {
        $this->data['plan'][] = array_merge(array('description' => $text), $item);
        if (!$this->json) {
            $this->output->writeln('<fg=cyan>[DRY RUN] Would: </>'.$text);
        }
    }

    public function title($text)
    {
        if (!$this->json) {
            $this->style->title($text);
        }
    }

    public function text($text)
    {
        $this->message('info', $text);
    }

    public function success($text)
    {
        $this->message('success', $text);
    }

    public function warning($text)
    {
        $this->message('warning', $text);
    }

    public function error($text)
    {
        $this->errorMessage = $this->errorMessage === null ? $text : $this->errorMessage;
        $this->message('error', $text);
    }

    protected function message($type, $text)
    {
        $this->messages[] = array('type' => $type, 'message' => $text);
        if ($this->json) {
            return;
        }
        if ($type === 'info') {
            $this->style->text($text);
        } else {
            $this->style->$type($text);
        }
    }

    public function table(array $columns, array $rows, $key = 'items')
    {
        $normalised = array();
        foreach ($rows as $row) {
            $row = (array)$row;
            $item = array();
            foreach ($columns as $column => $heading) {
                $item[$column] = array_key_exists($column, $row) ? $row[$column] : null;
            }
            $normalised[] = $item;
        }
        if ($this->json) {
            $this->data[$key] = $normalised;
            return;
        }
        if ($normalised) {
            $this->style->table(array_values($columns), array_map(function ($item) {
                return array_map(function ($value) {
                    return is_bool($value) ? ($value ? 'yes' : 'no') : (is_scalar($value) || $value === null ? (string)$value : json_encode($value));
                }, array_values($item));
            }, $normalised));
        }
    }

    public function setData($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function confirm($question, $default = false)
    {
        if (!$this->isInteractive()) {
            return $default;
        }
        return $this->style->confirm($question, $default);
    }

    public function finish($exitCode)
    {
        if (!$this->json) {
            return;
        }
        $document = array('command' => $this->command, 'success' => $exitCode === 0, 'exitCode' => $exitCode);
        if ($exitCode !== 0) {
            $document['error'] = array(
                'code' => isset(self::$errorCodes[$exitCode]) ? self::$errorCodes[$exitCode] : 'failed',
                'message' => $this->errorMessage,
            );
        }
        if ($this->isDryRun()) {
            $document['dryRun'] = true;
        }
        $document['data'] = (object)$this->data;
        $document['messages'] = $this->messages;
        $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        $this->output->writeln(json_encode($document, defined('JSON_INVALID_UTF8_SUBSTITUTE') ? $flags | constant('JSON_INVALID_UTF8_SUBSTITUTE') : $flags));
    }
}
