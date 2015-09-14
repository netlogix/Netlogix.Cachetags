<?php
namespace Netlogix\Cachetags\Service;

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

interface CacheTagServiceInterface {

	/**
	 * Creates a new cache tag environment
	 *
	 * @return void
	 */
	public function openEnvironment();

	/**
	 * Closes the current cache tag environment.
	 *
	 * @return void
	 */
	public function closeEnvironment();

	/**
	 * Returns all cache tags being applied to this environment.
	 *
	 * @return array
	 */
	public function getEnvironmentTags();

	/**
	 * Creates a cache tag based on various input types.
	 *
	 * string, integer, float:
	 *     This one is used as cache tag. Add e.g. "pages_5" or "mycachetag".
	 *
	 * AbstractDomainObject:
	 *     The object is converted to the "$tableName_$uid", just like the
	 *     TCEmain/DataHandler acts.
	 *
	 * DataTransferInterface:
	 *     Its very payload is used like normal AbstractDomainObjects.
	 *
	 * array, Iterator:
	 *     All segments are joined by the underscore character, individuals
	 *     are treated according to the definition from above.
	 *
	 * @param mixed $params
	 * @return string
	 */
	public function createCacheIdentifier($params);

	/**
	 * Creates cache tags based on various input types.
	 *
	 * string, integer, float:
	 *     This one is used as cache tag. Add e.g. "pages_5" or "mycachetag".
	 *
	 * AbstractDomainObject:
	 *     The object is converted to the "$tableName_$uid", just like the
	 *     TCEmain/DataHandler acts.
	 *
	 * DataTransferInterface:
	 *     Its very payload is used like normal AbstractDomainObjects.
	 *
	 * array, Iterator:
	 *     All segments are joined by the underscore character, individuals
	 *     are treated according to the definition from above.
	 *
	 * @param mixed $params
	 * @return array
	 */
	public function createCacheTags($params);

	/**
	 * Adds new cache tags to all open environments, which also includes
	 * the page cache. This does not cover sibling environments and even
	 * not child environment. If a child environment needs to be tagged
	 * as well, this method must be added there, too.
	 *
	 * @param mixed $objectOrCacheTag
	 * @return void
	 */
	public function addEnvironmentCacheTag($objectOrCacheTag);

	/**
	 * Flushes entries tagged by the specified tag of all registered
	 * caches.
	 *
	 * @param mixed $objectOrCacheTag
	 */
	public function flushCachesByTag($objectOrCacheTag);

}