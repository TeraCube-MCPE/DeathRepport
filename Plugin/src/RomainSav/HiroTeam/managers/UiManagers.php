<?php
#██╗░░██╗██╗██████╗░░█████╗░████████╗███████╗░█████╗░███╗░░░███╗
#██║░░██║██║██╔══██╗██╔══██╗╚══██╔══╝██╔════╝██╔══██╗████╗░████║
#███████║██║██████╔╝██║░░██║░░░██║░░░█████╗░░███████║██╔████╔██║
#██╔══██║██║██╔══██╗██║░░██║░░░██║░░░██╔══╝░░██╔══██║██║╚██╔╝██║
#██║░░██║██║██║░░██║╚█████╔╝░░░██║░░░███████╗██║░░██║██║░╚═╝░██║
#╚═╝░░╚═╝╚═╝╚═╝░░╚═╝░╚════╝░░░░╚═╝░░░╚══════╝╚═╝░░╚═╝╚═╝░░░░░╚═╝
namespace RomainSav\HiroTeam\managers;

use pocketmine\Player;
use RomainSav\HiroTeam\DeathReport;
use RomainSav\HiroTeam\forms\CustomForm;
use RomainSav\HiroTeam\forms\ModalForm;
use RomainSav\HiroTeam\forms\SimpleForm;
use RomainSav\HiroTeam\webhook\Embed;
use RomainSav\HiroTeam\webhook\Message;
use RomainSav\HiroTeam\webhook\Webhook;

class UiManagers
{
    /**
     * @var DeathReport
     */
    private $main;

    /**
     * @var array
     */
    private $reportCooldown = [];

    /**
     * UiManagers constructor.
     * @param DeathReport $main
     */
    public function __construct(DeathReport $main)
    {
        $this->main = $main;
    }

    /**
     * @param Player $player
     * @return SimpleForm
     */
    public function mainForm(Player $player): SimpleForm
    {
        $form = new SimpleForm(function (Player $player, $data = null) {
            $target = $data;
            if (is_null($target)) return;

            if (isset($this->reportCooldown[$player->getName()]) && $this->reportCooldown[$player->getName()] > time()) {
                $this->mainForm($player);
                return;
            }

            switch ($target) {
                case 0:
                    $this->killsForm($player);
                    break;
                case 1:
                    $this->reportCooldown[$player->getName()] = (time() + 10);
                    $this->sendBugInfo($player, "map");
                    $this->mainForm($player);
                    break;
            }
        });
        $form->setTitle($this->main->getConfig()->get('forms')['main']['form_title']);
        $form->setContent($this->main->getConfig()->get('forms')['main']['form_content']);
        $form->addButton($this->main->getConfig()->get('forms')['main']['kills_button']);
        $form->addButton($this->main->getConfig()->get('forms')['main']['map_bug_button']);
        $form->addButton($this->main->getConfig()->get('forms')['main']['close_button']);
        $form->sendToPlayer($player);
        return $form;
    }

    /**
     * @param Player $player
     * @return CustomForm
     */
    public function killsForm(Player $player): CustomForm
    {
        $form = new CustomForm(function (Player $player, $data = null) {
            $target = $data;
            if (is_null($target)) {
                $this->mainForm($player);
                return;
            }

            if (isset($this->reportCooldown[$player->getName()]) && $this->reportCooldown[$player->getName()] > time()) {
                $this->mainForm($player);
                return;
            }

            $this->reportCooldown[$player->getName()] = (time() + 10);
            $this->sendBugInfo($player, "kill", is_null($target[1]) ? "" : $target[1], is_null($target[2]) ? "" : $target[2]);
            $this->mainForm($player);
        });
        $form->setTitle($this->main->getConfig()->get('forms')['kills']['form_title']);
        $form->addLabel($this->main->getConfig()->get('forms')['kills']['form_content']);
        $form->addInput("Joueur à report", "Joueur...");
        $form->addInput("Explications", "Explications...");
        $form->sendToPlayer($player);
        return $form;
    }

