<?php

namespace venndev\vmskyblock\api\event;

use pocketmine\entity\Entity;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;

interface IVMSBEntityItemPickupEvent
{
    public function getOrigin(): Entity;

    /**
     * Items to be received
     */
    public function getItem(): Item;

    /**
     * Change the items to receive.
     */
    public function setItem(Item $item): void;

    /**
     * Inventory to which received items will be added.
     */
    public function getInventory(): ?Inventory;

    /**
     * Change the inventory to which received items are added.
     */
    public function setInventory(?Inventory $inventory): void;
}