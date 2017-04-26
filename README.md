# MongoStar
ORM for the MongoDb v>=3 and the file-database (JSON)

> MongoStar its an amazing ORM for building web apps based on php and mongodb.
> Currenty we are using this ORM for all internal projects 
> (c) Seergey Bryn, SEO Googl Inc

## Requirements:
  - PHP v7.0 or better
  - MongoDb version 3.0 or better
  - PHP Extension MongoDb 1.2 or better
  - PHP Extension OpenSSL (any version)

## Instaliation via composer
```javascript
{
    "repositories": [
        {
            "url": "https://github.com/execrot/mongostar7.git",
            "type": "git"
        }
    ],
    "require": {
        "mongostar/php7": "master-dev"
    }
}
```

Run:

```sh
$ composer install
```

---

# Overview
> Just scroll down to the **What we can do** section If you want to skip all this shit. :)

## 1. Model

**\MongoStar\Model** it is facade of the several classes **Driver**, **Cursor**, **Document**.
**Driver** works like a proxy for **Cursor** or **Document**.
**Cursor** works with collection of the **Documents**.
**Document** works with specific collection row.

Version 1.2 has two **Drivers** with own **Cursors** and **Documents**:
  - **Mongodb**
    -- \MongoStar\Model\Driver\ **Mongodb \ Driver**
    -- \MongoStar\Model\Driver\ **Mongodb \ Cursor**
    -- \MongoStar\Model\Driver\ **Mongodb \ Document**
  - **Flat**
    -- \MongoStar\Model\Driver\ **Flat \ Driver**
    -- \MongoStar\Model\Driver\ **Flat \ Cursor**
    -- \MongoStar\Model\Driver\ **Flat \ Document**

Also you can implement your own **Driver**, **Cursor** and **Document**, below is a description of how to do it.

### 1.2. Model scope

```php
// Get collection meta info (see \MongoStar\Model\Meta documentation)
public function getMeta(): Model\Meta;

// Get active document
public function getDocument() : Model\Driver\DocumentAbstract;

// Get active driver
public function getDriver() : Model\Driver\DriverAbstract;

// Sets the custom driver
public function setDriver(Model\Driver\DriverAbstract $myCustomDriver) : void;

// Get called Model class name
public function getModelClassName() : string;
```


### 1.3. Driver scope
```php
// Returns active model
public function getModel(): \MongoStar\Model;

// Sets the model
public function setModel(\MongoStar\Model $model) : void;

// Returns the Cursor with selected data
public function fetchAll($cond = null, $sort = null, int $count = null, int $offset = null) : Cursor;

// Returns the founded Document or NULL if nothing found
public function fetchOne($cond = null, $sort = null) : \MongoStar\Model | null;

// Returns the founded Document or an empty Document if nothing found
public function fetchObject($cond = null, array $sort = null) : \MongoStar\Model;

// Returns the count of the founded documents
public function count($cond = null) : int;

// Inserts the data and returns count of the inserted documents
public function batchInsert(array $data = null) : int;

// Saves the model
public function save() : void;

// Removes the founded documents and returns count of removed documents
public function remove($cond = null, int $limit = null) : int;
```

## 1.4. Cursor scope
```php
// Goes over all documents and builds an array with the Document in the array representation
public function toArray(): array;

// Returns active or empty model, uses to get model class name
public function getModel() : \MongoStar\Model;

// Sets the active or emptry model, uses to get model class name
public function setModel(\MongoStar\Model $model)

// Return cursor data in the array representation
public function getCursorData(): array

// Returns active row index
public function getCursorIndex(): int;

// Sets active row index
public function setCursorIndex(int $cursorIndex)

// Sets active row data
public function setCursorData(array $cursorData)

// Goes over the documents and call 'save' for each, returns number of the saved documents
public function save() : int;

// Initialize new \MongoStar\Model with provided data and consider index
public function getRowWithIndex(array $data, int $index) : \MongoStar\Model;

// Should maps provided data row and returns the results
// Overloaded in the \MongoStar\Model\Driver\Mongodb\Cursor
public function processDataRow(array $data) : array;
```

