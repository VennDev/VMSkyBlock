<?php

declare(strict_types=1);

namespace venndev\vmskyblock\event;

use pocketmine\event\player\PlayerEvent;
use pocketmine\player\Player;
use venndev\vmskyblock\api\event\IVMSBLeaveIslandEvent;

final class VMSBLeaveIslandEvent extends PlayerEvent implements IVMSBLeaveIslandEvent
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