<?php
#██╗░░██╗██╗██████╗░░█████╗░████████╗███████╗░█████╗░███╗░░░███╗
#██║░░██║██║██╔══██╗██╔══██╗╚══██╔══╝██╔════╝██╔══██╗████╗░████║
#███████║██║██████╔╝██║░░██║░░░██║░░░█████╗░░███████║██╔████╔██║
#██╔══██║██║██╔══██╗██║░░██║░░░██║░░░██╔══╝░░██╔══██║██║╚██╔╝██║
#██║░░██║██║██║░░██║╚█████╔╝░░░██║░░░███████╗██║░░██║██║░╚═╝░██║
#╚═╝░░╚═╝╚═╝╚═╝░░╚═╝░╚════╝░░░░╚═╝░░░╚══════╝╚═╝░░╚═╝╚═╝░░░░░╚═╝
namespace RomainSav\HiroTeam\tasks;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use RomainSav\HiroTeam\webhook\Message;

class WebhookSendAsyncTask extends AsyncTask
{
    /**
     * @var string
     */
    private $webhook_url;

    /**
     * @var Message
     */
    private $message;

    /**
     * WebhookSendAsyncTask constructor.
     * @param string $webhook_url
     * @param Message $message
     */
    public function __construct(string $webhook_url, Message $message)
    {
        $this->webhook_url = $webhook_url;
        $this->message = $message;
    }

    public function onRun()
    {
        $curl = curl_init($this->webhook_url);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($this->message));
        curl_setopt($curl, CURLOPT_POST,true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        $this->setResult(curl_exec($curl));
        curl_close($curl);
    }

    /**
     * @param Server $server
     */
    public function onCompletion(Server $server)
    {
        $response = $this->getResult();
        if ($response !== "") {
            $server->getLogger()->error("Webhook error: $response");
        }
    }
}