    /**
     * @param Player $player
     * @param string $reportType
     * @param string $reportPlayer
     * @param string $explications
     */
    public function sendBugInfo(Player $player, string $reportType, string $reportPlayer = "", string $explications = "")
    {
        $data = $this->main->lastWorlds[$player->getName()];
        $webhook = new Webhook($this->main->getConfig()->get('webhook_url'));
        $msg = new Message();
        $embed = new Embed();
        switch ($reportType) {

            case 'map':
                $embed->setTitle("Report de bug de map");
                $embed->setDescription("**Pseudo:** " . $player->getName() . "\n**Coordonnées:** " . $data['x'] . ", " . $data['y'] . ", " . $data['z'] . "\n**Monde:** " . $data['world']);
                $embed->setTimestamp(new \DateTime());
                $this->main->getReportManager()->add($player->getName(), 'map', '-', '-', $data['x'] . "_" . $data['y'] . "_" . $data['z'], $data['world']);
                break;

            case 'kill':
                $embed->setTitle("Report de mort abusive");
                $embed->setDescription("**Pseudo:** " . $player->getName() . "\n**Pseudo Report:** " . $reportPlayer . "\n**Explications:** " . $explications);
                $embed->setTimestamp(new \DateTime());
                $this->main->getReportManager()->add($player->getName(), 'kill', $reportPlayer, $explications ?? '-');
                break;
        }
        $embed->setColor(3447003);
        $msg->addEmbed($embed);
        $webhook->send($msg);
    }

    /**
     * @param Player $player
     * @return SimpleForm
     */
    public function choseReportsList(Player $player)
    {
        $form = new SimpleForm(function (Player $player, $data = null) {
            $target = $data;
            if (is_null($target)) return;
            $this->reportsList($player, $target);
        });
        $form->setTitle($this->main->getConfig()->get('forms')['admin']['form_title']);
        $form->setContent($this->main->getConfig()->get('forms')['admin']['form_content']);
        $form->addButton($this->main->getConfig()->get('forms')['main']['kills_button'], -1, '', 'kill');
        $form->addButton($this->main->getConfig()->get('forms')['main']['map_bug_button'], -1, '', 'map');
        $form->sendToPlayer($player);
        return $form;
    }

    /**
     * @param Player $player
     * @param string $type
     * @return SimpleForm
     */
    public function reportsList(Player $player, string $type)
    {
        $form = new SimpleForm(function (Player $player, $data = null) use ($type) {
            $target = $data;
            if (is_null($target)) return;
            $this->reportDetail($player, $type, $target);
        });
        $form->setTitle($this->main->getConfig()->get('forms')['admin']['form_title']);
        $form->setContent($this->main->getConfig()->get('forms')['admin']['form_content']);
        if ($type === 'kill') {
            foreach ($this->main->getReportManager()->getAll($type) as $id => $param) {
                $form->addButton("Kill de " . $param['name'] . " par " . $param['reportPlayer'], -1, '', $id);
            }
        }
        if ($type === 'map') {
            foreach ($this->main->getReportManager()->getAll($type) as $id => $param) {
                $form->addButton($param['name'], -1, '', $id);
            }
        }
        $form->sendToPlayer($player);
        return $form;
    }

    public function reportDetail(Player $player, string $type, int $id)
    {
        $form = new SimpleForm(function (Player $player, $data = null) use ($type) {
            $target = $data;
            if (is_null($target)) return;
            $this->reportsList($player, $type);
        });
        $data = $this->main->getReportManager()->getAll($type)[$id];
        $form->setTitle($this->main->getConfig()->get('forms')['admin']['form_title']);
        if ($type === 'kill') {
            $form->setContent("Report de kill abusif\n\nKill par : " . (string)$data['reportPlayer'] . "\nVictime : " . (string)$data['name'] . "\n\nExplications : " . (string)$data['explications']);
            $form->addButton($this->main->getConfig()->get('forms')['admin']['back']);
        }
        if ($type === 'map') {
            $form->setContent("Report de bug de map\n\nJoueur : ".(string)$data['name']."\n\nCoordonnées : ".str_replace('_', ' ', $data['coordinates']."\n\nMonde : ".(string)$data['world']));
            $form->addButton($this->main->getConfig()->get('forms')['admin']['back']);
        }
        $form->sendToPlayer($player);
        return $form;
    }
}
