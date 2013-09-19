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

use Annotation;
use Exception;
use Reflection;
use ReflectionAnnotatedProperty;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

require_once(dirname(__FILE__) . '/../../../lib/addendum/annotations.php');


define('ANNOTATION_EXPOSE', 'Expose');
define('ANNOTATION_CLASSE', 'FieldClass');

define('FIELD_TYPE_OBJECT', 'object');

/**
 * Annotation attribute class
 * @see Addendum library
 */
class FieldClass extends Annotation { }

/**
 * Annotation for exclusion strategy
 * @see Addendum library
 */
class Expose extends Annotation { }

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
     * Convert json string to obect of specified class.
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

        $data = array();
        try {

            $class           = new ReflectionClass($object);
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


                if (gettype($val) != FIELD_TYPE_OBJECT) {
                    $data[$name] = $val;
                } else {
                    $data[$name] = $this->objectToArray($val);
                }
            }
        } catch (ReflectionException $e) {
            throw new Exception($e->getMessage());
        }

        return $data;
    }

    /**
     * Parse json object.
     * @since 1.0
     */
    private function parseJsonObject($jsonObject, $className)
    {

        $class  = new ReflectionClass($className);
        $object = $class->newInstanceWithoutConstructor();

        foreach ($jsonObject as $attr => $val) {
        	
        	//-- Get property
			$property = $class->getProperty($attr);
			
			//-- Get annotation if it exists
			$reflectedAttr = new ReflectionAnnotatedProperty($className, $attr);		
			
			//-- Apply @Expose exclusion contraint
			if($this->excludeNotExposed && !$reflectedAttr->hasAnnotation(ANNOTATION_EXPOSE)){
				continue;
			}
			
			//-- skip unknow field
			if(!$class->hasProperty($attr)){
				continue;
			}

            if (gettype($val) != FIELD_TYPE_OBJECT) {
                $this->_setPropertyValue($class, $object, $attr, $val);
            } else {

                try {

                    if ($reflectedAttr->hasAnnotation(ANNOTATION_CLASSE)) {
                        $name = $reflectedAttr->name;
                        $type = $reflectedAttr->getAnnotation(ANNOTATION_CLASSE)->value;

                        if ($attr == $name) {
                            $obj = $this->parseJsonObject($val, $type);
                            $this->_setPropertyValue($class, $object, $attr, $obj);
                        }
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
