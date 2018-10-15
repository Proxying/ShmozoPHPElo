<?php

namespace Proxying\elo;
use Proxying\ShmozoElo;
use Proxying\utils\AbstractEnum;

/**
 * Created by PhpStorm.
 * User: Kieran
 * Date: 12-Aug-17
 * Time: 12:11 PM
 */

class EloTypes extends AbstractEnum {

    /** @var EloTypes */
    private static $FFA;

    /** @var EloTypes */
    private static $SOLO;

    /** @var EloTypes */
    private static $DUO;

    /** @var EloTypes */
    private static $TEAM;


    public static function _init() {
        self::$FFA = self::enum(1, 'FFA');
        self::$SOLO = self::enum(2, 'SOLO');
        self::$DUO = self::enum(3, 'DUO');
        self::$TEAM = self::enum(4, 'TEAM');
    }
}