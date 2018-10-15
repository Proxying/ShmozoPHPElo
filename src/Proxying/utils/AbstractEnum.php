<?php

namespace Proxying\utils;
use Exception;
use Proxying\ShmozoElo;

/**
 * Created by PhpStorm.
 * User: Kieran
 * Date: 12-Aug-17
 * Time: 12:17 PM
 */

abstract class AbstractEnum {
    /** @var array cache of all enum instances by class name and integer value */
    private static $allEnumMembers = array();

    /** @var mixed */
    private $code;

    /** @var string */
    private $description;


    /**
     * Return an enum instance of the concrete type on which this static method is called, assuming an instance
     * exists for the passed in value.  Otherwise an exception is thrown.
     *
     * @param $description
     * @return AbstractEnum
     * @throws Exception
     */
    public static function getByKey($description) {
        $concreteMembers = &self::getConcreteMembers();

        if (array_key_exists($description, $concreteMembers)) {
            return $concreteMembers[$description];
        }

        throw new Exception("Value '$description' does not exist for enum '" . get_called_class() . "'");
    }

    public static function getByValue($code) {
        $concreteMembers = &self::getConcreteMembers();

        $key = array_search($code, $concreteMembers);
        ShmozoElo::getInstance()->getLogger()->info("Used Array Search " . $key);

        if (!is_bool($key)) {
            ShmozoElo::getInstance()->getLogger()->info("Key Found " . $key);
            return self::getByCode($key);
        }

        throw new \Exception("Value '$code' does not exist for enum '" . get_called_class() . "'");
    }

    public static function getAllMembers() {
        return self::getConcreteMembers();
    }

    /**
     * Create, cache and return an instance of the concrete enum type for the supplied primitive value.
     *
     * @param mixed $code code to uniquely identify this enum
     * @param string $description
     * @throws Exception
     * @return AbstractEnum
     */
    protected static function enum($code, $description) {
        $concreteMembers = &self::getConcreteMembers();

        if (array_key_exists($description, $concreteMembers)) {
            throw new Exception("Value '$description' has already been added to enum '" . get_called_class() . "'");
        }

        $concreteMembers[$description] = $concreteEnumInstance = new static($code, $description);

        ShmozoElo::getInstance()->getLogger()->info("Enum Added -> Key " . $description . " Value " . $code);

        return $concreteEnumInstance;
    }

    /**
     * @return AbstractEnum[]
     */
    protected static function &getConcreteMembers() {
        $thisClassName = get_called_class();

        if (!array_key_exists($thisClassName, self::$allEnumMembers)) {
            $concreteMembers = array();
            self::$allEnumMembers[$thisClassName] = $concreteMembers;
        }

        return self::$allEnumMembers[$thisClassName];
    }

    private function __construct($code, $description) {
        $this->code = $code;
        $this->description = $description;
    }

    public function getCode() {
        return $this->code;
    }

    public function getDescription() {
        return $this->description;
    }
}