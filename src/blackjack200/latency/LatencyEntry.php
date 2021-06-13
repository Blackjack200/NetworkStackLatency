<?php


namespace blackjack200\latency;


use pocketmine\network\mcpe\protocol\NetworkStackLatencyPacket;

class LatencyEntry
{
    private NetworkStackLatencyPacket $packet;
    private float $time;
    private float $latency;

    public function __construct()
    {
        $pk = new NetworkStackLatencyPacket();
        $pk->needResponse = true;
        $pk->timestamp = 0b1000000000000000000000000000000000000000000000000000000000000000;
        $this->packet = $pk;
    }

    public function getLatency() : float
    {
        return $this->latency;
    }

    public function latency(float $latency) : LatencyEntry
    {
        $this->latency = $latency;
        return $this;
    }

    public function getPacket() : NetworkStackLatencyPacket
    {
        return $this->packet;
    }

    public function time() : LatencyEntry
    {
        $this->time = microtime(true);
        return $this;
    }

    public function duration() : float
    {
        return microtime(true) - $this->time;
    }
}