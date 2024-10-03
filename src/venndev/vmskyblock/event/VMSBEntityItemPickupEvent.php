<?php

declare(strict_types=1);

namespace venndev\vmskyblock\event;

use pocketmine\entity\Entity;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\entity\EntityEvent;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use venndev\vmskyblock\api\event\IVMSBEntityItemPickupEvent;

final class VMSBEntityItemPickupEvent extends EntityEvent implements Cancellable, IVMSBEntityItemPickupEvent
{
    use CancellableTrait;

    public function __construct(
        Entity $collector,
        private readonly Entity $origin,
        private Item $item,
        private ?Inventory $inventory
    ) {
        $this->entity = $collector;
    }

    public function getOrigin(): Entity
    {
        return $this->origin;
    }

    public function getItem(): Item
    {
        return clone $this->item;
    }

    public function setItem(Item $item): void
    {
        $this->item = clone $item;
    }

    public function getInventory(): ?Inventory
    {
        return $this->inventory;
    }

    public function setInventory(?Inventory $inventory): void
    {
        $this->inventory = $inventory;
    }

}