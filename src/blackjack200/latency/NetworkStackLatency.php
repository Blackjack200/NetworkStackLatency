<?php


namespace blackjack200\latency;


use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\NetworkStackLatencyPacket;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class NetworkStackLatency extends PluginBase implements Listener
{
    /** @var LatencyEntry[] */
    public array $session = [];

    public function onEnable() : void
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getScheduler()->scheduleRepeatingTask(
            new class($this) extends Task {
                private NetworkStackLatency $instance;

                public function __construct(NetworkStackLatency $instance)
                {
                    $this->instance = $instance;
                }

                public function onRun(int $currentTick) : void
                {
                    foreach (Server::getInstance()->getLoggedInPlayers() as $player) {
                        $session = &$this->instance->session[spl_object_hash($player)];
                        if ($session !== null) {
                            $player->sendDataPacket($session->getPacket(), true, true);
                            $session->time();
                        }
                    }
                }
            },
            2
        );
    }

    public function onPlayerJoin(PlayerJoinEvent $event) : void
    {
        $entry = (new LatencyEntry())->time();
        $event->getPlayer()->sendDataPacket($entry->getPacket(), true, true);
        $this->session[spl_object_hash($event->getPlayer())] = $entry;
    }

    public function onPlayerQuit(PlayerQuitEvent $event) : void
    {
        unset($this->session[spl_object_hash($event->getPlayer())]);
    }

    public function onDataPacketReceive(DataPacketReceiveEvent $event) : void
    {
        $pk = $event->getPacket();
        if (
            $pk instanceof NetworkStackLatencyPacket &&
            $pk->timestamp === 0b111111111111111111111111111111111111111111111111111110011011000
        ) {
            $session = &$this->session[spl_object_hash($event->getPlayer())];
            $session->latency($session->duration() * 1000);
            $event->getPlayer()->updatePing($session->getLatency());
        }
    }
}