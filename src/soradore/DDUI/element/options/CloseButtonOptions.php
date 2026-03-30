<?php

declare(strict_types=1);

namespace soradore\DDUI\element\options;

use soradore\DDUI\Observable;

class CloseButtonOptions
{
    public function __construct(
        public readonly string|Observable $label = 'Close',
        public readonly bool|Observable $visible = true,
    ) {}
}
