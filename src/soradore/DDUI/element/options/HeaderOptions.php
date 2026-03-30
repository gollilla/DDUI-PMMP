<?php

declare(strict_types=1);

namespace soradore\DDUI\element\options;

use soradore\DDUI\Observable;

class HeaderOptions
{
    public function __construct(
        public readonly bool|Observable $visible = true,
    ) {}
}
