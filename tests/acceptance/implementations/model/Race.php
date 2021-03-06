<?php

namespace LazyDataMapper\Tests;

use LazyDataMapper;

require_once __DIR__ . '/default.php';
require_once __DIR__ . '/serviceAccessor.php';

class Race extends LazyDataMapper\Entity
{

	protected function getCar()
	{
		return $this->getChild('LazyDataMapper\Tests\Car');
	}
}


class Races extends LazyDataMapper\EntityCollection
{
}


class RaceFacade extends LazyDataMapper\Facade
{
}


class RaceRestrictor extends LazyDataMapper\FilterRestrictor
{

	public function limitCar(Car $car)
	{
		$this->equals('car', $car->id);
	}
}


class RaceParamMap extends LazyDataMapper\ParamMap
{

	protected function loadMap()
	{
		return ['car', 'country'];
	}
}


class RaceMapper extends defaultMapper
{

	public static $calledGetById = 0;
	public static $calledGetByRestrictions = 0;

	/** @var LazyDataMapper\Suggestor */
	public static $lastSuggestor;

	public static $data;
	public static $staticData = [
		1 => ['car' => '3', 'country' => 'Montana'],
		2 => ['car' => '3', 'country' => 'Iowa'],
		3 => ['car' => '1', 'country' => 'Ontario'],
		4 => ['car' => '2', 'country' => 'Texas'],
		5 => ['car' => '7', 'country' => 'Oregon'],
		6 => ['car' => '7', 'country' => 'Buffalo'],
	];


	public function getById($id, LazyDataMapper\Suggestor $suggestor, LazyDataMapper\DataHolder $holder = NULL)
	{
		$holder = parent::getById($id, $suggestor, $holder);

		if ($suggestor->car) {
			$data = array_intersect_key(CarMapper::$data[static::$data[$id]['car']] ,array_flip($suggestor->car->getSuggestions()));
			$holder->car->setData($data);

			if ($suggestor->car->driver) {
				$data = array_intersect_key(DriverMapper::$data[$data['driver']] ,array_flip($suggestor->car->driver->getSuggestions()));
				$holder->car->driver->setData($data);
			}
		}

		return $holder;
	}


	public function getByIdsRange(array $ids, LazyDataMapper\Suggestor $suggestor, LazyDataMapper\DataHolder $holder = NULL)
	{
		$holder = parent::getByIdsRange($ids, $suggestor, $holder);

		if ($suggestor->car) {
			$suggestions = array_flip($suggestor->car->getSuggestions());
			foreach ($ids as $id) {
				$carId = static::$data[$id]['car'];
				$data = array_intersect_key(CarMapper::$data[$carId] ,$suggestions);
				$holder->car->setRelation($carId, $id)->setData([$carId => $data]);
			}
		}

		return $holder;
	}
}
