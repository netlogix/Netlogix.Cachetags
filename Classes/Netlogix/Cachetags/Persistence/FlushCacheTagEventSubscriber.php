<?php
namespace Netlogix\Cachetags\Persistence;

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

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Netlogix\Cachetags\Service\CacheTagService;
use TYPO3\Flow\Annotations as Flow;

class FlushCacheTagEventSubscriber implements EventSubscriber {

	/**
	 * @var CacheTagService
	 * @Flow\Inject
	 */
	protected $cacheTagService;

	/**
	 * @var \TYPO3\Flow\Persistence\PersistenceManagerInterface
	 * @Flow\Inject
	 */
	protected $persistenceManager;

	/**
	 * @inheritdoc
	 * @return array
	 */
	public function getSubscribedEvents() {
		return ['postRemove', 'postPersist', 'postUpdate'];
	}

	/**
	 * Flush cache after the entity has been deleted
	 * @param LifecycleEventArgs $eventArgs
	 */
	public function postRemove(LifecycleEventArgs $eventArgs) {
		$this->cacheTagService->flushCachesByTag($eventArgs->getObject());
	}

	/**
	 * Flush cache after the entity has been made persistent
	 * @param LifecycleEventArgs $eventArgs
	 */
	public function postPersist(LifecycleEventArgs $eventArgs) {
		$this->cacheTagService->flushCachesByTag($eventArgs->getObject());
	}

	/**
	 * Flush cache after the database update operations to entity data
	 * @param LifecycleEventArgs $eventArgs
	 */
	public function postUpdate(LifecycleEventArgs $eventArgs) {
		$this->cacheTagService->flushCachesByTag($eventArgs->getObject());
	}

}