<?php

/**
 * Pson PHP Json mapper Library
 * https://github.com/mikangali-labs/pson
 *
 * Copyright (C) 2013 Mikangali Labs <labs.mikangali.com>

 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.

 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.

 * You should have received a copy of the GNU Lesser General Public.
 *
 **/

namespace Mikangali\Pson;

use Mikangali\Pson\Pson;

/**
 * Use this builder to construct a Pson instance when you need to set configuration options other than the default
 * @author @mikamboo
 */
class PsonBuilder {
	
	private $pson;

	function __construct(){ 
		$this->pson = new Pson();
	}

	/**
	 * Configure Pson to serialize null fields.
	 * @return PsonBuilder
	 */
	public function serializeNulls() {
		$this->pson->setSerializeNulls(true);
		return $this;
	}

	/**
	 * Configures Pson to exclude all fields from consideration for serialization or deserialization
	 * that do not have the Expose annotation.
	 * @return PsonBuilder
	 */
	public function excludeFieldsWithoutExposeAnnotation() {
		$this->pson->setExcludeNotExposed(true);
		return $this;
	}
	
	/**
	 * Configures Pson to excludes all class fields that have the specified modifiers.
	 * @param arry $modifiers : Array of property mofiers, eg. array('private', 'static')
	 * @return PsonBuilder
	 */
	public function excludeFieldsWithModifiers($modifiers){
		$this->pson->setExclusionModifiers($modifiers);
		return $this;
	}

	/**
	 * Creates a Pson instance based on the current configuration.
	 * @return Pson
	 */
	public function build(){
		return $this->pson;
	}
}