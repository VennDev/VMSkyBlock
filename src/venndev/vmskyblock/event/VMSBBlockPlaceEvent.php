<?php

declare(strict_types=1);

namespace venndev\vmskyblock\event;

use pocketmine\block\Block;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use venndev\vmskyblock\api\event\IVMSBBlockPlaceEvent;

final class VMSBBlockPlaceEvent extends Event implements Cancellable, IVMSBBlockPlaceEvent
{
    use CancellableTrait;

    public function __construct(
        protected Player           $player,
        protected BlockTransaction $transaction,
        protected Block            $blockAgainst,
        protected Item             $item
    )
    {
        $world = $this->blockAgainst->getPosition()->getWorld();
        foreach ($this->transaction->getBlocks() as [$x, $y, $z, $block]) {
            $block->position($world, $x, $y, $z);
        }
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getItem(): Item
    {
        return clone $this->item;
    }

    public function getTransaction(): BlockTransaction
    {
        return $this->transaction;
    }

    public function getBlockAgainst(): Block
    {
        return $this->blockAgainst;
    }

}