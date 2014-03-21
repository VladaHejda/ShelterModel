<?php

namespace LazyDataMapper\Tests\Get;

use LazyDataMapper,
	LazyDataMapper\Tests,
	LazyDataMapper\Tests\SuggestorCache,
	LazyDataMapper\Tests\CarMapper,
	LazyDataMapper\Tests\DriverMapper;

require_once __DIR__ . '/implementations/model/Car.php';
require_once __DIR__ . '/implementations/model/Driver.php';

class EmptyTest extends LazyDataMapper\Tests\AcceptanceTestCase
{

	public function testSingle()
	{
		$requestKey = new LazyDataMapper\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new LazyDataMapper\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\CarFacade($accessor, $serviceAccessor);

		$facade->getById(6);

		$facade->getById(3)->driver;

		$this->assertEquals(3, SuggestorCache::$calledGetCached);
		$this->assertEquals(1, SuggestorCache::$calledCacheParamName);
		$this->assertEquals(1, SuggestorCache::$calledCacheDescendant);

		return [$cache, $facade];
	}


	/**
	 * @depends testSingle
	 */
	public function testCachingSingle(array $services)
	{
		list($cache, $facade) = $services;

		$facade->getById(3);

		$this->assertEquals(0, CarMapper::$calledGetById);
		$this->assertEquals(0, DriverMapper::$calledGetById);

		$facade->getById(6)->driver;

		$this->assertEquals(1, CarMapper::$calledGetById);
		$this->assertEquals(0, DriverMapper::$calledGetById);

		$this->assertCount(1, $cache->cache);

		$this->assertEquals(4, SuggestorCache::$calledGetCached); // todo reduce to 3
		$this->assertEquals(0, SuggestorCache::$calledCacheParamName);
		$this->assertEquals(1, SuggestorCache::$calledCacheDescendant); // todo reduce to 0
	}


	public function testContainer()
	{
		$requestKey = new LazyDataMapper\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new LazyDataMapper\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\CarFacade($accessor, $serviceAccessor);

		$facade->getByIdsRange([1, 2]);

		$facade->getByIdsRange([6, 3])[0]->driver;

		$this->assertEquals(3, SuggestorCache::$calledGetCached);
		$this->assertEquals(1, SuggestorCache::$calledCacheParamName);
		$this->assertEquals(0, SuggestorCache::$calledCacheDescendant);

		return [$cache, $facade];
	}


	/**
	 * @depends testContainer
	 */
	public function testCachingContainer(array $services)
	{
		list($cache, $facade) = $services;

		$facade->getByIdsRange([6, 3]);

		$this->assertEquals(0, CarMapper::$calledGetById);
		$this->assertEquals(0, DriverMapper::$calledGetById);
		$this->assertEquals(0, CarMapper::$calledGetByRestrictions);
		$this->assertEquals(0, DriverMapper::$calledGetByRestrictions);

		$facade->getByIdsRange([1, 2])[0]->driver;

		$this->assertEquals(0, CarMapper::$calledGetById);
		$this->assertEquals(0, DriverMapper::$calledGetById);
		$this->assertEquals(1, CarMapper::$calledGetByRestrictions);
		$this->assertEquals(0, DriverMapper::$calledGetByRestrictions);

		$this->assertCount(1, $cache->cache);

		$this->assertEquals(3, SuggestorCache::$calledGetCached);
		$this->assertEquals(0, SuggestorCache::$calledCacheParamName);
		$this->assertEquals(0, SuggestorCache::$calledCacheDescendant);
	}


	public function testSingleWithNoParam()
	{
		$requestKey = new LazyDataMapper\RequestKey;
		$cache = new Tests\Cache\SimpleCache;
		$serviceAccessor = new Tests\ServiceAccessor;
		$suggestorCache = new SuggestorCache($cache, $requestKey, $serviceAccessor);
		$accessor = new LazyDataMapper\Accessor($suggestorCache, $serviceAccessor);
		$facade = new Tests\CarFacade($accessor, $serviceAccessor);

		$facade->getById(3)->bestDriver;

		$this->assertEquals(2, SuggestorCache::$calledGetCached);
		$this->assertEquals(0, SuggestorCache::$calledCacheParamName);
		$this->assertEquals(1, SuggestorCache::$calledCacheDescendant);

		return [$cache, $facade];
	}


	/**
	 * @depends testSingleWithNoParam
	 */
	public function testCachingSingleWithNoParam(array $services)
	{
		list($cache, $facade) = $services;

		$facade->getById(6)->bestDriver;

		$this->assertEquals(0, CarMapper::$calledGetById);
		$this->assertEquals(0, DriverMapper::$calledGetById);

		$this->assertCount(1, $cache->cache);

		$this->assertEquals(2, SuggestorCache::$calledGetCached);
		$this->assertEquals(0, SuggestorCache::$calledCacheParamName);
		$this->assertEquals(1, SuggestorCache::$calledCacheDescendant); // todo reduce to 0
	}
}
