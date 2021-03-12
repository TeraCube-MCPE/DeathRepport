<?php
#██╗░░██╗██╗██████╗░░█████╗░████████╗███████╗░█████╗░███╗░░░███╗
#██║░░██║██║██╔══██╗██╔══██╗╚══██╔══╝██╔════╝██╔══██╗████╗░████║
#███████║██║██████╔╝██║░░██║░░░██║░░░█████╗░░███████║██╔████╔██║
#██╔══██║██║██╔══██╗██║░░██║░░░██║░░░██╔══╝░░██╔══██║██║╚██╔╝██║
#██║░░██║██║██║░░██║╚█████╔╝░░░██║░░░███████╗██║░░██║██║░╚═╝░██║
#╚═╝░░╚═╝╚═╝╚═╝░░╚═╝░╚════╝░░░░╚═╝░░░╚══════╝╚═╝░░╚═╝╚═╝░░░░░╚═╝
namespace RomainSav\HiroTeam\listeners;

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use RomainSav\HiroTeam\DeathReport;
use RomainSav\HiroTeam\tasks\BrightnessTask;

class PlayerListener implements Listener
{
    /**
     * @var DeathReport
     */
    private $main;

    /**
     * PlayerListener constructor.
     * @param DeathReport $main
     */
    public function __construct(DeathReport $main)
    {
        $this->main = $main;
    }

    /**
     * @param PlayerDeathEvent $event
     */
    public function onDeath(PlayerDeathEvent $event)
    {
        $player = $event->getPlayer();
        $data = [
            'world' => $player->getLevel()->getName(),
            'x' => round($player->x),
            'y' => round($player->y),
            'z' => round($player->z)
        ];
        $this->main->lastWorlds[$event->getPlayer()->getName()] = $data;
    }

    /**
     * @param PlayerRespawnEvent $event
     */
    public function onRespawn(PlayerRespawnEvent $event)
    {
        $player = $event->getPlayer();
        $this->main->getUiManager()->mainForm($player);
        $player->setImmobile(true);

        new BrightnessTask($this->main, $player->getName(), (int)$this->main->getConfig()->get('ui_time'));
    }

    /**
     * @param PlayerJoinEvent $event
     */
    public function onJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();
        if ($player->isImmobile()) {
            $player->setImmobile(false);
        }
    }
}
