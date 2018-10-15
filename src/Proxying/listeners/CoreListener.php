<?php

namespace Proxying\listeners;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat;
use Proxying\elo\EloTypes;
use Proxying\player\EloManager;
use Proxying\player\EloPlayer;
use Proxying\ShmozoElo;
use Proxying\sql\Database;

/**
 * Created by PhpStorm.
 * User: Kieran
 * Date: 09-Aug-17
 * Time: 7:05 PM
 */

class CoreListener implements Listener {

    public $os = ["Unknown", "Android", "iOS", "macOS", "FireOS", "GearVR", "HoloLens", "Windows 10", "Windows", "Dedicated", "Orbis", "NX"];

    public function onPlayerPreJoin(PlayerPreLoginEvent $event) {
        $player = $event->getPlayer();
        $playerID = Database::getInstance()->checkForNewPlayer($player->getUniqueId(), $player->getName());

        $eloPlayer = new EloPlayer($player->getUniqueId(), $playerID, $player->getName());
        if ($eloPlayer->playerID === -1) {
            $event->setCancelled(true);
            $event->setKickMessage("Your data could not be loaded correctly.");
        } else {
            EloManager::getInstance()->addPlayer($eloPlayer);
        }
    }

    public function onPacketReceived(DataPacketReceiveEvent $event) {
        $pk = $event->getPacket();
        if ($pk instanceof LoginPacket) {
            ShmozoElo::getInstance()->addToMap($pk->username, $pk);
        }
    }

    public function onPlayerJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();
        $player->sendMessage("Hey dude, welcome to my server its me " . $player->getName() . ".");

        if (array_key_exists($player->getName(), ShmozoElo::getInstance()->getPlayerData())) {
            $pk = ShmozoElo::getInstance()->getPlayerPacket($player->getName());
            $deviceOS = $this->os[$pk->clientData["DeviceOS"]];
            $player->setDisplayName("§f[§c" . $deviceOS . "§f] " . $player->getName());
            if (strpos($deviceOS, "iOS") !== false) {
                $player->setScale(1);
            } else {
                $player->setScale(0.2);
            }
        }

        //$player->sendMessage("Your FFA Elo is: " . EloManager::getInstance()->getEloRating($player, EloTypes::getByKey("FFA")));
    }

    public function onPlayerQuit(PlayerQuitEvent $event) {
        $eloPlayer = EloManager::getInstance()->getEloPlayer($event->getPlayer()->getName());
        //todo: Logout updates via SQL.
        EloManager::getInstance()->removePlayer($eloPlayer);
    }

    public function onPlayerDeath(PlayerDeathEvent $event) {
        $event->getEntity()->sendMessage("Fucking Nig.");
    }

}