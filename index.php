<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('vendor/autoload.php');

/**
 * Class User
 *
 * @collection User
 * @primary id
 *
 * @property string     $id       Identifier
 * @property string     $name     Just a name
 * @property int        $age      Just an age
 * @property array      $pets     Users pet list
 * @property Country    $country  Users pet list
 */
class User extends \MongoStar\Model {}

/**
 * Class User
 *
 * @collection Country
 *
 * @property string    $id
 * @property string    $name Country name
 * @property City    $city City Id
 */
class Country extends \MongoStar\Model {}

/**
 * Class User
 *
 * @collection City
 *
 * @property string    $id  identifier
 * @property string    $name City name
 */
class City extends \MongoStar\Model {}

MongoStar\Config::setConfig([
    'driver'   => 'mongodb',
    'server'   => 'mongodb://localhost:27017',
    'db'       => 'dbname',
    'username' => 'username',
    'password' => 'password',
]);

// MongoStar\Model::setDriver(new CustomDriver());

//$country = Country::fetchObject([
//    'name' => 'Russia'
//]);

$users = User::fetchAll();

var_dump(count($users)); die();

$user = $users[0];

// var_dump($user); die();

echo $user->country->name . "\n\n";


foreach (User::fetchAll() as $user) {

}

foreach (User::fetchAll() as $user) {
    echo $user->country->name . "\n\n";
}


die();

//var_dump($user); die();




if ($country) {

    die("Ok");
}

die("No country");
