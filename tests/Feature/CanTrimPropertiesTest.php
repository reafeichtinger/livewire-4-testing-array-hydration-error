<?php

use App\Attributes\FilterWhenTrimming;
use App\Traits\CanTrimProperties;
use Livewire\Component;
use Livewire\Livewire;
use Tests\Helpers\EmptyObject;

beforeEach(function () {
    $this->component = Livewire::test(new class extends Component
    {
        use CanTrimProperties;

        // Ints
        public int $int = 0;
        public ?int $nInt = null;

        // Floats
        public float $float = 0.0;
        public ?float $nFloat = null;

        // Strings
        public string $string = '';
        public ?string $nString = null;

        // Bools
        public bool $bool = false;
        public ?bool $nBool = null;

        // Arrays
        public array $array = [];
        public ?array $nArray = null;

        // Filterable Arrays
        #[FilterWhenTrimming]
        public array $filterArray = [];

        #[FilterWhenTrimming()]
        public ?array $nFilterArray = null;

        #[FilterWhenTrimming(maxDepth: 2)]
        public array $maxFilterArray = [];

        // Objects
        public EmptyObject $object;
        public ?EmptyObject $nObject = null;

        // Exceptions
        public ?int $notTrimmedInt = null;
        public array $trimPropertiesExcept = ['notTrimmedInt'];

        public function mount(): void
        {
            $this->object = new EmptyObject;
        }

        public function render(): mixed
        {
            return '<div></div>';
        }
    });
});

it('can handle simple array values', function () {
    // Don't trim array ints
    $this->component->set('array', [0 => 1]);
    $this->component->set('nArray', [0 => 1]);
    $this->component->call('trimProperties');
    $this->component->assertSetStrict('array', [0 => 1]);
    $this->component->assertSetStrict('nArray', [0 => 1]);

    // Don't trim array strings
    $this->component->set('array', [0 => 'x']);
    $this->component->set('nArray', [0 => 'x']);
    $this->component->call('trimProperties');
    $this->component->assertSetStrict('array', [0 => 'x']);
    $this->component->assertSetStrict('nArray', [0 => 'x']);

    // Don't trim false array bools
    $this->component->set('array', [0 => false]);
    $this->component->set('nArray', [0 => false]);
    $this->component->call('trimProperties');
    $this->component->assertSetStrict('array', [0 => false]);
    $this->component->assertSetStrict('nArray', [0 => false]);

    // Don't trim true array bools
    $this->component->set('array', [0 => true]);
    $this->component->set('nArray', [0 => true]);
    $this->component->call('trimProperties');
    $this->component->assertSetStrict('array', [0 => true]);
    $this->component->assertSetStrict('nArray', [0 => true]);

    // Don't trim array objects
    $object = new EmptyObject;
    $this->component->set('array', [0 => $object]);
    $this->component->set('nArray', [0 => $object]);
    $this->component->call('trimProperties');
    $this->component->assertSet('array', [0 => $object]);
    $this->component->assertSet('nArray', [0 => $object]);

    #region Exception

    /**
     * Setting the array or nArray property to an array [int => int|float] throws an exception.
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
     * This works with v4.3.3 but not with v4.4.2.
     */

    // Don't trim true array ints
    $this->component->set('array', [0 => 1]);
    $this->component->set('nArray', [0 => 1]);
    $this->component->call('trimProperties');
    $this->component->assertSetStrict('array', [0 => 1]);
    $this->component->assertSetStrict('nArray', [0 => 1]);

    // Don't trim true array floats
    $this->component->set('array', [0 => 0.1]);
    $this->component->set('nArray', [0 => 0.1]);
    $this->component->call('trimProperties');
    $this->component->assertSetStrict('array', [0 => 0.1]);
    $this->component->assertSetStrict('nArray', [0 => 0.1]);

    #endregion Exception

    // Do trim empty arrays
    $this->component->set('array', []);
    $this->component->set('nArray', []);
    $this->component->call('trimProperties');
    $this->component->assertSetStrict('array', []);
    $this->component->assertSetStrict('nArray', null);

    // Do trim empty array strings
    $this->component->set('array', [0 => '']);
    $this->component->set('nArray', [0 => '']);
    $this->component->call('trimProperties');
    $this->component->assertSetStrict('array', [0 => null]);
    $this->component->assertSetStrict('nArray', [0 => null]);

    // Do trim false array ints
    $this->component->set('array', [0 => 0]);
    $this->component->set('nArray', [0 => 0]);
    $this->component->call('trimProperties');
    $this->component->assertSetStrict('array', [0 => null]);
    $this->component->assertSetStrict('nArray', [0 => null]);

    // Do trim false array floats
    $this->component->set('array', [0 => 0.0]);
    $this->component->set('nArray', [0 => 0.0]);
    $this->component->call('trimProperties');
    $this->component->assertSetStrict('array', [0 => null]);
    $this->component->assertSetStrict('nArray', [0 => null]);
});