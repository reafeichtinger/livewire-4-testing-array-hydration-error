<?php

namespace Tests\Helpers;

use Livewire\Wireable;

/**
 * This is a helper object to show how CanTrimProperties handles custom Wireable objects
 */
class EmptyObject implements Wireable
{
    public function toLivewire(): array
    {
        return [];
    }

    public static function fromLivewire($value): static
    {
        return new static;
    }
}
