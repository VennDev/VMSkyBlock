<?php

namespace venndev\vmskyblock\api\event;

use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\math\Vector3;

interface IVMSBPlayerInteractEvent
{
    public function getAction(): int;

    public function getItem(): Item;

    public function getBlock(): Block;

    public function getTouchVector(): Vector3;

    public function getFace(): int;
}