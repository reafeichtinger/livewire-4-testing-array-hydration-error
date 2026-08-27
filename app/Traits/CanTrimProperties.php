<?php

namespace App\Traits;

use App\Attributes\FilterWhenTrimming;
use ReflectionNamedType;
use ReflectionProperty;

/**
 * Provide a function to trim properties and set them to null if supported.
 */
trait CanTrimProperties
{
    /**
     * Loops through all properties, trims them and sets them to null if needed.
     */
    public function trimProperties(): void
    {
        $exceptions = $this->getTrimExceptions();
        foreach (array_keys(get_object_vars($this)) as $property) {
            if (!in_array($property, $exceptions)) {
                $this->trimProperty($property);
            }
        }
    }

    /**
     * Trims the given property and sets it to null if needed.
     */
    public function trimProperty(string $property): void
    {
        if (property_exists($this, $property)) {
            $reflection = new ReflectionProperty($this, $property);
            if (!$reflection->isPublic() || !($reflection?->getType() ?? null)) {
                // Abort if property is not public
                return;
            }
            data_set($this, $property, $this->parseValue($reflection, $this->{$property}));
        }
    }

    public function parseValue(ReflectionProperty $property, mixed $value): mixed
    {
        // When the value is already null we don't need to do anything
        if ($value === null) {
            return null;
        }

        // Get the type of the property so we know how to handle it
        $type = $property->getType();
        $typeName = match (true) {
            $type instanceof ReflectionNamedType => $type?->getName(),
            default => $type->getTypes()[0]?->getName(),
        };

        switch ($typeName) {
            case 'string':
                // Also support empty input from Quill rich text editor
                $value = $value === '{"ops":[{"insert":"\n"}]}' ? '' : trim(strval($value));
                break;
            case 'int':
                $value = intval($value);
                break;
            case 'float':
            case 'double':
                $value = floatval($value);
                break;
            case 'boolean':
                $value = boolval($value);
                break;
            case 'array':
                if (!is_array($value)) {
                    break;
                }
                array_walk_recursive($value, function (&$v) {
                    switch (gettype($v)) {
                        case 'boolean': // Ignore if boolean
                        case 'object': // Ignore objects like TemporaryUploadedFile
                            break;
                        case 'string':
                            $v = trim($v);
                            // break; has been omitted intentionally!
                        default:
                            $v = empty($v) ? null : $v;
                            break;
                    }
                });

                // When the FilterWhenTrimming attribute is given, all null key/value pairs should be removed
                if ($filterWhenTrimming = $property->getAttributes(FilterWhenTrimming::class)[0] ?? null) {
                    $value = $this->array_filter_recursive($value, maxDepth: $filterWhenTrimming->getArguments()[0] ?? $filterWhenTrimming->getArguments()['maxDepth'] ?? null);
                }
                break;
        }

        if ($type->allowsNull()) {
            $value = ($value && $value !== '') ? $value : null;
        }

        return $value;
    }

    public function getTrimExceptions(): array
    {
        $ignoredProperties = [
            'id', 'queryString', 'computedPropertyCache', 'shouldSkipRender', 'preRenderedView',
            'forStack', 'errorBag', 'withValidatorCallback', 'eventQueue', 'dispatchQueue',
            'listeners', 'redirectTo', 'renderedChildren', 'previouslyRenderedChildren',
            'trimPropertiesExcept',
        ];

        if (method_exists($this, 'trimPropertiesExcept')) {
            return array_unique(array_merge($ignoredProperties, $this->trimPropertiesExcept() ?? []));
        } else {
            return array_unique(array_merge($ignoredProperties, $this->trimPropertiesExcept ?? []));
        }
    }

    /**
     * Recursively filter null and empty array values from an array.
     */
    public function array_filter_recursive(array $array, ?int $maxDepth = null, int $currentDepth = 1): array
    {
        // Default value of maxDepth
        if ($maxDepth === null) {
            $maxDepth = PHP_INT_MAX;
        }

        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                // Filter the array or keep the value after maxDepth
                $result[$key] = $currentDepth < $maxDepth
                    ? ($this->array_filter_recursive($value, $maxDepth, $currentDepth + 1) ?: null)
                    : $value;

                // Unset null or empty arrays
                if ($result[$key] === null || $result[$key] === []) {
                    unset($result[$key]);
                }
            } // Keep the value if it is not an array
            elseif ($value !== null) {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
