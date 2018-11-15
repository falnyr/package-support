<?php

namespace Falnyr\PackageSupport;

use Symfony\Component\Console\Exception\RuntimeException;

class Comparator
{
    const VERSION = '0.0';

    const PACKAGES_FILE = __DIR__.'/../packages.json';

    /**
     * @var array
     */
    private $supported;

    public function __construct()
    {
        $this->supported = json_decode(file_get_contents(self::PACKAGES_FILE), true);
    }

    /**
     * @param $lock
     * @param string $format
     * @return mixed
     * @throws RuntimeException
     */
    public function compare($lock, $format = 'json')
    {
        $lock = $this->getLock($lock);
        $lockContents = $this->getLockContents($lock);

        $this->isSupported('php', '8.1.1');

        foreach ($lockContents['packages'] as $packageName => $versionDetails) {
            $this->isSupported($packageName, $versionDetails['version']);
        }

        foreach ($lockContents['packages-dev'] as $packageName => $versionDetails) {
            $this->isSupported($packageName, $versionDetails['version']);
        }
    }

    /**
     * @param $lock
     * @return mixed|string
     * @throws RuntimeException
     */
    protected function getLock($lock)
    {
        if (strpos($lock, 'data://text/plain;base64,') !== 0) {
            if (is_dir($lock) && file_exists($lock . '/composer.lock')) {
                $lock = $lock . '/composer.lock';
            } elseif (preg_match('/composer\.json$/', $lock)) {
                $lock = str_replace('composer.json', 'composer.lock', $lock);
            }

            if (!is_file($lock)) {
                throw new RuntimeException('Lock file does not exist.');
            }
        }
        return $lock;
    }

    private function getLockContents($lock)
    {
        $contents = json_decode(file_get_contents($lock), true);
        $packages = ['packages' => [], 'packages-dev' => []];
        foreach (['packages', 'packages-dev'] as $key) {
            if (!is_array($contents[$key])) {
                continue;
            }
            foreach ($contents[$key] as $package) {
                $data = [
                    'version' => $package['version'],
                ];
                if (isset($package['time']) && strpos($package['version'], 'dev') !== false) {
                    $data['time'] = $package['time'];
                }
                $packages[$key][$package['name']] = $data;
            }
        }

        return $packages;
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
                $supportDates = $this->supported[$packageName][$minor];
                dump($packageName, $minor, $supportDates);
            } else {
                // TODO: catch exceptions and add them to array of failed checks
                throw new RuntimeException("Unknown version '$minor' for '$packageName'");
            }
        }
    }
}
