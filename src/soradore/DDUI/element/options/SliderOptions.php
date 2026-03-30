<?php

declare(strict_types=1);

namespace soradore\DDUI\element\options;

use soradore\DDUI\Observable;

class SliderOptions
{
    public function __construct(
        public readonly string|Observable $description = '',
        public readonly bool|Observable $disabled = false,
        public readonly bool|Observable $visible = true,
        public readonly float|int|Observable $step = 1,
    ) {}
}
