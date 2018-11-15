<?php

namespace Falnyr\PackageSupport;

use Carbon\Carbon;
use Falnyr\PackageSupport\Exception\UnsupportedPackageException;
use Symfony\Component\Console\Exception\RuntimeException;

class Checker
{
    const VERSION = '0.0';

    const DATE_FORMAT = 'd F Y';

    const THRESHOLD_DAYS_FIXES = 30;
    const THRESHOLD_DAYS_SECURITY = 60;

    /**
     * @var array
     */
    private $supported;
    /**
     * @var Parser
     */
    private $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
        $this->supported = json_decode(file_get_contents(__DIR__.'/../packages.json'), true);
    }

    /**
     * @param $lock
     * @param string $format
     * @return mixed
     * @throws RuntimeException
     */
    public function check($lock, $format = 'json')
    {
        $lockContents = $this->parser->getLockContents($lock);
        $unsupported = array();

        foreach ($lockContents['packages'] as $packageName => $versionDetails) {
            try {
                $this->isSupported($packageName, $versionDetails['version']);
            } catch (UnsupportedPackageException $e) {
                $unsupported[$e->getPackage()] = $e->getMessage();
            }
        }

        foreach ($lockContents['packages-dev'] as $packageName => $versionDetails) {
            try {
                $this->isSupported($packageName, $versionDetails['version']);
            } catch (UnsupportedPackageException $e) {
                $unsupported[$e->getPackage()] = $e->getMessage();
            }
        }

        dump($unsupported);
    }

    /**
     * @param string $packageName
     * @param string $version
     * @throws RuntimeException
     */
    private function isSupported($packageName, $version)
    {
        if (array_key_exists($packageName, $this->supported)) {
            preg_match('/v?(\d+\.\d+)\.?\d*/', $version, $matches);
            $minor = $matches[1];

            if (array_key_exists($minor, $this->supported[$packageName])) {
                $now = new Carbon();
                $now->startOfDay();
                $bugSupport = new Carbon($this->supported[$packageName][$minor]['support_ends']);
                $securitySupport = new Carbon($this->supported[$packageName][$minor]['security_ends']);
                $securitySupportDiff = $now->diff($securitySupport);
                $bugSupportDiff = $now->diff($bugSupport);

                if ($securitySupportDiff->invert === 1) {
                    throw new UnsupportedPackageException($packageName, sprintf("[SECURITY] Support for version '%s' has ended on %s (%s days ago)!", $version, $securitySupport->format(self::DATE_FORMAT), $securitySupportDiff->days));
                } elseif ($bugSupportDiff->invert === 1) {
                    throw new UnsupportedPackageException($packageName, sprintf("[BUG] Support for version '%s' has ended on %s! (%s days ago)", $version, $bugSupport->format(self::DATE_FORMAT), $bugSupportDiff->days));
                } elseif ($securitySupportDiff->days <= self::THRESHOLD_DAYS_SECURITY) {
                    throw new UnsupportedPackageException($packageName, sprintf("[SECURITY] Support for version '%s' ends on %s (%s days)", $version, $securitySupport->format(self::DATE_FORMAT), $securitySupportDiff->days));
                } elseif ($bugSupportDiff->days <= self::THRESHOLD_DAYS_FIXES) {
                    throw new UnsupportedPackageException($packageName, sprintf("[BUG] Support for version '%s' ends on %s (%s days)", $version, $bugSupport->format(self::DATE_FORMAT), $bugSupportDiff->days));
                }

            } else {
                throw new RuntimeException("Unknown version '$version' for '$packageName'");
            }
        }
    }
}
