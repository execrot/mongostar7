<?php

declare(strict_types = 1);

namespace MongoStar\Model\Driver\Flat\Query;

/**
 * Class Query
 * @package MongoStar\Model\Driver\Flat\Query
 */
class Query
{
    /**
     * @param array $query
     * @param array $document
     * @param string $logicalOperator
     *
     * @return bool
     * @throws Exception\LogicalOperatorRequiresNonEmptyArray
     */
    public static function execute(array $query = [], array &$document, string $logicalOperator = '$and')
    {
        if($logicalOperator !== '$and' && (!count($query) || !isset($query[0]))) {
            throw new Exception\LogicalOperatorRequiresNonEmptyArray($logicalOperator);
        }

        $isIndexedArray = function($a) {
            return (!empty($a) && array_keys($a) === range(0, count($a) - 1));
        };

        $queryIsIndexedArray = $isIndexedArray($query);

        foreach($query as $index => $queryItem) {

            if (is_string($index) && substr($index, 0, 1) === '$') {

                if ($index === '$not') {
                    $pass = !self::execute($queryItem, $document);
                }

                else {
                    $pass = self::execute($queryItem, $document, $index);
                }

            }
            else if ($logicalOperator === '$and') {

                if($queryIsIndexedArray) {
                    $pass = self::execute($queryItem, $document);
                }

                else if(is_array($queryItem)) {
                    $pass = self::_executeQueryOnElement($queryItem, $index, $document);
                }

                else {
                    $pass = self::_executeOperatorOnElement('$e', $queryItem, $index, $document);
                }
            }
            else {
                $pass = self::execute($queryItem, $document, '$and');
            }

            switch($logicalOperator) {

                case '$and':
                    if (!$pass) {
                        return false;
                    }
                    break;


                case '$or':
                    if ($pass) {
                        return true;
                    }
                    break;


                case '$nor':
                    if ($pass) {
                        return false;
                    }
                    break;


                default:
                    return false;
            }
        }

        if ($logicalOperator == '$and' || $logicalOperator == '$nor') {
            return true;
        }

        return false;
    }

    /**
     * @param array $query
     * @param $element
     * @param array $document
     *
     * @return bool
     */
    private static function _executeQueryOnElement(array $query, $element, array &$document) : bool
    {
        foreach($query as $op => $opVal) {

            if (!self::_executeOperatorOnElement($op, $opVal, $element, $document)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $value
     * @param $operatorValue
     *
     * @return bool
     */
    private static function _isEqual($value, $operatorValue) : bool
    {
        if (is_array($value) && is_array($operatorValue)) {
            return $value == $operatorValue;
        }

        if (is_array($value)) {
            return in_array($operatorValue, $value);
        }

        if (is_string($operatorValue) && preg_match('/^\/(.*?)\/([a-z]*)$/i', $operatorValue, $matches)) {
            return (bool)preg_match('/'.$matches[1].'/'.$matches[2], $value);
        }

        return $operatorValue === $value;
    }

    /**
     * @param $operator
     * @param $operatorValue
     * @param $element
     * @param array $document
     *
     * @return bool|mixed
     *
     * @throws Exception\OperatorAllRequiresArray
     * @throws Exception\OperatorInRequiresArray
     * @throws Exception\OperatorModRequiresArray
     * @throws Exception\OperatorModRequiresTwoParametersInArrayDevesorAndRemainder
     * @throws Exception\OperatorNinRequiresArray
     * @throws Exception\ReturnValueOfUnknownOperatorCallbackMustBeBoolean
     * @throws Exception\UnknownOperator
     */
    private static function _executeOperatorOnElement($operator, $operatorValue, $element, array &$document)
    {
        if ($operator === '$not') {
            return !self::_executeQueryOnElement($operatorValue, $element, $document);
        }

        $elementSpecifier = explode('.', $element);
        $v =& $document;
        $exists = true;

        foreach($elementSpecifier as $index => $es) {

            if (empty($v)) {
                $exists = false;
                break;
            }

            if (isset($v[0])) {

                $newSpecifier = implode('.', array_slice($elementSpecifier, $index));

                foreach ($v as $item) {

                    if (self::_executeOperatorOnElement($operator, $operatorValue, $newSpecifier, $item)) {
                        return true;
                    }
                }
                return false;
            }

            if (isset($v[$es])) {
                $v =& $v[$es];
            }
            else {
                $exists = false;
                break;
            }
        }

        switch ($operator) {

            case '$all':

                if (!$exists) {
                    return false;
                }

                if (!is_array($operatorValue)) {
                    throw new Exception\OperatorAllRequiresArray($operatorValue);
                }

                if (count($operatorValue) === 0) {
                    return false;
                }

                if (!is_array($v)) {

                    if(count($operatorValue) === 1) {
                        return $v === $operatorValue[0];
                    }
                    return false;
                }
                return count(array_intersect($v, $operatorValue)) === count($operatorValue);

            case '$e':

                if (!$exists) {
                    return false;
                }

                return self::_isEqual($v, $operatorValue);

            case '$in':
                if (!$exists) {
                    return false;
                }

                if (!is_array($operatorValue)) {
                    throw new Exception\OperatorInRequiresArray($operatorValue);
                }

                if (count($operatorValue) === 0) {
                    return false;
                }

                if (is_array($v)) {
                    return count(array_intersect($v, $operatorValue)) > 0;
                }

                return in_array($v, $operatorValue);

            case '$lt':
                return $exists && $v < $operatorValue;

            case '$lte':
                return $exists && $v <= $operatorValue;

            case '$gt':
                return $exists && $v > $operatorValue;

            case '$gte':
                return $exists && $v >= $operatorValue;

            case '$ne':
                return (!$exists && $operatorValue !== null) || ($exists && !self::_isEqual($v, $operatorValue));


            case '$nin':
                if (!$exists) {
                    return true;
                }

                if (!is_array($operatorValue)) {
                    throw new Exception\OperatorNinRequiresArray($operatorValue);
                }

                if (count($operatorValue) === 0) {
                    return true;
                }

                if (is_array($v)) {
                    return count(array_intersect($v, $operatorValue)) === 0;
                }

                return !in_array($v, $operatorValue);


            case '$exists':
                return ($operatorValue && $exists) || (!$operatorValue && !$exists);


            case '$mod':
                if (!$exists) {
                    return false;
                }

                if (!is_array($operatorValue)) {
                    throw new Exception\OperatorModRequiresArray($operatorValue);
                }

                if (count($operatorValue) !== 2) {
                    throw new Exception\OperatorModRequiresTwoParametersInArrayDevesorAndRemainder();
                }

                return $v % $operatorValue[0] === $operatorValue[1];


            default:

                if (empty($options['unknownOperatorCallback']) || !is_callable($options['unknownOperatorCallback'])) {
                    throw new Exception\UnknownOperator($operator);
                }

                $res = call_user_func($options['unknownOperatorCallback'], $operator, $operatorValue, $element, $document);

                if ($res === null) {
                    throw new Exception\UnknownOperator($operator);
                }

                if (!is_bool($res)) {
                    throw new Exception\ReturnValueOfUnknownOperatorCallbackMustBeBoolean($res);
                }

                return $res;
        }
    }
}