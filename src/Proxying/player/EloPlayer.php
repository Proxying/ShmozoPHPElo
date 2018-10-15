<?php

namespace Proxying\player;

use pocketmine\utils\UUID;
use Proxying\elo\EloRating;
use Proxying\elo\EloTypes;
use Proxying\sql\Database;

/**
 * Created by PhpStorm.
 * User: Kieran
 * Date: 17-Aug-17
 * Time: 7:36 PM
 */

class EloPlayer {

    public $uuid;
    public $username;
    public $playerID;
    public $eloRatings = array();

    public function __construct(UUID $uuid, int $playerID, string $username) {
        $this->uuid = $uuid;
        $this->playerID = $playerID;
        $this->username = $username;
        $this->eloRatings = Database::getInstance()->getElosOfPlayer($playerID);
    }

    public function getEloRatingByType(EloTypes $eloTypes): EloRating {
        if (isset($this->eloRatings[$eloTypes->getDescription()])) {
            return $this->eloRatings[$eloTypes->getDescription()];
        } else {
            return null;
        }
    }
}