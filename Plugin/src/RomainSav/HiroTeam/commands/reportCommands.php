<?php

namespace RomainSav\HiroTeam\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use RomainSav\HiroTeam\DeathReport;

class reportCommands extends Command
{
    /**
     * @var DeathReport
     */
    private $main;

    /**
     * reportCommands constructor.
     * @param DeathReport $main
     */
    public function __construct(DeathReport $main)
    {
        parent::__construct('reports', "See all the reports", '/reports');
        $this->setPermission('reports.cmd');
        $this->main = $main;
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed|void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if ($sender instanceof Player) {

            if(!$this->testPermission($sender)){
                return true;
            }

            $this->main->getUiManager()->choseReportsList($sender);
        }
    }
}
