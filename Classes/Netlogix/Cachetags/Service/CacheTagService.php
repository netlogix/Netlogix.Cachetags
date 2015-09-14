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

use TYPO3\Flow\Annotations as Flow;
use Netlogix\Cachetags\ObjectIdentificationHelper\DomainObjectIdentificationHelper;
use Netlogix\Crud\ObjectIdentificationHelper\DataTransferObjectIdentificationHelper;

class CacheTagService implements CacheTagServiceInterface {

	/**
	 * @Flow\InjectConfiguration(package="Netlogix.Cachetags")
	 */
	protected $settings;

	/**
	 * @var \TYPO3\Flow\Object\ObjectManagerInterface
	 * @Flow\Inject
	 */
	protected $objectManager;

	/**
	 * @var \TYPO3\Flow\Cache\CacheManager
	 * @Flow\Inject
	 */
	protected $cacheManager;

	/**
	 * @var \Netlogix\Cachetags\ObjectIdentificationHelper\ObjectIdentificationHelperInterface[]
	 */
	protected $objectIdentificationHelpers;

	/**
	 * @var array<bool>
	 */
	protected $cacheIdentifierDefaults = [];

	/**
	 * An array of environments.
	 *
	 * The $this->environments[0] is currently active. All others are surrounding
	 * environments.
	 *
	 * @var array
	 */
	protected $environments = [[]];

	public function initializeObject() {
		foreach($this->settings['objectIdentificationHelpers'] as $objectIdentificationHelper) {
			$this->objectIdentificationHelpers[$objectIdentificationHelper] = $this->objectManager->get($objectIdentificationHelper);
		}
	}

	/**
	 * Creates a new cache tag environment
	 *
	 * @return void
	 */
	public function openEnvironment() {
		array_unshift($this->environments, []);
	}

	/**
	 * Closes the current cache tag environment.
	 *
	 * @return void
	 */
	public function closeEnvironment() {
		array_shift($this->environments);
	}

	/**
	 * Returns all cache tags being applied to this environment.
	 *
	 * @return array
	 */
	public function getEnvironmentTags() {
		return array_merge($this->environments[0]);
	}

	/**
	 * Creates a cache tag based on various input types.
	 *
	 * string, integer, float:
	 *     This one is used as cache tag. Add e.g. "AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA" or "mycachetag".
	 *
	 * AbstractDomainObject:
	 *     The object is converted to the "AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA".
	 *
	 * DataTransferInterface:
	 *     Its very payload is used like normal DomainObjects.
	 *
	 * array, Iterator:
	 *     All segments are joined by the underscore character, individuals
	 *     are treated according to the definition from above.
	 *
	 * @param mixed $params
	 * @return string
	 */
	public function createCacheIdentifier($params) {
		$cacheIdentifierModifiers = array(
			'params' => $params,
			'domain' => (isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST']),
		);

		$cacheIdentifier =  md5(join('_', $this->createCacheTagsInternal($cacheIdentifierModifiers)));
		return $cacheIdentifier;
	}

	/**
	 * Creates cache tags based on various input types.
	 *
	 * string, integer, float:
	 *     This one is used as cache tag. Add e.g. "AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA" or "mycachetag".
	 *
	 * AbstractDomainObject:
	 *     The object is converted to the "AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA".
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
	public function createCacheTags($params) {
		$cacheTags = $this->createCacheTagsInternal($params);
		foreach ($cacheTags as &$cacheTag) {
			$cacheTag = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $cacheTag);
			if (strlen($cacheTag) > 250) {
				$cacheTag = substr($cacheTag, 0, 100) . md5($cacheTag) . substr($cacheTag, -100);
			}
		}
		return $cacheTags;
	}

	/**
	 * Adds new cache tags to all open environments, which also includes
	 * the page cache. This does not cover sibling environments and even
	 * not child environment. If a child environment needs to be tagged
	 * as well, this method must be added there, too.
	 *
	 * @param mixed $objectOrCacheTag
	 * @return void
	 */
	public function addEnvironmentCacheTag($objectOrCacheTag) {
		$tagNames = $this->createCacheTags($objectOrCacheTag);
		foreach ($this->environments as &$environment) {
			foreach ($tagNames as $tagName) {
				$environment[$tagName] = $tagName;
			}
		}
	}

	/**
	 * Flushes entries tagged by the specified tag of all registered
	 * caches.
	 *
	 * @param mixed $objectOrCacheTag
	 */
	public function flushCachesByTag($objectOrCacheTag) {
		foreach ($this->createCacheTags($objectOrCacheTag) as $tag) {
			$this->cacheManager->flushCachesByTag($tag);
		}
	}

	/**
	 * Creates cache tags based on various input types.
	 *
	 * string, integer, float:
	 *     This one is used as cache tag. Add e.g. "AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA" or "mycachetag".
	 *
	 * AbstractDomainObject:
	 *     The object is converted to the "AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA".
	 *
	 * DataTransferInterface:
	 *     Its very innermostSelf is used like normal AbstractDomainObjects.
	 *
	 * array, Iterator:
	 *     All segments are joined by the underscore character, individuals
	 *     are treated according to the definition from above.
	 *
	 * @param mixed $params
	 * @return array
	 */
	protected function createCacheTagsInternal($params) {
		$cacheParts = [];
		if (is_array($params) && count($params) === 1) {
			$params = reset($params);
		}

		if ($params === NULL) {
			return [];
		} elseif (is_scalar($params)) {
			$cacheParts[] = $params;
		} elseif (is_array($params)) {
			foreach ($params as $cachePartSource) {
				$cachePart = $this->createCacheTagsInternal($cachePartSource);
				if ($cachePart !== NULL) {
					$cacheParts = array_merge($cacheParts, $cachePart);
				}
			}
		} elseif (is_object($params)) {
			foreach ($this->objectIdentificationHelpers as $objectIdentificationHelper) {
				$identifiedContent = $objectIdentificationHelper->identifyCacheTagForObject($params);
				if ($identifiedContent) {
					$cacheParts = array_merge($cacheParts, $this->createCacheTagsInternal($identifiedContent));
				}
			}
		}

		return array_unique($cacheParts);
	}

}