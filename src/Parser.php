<?php

namespace Falnyr\PackageSupport;

use RuntimeException;

class Parser
{
    /**
     * @param $lock
     * @return mixed|string
     * @throws RuntimeException
     */
    private function getLock($lock)
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

    public function getLockContents($lock)
    {
        $contents = json_decode(file_get_contents($this->getLock($lock)), true);
        $packages = array('packages' => array(), 'packages-dev' => array());
        foreach (array('packages', 'packages-dev') as $key) {
            if (!is_array($contents[$key])) {
                continue;
            }
            foreach ($contents[$key] as $package) {
                $data = array(
                    'version' => $package['version'],
                );
                if (isset($package['time']) && strpos($package['version'], 'dev') !== false) {
                    $data['time'] = $package['time'];
                }
                $packages[$key][$package['name']] = $data;
            }
        }

        if ( ! array_key_exists('php', $packages['packages'])) {
            $packages['packages'] = array_merge(
                array('php' => array('version' => (string) PHP_VERSION)),
                $packages['packages']
            );
        }

        return $packages;
    }
}