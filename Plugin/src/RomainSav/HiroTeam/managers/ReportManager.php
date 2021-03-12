<?php

namespace RomainSav\HiroTeam\managers;

use pocketmine\utils\Config;
use RomainSav\HiroTeam\DeathReport;
use mysqli;

class ReportManager
{
    /**
     * @var DeathReport
     */
    private $main;

    /**
     * ReportManager constructor.
     * @param DeathReport $main
     */
    public function __construct(DeathReport $main)
    {
        $this->main = $main;
        $this->init();
    }

    /**
     * @return mysqli
     */
    public function getDB(): mysqli
    {
        return new mysqli($this->main->getConfig()->get('mysql')['host'], $this->main->getConfig()->get('mysql')['username'], $this->main->getConfig()->get('mysql')['password'], $this->main->getConfig()->get('mysql')['dbname']);
    }

    /**
     * @return void
     */
    public function init(): void
    {
        $db = $this->getDB();
        $db->query("CREATE TABLE IF NOT EXISTS deathReport (ID INTEGER PRIMARY KEY AUTO_INCREMENT NOT NULL, name VARCHAR(255), type VARCHAR(255), reportPlayer VARCHAR(255), explications TEXT, coordinates VARCHAR(255), world VARCHAR(255));");
    }

    /**
     * @param string $name
     * @param string $type
     * @param string $reportPlayer
     * @param string $explications
     * @param string $coordinates
     * @param string $world
     */
    public function add(string $name, string $type, string $reportPlayer = '-', string $explications = '-', string $coordinates = '-', string $world = '-'): void
    {
        $db = $this->getDB();
        $db->query("INSERT INTO deathReport (name, type, reportPlayer, explications, coordinates, world) VALUES ('$name', '$type', '$reportPlayer', '$explications', '$coordinates', '$world');");
        $db->close();
    }

    public function getAll(string $type): array
    {
        $db = $this->getDB();
        $res = $db->query("SELECT * FROM deathReport WHERE type='$type';");
        $db->close();

        $reports = [];
        while ($resArr = $res->fetch_array()) {
            $id = $resArr['ID'];
            unset($resArr['ID']);
            $reports[$id] = $resArr;
        }
        return $reports;
    }
}
