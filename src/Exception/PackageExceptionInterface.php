<?php

namespace Falnyr\PackageSupport\Exception;

interface PackageExceptionInterface
{
    /**
     * @return string
     */
    public function getPackage();
}
