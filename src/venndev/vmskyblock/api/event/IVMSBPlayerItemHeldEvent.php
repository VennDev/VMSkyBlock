<?php

namespace venndev\vmskyblock\api\event;

use pocketmine\item\Item;

interface IVMSBPlayerItemHeldEvent
{
    /**
     * Returns the hot bar slot the player is attempting to hold.
     *
     * NOTE: This event is called BEFORE the slot is equipped server-side. Setting the player's held item during this
     * event will result in the **old** slot being changed, not this one.
     *
     * To change the item in the slot that the player is attempting to hold, set the slot that this function reports.
     */
    public function getSlot(): int;

    /**
     * Returns the item in the slot that the player is trying to equip.
     */
    public function getItem(): Item;
}