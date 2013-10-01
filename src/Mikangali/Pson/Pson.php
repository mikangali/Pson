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
 * */
namespace Mikangali\Pson;

require_once(dirname(__FILE__) . '/Annotations.php');

use Exception;
use Reflection;
use ReflectionAnnotatedProperty;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

const ANNOTATION_EXPOSE 	= "Expose";
const ANNOTATION_CLASS 		= "FieldClass";

/**
 * Pson class lib main class.
 * Pson is typically used by first constructing a Pson instance and then
 * invoking {@link #toJson(Object)} or {@link #fromJson(String, Class)} methods on it.
 *
 * @author @mikamboo
 *
 */
class Pson {

    private $serializeNulls;
    private $excludeNotExposed;
    private $exclusionModifiers;
    
    /**
     * Constructs a Pson object with specific configuration if user provides params,
     * or constructs a Gson object with default configuration.
     * @param boolean $serializeNulls 	: Ask Pson to serialize null fields
     * @param array $exclusionModifiers : List of excluded modifiers eg. array('protected', 'static', 'private')
     * @param boolean $excludeFieldsWithoutExpose : Ask Pson to skip field without @Expose annotation 
     */
    function __construct(){
    	$this->serializeNulls 		= false;
    	$this->excludeNotExposed 	= false;
    	$this->exclusionModifiers 	= array('protected');
    } 

    /**
     * Exclude null fiels from serialization
	 * @param boolean $serializeNulls
	 */
	public function setSerializeNulls($serializeNulls) {
		$this->serializeNulls = $serializeNulls;
	}

	/**
	 * Exclude fiels without @Expose annotation from serialization
	 * @param boolean $excludeNotExposed
	 */
	public function setExcludeNotExposed($excludeNotExposed) {
		$this->excludeNotExposed = $excludeNotExposed;
	}

	/**
	 * Set list of modifiers to exude from serillization, eg. array('protected', 'static', 'private')
	 * @param multitype:string  $exclusionModifiers
	 */
	public function setExclusionModifiers($exclusionModifiers) {
		$this->exclusionModifiers = is_array($exclusionModifiers) ? $exclusionModifiers : array('protected');
	}

	/**
     * Convert json to object instance of specified class.
     * @param json $json String formated in Json
     * @param String $className Destination name class
     * @thows Exception if $json provided is invalide
     * @since 1.0
     */
    public function fromJson($json, $className)
    {

        //-- Check if class name is accessible
        if (!class_exists($className)) {
            throw new Exception("Class '$className' not found.");
        }

        //-- Get object from json
        $jsonObject = json_decode($json);

        //-- Check if json is valide
        if (gettype($jsonObject) != "object") {
            throw new Exception("Invalide json string provided: $json");
        }

        //-- Convert json object to provided type
        return $this->parseJsonObject($jsonObject, $className);
    }
    
    /**
     * Convert json to objects array of specified class.
     * @param json $json String formated in Json
     * @param String $className Destination name class
     * @thows Exception if $json provided is not an array of objects
     * @since 1.0
     */    
    public function fromJsonArray($json, $className)
    {
    	//-- Get object from json
    	$jsonArray = json_decode($json);
    
    	//-- Check if class name is accessible
    	if (!class_exists($className)) {
    		throw new Exception("Optput class '$className' not found.");
    	}
    
    	//-- Check if json is valide
    	if (gettype($jsonArray) == "array") {
    
    		$result = array();
    		foreach ($jsonArray as $object) {
    			if (gettype($object) != "object") {
    				//-- the json must be a simple array of objects
    				throw new Exception("json provided is not an array of objects !");
    			}
    			$result[] = $this->parseJsonObject($object, $className);
    		}
    	} else{
    		//-- the json must be a simple array of objects
    		throw new Exception("json provided is not an array of objects !");
    	}
    
    	//-- Return array of object
    	return $result;
    }
    
	/**
     * Extract json sub-element from a json string or an object.
     * 
     * @param object|string $source json
     * @param string $key identifier
     * 
	 * @return NULL|string the json extracted
	 * @since 1.0
 	 */
    public function getJson($source, $key)
    {
    	//-- Transform source element to object
    	$object = $source;
    	if (gettype($source) != "object") {
    		$object = json_decode((string)$source);
    		if (gettype($object) != "object") {
    			return NULL;
    		}
    	} 
    	
    	//-- Get property from object
    	$className = get_class($object);
    	
    	$json = null;
    	if ($className == "stdClass") {
    		if (isset($object->$key)) {
    			$json = $this->toJson($object->$key);
    		} 
    	} else{
    		
    		$class = new ReflectionClass($className);
    		if ($class->hasProperty($key)) {
    			$property = $class->getProperty($key);
    			$property->setAccessible(true);
    			$value = $property->getValue($object);
    			$json = $json = $this->toJson($value);
    		}
    	}
    	return $json;
    }

