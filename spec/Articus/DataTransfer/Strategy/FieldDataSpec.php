<?php

namespace spec\Articus\DataTransfer\Strategy;

use spec\Example;
use Articus\DataTransfer as DT;
use PhpSpec\ObjectBehavior;

class FieldDataSpec extends ObjectBehavior
{
    public function it_extracts_array_from_object(
		DT\Strategy\StrategyInterface $strategy1,
		DT\Strategy\StrategyInterface $strategy2
	)
    {
		$from1 = 'a';
		$from2 = 'b';
		$to1 = 'c';
		$to2 = 'd';

		$strategy1->extract($from1)->shouldBeCalledOnce()->willReturn($to1);
		$strategy2->extract($from2)->shouldBeCalledOnce()->willReturn($to2);
        $source = new Example\DTO\Data();
        $source->test1 = $from1;
        $source->setTest2($from2);
		$fieldName1 = 'test_1';
		$fieldName2 = 'test_2';
		$fields = [
        	[$fieldName1, ['test1', false], null, $strategy1],
			[$fieldName2, ['getTest2', true], null, $strategy2],
		];
		$this->beConstructedWith(Example\DTO\Data::class, $fields, false);
    	$this->extract($source)->shouldBe([$fieldName1 => $to1, $fieldName2 => $to2]);
    }

	public function it_extracts_std_class_from_object(
		DT\Strategy\StrategyInterface $strategy1,
		DT\Strategy\StrategyInterface $strategy2
	)
	{
		$from1 = 'a';
		$from2 = 'b';
		$to1 = 'c';
		$to2 = 'd';

		$strategy1->extract($from1)->shouldBeCalledOnce()->willReturn($to1);
		$strategy2->extract($from2)->shouldBeCalledOnce()->willReturn($to2);
		$source = new Example\DTO\Data();
		$source->test1 = $from1;
		$source->setTest2($from2);
		$fieldName1 = 'test_1';
		$fieldName2 = 'test_2';
		$fields = [
			[$fieldName1, ['test1', false], null, $strategy1],
			[$fieldName2, ['getTest2', true], null, $strategy2],
		];
		$this->beConstructedWith(Example\DTO\Data::class, $fields, true);

		$data = new \stdClass();
		$data->{$fieldName1} = $to1;
		$data->{$fieldName2} = $to2;

		$this->extract($source)->shouldBeLike($data);
	}

	public function it_throws_on_object_of_invalid_type($source)
	{
		$this->beConstructedWith(Example\DTO\Data::class, [], true);
		$this->shouldThrow(\LogicException::class)->during('extract', [$source]);
	}

	public function it_rethrows_wrapped_invalid_data_exception(DT\Strategy\StrategyInterface $strategy)
	{
		$from = 'a';
		$violations = ['test' => 123];
		$innerError = new DT\Exception\InvalidData($violations);

		$strategy->extract($from)->shouldBeCalledOnce()->willThrow($innerError);
		$source = new Example\DTO\Data();
		$source->test1 = $from;
		$fieldName = 'test_field';
		$fields = [
			[$fieldName, ['test1', false], null, $strategy],
		];
		$error = new DT\Exception\InvalidData([DT\Validator\FieldData::INVALID_INNER => [$fieldName => $violations]], $innerError);

		$this->beConstructedWith(Example\DTO\Data::class, $fields, false);
		$this->shouldThrow($error)->during('extract', [$source]);
	}
}
