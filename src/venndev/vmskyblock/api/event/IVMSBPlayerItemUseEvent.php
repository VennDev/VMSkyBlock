<?php

namespace venndev\vmskyblock\api\event;

use pocketmine\item\Item;
use pocketmine\math\Vector3;

interface IVMSBPlayerItemUseEvent
{
    /**
     * Returns the item used.
     */
    public function getItem(): Item;

    /**
     * Returns the direction the player is aiming when activating this item. Used for projectile direction.
     */
    public function getDirectionVector(): Vector3;
}