    /**
     * Convert Object to json string.
     * @param $object Object to convert
     * @since 1.0
     */
    public function toJson($object)
    {
        $data = $this->objectToArray($object);
        return json_encode($data);
    }

    /**
     * Parse object, access private fields.
     * @return Array of object data
     * @since 1.0
     */
    private function objectToArray($object)
    {
		
    	if (gettype($object) != "object"){
    		return $object;
    	}
    	
    	$data = array();
    	
    	// default type (stdClass) php object
    	if (get_class($object) == "stdClass") {
    		
    		foreach ($object as $key => $val) {
    			
    			//-- Apply serializeNulls constraint
    			if (!$this->serializeNulls && $val == null) {
    				continue;
    			}
    			
    			if (gettype($val) != "object") {
    				$data[$key] = $val;
    			} else {
    				$data[$key] = $this->objectToArray($val);
    			}
    		}
    	} else {
    		
	        try {
	
	            $class = new ReflectionClass($object);
	            $classPorperties = $class->getProperties();
	
	            foreach ($classPorperties as $property) {
	
	                //-- Apply modifiers exclusion strategy
	                if ($this->propertyExcluded($property)) {
	                    continue;
	                }
	
	                $property->setAccessible(true);
	                $name = $property->getName();
	                $val  = $property->getValue($object);
	
	                //-- Apply serializeNulls constraint
	                if (!$this->serializeNulls && $val == null) {
	                    continue;
	                }
	
	                if (gettype($val) != "object") {
	                    $data[$name] = $val;
	                } else {
	                    $data[$name] = $this->objectToArray($val);
	                }
	            }
	        } catch (ReflectionException $e) {
	            throw new Exception($e->getMessage());
	        }
    	}
        return $data;
    }

    /**
     * Parse json object.
     * @see #fromJson(), #fromJsonArray()
     * @since 1.0
     */
    private function parseJsonObject($jsonObject, $className)
    {

        $class  = new ReflectionClass($className);
        $object = $class->newInstanceWithoutConstructor();

        foreach ($jsonObject as $attr => $val) {
        	
			//-- skip unknow field
			if(!$class->hasProperty($attr)){
				continue;
			}
			
        	//-- Get property
			$property = $class->getProperty($attr);
			
			//-- Get annotation if it exists
			$reflectedAttr = new ReflectionAnnotatedProperty($className, $attr);	
			
			//-- Apply @Expose exclusion contraint
			if($this->excludeNotExposed && !$reflectedAttr->hasAnnotation(ANNOTATION_EXPOSE)){
				continue;
			}
			
			//TODO :  if (gettype($val) == "array of objects") ?
			
            if (gettype($val) != "object") {
                $this->_setPropertyValue($class, $object, $attr, $val);
            } else {

                try {

                    if ($reflectedAttr->hasAnnotation(ANNOTATION_CLASS)) {
                    	//-- Get prerty type from annotation
                        $type = $reflectedAttr->getAnnotation(ANNOTATION_CLASS)->value;
                        
                        $obj = $this->parseJsonObject($val, $type);
                        $this->_setPropertyValue($class, $object, $attr, $obj);
                    } else {
                    	
                        //-- No 'FieldClass' annotation found
                        $property->setAccessible(true);
                        $property->setValue($object, $val);
                    }
                } catch (ReflectionException $e) {
                    //echo $e->getMessage();
                    //-- attr not in class model
                }
            }
        }
        return $object;
    }

    /**
     * ReflectionClass property setter util function.
     * @since 1.0
     */
    private function _setPropertyValue(ReflectionClass $class, $object, $attr, $val)
    {

        if ($class->hasProperty($attr)) {
            $property = $class->getProperty($attr);
            $property->setAccessible(true);
            $property->setValue($object, $val);
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Check if a property modifer processing is allowed.
     * @param ReflectionProperty $property
     * @return boolean
     * @since 1.0
     */
    function propertyExcluded(ReflectionProperty $property)
    {
        foreach ($this->exclusionModifiers as $excluded) {
            $modifiers = Reflection::getModifierNames($property->getModifiers());

            if (in_array($excluded, $modifiers)) {
                return TRUE;
            }
        }
        return FALSE;
    }

}
