<?php

namespace Falnyr\PackageSupport;

use Carbon\Carbon;
use Eloquent\Enumeration\Exception\UndefinedMemberExceptionInterface;
use Falnyr\PackageSupport\Exception\UnknownPackageException;
use Falnyr\PackageSupport\Exception\UnsupportedPackageException;
use InvalidArgumentException;

class Checker
{
    const VERSION = '0.1-alpha';
    const DATE_FORMAT = 'd F Y';
    const THRESHOLD_DAYS_FIXES = 60;
    const THRESHOLD_DAYS_SECURITY = 90;

    /**
     * @var array
     */
    private $supported;
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var int
     */
    private $precision = Precision::OUTDATED;

    /**
     * @param Parser $parser
     */
    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
        $this->supported = json_decode(file_get_contents(__DIR__.'/../packages.json'), true);
    }

    /**
     * @param string $lock
     * @param int    $precision
     * @param bool   $noDev
     * @param bool   $showUnknown
     *
     * @return array
     *
     * @throws InvalidArgumentException
     */
    public function check($lock, $precision, $noDev, $showUnknown)
    {
        $lockContents = $this->parser->getLockContents($lock);
        $this->setPrecision($precision);
        $errors = array();

        foreach ($lockContents['packages'] as $packageName => $versionDetails) {
            try {
                $this->isSupported($packageName, $versionDetails['version']);
            } catch (UnsupportedPackageException $e) {
                $errors[$packageName] = $e;
            } catch (UnknownPackageException $e) {
                if ($showUnknown === true) {
                    $errors[$packageName] = $e;
                }
            }
        }

        if ($noDev === false) {
            foreach ($lockContents['packages-dev'] as $packageName => $versionDetails) {
                try {
                    $this->isSupported($packageName, $versionDetails['version']);
                } catch (UnsupportedPackageException $e) {
                    $errors[$packageName] = $e;
                } catch (UnknownPackageException $e) {
                    if ($showUnknown === true) {
                        $errors[$packageName] = $e;
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * @param $packageName
     * @param $version
     *
     * @throws UnknownPackageException
     * @throws UnsupportedPackageException
     */
    private function isSupported($packageName, $version)
    {
        if (array_key_exists($packageName, $this->supported)) {
            $this->checkAbandoned($packageName);

            preg_match('/v?(\d+\.\d+)\.?\d*/', $version, $matches);
            $minor = $matches[1];

            if (array_key_exists($minor, $this->supported[$packageName])) {
                $this->checkSupportDates($packageName, $version, $minor);
            } else {
                throw new UnknownPackageException($packageName, "Unknown version '$version' for '$packageName'");
            }
        } else {
            throw new UnknownPackageException($packageName, "Unknown package '$packageName'");
        }
    }

    /**
     * @param $precision
     *
     * @throws InvalidArgumentException
     */
    private function setPrecision($precision)
    {
        $precision = (int) $precision;

        try {
            Precision::memberByValue($precision);
        } catch (UndefinedMemberExceptionInterface $e) {
            throw new InvalidArgumentException("Invalid precision ({$e->getMessage()})", 0, $e);
        }

        $this->precision = $precision;
    }

    /**
     * @param string $packageName
     * @param mixed  $version
     * @param string $minor
     *
     * @throws UnsupportedPackageException
     */
    private function checkSupportDates($packageName, $version, $minor)
    {
        $now = new Carbon();
        $now->startOfDay();
        $bugSupport = new Carbon($this->supported[$packageName][$minor]['support_ends']);
        $securitySupport = new Carbon($this->supported[$packageName][$minor]['security_ends']);
        $securitySupportDiff = $now->diff($securitySupport);
        $bugSupportDiff = $now->diff($bugSupport);

        if ($securitySupportDiff->invert === 1 && $this->precision >= Precision::VULNERABLE) {
            throw new UnsupportedPackageException(
                Precision::memberByValue(Precision::VULNERABLE),
                $packageName,
                sprintf(
                    "Support for version '%s' has ended on %s (%s days ago)!",
                    $version,
                    $securitySupport->format(self::DATE_FORMAT),
                    $securitySupportDiff->days
                )
            );
        } elseif ($bugSupportDiff->invert === 1 && $this->precision >= Precision::LEGACY) {
            throw new UnsupportedPackageException(
                Precision::memberByValue(Precision::LEGACY),
                $packageName,
                sprintf(
                    "Support for version '%s' has ended on %s! (%s days ago). Security fixes will be available for %s more days.",
                    $version,
                    $bugSupport->format(self::DATE_FORMAT),
                    $bugSupportDiff->days,
                    $securitySupportDiff->days
                )
            );
        } elseif ($securitySupportDiff->days <= self::THRESHOLD_DAYS_SECURITY && $this->precision >= Precision::DEPRECATED) {
            throw new UnsupportedPackageException(
                Precision::memberByValue(Precision::DEPRECATED),
                $packageName,
                sprintf(
                    "Support for version '%s' ends on %s (%s days left)",
                    $version,
                    $securitySupport->format(self::DATE_FORMAT),
                    $securitySupportDiff->days
                )
            );
        } elseif ($bugSupportDiff->days <= self::THRESHOLD_DAYS_FIXES && $this->precision >= Precision::OUTDATED) {
            throw new UnsupportedPackageException(
                Precision::memberByValue(Precision::OUTDATED),
                $packageName,
                sprintf(
                    "Support for version '%s' ends on %s (%s days left).",
                    $version,
                    $bugSupport->format(self::DATE_FORMAT),
                    $bugSupportDiff->days
                )
            );
        }
    }

    /**
     * @param $packageName
     *
     * @throws UnsupportedPackageException
     */
    private function checkAbandoned($packageName)
    {
        if (array_key_exists('abandoned', $this->supported[$packageName])) {
            throw new UnsupportedPackageException(
                Precision::memberByValue(Precision::DISCONTINUED),
                $packageName,
                'Abandoned! No new bug fixes or security fixes are available!'
            );
        }
    }
}
