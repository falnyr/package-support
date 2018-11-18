<?php

namespace Falnyr\PackageSupport\Exception;

use Exception;
use Falnyr\PackageSupport\Precision;

class UnsupportedPackageException extends Exception implements PackageExceptionInterface
{
    /** @var string */
    private $package;

    /** @var Precision */
    private $precision;

    public function __construct($precision, $package, $message)
    {
        $this->package = $package;
        $this->precision = $precision;
        parent::__construct($message);
    }

    /**
     * {@inheritdoc}
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * @return Precision
     */
    public function getPrecision()
    {
        return $this->precision;
    }
}
