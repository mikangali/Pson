

<?php

require 'Pson.class.php';
	/**
	 * @Field(name='_voiture', class='Voiture')
	 * @Field(name='_voiture2', class='Voiture')
	 *
	 * @Fields({name='_user', class='Mike'},{name='_user', class='Mike'})
	 */
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
		
		/**
		 * @FieldClass('Voiture')
		 */
		private $_voiture2;

		function User(){ 
			$this->_voiture = new Voiture();
		}
	}

	class Voiture {
		public $_modele;
		public $_prix;
	}

	$json = '{"_nom":"mike","_prenom":"phoenix","_voiture":{"_modele":"Audi A4","_prix":20000},"_voiture2":{"_modele":"Audi A3","_prix":10000}, "_user":{"_nom":"TEST"}}';
	
	$pson = new Pson();
	
	$user = $pson->fromJSON($json,'User');
	
	echo "<pre>"; print_r($user);
	echo "<pre>"; print_r($pson->fromJSON($pson->toJSON($user), 'User'));


	
