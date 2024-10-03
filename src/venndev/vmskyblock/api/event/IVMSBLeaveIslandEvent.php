<?php

namespace venndev\vmskyblock\api\event;

use pocketmine\player\Player;

interface IVMSBLeaveIslandEvent
{
    public function getPlayer(): Player;

    public function getIslandId(): string;
}