<?php

namespace Falnyr\PackageSupport\Exception;

use Exception;

class UnknownPackageException extends Exception implements PackageExceptionInterface
{
    /** @var string */
    private $package;

    public function __construct($package, $message)
    {
        $this->package = $package;
        parent::__construct($message);
    }

    /**
     * {@inheritdoc}
     */
    public function getPackage()
    {
        return $this->package;
    }
}
