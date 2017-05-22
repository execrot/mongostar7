<?php

declare(strict_types = 1);

namespace MongoStar\Model\Driver\Flat;

/**
 * Class Driver
 * @package MongoStar\Model\Driver\Flat
 */
class Driver extends \MongoStar\Model\Driver\DriverAbstract
{
    const ID_UNIQUE_LENGTH = 13;

    /**
     * @var bool
     */
    private $_isSecured = false;

    /**
     * Driver constructor.
     *
     * @throws Exception\OpenSSLFunctionDoesNotExists
     * @throws Exception\OpenSSLUnknownCryptMethod
     */
    public function __construct()
    {
        $config = \MongoStar\Config::getConfig();

        if (isset($config['secure']) && $config['secure']) {

            $opensslFunctions = [
                'openssl_get_cipher_methods',
                'openssl_random_pseudo_bytes',
                'openssl_cipher_iv_length',
                'openssl_encrypt',
                'openssl_decrypt'
            ];

            foreach ($opensslFunctions as $opensslFunction) {
                if (!function_exists($opensslFunction)) {
                    throw new Exception\OpenSSLFunctionDoesNotExists($opensslFunction);
                }
            }

            if (!in_array($config['method'], openssl_get_cipher_methods())) {
                throw new Exception\OpenSSLUnknownCryptMethod($config['method']);
            }

            $this->_isSecured = true;
        }
    }

    /**
     * @return mixed
     */
    public function save()
    {
        $primaryName = $this->getModel()->getMeta()->getPrimary();
        $primaryValue = $this->getModel()->{$this->getModel()->getMeta()->getPrimary()};

        /////////////////////////////////////////////////////////////////
        if (!$primaryValue) {
            $this->getModel()->{$primaryName} = substr(uniqid(), 0, self::ID_UNIQUE_LENGTH) . time();
            $primaryValue = $this->getModel()->{$primaryName};
        }
        /////////////////////////////////////////////////////////////////
        
        if ($primaryValue) {

            $cond[$primaryName] = $primaryValue;

            $content = $this->_getCollectionContent();

            for ($i = 0; $i < count($content); $i++) {

                if ($content[$i][$primaryName] == $primaryValue) {

                    $content[$i] = $this->getModel()->getData();

                    $this->_setCollectionContent($content);

                    return 1;
                }
            }
        }

        return $this->batchInsert([
            $data = $this->getModel()->getData()
        ]);
    }

    /**
     * @param array|string|null $cond
     * @param int|null $limit
     *
     * @return int
     */
    public function remove($cond = [], int $limit = null) : int
    {
        $primaryName = $this->getModel()->getMeta()->getPrimary();
        $primaryValue = $this->getModel()->{$this->getModel()->getMeta()->getPrimary()};

        if ($primaryValue) {
            $cond[$primaryName] = $primaryValue;
            $limit = 1;
        }

        $content = $this->_getCollectionContent();

        $selectedIndexes = [];

        for ($i = 0; $i < count($content); $i++) {

            if (Query\Query::execute($cond, $content[$i])) {

                $selectedIndexes[] = $i;

                if (count($selectedIndexes) == $limit) {
                    break;
                }
            }
        }

        foreach ($selectedIndexes as $index) {
            unset($content[$index]);
        }

        $content = array_values($content);

        if ($this->_setCollectionContent($content)) {
            return count($content);
        }

        return 0;
    }

    /**
     * @param array|string|null $cond
     * @param array|string|null $sort
     *
     * @return \MongoStar\Model|null
     */
    public function fetchOne($cond = null, $sort = null)
    {
        $content = $this->_select($cond, $sort, 1);

        if (count($content) == 0) {
            return null;
        }

        $cursor = new Cursor($this->getModel(), $content);
        return $cursor->current();
    }

    /**
     * @param array|string|null $cond
     * @param array|string|null $sort
     *
     * @param int|null $count
     * @param int|null $offset
     *
     * @return Cursor
     */
    public function fetchAll($cond = null, $sort = null, int $count = null, int $offset = null)
    {
        return new Cursor(
            $this->getModel(),
            $this->_select($cond, $sort, $count, $offset)
        );
    }

    /**
     * @param array|string|null $cond
     * @return int
     */
    public function count($cond = null) : int
    {
        return count($this->_select($cond));
    }