Also **CursorAbstract** implements [\IteratorIterator](http://php.net/manual/ru/class.iterator.php), [\ArrayAccess](http://php.net/manual/ru/class.arrayaccess.php), [\Countable](http://php.net/manual/ru/class.countable.php).
Mongodb's driver overloads these functioning.

## 1.5. Document scope
```php
// Returns active model or NULL
public function getModel() : \MongoStar\Model | null;

// Sets active model
public function setModel(\MongoStar\Model $model) : void;

// Returns model raw data
public function getData() : array;

// Sets raw data to the model data and initialize model
public function setData(array $data) : void;

// Sets data to the model data with datatypes coverting
public function populate(array $data) : void;

// Returns property value, in the consider datatype
public function getProperty(string $name, bool $toArray = false) : mixed;

/** Sets property (see \MongoStar\Model\Meta documentation) with provided value
  * $fromSet - tells that the function was called 
  * from magic __set function or no, the difference is the datatype cast */
public function setProperty(\MongoStar\Model\Meta\Property $property, $value, bool $fromSet = false) : void;

// Goes over all model properties and returns model data in the array representation
public function toArray(): array;

/** Must be overloaded from the children class, 
  * should returns the timestamp of the document.
  * Also its already overloaded in the Mongodb and Flat drivers */
abstract public function getTimestamp() : int;
```

Also **DocumentAbstract** implements [\ArrayAccess](http://php.net/manual/ru/class.arrayaccess.php).

---
## 2. Integration

Currently MongoStar can work with the MongoDb or use the files as database (data will be stored in the JSON, and can be secured). So you can specify what the storage you want to use in the project. You must set config in your project bootstrap/initialize moment.

### 2.1. Mongodb

**Configuration example:**
```php
<?php
MongoStar\Config::setConfig([
    'driver' => 'mongodb',
    'servers' => [
        ['host' => 'host1', 'port' => 'port1'],
        ['host' => 'host2', 'port' => 'port2']
    ],
    'replicaSetName' => 'rs0',
    'db' => 'dbname',
    'username' => 'username',
    'password' => 'password',
]);
```
**What it was?**

| Name | Description |
| ------ | ------ |
| **driver** | specify the database driver, can be 'mongodb' or 'flat' (about Flat we will talk later) |
| **servers** | array with the database servers, in case if you are using replica you can specify a few db servers |
| **replicaSetName** | unnecessary, if you are using the replica you should specify its name |
| **username** | unnecessary, db username |
| **password** | unnecessary, db password |


### 2.2. Flat

**Flat** - its a file database driver. 
 - Data stores in the JSON format.
 - Each collection stored at the own .json file.
 - Querying works the same as in the [MongoDb](https://docs.mongodb.com/manual/tutorial/query-documents/) driver. 
 - Collection and database will be created when you will try to save data.
 - [OpenSSL](https://www.openssl.org/) [Encryption](http://php.net/manual/ru/function.openssl-encrypt.php) and [Decryption](http://php.net/manual/ru/function.openssl-decrypt.php) supporting.
 

**Configuration example:**
```php
<?php
MongoStar\Config::setConfig([
    'driver'   => 'flat',
    'dir'      => __DIR__ . '/data',
    'db'       => 'dbname',
    'pretty'   => true,
    'secure'   => true,
    'method'   => 'AES-128-CBC-HMAC-SHA256',
    'username' => 'hitman',
    'password' => '123456',
]);
```
**What it was?**

| Name | Description |
| ------ | ------ |
| **driver** | specify the database driver, can be 'mongodb' or 'flat'. |
| **dir** | readable and writable dir for files stoeage, for each collection data will be saved in the {collection}.json. |
| **db** | database name, actually its a folder which will be created in the "dir" directory.  |
| **pretty** | unnecessary, will be saved JSON data in the pretty view. |
| **secure** | unnecessary, but if you want to protect you data you should set this option to "true" and specify 'method', 'username' and 'password' options. For encryption and decryption uses [OpenSSL](https://www.openssl.org/).  |
| **method** | unnecessary if the 'secure' option is 'false', an item from the [openssl_get_cipher_methods](http://php.net/manual/ru/function.openssl-get-cipher-methods.php) |
| **username** | unnecessary if the 'secure' option is 'false', will be applyed only first 16 symbols |
| **password** | unnecessary if the 'secure' option is 'false' |

---

## 3. How to use

You need to create the model class and extend it from the abstract \MongoStar\Model class.
```php
<?php
/**
 * Class User
 *
 * @collection User
 *
 * @primary id
 *
 * @property string     $id       
 * @property string     $name     
 * @property int        $age      
 * @property array      $pets     
 *
 * @method static User[]    fetchAll($cond = null, $sort = null, int $count = null, int $offset = null)
 * @method static User|null fetchOne($cond = null, $sort = null)
 * @method static User      fetchObject($cond = null, $sort = null)
 *
 * @method void save()
 */
class User extends \MongoStar\Model {}
```

**What it that?**

| DocsBlock tag               | Description |
| --------------------------- | ----------- |
| **@collection**             | collection name, if its will not be found at DocsBlock will be thrown CollectionNameDoesNotExists exception |
| **@primary**                | its something like primary key for the collection, if not specified will be used 'id' by default, and if you specify non-existing fields will be thrown CollectionCantBeWithoutPrimary exception (lets talk about 'primary' later) |
| **@property** [type] [name] | collection field, if no onw is found in the DocsBlock will be thrown PropertyWasNotFound exception |
| **@method**                 | unnecessary, just for autocompeting |

**Important**:
- For the MongoDb driver field names 'id' and '_id' is the same things, you must always use 'id'
- For the Flat driver 'id' its an autogenerated field. By results id will consists from " {[uniqid](http://php.net/manual/ru/function.uniqid.php)}{timestamp}", so you can call Document::getTimestamp function to get date created.
---

## 4. What we can do

### 4.1. Querying

**\MongoStar\Model::fetchAll** - returns the Cursor for the search results.

**Synopsis**
```php
public static \MongoStar\Model::fetchAll($cond = null, $sort = null, int $count = null, int $offset = null) : \MongoStar\Model[]
```

| Arg | Description |
| ------ | ------ |
| **$cond** | query condition (MongoDb style - ['field' => 'value'] and etc) |
| **$sort** | MongoDb styled sort parameter (like ['field' => -1 or 1 ] ) |
| **$count** | number of the search results |
| **$offset** | offset of the search results |

**Example**
```php
// Get all users
$users = Users::fetchAll(); 

// Get all users with name 'Edward'
$users = Users::fetchAll(['name' => 'Edward']); 

// Get all users with name 'Edward' and age greater than 20
$users = Users::fetchAll(['name' => 'Edward', 'age' => ['$gt' => 20]]);

// Get all users with name 'Edward' and age greater than 20, ordered by age DESC
$users = Users::fetchAll(['name' => 'Edward', 'age' => ['$gt' => 20]], ['age' => -1]);

// Get first 2 users with name 'Edward' and age greater than 20, ordered by age DESC
$users = Users::fetchAll(['name' => 'Edward', 'age' => ['$gt' => 20]], ['age' => -1], 2);

// Skipping 4 users and get first 2 users with name 'Edward' and age greater than 20, ordered by age DESC
$users = Users::fetchAll(['name' => 'Edward', 'age' => ['$gt' => 20]], ['age' => -1], 2, 4);  
```

---

**\MongoStar\Model::fetchOne** - returns the Document for the search results or NULL.
**\MongoStar\Model::fetchObject** - the same function, but if the search results will not be found instead of NULL will return empty \MongoStar\Model object (just new \MongoStar\Mondel()).

**Synopsis**
```php
public static \MongoStar\Model::fetchOne($cond = null, $sort = null) : \MongoStar\Model[]
```

| Arg | Description |
| ------ | ------ |
| **$cond** | query condition (MongoDb style - ['field' => 'value'] and etc) |
| **$sort** | MongoDb styled sort parameter (like ['field' => -1 or 1 ] ) |

**Example**

```php
// Get the first entry
$user = User::fetchOne();

// Get the first entry of the Joe users 
$userJoe = User::fetchOne(['name' => 'Joe']);

// Get the older Joe
$userJoe = User::fetchOne(['name' => 'Joe'], ['age' => -1]);
```

---

**\MongoStar\Model::count** - returns count of the search results. Also **Cursor** class implements **Countable** interface.

**Synopsis**
```php
public static \MongoStar\Model::count($cond = null) : int
```

| Arg | Description |
| ------ | ------ |
| **$cond** | query condition (MongoDb style - ['field' => 'value'] and etc) |

**Example**

```php
// Get total user count
$user = User::count();

// Get count of the Joe users
$userJoe = User::count(['name' => 'Joe']);

// Countable 
$users = User::fetchAll(['age' => ['$gt' => 1]]);
echo count($users);
```

---

**\MongoStar\Model::toArray** - returns data in the array representation. Works with the **Cursor** and **Document**.
**Cursor::toArray** - goes over all documents, call **Document::toArray** for the each, returns array of the document arrays.
**Document::toArray** - returns all object data in the assoc array representation.

**Synopsis**
```php
public \MongoStar\Model::toArray() : array
```

**Example**

```php
$users = User::fethAll()->toArray();
// will give us an array of the assoc arrays
[
    ['id' => 'id1', 'name' => 'Name1', 'age' => 1, ...],
    ['id' => 'id2', 'name' => 'Name2', 'age' => 2, ...],
    ...
];

$user = User::fetchOne(['id' => 'id1'])->toArray();
// will give us an assoc array
[
    'id' => 'id1', 
    'name' => 'Name1',
    'age' => 1,
    ...
];
```
---

### 4.2. Data operation examples
#### 4.2.1. Read
Cursor implements **\ArrayAccess** and **\Iterator** interfaces

```php
$users = Users:fetchAll();

foreach ($users as $user) {
    // TODO: Something with $user
}

// Get property value from the first entry
$users[0]->name;

// Set the property from the first entry
$users[0]->name = 'Vasya';

// Saves the document
$users[0]->save();

// Saves all document within the Cursor
$users->save();
```
#### 4.2.2. Write
```php
// First way
$user = new User();
$user->populate([
    'name' => 'Name1',
    'age' => 20
]);
$user->save();

// Second way
$user = new User();
$user->name = 'Name1';
$user->age = 20;
$user->save();

// Batch
$users = [
    ['name' => 'Name1', 'age' => 1],
    ['name' => 'Name2', 'age' => 2],
    ...
];
User::batchInsert($users);
```

#### 4.2.3. Remove
```php
// Removes all users with name = 'Name1'
User::remove(['name' => 'Name1']);

// Removes first entry user with name = 'Name1'
User::remove(['name' => 'Name1'], 1);

// Another way to remove the user
$user = User::fetchOne(['name' => 'Name1']);
$user->remove();
```

### 4.3. Relations 
**MongoStar supports relations** between the collections. Lets see how it looks like

UserModel.php
```php 
<?php
/**
 * @collection User
 *
 * @property string  $id       
 * @property string  $name     
 * @property Country $country  
 */
class UserModel extends \MongoStar\Model {}
```

CountryModel.php
```php
/**
 * @collection Country
 *
 * @property string $id
 * @property string $title
 */
class Country extends \MongoStar\Model {}
```
Pay your attension on the **User::$country** property, lets see what we can:
```php
$country = new Country();
$country->title = 'Uganda';
$country->save();

$user = new User();
$user->country = $country;
$user->name = 'Ivan';
$user->save();

$user = User::fetchOne(['name' => 'Ivan']);
$user->country->title;
// Gives us 'Uganda'

$user->toArray();
// Gives us:
[
    'id' => {userid}
    'name' => 'Ivan',
    'country' => [
        'id' => {countryid}
        'title' => 'Uganda'
    ]
]
```

**What was it?**

***Write magic*** - on the **Driver** level users country was saved just the country "id" ('id' because the primary field of the Country collection is 'id' by default, but if you need you can specify another field for the mapping as was written previously).

***Read magic*** - on the driver level when we try to get $user->country - was made an additional query Country::fetchObject(['id' => {user-country-id}]) and return the results.

---

## 5. Helper classes

### 5.1. DataMapper
**\MongoStar\Map** - it is data mapper for any data. Supports types **Cursor**, **Document**, **array**, **stdObject**.
Also its implements the **\Iterator** interface so you can "foreach" your mapper.

**Example**:
```php
<?php
class UserMap extends \MongoStar\Map
{
    /**
     * @return array
     */
    public function common () : array
    {
        return [
            'id'        => 'id',
            'name'      => 'fullname',
            'age'       => 'age',
            'countryId' => 'country',

            'nonExistingProperty' => 'additionalInfo'
        ];
    }
    
    /**
     * @return array
     */
    public function front () : array
    {
        return [
            'id' => 'id',
            'name' => 'name',
            'age' => 'age',
        ];
    }

    /**
     * @param mixed $userDataRow
     * @return string
     */
    public function getName($userDataRow)
    {
        return ucfirst($userDataRow['name']) . ' ' . ucfirst($userDataRow['surname']);
    }

    /**
     * @param mixed $userDataRow
     * @return string
     */
    public function getAge($userDataRow)
    {
        return $userDataRow['age'] . ' years old';
    }

    /**
     * @param $userDataRow
     * @return array
     */
    public function getCountry($userDataRow)
    {
        $country = CountryModel::fetchOne([
            'id' => $userDataRow['countryId']
        ]);

        return $country->toArray();
    }

    /**
     * @param mixed $userDataRow
     * @return string
     */
    public function getNonExistingProperty($userDataRow)
    {
        $externalData = $this->getUserData();
        
        if ($userDataRow['id'] == 1) {
            return $externalData['additional-important-data'];
        }
        return $externalData['another-data'];
    }
}
```
#### \MongoStar\Map::toArray()
```php

$usersData = [
    ['id' => 1, 'name' => 'vasya', 'surname' => 'pupkin', 'age' => 20, 'countryId' => 1],
    ['id' => 2, 'name' => 'fedya', 'surname' => 'visyin', 'age' => 23, 'countryId' => 1],
    ...
];

$userMapper = UserMap::execute($usersData, 'common', [
    'additional-important-data' => 'Some data',
    'another-data' => 'Another data'
]);

$userMapper->toArray();
// Gives us
[
    [
        'id' => 1,
        'fullname' => 'Vasya Pupkin',
        'age' => '20 years old',
        'country' => [
            'id' => 'country-id',
            'title' => 'country name'
        ],
        'additionalInfo' => 'Some data'
    ],
    [
        'id' => 2,
        'fullname' => 'Fedya Visyin',
        'age' => '23 years old',
        'country' => [
            'id' => 'country-id',
            'title' => 'country name'
        ],
        'additionalInfo' => 'Another data'
    ],
    ...
]

$usersData = [
    'id' => 1, 
    'name' => 'vasya', 
    'surname' => 'pupkin', 
    'age' => 20, 
    'countryId' => 1
];

$userMapper = UserMap::execute($usersData, 'common', [
    'additional-important-data' => 'Some data',
    'another-data' => 'Another data'
]);

$userMapper->toArray();
// Gives us:

[
    'id' => 1,
    'fullname' => 'Vasya Pupkin',
    'age' => '20 years old',
    'country' => [
        'id' => 'country-id',
        'title' => 'country name'
    ],
    'additionalInfo' => 'Some data'
]
```

#### Foreach, \Iterator

```php
$usersData = [
    ['id' => 1, 'name' => 'vasya', 'surname' => 'pupkin', 'age' => 20, 'countryId' => 1],
    ['id' => 2, 'name' => 'fedya', 'surname' => 'visyin', 'age' => 23, 'countryId' => 1],
    ...
];

$userMapper = UserMap::execute($usersData);

foreach ($userMapper as $userArrayData) {
    // TODO: Something with $userArrayData
}
```

#### \MongoStar\Model

```php

// With collection
$users = User::fetchAll();
$userMapper = UserMap::execute($users);

// Results will be the same as from array example
$userMapper->toArray();

// Results will be the same as from array example
foreach ($userMapper as $userArrayData) {
    // TODO: Something with $userArrayData
}


// With document
$user = User::fetchOne(['id' => 'user-id']);
$userMapper = UserMap::execute($user);

// Returns assoc array with mapped user data
$userMapper->toArray();
```
---
### 5.2. Pagination 

**MongoStar** has own paginator (**\MongoStar\Paginator**) for using it wit **Zend Framework 1**,
**\MongoStar\Paginator** class implements **\Zend_Paginator_Adapter_Interface**
**Example:**
```php 
<?php

$condition = ['age' => ['$gt' => 20]];
$sort = ['age' => 1];
$currentPage = 4;

$mongoStarPaginatorAdapter = new \MongoStar\Model\Paginator(
    new UserModel(),
    $condition,
    $sort
);

$paginator = new Zend_Paginator($mongoStarPaginatorAdapter);
$paginator->setCurrentPageNumber($currentPage);
```
---

This is it!

## Licence: 
> Some day, and that day may never come, I will call upon you to do a service for me. But until that day, consider this justice a gift on my daughter's wedding day.
