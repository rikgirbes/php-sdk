<?php

declare(strict_types=1);

namespace PayNL\Sdk\Packages\Laminas\Hydrator\NamingStrategy;

use function array_map;

final class CompositeNamingStrategy implements NamingStrategyInterface
{
    /** @var NamingStrategyInterface[] */
    private $namingStrategies = [];

    /** @var NamingStrategyInterface */
    private $defaultNamingStrategy;

    /**
     * @param NamingStrategyInterface[]    $strategies            indexed by the name they translate
     */
    public function __construct(array $strategies, ?NamingStrategyInterface $defaultNamingStrategy = null)
    {
        $this->namingStrategies = array_map(
            function (NamingStrategyInterface $strategy) {
                // this callback is here only to ensure type-safety
                return $strategy;
            },
            $strategies
        );

        $this->defaultNamingStrategy = $defaultNamingStrategy ?: new IdentityNamingStrategy();
    }

    /**
     * {@inheritDoc}
     */
    public function extract(string $name, ?object $object = null): string
    {
        $strategy = $this->namingStrategies[$name] ?? $this->defaultNamingStrategy;
        return $strategy->extract($name);
    }

    /**
     * {@inheritDoc}
     */
    public function hydrate(string $name, ?array $data = null): string
    {
        $strategy = $this->namingStrategies[$name] ?? $this->defaultNamingStrategy;
        return $strategy->hydrate($name);
    }
}
