<?php
/**
 * Created by PhpStorm.
 * User: Kieran
 * Date: 09-Aug-17
 * Time: 6:53 PM
 */

namespace Proxying;

use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\utils\TextFormat;
use Proxying\elo\EloTypes;
use Proxying\sql\Database;
use pocketmine\plugin\PluginBase;
use Proxying\listeners\CoreListener;

/**
 * The ShmozoElo API.
 * @package Proxying
 */


class ShmozoElo extends PluginBase {

    /** @var ShmozoElo */
    static private $instance;

    private $playerData = array();

    /**
     *
     * implementation of singleton pattern
     * @return ShmozoElo
     */
    static public function getInstance(): ShmozoElo {
        return self::$instance;
    }

    public function onLoad() {
        $this->getLogger()->info(TextFormat::WHITE . "Loaded");
    }

    public function onEnable() {
        self::$instance = $this;

        EloTypes::_init();

        @mkdir($this->getDataFolder());

        $this->saveDefaultConfig();
        $this->reloadConfig();

        $this->getServer()->getNetwork()->setName('§cShmozo Network');

        $this->getServer()->getPluginManager()->registerEvents(new CoreListener(), $this);
        Database::getInstance()->setup($this->getConfig()->get('database_ip'), $this->getConfig()->get('database_port'), $this->getConfig()->get('database_name'),
            $this->getConfig()->get('database_user'), $this->getConfig()->get('database_password'));

        $this->getLogger()->info(TextFormat::DARK_GREEN . "Enabled");
    }

    public function onDisable() {
        $this->getLogger()->info(TextFormat::RED . "Disabled");
    }

    public function getPlayerData() : array {
        return $this->playerData;
    }

    public function getPlayerPacket(string $username) : LoginPacket {
        if (array_key_exists($username, $this->playerData)) {
            return $this->playerData[$username];
        } else {
            return null;
        }
    }

    public function addToMap(string $username, LoginPacket $packet) {
        $this->playerData[$username] = $packet;
    }

}