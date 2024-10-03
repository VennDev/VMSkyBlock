<?php

namespace venndev\vmskyblock\api\event;

use pocketmine\item\Item;

interface IVMSBPlayerItemConsumeEvent
{
    public function getItem(): Item;
}