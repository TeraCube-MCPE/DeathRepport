<?php
#██╗░░██╗██╗██████╗░░█████╗░████████╗███████╗░█████╗░███╗░░░███╗
#██║░░██║██║██╔══██╗██╔══██╗╚══██╔══╝██╔════╝██╔══██╗████╗░████║
#███████║██║██████╔╝██║░░██║░░░██║░░░█████╗░░███████║██╔████╔██║
#██╔══██║██║██╔══██╗██║░░██║░░░██║░░░██╔══╝░░██╔══██║██║╚██╔╝██║
#██║░░██║██║██║░░██║╚█████╔╝░░░██║░░░███████╗██║░░██║██║░╚═╝░██║
#╚═╝░░╚═╝╚═╝╚═╝░░╚═╝░╚════╝░░░░╚═╝░░░╚══════╝╚═╝░░╚═╝╚═╝░░░░░╚═╝
namespace RomainSav\HiroTeam;

use pocketmine\plugin\PluginBase;
use RomainSav\HiroTeam\commands\reportCommands;
use RomainSav\HiroTeam\listeners\PlayerListener;
use RomainSav\HiroTeam\managers\ReportManager;
use RomainSav\HiroTeam\managers\UiManagers;

class DeathReport extends PluginBase
{
    /**
     * @var UiManagers
     */
    private $uiManager;

    /**
     * @var ReportManager
     */
    private $reportManager;

    /**
     * @var array
     */
    public $lastWorlds = [];

    public function onLoad()
    {
        $this->uiManager = new UiManagers($this);
        $this->reportManager = new ReportManager($this);
    }

    public function onEnable()
    {
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents(new PlayerListener($this), $this);
        $this->getServer()->getCommandMap()->register('reports', new reportCommands($this));
    }

    /**
     * @return UiManagers
     */
    public function getUiManager(): UiManagers
    {
        return $this->uiManager;
    }

    /**
     * @return ReportManager
     */
    public function getReportManager(): ReportManager
    {
        return $this->reportManager;
    }

    /**
     * @param int $int
     * @return array
     */
    public function convert(int $int)
    {
        $hour = floor($int / 3600);
        $minuteSec = $int % 3600;
        $minute = floor($minuteSec / 60);
        $remainingSec = $minuteSec % 60;
        $second = ceil($remainingSec);
        if (!isset($minute)) $minute = 0;
        if (!isset($second)) $second = 0;

        return ["hours" =>$hour, "min" => $minute, "sec" => $second];
    }
}
