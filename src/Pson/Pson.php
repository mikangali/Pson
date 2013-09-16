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
namespace Pson;

use Addendum;
use Annotation;
use Exception;
use Reflection;
use ReflectionAnnotatedProperty;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use ReflectionProperty as ReflectionProperty2;

require_once(dirname(__FILE__) . '/../../lib/addendum/annotations.php');

define('FIELD_CLASS_NAME', 'FieldClass');
define('FIELD_TYPE_OBJECT', 'object');

/**
 * Annotation attribute class
 * @see Addendum library
 */
class FieldClass extends Annotation {

}

/**
 * Pson class lib main class.
 * Pson is typically used by first constructing a Pson instance and then
 * invoking {@link #toJson(Object)} or {@link #fromJson(String, Class)} methods on it.
 *
 * @author @mikamboo
 *
 */
class Pson {

    private $serializeNulls     = true;
    private $exclusionModifiers = array('protected');

    /**
     * Convert json string to obect of specified class
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
        return $this->_parseJsonObject($jsonObject, $className);
    }

    /**
     * Convert Object to json string.
     * @param $object Object to convert
     * @since 1.0
     */
    public function toJson($object)
    {
        $data = $this->_objectToArray($object);
        return json_encode($data);
    }

    /**
     * Parse object, access private fields
     * @return Array of object data
     * @since 1.0
     */
    private function _objectToArray($object)
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
                    $data[$name] = $this->_objectToArray($val);
                }
            }
        } catch (ReflectionException $e) {
            throw new Exception($e->getMessage());
        }

        return $data;
    }

    /**
     * Parse json object
     * @since 1.0
     */
    private function _parseJsonObject($jsonObject, $className)
    {

        $class  = new ReflectionClass($className);
        $object = $class->newInstanceWithoutConstructor();

        foreach ($jsonObject as $attr => $val) {
            //-- skip unknow field
            if (!$class->hasProperty($attr)) {
                continue;
            }

            //-- Get property
            $property = $class->getProperty($attr);

            if (gettype($val) != FIELD_TYPE_OBJECT) {
                $this->_setPropertyValue($class, $object, $attr, $val);
            } else {

                try {

                    $reflectedAttr = new ReflectionAnnotatedProperty($className, $attr);

                    if ($reflectedAttr->hasAnnotation(FIELD_CLASS_NAME)) {
                        $name = $reflectedAttr->name;
                        $type = $reflectedAttr->getAnnotation(FIELD_CLASS_NAME)->value;

                        if ($attr == $name) {
                            $obj = $this->_parseJsonObject($val, $type);
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
     * ReflectionClass property setter util function
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
     * Check if a property modifer processing is allowed
     * @param ReflectionProperty $property
     * @return boolean
     * @since 1.0
     */
    function propertyExcluded(ReflectionProperty2 $property)
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
