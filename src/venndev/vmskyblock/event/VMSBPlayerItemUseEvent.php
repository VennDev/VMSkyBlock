<?php

declare(strict_types=1);

namespace venndev\vmskyblock\event;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\player\PlayerEvent;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use venndev\vmskyblock\api\event\IVMSBPlayerItemUseEvent;

final class VMSBPlayerItemUseEvent extends PlayerEvent implements Cancellable, IVMSBPlayerItemUseEvent
{
    use CancellableTrait;

    public function __construct(
        Player $player,
        private readonly Item $item,
        private readonly Vector3 $directionVector
    ) {
        $this->player = $player;
    }

    public function getItem(): Item
    {
        return clone $this->item;
    }

    public function getDirectionVector(): Vector3
    {
        return $this->directionVector;
    }

}