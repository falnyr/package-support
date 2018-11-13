<?php

namespace Falnyr\PackageSupport;

use Symfony\Component\Console\Exception\RuntimeException;

class Comparator
{
    const VERSION = '0.0';


    /**
     * @param $lock
     * @param string $format
     * @return mixed
     * @throws RuntimeException
     */
    public function compare($lock, $format = 'json')
    {
        $lock = $this->getLock($lock);

        var_dump($lock);

        // return $this->crawler->check($lock, $format);
    }

    /**
     * @param $lock
     * @return mixed|string
     * @throws RuntimeException
     */
    protected function getLock($lock)
    {
        if (0 !== strpos($lock, 'data://text/plain;base64,')) {
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
}
