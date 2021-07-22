<?php


namespace Articus\DataTransfer\Strategy;

use Articus\DataTransfer\Utility\MapAccessor;

class InnerField implements StrategyInterface, SetData
{
    const DELIMITER = '.';

    /** @var string $field */
    protected $field;

    protected $data;

    public function __construct($field)
    {
        $this->field = $field;
    }

    public function extract($from)
    {
        return $from;
    }

    public function hydrate($from, &$to): void
    {
        $keyParts = explode(static::DELIMITER, $this->field);

        if (empty($keyParts)) {
            return;
        }

        $innerData = $this->data;

        foreach ($keyParts as $fieldName) {
            $map = new MapAccessor($innerData);

            if (! $map->has($fieldName)) {
                return;
            }

            $innerData = $map->get($fieldName);
        }

        $to = $innerData;
    }


    public function setData($data): void
    {
        $this->data = $data;
    }
}