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


/*
 * Include Addendum library
 */
require_once(dirname(__FILE__) . '/../../../lib/addendum/annotations.php');

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


?>