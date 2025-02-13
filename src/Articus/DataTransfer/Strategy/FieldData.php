<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Strategy;

use Articus\DataTransfer\Exception\InvalidData;
use Articus\DataTransfer\Utility;
use Articus\DataTransfer\Validator;

/**
 * Strategy for object of specific type that can be treated as field set described by type metadata
 */
class FieldData implements StrategyInterface
{
	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @psalm-var iterable<array{0: string, 1: null|array{0: string, 1: bool}, 2: null|array{0: string, 1: bool}, 3: StrategyInterface}>
	 * @var iterable
	 */
	protected $typeFields;

	/**
	 * @var bool
	 */
	protected $extractStdClass = false;

	/**
	 * @param string $type
	 * @param iterable $typeFields list of tuples (<field name>, (<name of property or method to get field value>, <flag if getter is method>), (<name of property or method to set field value>, <flag if setter is method>))
	 * @param bool $extractStdClass
	 */
	public function __construct(string $type, iterable $typeFields, bool $extractStdClass)
	{
		$this->type = $type;
		$this->typeFields = $typeFields;
		$this->extractStdClass = $extractStdClass;
	}

	/**
	 * @inheritDoc
	 */
	public function extract($from)
	{
		if (!($from instanceof $this->type))
		{
			throw new \LogicException(\sprintf(
				'Extraction can be done only from %s, not %s',
				$this->type, \is_object($from) ? \get_class($from) : \gettype($from)
			));
		}

		$result = ($this->extractStdClass) ? new \stdClass() : [];
		$map = new Utility\MapAccessor($result);
		$object = new Utility\PropertyAccessor($from);
		foreach ($this->typeFields as [$fieldName, $sourceField, $getter, $setter, $strategy])
		{
			/** @var StrategyInterface $strategy */
			try
			{
				$rawValue = $object->get($getter);
				$fieldValue = $strategy->extract($rawValue);
				$map->set($sourceField, $fieldValue);
			}
			catch (InvalidData $e)
			{
				$violations = [Validator\FieldData::INVALID_INNER => [$fieldName => $e->getViolations()]];
				throw new InvalidData($violations, $e);
			}
		}
		return $result;
	}

	/**
	 * @inheritDoc
	 */
	public function hydrate($from, &$to): void
	{
		$map = new Utility\MapAccessor($from);
		if (!$map->accessible())
		{
			throw new \LogicException(\sprintf(
				'Hydration can be done only from key-value map, not %s.',
				\is_object($from) ? \get_class($from) : \gettype($from)
			));
		}
		if (!($to instanceof $this->type))
		{
			throw new \LogicException(\sprintf(
				'Hydration can be done only to %s, not %s',
				$this->type, \is_object($to) ? \get_class($to) : \gettype($to)
			));
		}
		$object = new Utility\PropertyAccessor($to);
		foreach ($this->typeFields as [$fieldName, $sourceField, $getter, $setter, $strategy])
		{
			/** @var StrategyInterface $strategy */
            try
            {
                $rawValue = $object->get($getter);
                $fieldValue = $map->get($sourceField);
                if ($strategy instanceof SetData) {
                    $strategy->setData($from);
                }
                $strategy->hydrate($fieldValue, $rawValue);
                $object->set($setter, $rawValue);
            }
            catch (InvalidData $e)
            {
                $violations = [Validator\FieldData::INVALID_INNER => [$fieldName => $e->getViolations()]];
                throw new InvalidData($violations, $e);
            }
		}
	}
}
