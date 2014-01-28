<?php

namespace Shelter;

class Identifier implements IIdentifier
{

	/** @var int top level operand counter */
	static protected $counter = array();

	/** @var string */
	protected $identifier;


	/**
	 * Computes output identifier based on inputs.
	 * @param string $entityClass
	 * @param bool $isContainer
	 * @param IIdentifier $parentIdentifier
	 * @param string $sourceParam
	 */
	public function __construct($entityClass, $isContainer = FALSE,  IIdentifier $parentIdentifier = NULL, $sourceParam = NULL)
	{
		$identifier = $entityClass;
		$identifier .= $isContainer ? '*' : '';
		$identifier .= NULL !== $sourceParam ? "|$sourceParam" : '';
		$identifier .= $parentIdentifier ? '>' . $parentIdentifier->composeIdentifier() : '';
		$this->identifier = $identifier;
	}


	/**
	 * @return string
	 */
	function composeIdentifier()
	{
		return $this->identifier;
	}
}
