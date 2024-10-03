<?php

namespace venndev\vmskyblock\api\event;

use pocketmine\item\Item;

interface IVMSBPlayerDropItemEvent
{
    public function getItem(): Item;
}