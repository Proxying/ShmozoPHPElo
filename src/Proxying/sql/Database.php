<?php

namespace Proxying\sql;

use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\UUID;
use Proxying\elo\EloRating;
use Proxying\elo\EloTypes;
use Proxying\ShmozoElo;


/**
 * Created by PhpStorm.
 * User: Kieran
 * Date: 10-Aug-17
 * Time: 10:48 AM
 */

class Database {

    private static $instance;

    /** @var \mysqli */
    protected static $connection;

    private $dbName;
    private $dbAddress;
    private $dbPort;
    private $dbUser;
    private $dbPassword;

    /**
     *
     * implementation of singleton pattern
     * @return Database
     */
    public static function getInstance(): Database {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function setup(string $address, int $port, string $name, string $user, string $pass) {
        $this->dbName = $name;
        $this->dbAddress = $address;
        $this->dbPort = $port;
        $this->dbUser = $user;
        $this->dbPassword = $pass;

        $this->connect();
        /*$resource = ShmozoElo::getInstance()->getResource("mysql.sql");
        self::$connection->query(stream_get_contents($resource));
        fclose($resource);*/
    }

    public function connect() {

        if (!isset(self::$connection)) {
            self::$connection = new \mysqli($this->dbAddress, $this->dbUser, $this->dbPassword, $this->dbName);
        }

        if (self::$connection === false) {
            return false;
        }

        if (self::$connection->connect_error) {
            return false;
        }

        ShmozoElo::getInstance()->getLogger()->info("Connected to MySQL server");

        //todo: Keep connection open with database->ping() every so often?

        return self::$connection;
    }

    public function query($query) {
        $connection = $this->connect();

        $result = $connection->query($query);

        return $result;
    }

    public function select($query) {
        $rows = array();

        $result = $this -> query($query);

        if($result === false) {
            return false;
        }

        while ($row = $result -> fetch_assoc()) {
            $rows[] = $row;
        }

        return $rows;
    }

    public function close(){
        self::$connection->close();
    }

    public function getPlayerByUUID(UUID $UUID) {
        $strUUID = $UUID->toString();

        $result = self::$connection->query("SELECT * FROM player_keys WHERE uuid = '" . self::$connection->escape_string($strUUID) . "'");

        if ($result instanceof \mysqli_result) {
            $data = $result->fetch_assoc();
            $result->free();
            if (isset($data["player_id"]) && isset($data["username"])) {
                return $data;
            }
        }

        return null;
    }

    public function checkForNewPlayer(UUID $UUID, string $name): int {
        $strUUID = $UUID->toString();

        $foundID = $this->getPlayerByUUID($UUID);
        if ($foundID == null) {
            $result = self::$connection->query("INSERT INTO player_keys (uuid, username) VALUES ('". self::$connection->escape_string($strUUID) . "', '" . self::$connection->escape_string($name) . "')");
            if (!$result) {
                return -1;
            } else {
                return self::$connection->insert_id;
            }
        } else {
            if ($foundID["username"] !== $name) {
                self::$connection->query("UPDATE player_keys SET username = '" . self::$connection->escape_string($name) . "' WHERE uuid = '" . self::$connection->escape_string($strUUID) . "'");
                ShmozoElo::getInstance()->getLogger()->info("Username updated for " . $name . " from previous " . $foundID["username"] . ".");
            }
            return $foundID["player_id"];
        }
    }

    public function getElosOfPlayer(int $playerID): array {
        $result = self::$connection->query("SELECT * FROM kit_pvp_elo WHERE player_id =" . $playerID . " LIMIT " . count(EloTypes::getAllMembers()));

        $amount = 0;

        $map = array();
        if ($result->num_rows) {
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            $result->free();

            foreach ($rows as $row) {
                $amount++;
                $eloType = EloTypes::getByKey($row["elo_type"]);
                $eloRating = new EloRating($row["ID"], $playerID, $row["elo_rating"], $eloType);
                $map[$row["elo_type"]] = $eloRating;
            }
        }
        if ($amount < $count = count(EloTypes::getAllMembers())) {
            foreach (EloTypes::getAllMembers() as $member) {
                if (!array_key_exists($member->getDescription(), $map)) {
                    $result = self::$connection->query("INSERT INTO kit_pvp_elo (player_id, elo_type, elo_rating) VALUES ($playerID, '" . self::$connection->escape_string($member->getDescription()) . "', 1000)");
                    if ($result) {
                        $returnedID = self::$connection->insert_id;
                        $eloRating = new EloRating($returnedID, $playerID, 1000, $member);
                        $map[$member->getDescription()] = $eloRating;
                    }
                }
            }
        }

        return $map;
    }
}