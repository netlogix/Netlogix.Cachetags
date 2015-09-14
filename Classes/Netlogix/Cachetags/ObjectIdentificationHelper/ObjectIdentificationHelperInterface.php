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

interface ObjectIdentificationHelperInterface {

	/**
	 * Returns cache cache tag parts for the given object if known, otherwise NULL.
	 *
	 * @param $object
	 * @return mixed
	 */
	public function identifyCacheTagForObject($object);

}