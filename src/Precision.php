<?php

namespace Falnyr\PackageSupport;

use Eloquent\Enumeration\AbstractEnumeration;

abstract class Precision extends AbstractEnumeration
{
    const DISCONTINUED = 1;
    const VULNERABLE = 2;
    const LEGACY = 3;
    const UNSUPPORTED = 4;
    const DEPRECATED = 5;
    const OBSOLETE = 6;
    const OLD = 7;
    const OUTDATED = 8;
}