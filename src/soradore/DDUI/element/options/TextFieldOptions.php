<?php

declare(strict_types=1);

namespace soradore\DDUI\element\options;

use soradore\DDUI\Observable;

class TextFieldOptions
{
    public function __construct(
        public readonly string|Observable $description = '',
        public readonly bool|Observable $disabled = false,
        public readonly bool|Observable $visible = true,
    ) {}
}
