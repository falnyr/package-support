<?php

namespace Falnyr\PackageSupport\Command;

use Falnyr\PackageSupport\Checker;
use Falnyr\PackageSupport\Precision;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CheckCommand extends Command
{
    protected static $defaultName = 'check';

    /**
     * @var Checker
     */
    private $checker;

    public function __construct(Checker $checker)
    {
        $this->checker = $checker;

        parent::__construct();
    }

    /**
     * @see Command
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this
            ->setName('check')
            ->setDescription('Checks outdated dependencies in composer.lock file')
            ->setDefinition(array(
                new InputArgument('lockfile', InputArgument::OPTIONAL, 'The path to the composer.lock file', 'composer.lock'),
                new InputOption('precision', 'p', InputOption::VALUE_REQUIRED, Precision::DEPRECATED),
                new InputOption('silent', 's', InputOption::VALUE_NONE, 'Disable error exit codes'),
                new InputOption('format', '', InputOption::VALUE_REQUIRED, 'The output format', 'ansi'),
            ));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $result = $this->checker->check($input->getArgument('lockfile'), $input->getOption('format'));
        } catch (RuntimeException $e) {
            dump($e->getMessage());
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            return 1;
        }

        $output->writeln((string) $result);

        if (count($result) > 0) {
            return 1;
        }
    }
}
