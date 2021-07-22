<?php


namespace Articus\DataTransfer\Strategy;

class Chain implements StrategyInterface, SetData
{
    /** @var StrategyInterface[] $hydrateStrategies */
    protected $hydrateStrategies;

    /** @var StrategyInterface[] $extractStrategies */
    protected $extractStrategies;

    public function __construct($strategies)
    {
        $this->hydrateStrategies = $strategies;
        $this->extractStrategies = array_reverse($strategies);
    }

    public function extract($from)
    {
        foreach ($this->extractStrategies as $strategy) {
            $from = $strategy->extract($from);
        }

        return $from;
    }

    public function hydrate($from, &$to): void
    {
        foreach ($this->hydrateStrategies as $strategy) {
            $result = is_object($to) ? clone $to : $to;
            $strategy->hydrate($from, $result);
            $from = $result;
        }

        $to = $from;
    }


    public function setData($data): void
    {
        foreach ($this->hydrateStrategies as $strategy) {
            if ($strategy instanceof SetData) {
                $strategy->setData($data);
            }
        }
    }
}