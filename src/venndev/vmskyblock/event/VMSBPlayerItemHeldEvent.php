<?php

declare(strict_types=1);

namespace venndev\vmskyblock\event;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\player\PlayerEvent;
use pocketmine\item\Item;
use pocketmine\player\Player;
use venndev\vmskyblock\api\event\IVMSBPlayerItemHeldEvent;

final class VMSBPlayerItemHeldEvent extends PlayerEvent implements Cancellable, IVMSBPlayerItemHeldEvent
{
    use CancellableTrait;

    public function __construct(
        Player                $player,
        private readonly Item $item,
        private readonly int  $hotbarSlot
    )
    {
        $this->player = $player;
    }

    public function getSlot(): int
    {
        return $this->hotbarSlot;
    }

    public function getItem(): Item
    {
        return clone $this->item;
    }

}