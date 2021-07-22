<?php


namespace Articus\DataTransfer\Strategy\Factory;


use Articus\DataTransfer\Strategy;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class InnerField implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if (! isset($options['field'])) {
            throw new \LogicException('Option "field" is required');
        }

        if (! is_string($options['field'])) {
            throw new \InvalidArgumentException('Option "field" must be string');
        }

        return new Strategy\InnerField($options['field']);
    }
}