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

require_once(dirname(__FILE__).'/addendum/annotations.php');

define('FIELD_CLASS_NAME','FieldClass');
define('FIELD_TYPE_OBJECT','object');

/**
 * Annotation attribute class
 * @see Addendum library
 */
class FieldClass extends Annotation { }


/**
 * Pson class
 * @author Michael
 *
 */
class Pson {
    
    /**
     * Convert json string to obect of specified class
     * @thows Exception if $json provided is invalide
     */
    public function fromJSON($json, $className) {
        
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
        return $this->_parseJsonObject($jsonObject, $className);
    }
    
    /**
     * Convert Object to json string.
     * @param $object : Object to convert
     */    
    public function toJSON($object) {
		//TODO : Exclusion strategy
    	$data = $this->_objectToArray($object);
        return json_encode($data);
    }

    /**
     * Parse object, access private fields
     * @return Array of object data
     */
    private function _objectToArray($object){
    	
    	$data = array();
    	try{
    		
    		$class = new ReflectionClass($object);
    		$classPorperties = $class->getProperties();
    		
    		foreach ( $classPorperties as $property ) {
    			
    			$property->setAccessible(true);
    			
    			$name = $property->getName();
    			$val  = $property->getValue($object);

    			if(gettype($val) != FIELD_TYPE_OBJECT){
    				$data[$name] = $val;
    			}
    			else{
    				$data[$name] = $this->_objectToArray($val);
    			}
    		}
    	}
    	catch (ReflectionException $e){
    		throw new Exception($e->getMessage());
    	}
    	 
    	return $data;
    }
    
    /**
     * Parse json object
     */
	private function _parseJsonObject($jsonObject, $className){

		$class 	= new ReflectionClass($className);
		$object	= $class->newInstanceWithoutConstructor();
		
		foreach ($jsonObject as $attr => $val) 
		{
			if(gettype($val) != FIELD_TYPE_OBJECT){
				 $this->_setPropertyValue($class, $object, $attr, $val);
			}
			else{
				
				try {
					
					$reflectedAttr = new ReflectionAnnotatedProperty($className, $attr);	
					
					if($reflectedAttr->hasAnnotation(FIELD_CLASS_NAME)){
						$name 	= $reflectedAttr->name;
						$type 	= $reflectedAttr->getAnnotation(FIELD_CLASS_NAME)->value;				
		
						if($attr == $name){
							$obj = $this->_parseJsonObject($val, $type);
						 	$this->_setPropertyValue($class, $object, $attr, $obj);
						}
					} else{
						//TODO : traiter cas si pas d'annotation
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
	 * ReflectionClass property setter util function
	 */
	private function _setPropertyValue(ReflectionClass $class, $object, $attr, $val){
		
		if($class->hasProperty($attr)){
			$property = $class->getProperty($attr);
			$property->setAccessible(true);
			$property->setValue($object, $val);
			return TRUE;
		}
		return FALSE;
	}	
	
}


