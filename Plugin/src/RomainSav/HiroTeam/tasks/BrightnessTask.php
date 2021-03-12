<?php

namespace RomainSav\HiroTeam\tasks;

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use RomainSav\HiroTeam\DeathReport;

class BrightnessTask extends Task
{
    /**
     * @var DeathReport
     */
    private $main;

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $seconds;

    /**
     * BrightnessTask constructor.
     * @param DeathReport $main
     * @param string $name
     * @param int $seconds
     */
    public function __construct(DeathReport $main, string $name, int $seconds)
    {
        $this->main = $main;
        $this->name = $name;
        $this->seconds = $seconds;
        $main->getScheduler()->scheduleRepeatingTask($this, 20);
    }

    public function onRun(int $currentTick)
    {
        $player = $this->main->getServer()->getPlayer($this->name);

        if ($player && $player instanceof Player) {

            if ($this->seconds > 0) {

                $player->addEffect(new EffectInstance(Effect::getEffect(Effect::BLINDNESS), 40));

                $time = $this->main->convert((int)$this->seconds);

                $msg = $this->main->getConfig()->get('hotbar_cooldown');
                $msg = str_replace('{HOURS}', $time['hours'], $msg);
                $msg = str_replace('{MIN}', $time['min'], $msg);
                $msg = str_replace('{SEC}', $time['sec'], $msg);
                $player->sendPopup($msg);
                $this->seconds--;

            } else {

                if ($player->hasEffect(Effect::BLINDNESS)) {
                    $player->removeEffect(Effect::BLINDNESS);
                }
                if ($player->isImmobile()) {
                    $player->setImmobile(false);
                }
                $this->main->getScheduler()->cancelTask($this->getTaskId());
            }
        } else {

            $this->main->getScheduler()->cancelTask($this->getTaskId());
        }
    }
}
