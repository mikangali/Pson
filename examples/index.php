
<?php

require '../src/Pson/Pson.class.php';

class User {

    private $_nom;
    public $_prenom;

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
$json = '{"_nom":"mike","_prenom":"brandon","_voiture":{"_modele":"Audi A4","_prix":20000}}';

$pson = new Pson();

$user = $pson->fromJson($json, 'User');

echo "<pre>";
print_r($user);
echo "<pre>";
print_r($pson->toJson($user));




