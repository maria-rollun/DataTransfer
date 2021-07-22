<?php


namespace Articus\DataTransfer\Strategy\Factory;


use Articus\DataTransfer\Strategy;
use Articus\DataTransfer\Strategy\PluginManager;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class Chain implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if (! isset($options['strategies'])) {
            throw new \LogicException('Option "strategies" is required');
        }

        if (! is_array($options['strategies'])) {
            throw new \InvalidArgumentException('Option "strategies" must be array');
        }
        
        $strategies = [];

        foreach ($options['strategies'] as $strategy) {
            if (! isset($strategy['name'])) {
                throw new \LogicException('Option "name" is required');
            }

            if (! is_string($strategy['name'])) {
                throw new \InvalidArgumentException('Option "name" must be string');
            }

            $strategyOptions = $strategy['options'] ?? null;

            $strategies[] = $this->getStrategyManager($container)->get($strategy['name'], $strategyOptions);
        }

        return new Strategy\Chain($strategies);
    }

    protected function getStrategyManager(ContainerInterface $container): PluginManager
    {
        return $container->get(PluginManager::class);
    }

}