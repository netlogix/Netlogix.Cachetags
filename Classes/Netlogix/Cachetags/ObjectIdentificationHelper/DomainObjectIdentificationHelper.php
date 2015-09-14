<?php
namespace Netlogix\Cachetags\ObjectIdentificationHelper;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Netlogix.Cachetags".    *
 * It's a forward port of the TYPO3 extension EXT:nxcachetags             *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Doctrine\ORM\Mapping as Doctrine;
use TYPO3\Flow\Annotations as Flow;

class DomainObjectIdentificationHelper implements ObjectIdentificationHelperInterface {

	/**
	 * @var \TYPO3\Flow\Reflection\ReflectionService
	 * @Flow\Inject
	 */
	protected $reflectionService;

	/**
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 * @Flow\Inject
	 */
	protected $persistenceManager;

	/**
	 * Returns cache cache tag parts for the given object if known, otherwise NULL.
	 *
	 * @param $object
	 * @return mixed
	 */
	public function identifyCacheTagForObject($object) {
		$className = get_class($object);
		if (
			property_exists($object, 'Persistence_Object_Identifier') ||
			$this->reflectionService->isClassAnnotatedWith($className, Flow\Entity::class) ||
			$this->reflectionService->isClassAnnotatedWith($className, Flow\ValueObject::class) ||
			$this->reflectionService->isClassAnnotatedWith($className, Doctrine\Entity::class)
		) {
			$identifier = $this->persistenceManager->getIdentifierByObject($object);
			return $className . '_' . $identifier;
		}
	}
}