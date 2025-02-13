<?php
declare(strict_types=1);

namespace Articus\DataTransfer\Strategy;

use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Factory\InvokableFactory;

class PluginManager extends AbstractPluginManager
{
	protected $instanceOf = StrategyInterface::class;

	protected $factories = [
	    Chain::class => Factory\Chain::class,
		FieldData::class => Factory\FieldData::class,
        InnerField::class => Factory\InnerField::class,
		NoArgObject::class => Factory\NoArgObject::class,
		NoArgObjectList::class => Factory\NoArgObjectList::class,
		Whatever::class => InvokableFactory::class,
	];

	protected $aliases = [
	    'Chain' => Chain::class,
        'InnerField' => InnerField::class,
		'Object' => NoArgObject::class,
		'object' => NoArgObject::class,
		'ObjectArray' => NoArgObjectList::class,
		'objectArray' => NoArgObjectList::class,
	];

	protected $shared = [
		Whatever::class => true,
	];

	/**
	 * Overwrite parent method just to add return type declaration
	 * @inheritDoc
	 * @return StrategyInterface
	 */
	public function get($name, array $options = null): StrategyInterface
	{
		return parent::get($name, $options);
	}
}
