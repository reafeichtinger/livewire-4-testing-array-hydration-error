<?php

use Livewire\Component;
use Livewire\Livewire;
use Tests\Helpers\EmptyObject;

/**
 * Setting the array property to a primitive value, e.g. [int => int|float] throws an exception.
 * This only happens if it was set ot an object (implementing the Wireable interface) first.
 *
 * foreach() argument must be of type array|object, int given
 *
 * at vendor/livewire/livewire/src/Features/SupportWireables/WireableSynth.php:42
 *    38▕         if (! isset($meta['class']) || ! is_a($meta['class'], Wireable::class, true)) {
 *    39▕             throw new \Exception('Livewire: Invalid wireable class.');
 *    40▕         }
 *    41▕
 * ➜  42▕         foreach ($value as $key => $child) {
 *    43▕             $value[$key] = $hydrateChild($key, $child);
 *    44▕         }
 *    45▕
 *    46▕         return $meta['class']::fromLivewire($value);
 *
 * This works with v4.4.0 but not with v4.4.1 or v4.4.2.
 */
it('can handle simple array values', function () {

    $component = Livewire::test(new class extends Component
    {
        public array $array = [];

        public function render(): mixed
        {
            return '<div></div>';
        }
    });

    $component->set('array', [0 => new EmptyObject]);
    $component->set('array', [0 => 1]);
    $component->assertSetStrict('array', [0 => 1]);

});
