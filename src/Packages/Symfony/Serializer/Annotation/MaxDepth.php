<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PayNL\Sdk\Packages\Symfony\Serializer\Annotation;

use PayNL\Sdk\Packages\Symfony\Serializer\Exception\InvalidArgumentException;

/**
 * Annotation class for @MaxDepth().
 *
 * @Annotation
 * @Target({"PROPERTY", "METHOD"})
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class MaxDepth
{
    /**
     * @var int
     */
    private $maxDepth;

    public function __construct(array $data)
    {
        if (!isset($data['value'])) {
            throw new InvalidArgumentException(sprintf('Parameter of annotation "%s" should be set.', static::class));
        }

        if (!\is_int($data['value']) || $data['value'] <= 0) {
            throw new InvalidArgumentException(sprintf('Parameter of annotation "%s" must be a positive integer.', static::class));
        }

        $this->maxDepth = $data['value'];
    }

    public function getMaxDepth()
    {
        return $this->maxDepth;
    }
}
