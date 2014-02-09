<?php

namespace Shelter\Tests;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
	protected function tearDown()
	{
		parent::tearDown();
		ServiceAccessor::resetStatics();
		\Mockery::close();
	}


	protected function assertException(callable $callback, $expectedException = 'Exception', $expectedCode = NULL, $expectedMessage = NULL)
	{
		try {
			$callback();
		} catch (\Exception $e) {
			$this->assertInstanceOf($expectedException, $e);
			if (NULL !== $expectedCode) {
				$this->assertEquals($expectedCode, $e->getCode());
			}
			if (NULL !== $expectedMessage) {
				$this->assertContains($expectedMessage, $e->getMessage());
			}
			return;
		}
		$this->fail('Failed asserting that exception is thrown.');
	}


	protected function mockArrayIterator(\Mockery\MockInterface $mock, array $items)
	{
		if ($mock instanceof \ArrayAccess) {
			foreach ($items as $key => $val) {
				$mock->shouldReceive('offsetGet')
					->with($key)
					->andReturn($val);

				$mock->shouldReceive('offsetExists')
					->with($key)
					->andReturn(TRUE);
			}

			$mock->shouldReceive('offsetExists')
				->andReturn(FALSE);
		}

		if ($mock instanceof \Iterator) {
			$counter = 0;

			$mock->shouldReceive('rewind')
				->andReturnUsing(function () use (& $counter) {
					$counter = 0;
				});

			$vals = array_values($items);
			$keys = array_values(array_keys($items));

			$mock->shouldReceive('valid')
				->andReturnUsing(function () use (& $counter, $vals) {
					return isset($vals[$counter]);
				});

			$mock->shouldReceive('current')
				->andReturnUsing(function () use (& $counter, $vals) {
					return $vals[$counter];
				});

			$mock->shouldReceive('key')
				->andReturnUsing(function () use (& $counter, $keys) {
					return $keys[$counter];
				});

			$mock->shouldReceive('next')
				->andReturnUsing(function () use (& $counter) {
					++$counter;
				});
		}

		if ($mock instanceof \Countable) {
			$mock->shouldReceive('count')
				->andReturn(count($items));
		}
	}
}
