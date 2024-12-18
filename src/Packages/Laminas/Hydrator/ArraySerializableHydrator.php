<?php

declare(strict_types=1);

namespace PayNL\Sdk\Packages\Laminas\Hydrator;

use Throwable;

use function array_merge;
use function is_callable;
use function method_exists;
use function sprintf;

class ArraySerializableHydrator extends AbstractHydrator
{
    /**
     * Extract values from the provided object
     *
     * Extracts values via the object's getArrayCopy() method.
     *
     * {@inheritDoc}
     *
     * @throws Exception\BadMethodCallException For an $object not implementing getArrayCopy().
     * @throws Exception\RuntimeException If a part of $data could not be extracted.
     */
    public function extract(object $object): array
    {
        if (! method_exists($object, 'getArrayCopy') || ! is_callable([$object, 'getArrayCopy'])) {
            throw new Exception\BadMethodCallException(
                sprintf('%s expects the provided object to implement getArrayCopy()', __METHOD__)
            );
        }

        $data   = $object->getArrayCopy();
        $filter = $this->getFilter();

        foreach ($data as $name => $value) {
            $name = (string) $name;

            if (! $filter->filter($name)) {
                unset($data[$name]);
                continue;
            }

            $extractedName = $this->extractName($name, $object);

            // replace the original key with extracted, if differ
            if ($extractedName !== $name) {
                unset($data[$name]);
                $name = $extractedName;
            }

            try {
                $data[$name] = $this->extractValue($name, $value, $object);
            } catch (Throwable $t) {
                throw new Exception\RuntimeException(
                    sprintf("Could not extract field %s", $name),
                    0,
                    $t
                );
            }
        }

        return $data;
    }

    /**
     * Hydrate an object
     *
     * Hydrates an object by passing $data to either its exchangeArray() or
     * populate() method.
     *
     * {@inheritDoc}
     *
     * @throws Exception\BadMethodCallException For an $object not implementing exchangeArray() or populate().
     * @throws Exception\RuntimeException If a part of $data could not be hydrated.
     */
    public function hydrate(array $data, object $object)
    {
        $replacement = [];
        foreach ($data as $key => $value) {
            $name = $this->hydrateName($key, $data);

            try {
                $replacement[$name] = $this->hydrateValue($name, $value, $data);
            } catch (Throwable $t) {
                throw new Exception\RuntimeException(
                    sprintf("Could not hydrate field %s", $name),
                    0,
                    $t
                );
            }
        }

        if (method_exists($object, 'exchangeArray') && is_callable([$object, 'exchangeArray'])) {
            // Ensure any previously populated values not in the replacement
            // remain following population.
            if (method_exists($object, 'getArrayCopy') && is_callable([$object, 'getArrayCopy'])) {
                $original    = $object->getArrayCopy();
                $replacement = array_merge($original, $replacement);
            }
            $object->exchangeArray($replacement);
            return $object;
        }

        if (method_exists($object, 'populate') && is_callable([$object, 'populate'])) {
            $object->populate($replacement);
            return $object;
        }

        throw new Exception\BadMethodCallException(
            sprintf('%s expects the provided object to implement exchangeArray() or populate()', __METHOD__)
        );
    }
}
