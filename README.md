Pson
====

A PHP library to convert an Object into its JSON representation and vice versa. Use Addendum PHP Lib.
The librairy try to do the same job that Java Gson Library.

Version : 1.0
Require : PHP 5.3.2 +
____

### Features

* Convert object to json and Json to object
* Convert PHP object containning private fiels
* Use annotation to serialize/deserialize complex class fileds

____

### Package content 

* ./Pson.class.php 	: Pson lib class
* ./addendum/ 		: Addendum lib used by Pson
* ./index.php		: Pson usage sample

____

### Usage

				<?php
				
				//-- Include Pson Lib
				require 'Pson.class.php';
				
				//-- Simple PHP modele class
				class User{
		
					private $_nom;			//-- private field
					public $_prenom;
						
					/**
					 * @FieldClass('Voiture')
					 */
					private $_voiture;		//-- complex type field
				
				}
				
				class Voiture {
					public $_modele;
					public $_prix;
				}			
				
				//-- json string
				$json = '{"_nom":"mike","_prenom":"phoenix","_voiture":{"_modele":"Audi A4","_prix":20000},"_voiture2":{"_modele":"Audi A3","_prix":10000}}';
				
				$pson = new Pson();
				
				$user = $pson->fromJSON($json,'User');
				
				//-- #1
				echo "<pre>"; print_r($user);
				
				//-- #2
				echo "<pre>"; print_r($pson->toJSON($user));

### Ouput

				//-- #1
				User Object
				(
				    [_nom:User:private] => mike
				    [_prenom] => phoenix
				    [_user] => 
				    [_voiture:User:private] => Voiture Object
				        (
				            [_modele] => Audi A4
				            [_prix] => 20000
				        )
				
				)
				
				//-- #2
				{"_nom":"mike","_prenom":"phoenix","_user":null,"_voiture":{"_modele":"Audi A4","_prix":20000}}


_____


(c) 2013 [Mikangali Labs](http://mikangali.com)
