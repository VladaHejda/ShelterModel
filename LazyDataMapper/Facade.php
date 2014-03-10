<?php

namespace LazyDataMapper;

/**
 * Outer cover for getting operands (Entity or EntityContainer).
 * There are two ways of determining Entity / EntityContainer classname:
 * - override property $entityClass in the descendant of this class due to the array pattern:
 *   [<EntityClassname>, <EntityContainerClassname>]
 * - apply solution in IEntityServiceAccessor method getEntityClass() and getEntityContainerClass().
 *   There is some default solution.
 */
abstract class Facade implements IFacade
{

	/** @var array|string */
	protected $entityClass;

	/** @var Accessor */
	private $accessor;


	/**
	 * @param Accessor $accessor
	 * @param IEntityServiceAccessor $serviceAccessor
	 * @throws Exception
	 */
	public function __construct(Accessor $accessor, IEntityServiceAccessor $serviceAccessor = NULL)
	{
		$this->accessor = $accessor;
		if (NULL === $this->entityClass) {
			if (!$serviceAccessor) {
				$class = get_class($this);
				throw new Exception($class . ": inject IEntityServiceAccessor or fill the $class::\$entityClass property.");
			}
			$this->entityClass = $serviceAccessor->getEntityClass($this);
			if (NULL === $this->entityClass) {
				throw new Exception(get_class($this) . ": IEntityServiceAccessor::getEntityClass() does not return entity classname.");
			}
		}
	}


	/**
	 * @param int $id
	 * @return IEntity
	 */
	public function getById($id)
	{
		return $this->accessor->getById($this->entityClass, $id);
	}


	/**
	 * @param int[] $ids
	 * @return IEntityContainer
	 */
	public function getByIdsRange(array $ids)
	{
		return $this->accessor->getByRestrictions($this->entityClass, $ids);
	}


	/**
	 * @param IRestrictor $restrictor
	 * @return IEntityContainer
	 */
	public function getByRestrictions(IRestrictor $restrictor)
	{
		return $this->accessor->getByRestrictions($this->entityClass, $restrictor);
	}


	/**
	 * @param int $id
	 */
	public function remove($id)
	{
		$this->accessor->remove($this->entityClass, $id);
	}


	/**
	 * @param int[] $ids
	 */
	public function removeByIdsRange(array $ids)
	{
		$this->accessor->removeByRestrictions($this->entityClass, $ids);
	}


	/**
	 * @param IRestrictor $restrictor
	 */
	public function removeByRestrictions(IRestrictor $restrictor)
	{
		$this->accessor->removeByRestrictions($this->entityClass, $restrictor);
	}


	/**
	 * For creation it is recommended to create method self::create() in descendant
	 * and require mandatory parameters for new Entity.
	 * @param array $data
	 * @param bool $throwFirst whether throw first IntegrityException from Checker
	 * @return IEntity
	 */
	protected function createEntity(array $data = array(), $throwFirst = TRUE)
	{
		return $this->accessor->create($this->entityClass, $data, $throwFirst);
	}
}
