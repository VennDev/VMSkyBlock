<?php

namespace venndev\vmskyblock\api\event;

use pocketmine\player\Player;

interface IVMSBJoinIslandEvent
{
    public function getPlayer(): Player;

    public function getIslandId(): string;
}