<?php

namespace Falnyr\PackageSupport\Exception;

use Exception;

class UnsupportedPackageException extends Exception implements PackageExceptionInterface
{
    /** @var string */
    private $package;

    /** @var integer */
    private $precision;

    public function __construct($precision, $package, $message)
    {
        $this->package = $package;
        $this->precision = $precision;
        parent::__construct($message);
    }

    /**
     * @inheritdoc
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * @return int
     */
    public function getPrecision()
    {
        return $this->precision;
    }
}
