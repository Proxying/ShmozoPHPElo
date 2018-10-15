<?php

namespace Proxying\elo;

/**
 * Created by PhpStorm.
 * User: Kieran
 * Date: 12-Aug-17
 * Time: 12:10 PM
 */

class EloRating {

    public $sqlID;
    public $playerID;
    public $eloRating;
    public $eloType;

    public function __construct(int $sqlID, int $playerID, int $eloRating, EloTypes $eloType) {
        $this->sqlID = $sqlID;
        $this->playerID = $playerID;
        $this->eloRating = $eloRating;
        $this->eloType = $eloType;
    }

    public function addEloRating(int $amount) {
        $this->eloRating += $amount;
    }

    public function lowerEloRating(int $amount) {
        if (($this->eloRating - $amount) < 1) {
            $this->eloRating = 1;
        } else {
            $this->eloRating -= $amount;
        }
    }

    public function getEloRating() : int {
        return $this->eloRating;
    }
}