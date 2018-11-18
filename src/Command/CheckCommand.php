<?php

namespace Falnyr\PackageSupport\Command;

use Falnyr\PackageSupport\Checker;
use Falnyr\PackageSupport\Exception\UnknownPackageException;
use Falnyr\PackageSupport\Exception\UnsupportedPackageException;
use Falnyr\PackageSupport\Precision;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
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
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this
            ->setName('check')
            ->setDescription('Checks outdated dependencies in composer.lock file')
            ->setDefinition(array(
                new InputArgument('lockfile', InputArgument::OPTIONAL, 'The path to the composer.lock file', 'composer.lock'),
                new InputOption('precision', 'p', InputOption::VALUE_REQUIRED, 'Sets precision for the check', Precision::OUTDATED),
                new InputOption('silent', 's', InputOption::VALUE_NONE, 'Disable error exit codes'),
                new InputOption('no-dev', '', InputOption::VALUE_NONE, 'Disable checking for dev dependencies'),
                new InputOption('show-unknown', '', InputOption::VALUE_NONE, 'Show unknown packages'),
            ));
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null
     *
     * @throws RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $errors = $this->checker->check(
            $input->getArgument('lockfile'),
            $input->getOption('precision'),
            $input->getOption('no-dev') ?: false,
            $input->getOption('show-unknown') ?: false
        );

        if ($errors) {
            $errorCount = 0;
            foreach ($errors as $package => $error) {
                if ($error instanceof UnsupportedPackageException) {
                    $this->outputMessage($output, 'red', $error->getPrecision()->key(), $package, $error->getMessage());
                    ++$errorCount;
                } elseif ($error instanceof UnknownPackageException) {
                    $this->outputMessage($output, 'magenta', 'UNKNOWN', $package, $error->getMessage());
                } else {
                    throw new RuntimeException('Not implemented');
                }
            }
        } else {
            /** @var Precision $precision */
            $precision = Precision::memberByValue((int) $input->getOption('precision'));
            $output->writeln("<info>All dependencies are supported. (Precision: {$precision->key()})</info>");
        }

        return $input->getOption('silent') ? 0 : (int) ($errorCount > 0);
    }

    /**
     * @param OutputInterface $output
     * @param string          $color
     * @param string          $violation
     * @param string          $package
     * @param string          $message
     */
    private function outputMessage(OutputInterface $output, $color, $violation, $package, $message)
    {
        $output->writeln(sprintf(
            '<bg=default;fg=%s;>[%s]</> <bg=default;fg=yellow;>%s</>: %s',
            $color,
            $violation,
            $package,
            $message
        ));
    }
}
