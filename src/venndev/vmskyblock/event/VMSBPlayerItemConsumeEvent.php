<?php

declare(strict_types=1);

namespace venndev\vmskyblock\event;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\player\PlayerEvent;
use pocketmine\item\Item;
use pocketmine\player\Player;
use venndev\vmskyblock\api\event\IVMSBPlayerItemConsumeEvent;

final class VMSBPlayerItemConsumeEvent extends PlayerEvent implements Cancellable, IVMSBPlayerItemConsumeEvent
{
    use CancellableTrait;

    public function __construct(
        Player       $player,
        private readonly Item $item
    )
    {
        $this->player = $player;
    }

    public function getItem(): Item
    {
        return clone $this->item;
    }

}