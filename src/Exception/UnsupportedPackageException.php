<?php

namespace Falnyr\PackageSupport\Exception;

class UnsupportedPackageException extends \Exception
{
    private $package;

    public function __construct($package, $message)
    {
        $this->package = $package;
        parent::__construct($message);
    }

    public function getPackage()
    {
        return $this->package;
    }
}
