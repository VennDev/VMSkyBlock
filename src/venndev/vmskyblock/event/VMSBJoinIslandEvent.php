<?php

declare(strict_types=1);

namespace venndev\vmskyblock\event;

use pocketmine\event\player\PlayerEvent;
use pocketmine\player\Player;
use venndev\vmskyblock\api\event\IVMSBJoinIslandEvent;

final class VMSBJoinIslandEvent extends PlayerEvent implements IVMSBJoinIslandEvent
{

    public function __construct(
        protected Player        $player,
        private readonly string $islandId
    )
    {
        // TODO: Implement constructor
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getIslandId(): string
    {
        return $this->islandId;
    }

}