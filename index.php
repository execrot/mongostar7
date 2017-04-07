<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('vendor/autoload.php');

/**
 * Class User
 *
 * @collection User
 *
 * @property string     $id       Identifier
 * @property string     $name     Just a name
 * @property int        $age     Just an age
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

class UserMap extends \MongoStar\Map
{
    public function common() : array
    {
        return [
            'id' => 't-id',
            'name' => 't-name',
            'country' => 't-country'
        ];
    }
}

MongoStar\Config::setConfig([
    'driver'   => 'mongodb',
    'server'   => 'mongodb://localhost:27017',
    'db'       => 'dbname',
    'username' => 'username',
    'password' => 'password',
]);

// $users = [];

//for ($i=0; $i<10; $i++) {
//
//    $user = new User();
//
//    $user->name = 'edward#'.($i+1);
//    $user->age = $i +1;
//
//    $user->save();
//}




//User::remove();

$user = User::fetchOne();

var_dump(UserMap::execute($user)->toArray());
