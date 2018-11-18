<?php

namespace Falnyr\PackageSupport;

use Eloquent\Enumeration\AbstractEnumeration;

final class Precision extends AbstractEnumeration
{
    const DISCONTINUED = 1;
    const VULNERABLE = 2;
    const LEGACY = 3;
    const DEPRECATED = 4;
    const OUTDATED = 5;
}
