<?php

namespace App\Attributes;

use Attribute;

/**
 * This Attribute should be used on array properties where the CanTrimProperties trait should remove all nested keys that resolve to a null value.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class FilterWhenTrimming
{
    /**
     * @param  ?int  $maxDepth  How deep the filtering of the array is allowed to go.
     */
    public function __construct(public ?int $maxDepth = null) {}
}
