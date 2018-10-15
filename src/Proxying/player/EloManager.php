<?php
/**
 * Created by PhpStorm.
 * User: Kieran
 * Date: 17-Aug-17
 * Time: 7:41 PM
 */

namespace Proxying\player;


use pocketmine\Player;
use Proxying\elo\EloTypes;
use Proxying\ShmozoElo;

class EloManager {

    private static $instance;

    private $eloPlayers = array();

    /**
     *
     * implementation of singleton pattern
     * @return EloManager
     */
    public static function getInstance(): EloManager {
        if (!self::$instance) {
            self::$instance = new EloManager();
        }
        return self::$instance;
    }

    public function addPlayer(EloPlayer $eloPlayer) {
        if (array_key_exists($eloPlayer->username, $this->eloPlayers)) {
            ShmozoElo::getInstance()->getLogger()->warning("User " . $eloPlayer->username . " already exists within the cached map.");
        } else {
            $this->eloPlayers[$eloPlayer->username] = $eloPlayer;
            ShmozoElo::getInstance()->getLogger()->info("User " . $eloPlayer->username . " added to cached map successfully.");
        }
    }

    public function removePlayer(EloPlayer $eloPlayer) {
        if (array_key_exists($eloPlayer->username, $this->eloPlayers)) {
            unset($this->eloPlayers[$eloPlayer->username]);
            ShmozoElo::getInstance()->getLogger()->info("User " . $eloPlayer->username . " removed from within the cached map.");
        } else {
            ShmozoElo::getInstance()->getLogger()->warning("User " . $eloPlayer->username . " cannot be removed from the cached map.");
        }
    }

    public function getEloPlayer(string $username) {
        if (array_key_exists($username, $this->eloPlayers)) {
            return $this->eloPlayers[$username];
        } else {
            return null;
        }
    }

    public function addEloToPlayer(Player $player, EloTypes $eloTypes, int $amount) {
        $eloPlayer = $this->getEloPlayer($player->getName());
        if (is_null($eloPlayer)) {
            return;
        }
        if ($eloPlayer instanceof EloPlayer) {
            if (isset($eloPlayer->eloRatings[$eloTypes->getDescription()])) {
                $currentRating = $eloPlayer->getEloRatingByType($eloTypes);
                $currentRating->addEloRating($amount);
            } else {
                return;
            }
        } else {
            return;
        }
    }

    public function removeEloFromPlayer(Player $player, EloTypes $eloTypes, int $amount) {
        $eloPlayer = $this->getEloPlayer($player->getName());
        if (is_null($eloPlayer)) {
            return;
        }
        if (isset($eloPlayer->eloRatings[$eloTypes->getDescription()])) {
            $currentRating = $eloPlayer->getEloRatingByType($eloTypes);
            $currentRating->lowerEloRating($amount);
        } else {
            return;
        }
    }

    public function getEloRating(Player $player, EloTypes $eloTypes) : int {
        $eloPlayer = $this->getEloPlayer($player->getName());
        if (is_null($eloPlayer)) {
            return -1;
        }
        if (isset($eloPlayer->eloRatings[$eloTypes->getDescription()])) {
            $currentRating = $eloPlayer->getEloRatingByType($eloTypes);
            return $currentRating->getEloRating();
        } else {
            return -1;
        }
    }

    public function getEloPlayers(): array {
        return $this->eloPlayers;
    }
}