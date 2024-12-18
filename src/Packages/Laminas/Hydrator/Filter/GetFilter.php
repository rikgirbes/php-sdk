<?php

declare(strict_types=1);

namespace PayNL\Sdk\Packages\Laminas\Hydrator\Filter;

use function strpos;
use function substr;

final class GetFilter implements FilterInterface
{
    public function filter(string $property, ?object $instance = null): bool
    {
        $pos = strpos($property, '::');
        if ($pos !== false) {
            $pos += 2;
        } else {
            $pos = 0;
        }

        return substr($property, $pos, 3) === 'get';
    }
}