    /**
     * @param array|null $data
     *
     * @return int
     */
    public function batchInsert(array $data = null) : int
    {
        $content = $this->_getCollectionContent();

        $cursor = new Cursor($this->getModel(), $data);

        foreach ($cursor as $model) {

            $modelData = $model->getData();
            $modelData[$this->getModel()->getMeta()->getPrimary()] = substr(uniqid(), 0, self::ID_UNIQUE_LENGTH) . time();

            $content[] = $modelData;
        }

        if ($this->_setCollectionContent($content)) {
            return count($cursor);
        }
        return 0;
    }

    /**
     * @param null $cond
     * @param null $sort
     * @param int|null $count
     * @param int $offset
     * @param bool $unSelect
     *
     * @return array
     */
    private function _select($cond = null, $sort = null, int $count = null, int $offset = null, bool $unSelect = false) : array
    {
        $content = $this->_getSortedCollectionContent($sort);

        $selectedItems = [];

        $offset = intval($offset);

        for ($i = $offset; $i < count($content); $i++) {

            if ($count > 0 && count($selectedItems) == $count) {
                break;
            }

            if (is_null($cond)) {
                $cond = [];
            }

            if (Query\Query::execute($cond, $content[$i])) {

                if (!$unSelect) {
                    $selectedItems[] = $content[$i];
                }
            }

            else if ($unSelect) {
                $selectedItems[] = $content[$i];
            }
        }

        return $selectedItems;
    }

    /**
     * @param null $sort
     * @return array
     */
    private function _getSortedCollectionContent($sort = null) : array
    {
        $content = $this->_getCollectionContent();

        if ($sort && is_array($sort) && !isset($sort[0]) && count($sort)) {

            $key = array_keys($sort)[0];
            $value = array_values($sort)[0];

            try {
                $property = $this->getModel()->getMeta()->getPropertyWithName($key);

                if ($property->getType() == 'string') {

                    usort($content, function ($a, $b) use ($key, $value) {

                        $a[$key] = isset($a[$key])?$a[$key]:null;
                        $b[$key] = isset($b[$key])?$b[$key]:null;

                        if ($value > 0) {
                            return  strtolower($a[$key]) > strtolower($b[$key]) ? 1: -1;
                        }

                        return  strtolower($a[$key]) < strtolower($b[$key]) ? 1: -1;
                    });
                }

                else if ($property->getType() == 'array') {

                    usort($content, function ($a, $b) use ($key, $value) {

                        $a[$key] = isset($a[$key])?$a[$key]:null;
                        $b[$key] = isset($b[$key])?$b[$key]:null;

                        if ($value > 0) {
                            return  count($a[$key]) > count($b[$key]) ? 1: -1;
                        }

                        return  count($a[$key]) < count($b[$key]) ? 1: -1;
                    });
                }

                else {

                    usort($content, function ($a, $b) use ($key, $value) {

                        $a[$key] = isset($a[$key])?$a[$key]:null;
                        $b[$key] = isset($b[$key])?$b[$key]:null;

                        if ($value > 0) {
                            return $a[$key] - $b[$key];
                        }

                        return $b[$key] - $a[$key];
                    });
                }
            }
            catch (\Exception $e) {}
        }

        return $content;
    }

    /**
     * @param array $content
     * @return bool
     */
    private function _setCollectionContent(array $content)
    {
        $config = \MongoStar\Config::getConfig();

        $dataSourceDir = $this->_getCollectionFilePath(true);

        if (isset($config['pretty']) && $config['pretty']) {
            $content = json_encode($content, JSON_PRETTY_PRINT);
        }
        else {
            $content = json_encode($content);
        }

        return $this->_writeDataSourceFile($dataSourceDir, $content);
    }

    /**
     * @return array
     */
    private function _getCollectionContent() : array
    {
        $dataSource = $this->_getCollectionFilePath();

        if (!file_exists($dataSource)) {
            return [];
        }

        if ($content = json_decode($this->_readDataSourceFile($dataSource), true)) {
            return $content;
        }
        return [];
    }

    /**
     * @param bool $createOfNotExists
     * @return string
     *
     * @throws Exception\DataSourceFileIsNotReadable
     * @throws Exception\DataSourceFileIsNotWritable
     */
    private function _getCollectionFilePath(bool $createOfNotExists = false) : string
    {
        $dataSourcePath = $this->_getCollectionDirPath($createOfNotExists);

        $dataSourcePath .= '/' . $this->getModel()->getMeta()->getCollection() . '.json';

        if ($createOfNotExists && file_exists($dataSourcePath) && !is_writable($dataSourcePath)) {
            throw new Exception\DataSourceFileIsNotWritable($dataSourcePath, $this->_getPosixData());
        }

        if (file_exists($dataSourcePath) && !is_readable($dataSourcePath)) {
            throw new Exception\DataSourceFileIsNotReadable($dataSourcePath, $this->_getPosixData());
        }

        if ($createOfNotExists && !file_exists($dataSourcePath)) {
            $this->_writeDataSourceFile($dataSourcePath, '[]');
        }

        return $dataSourcePath;
    }

