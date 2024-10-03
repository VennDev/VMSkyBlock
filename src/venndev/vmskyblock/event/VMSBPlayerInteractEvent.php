<?php

declare(strict_types=1);

namespace venndev\vmskyblock\event;

use pocketmine\block\Block;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\player\PlayerEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use venndev\vmskyblock\api\event\IVMSBPlayerInteractEvent;

final class VMSBPlayerInteractEvent extends PlayerEvent implements Cancellable, IVMSBPlayerInteractEvent
{
    use CancellableTrait;

    public const LEFT_CLICK_BLOCK = 0;
    public const RIGHT_CLICK_BLOCK = 1;

    protected Vector3 $touchVector;

    public function __construct(
        Player          $player,
        protected Item  $item,
        protected Block $blockTouched,
        ?Vector3        $touchVector,
        protected int   $blockFace,
        protected int   $action = PlayerInteractEvent::RIGHT_CLICK_BLOCK
    )
    {
        $this->player = $player;
        $this->touchVector = $touchVector ?? Vector3::zero();
    }

    public function getAction(): int
    {
        return $this->action;
    }

    public function getItem(): Item
    {
        return clone $this->item;
    }

    public function getBlock(): Block
    {
        return $this->blockTouched;
    }

    public function getTouchVector(): Vector3
    {
        return $this->touchVector;
    }

    public function getFace(): int
    {
        return $this->blockFace;
    }

}