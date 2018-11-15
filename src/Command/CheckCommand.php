<?php

namespace Falnyr\PackageSupport\Command;

use Falnyr\PackageSupport\Comparator;
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
     * @var Comparator
     */
    private $comparator;

    public function __construct(Comparator $comparator)
    {
        $this->comparator = $comparator;

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
            ->setDefinition([
                new InputArgument('lockfile', InputArgument::OPTIONAL, 'The path to the composer.lock file', 'composer.lock'),
                new InputOption('format', '', InputOption::VALUE_REQUIRED, 'The output format', 'ansi'),
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $result = $this->comparator->compare($input->getArgument('lockfile'), $input->getOption('format'));
        } catch (RuntimeException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            return 1;
        }

        $output->writeln((string) $result);

        if (count($result) > 0) {
            return 1;
        }
    }
}