    /**
     * @param bool $createOfNotExists
     * @return string
     *
     * @throws Exception\DataSourceDbDirIsNotReadable
     * @throws Exception\DataSourceDbDirIsNotWritable
     */
    private function _getCollectionDirPath (bool $createOfNotExists = false) : string
    {
        $dataSourcePath = $this->_getDbPath($createOfNotExists);

        $dataSourcePath .= '/' . \MongoStar\Config::getConfig()['db'];

        if ($createOfNotExists && file_exists($dataSourcePath) && !is_writable($dataSourcePath)) {
            throw new Exception\DataSourceDbDirIsNotWritable($dataSourcePath, $this->_getPosixData());
        }

        if ($createOfNotExists && !file_exists($dataSourcePath)) {
            mkdir($dataSourcePath);
        }

        if (file_exists($dataSourcePath) && !is_readable($dataSourcePath)) {
            throw new Exception\DataSourceDbDirIsNotReadable($dataSourcePath, $this->_getPosixData());
        }

        return $dataSourcePath;
    }

    /**
     * @param bool $createOfNotExists
     * @return string
     *
     * @throws Exception\DataSourceDirIsNotReadable
     * @throws Exception\DataSourceDirIsNotWritable
     */
    private function _getDbPath(bool $createOfNotExists = false) : string
    {
        $config = \MongoStar\Config::getConfig();

        $dataSourcePath = '/' . implode('/', array_filter(explode('/', $config['dir'])));

        if ($createOfNotExists && !is_writable($dataSourcePath)) {
            throw new Exception\DataSourceDirIsNotWritable($dataSourcePath, $this->_getPosixData());
        }

        if (!is_readable($dataSourcePath)) {
            throw new Exception\DataSourceDirIsNotReadable($dataSourcePath, $this->_getPosixData());
        }

        return $dataSourcePath;
    }

    /**
     * @param string $dataSourceFilePath
     * @param string $content
     *
     * @return bool
     * @throws Exception\OpenSSLCouldNotEncryptCollection
     */
    private function _writeDataSourceFile(string $dataSourceFilePath, string $content) : bool
    {
        if ($this->_isSecured) {

            $method   = \MongoStar\Config::getConfig()['method'];
            $password = \MongoStar\Config::getConfig()['password'];

            $iv = $this->_getInitializeVector();

            $content = openssl_encrypt($content, $method, $password, 0, $iv);

            if (!$content) {
                throw new Exception\OpenSSLCouldNotEncryptCollection(\MongoStar\Config::getConfig());
            }
        }

        return (bool)file_put_contents($dataSourceFilePath, $content, LOCK_EX);
    }

    /**
     * @param string $dataSourceFilePath
     *
     * @return string
     * @throws Exception\OpenSSLCouldNotDecryptCollection
     */
    private function _readDataSourceFile(string $dataSourceFilePath) : string
    {
        $content = file_get_contents($dataSourceFilePath);

        if ($this->_isSecured) {

            $password = \MongoStar\Config::getConfig()['password'];
            $method   = \MongoStar\Config::getConfig()['method'];

            $iv = $this->_getInitializeVector();

            $content = openssl_decrypt($content, $method, $password, 0, $iv);

            if (!$content) {
                throw new Exception\OpenSSLCouldNotDecryptCollection(\MongoStar\Config::getConfig());
            }
        }

        return $content;
    }

    /**
     * @return string
     */
    private function _getInitializeVector() : string
    {
        $username = \MongoStar\Config::getConfig()['username'];
        $method   = \MongoStar\Config::getConfig()['method'];

        $iv = str_split(substr($username, 0, openssl_cipher_iv_length($method)));

        return implode(
            '',
            array_merge(
                $iv,
                array_fill(0, openssl_cipher_iv_length($method)- count($iv), 0)
        ));
    }

    /**
     * @return array
     */
    private function _getPosixData() : array
    {
        return posix_getpwuid(posix_geteuid());
    }
}
