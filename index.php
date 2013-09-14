

<?php

require 'Pson.class.php';

	class User{
		
		private $_nom;
		public $_prenom;
		
		/**
		 * @FieldClass('User')
		 */
		public $_user;
		
		/**
		 * @FieldClass('Voiture')
		 */
		private $_voiture;
		
		// other fiedls or methods ... 
	}

	class Voiture {
		public $_modele;
		public $_prix;
	}

	$json = '{"_nom":"mike","_prenom":"phoenix","_voiture":{"_modele":"Audi A4","_prix":20000},"_voiture2":{"_modele":"Audi A3","_prix":10000}}';
	
	$pson = new Pson();
	
	$user = $pson->fromJSON($json,'User');
	
	echo "<pre>"; print_r($user);
	echo "<pre>"; print_r($pson->toJSON($user));


	
