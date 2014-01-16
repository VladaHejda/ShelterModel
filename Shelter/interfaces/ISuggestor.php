<?php

namespace Shelter;

/**
 * Suggests parameter names and descendants to Mapper.
 */
interface ISuggestor extends \Iterator
{

	/**
	 * @param string $type
	 * @return bool
	 */
	function isSuggestedType($type);


	/**
	 * If is separated by type but type is omitted, it returns all param names merged.
	 * @param string $type
	 * @return string[]
	 */
	function getParamNames($type = NULL);


	/**
	 * @return string
	 */
	function getIdentifier();


	/**
	 * Says whether has at least one descendant.
	 * @return bool
	 */
	function hasDescendants();


	/**
	 * @param string $entityClass
	 * @param string $sourceParam
	 * @return self
	 * @throws Exception on unknown descendant
	 */
	function getDescendant($entityClass, &$sourceParam = NULL);


	/**
	 * @return IParamMap
	 */
	function getParamMap();
}
