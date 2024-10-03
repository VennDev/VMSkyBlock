<?php

declare(strict_types=1);

namespace venndev\vmskyblock\event;

use pocketmine\block\Block;
use pocketmine\event\block\BlockEvent;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\item\Item;
use pocketmine\player\Player;
use venndev\vmskyblock\api\event\IVMSBBlockBreakEvent;

final class VMSBBlockBreakEvent extends BlockEvent implements Cancellable, IVMSBBlockBreakEvent
{
    use CancellableTrait;

    /** @var Item[] */
    protected array $blockDrops = [];

    /**
     * @param Item[] $drops
     */
    public function __construct(
        protected Player $player,
        Block            $block,
        protected Item   $item,
        protected bool   $instaBreak = false,
        array            $drops = [],
        protected int    $xpDrops = 0
    )
    {
        parent::__construct($block);
        $this->setDrops($drops);
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getItem(): Item
    {
        return clone $this->item;
    }

    public function getInstaBreak(): bool
    {
        return $this->instaBreak;
    }

    public function setInstaBreak(bool $instaBreak): void
    {
        $this->instaBreak = $instaBreak;
    }

    public function getDrops(): array
    {
        return $this->blockDrops;
    }

    public function setDrops(array $drops): void
    {
        $this->setDropsVariadic(...$drops);
    }

    public function setDropsVariadic(Item ...$drops): void
    {
        $this->blockDrops = $drops;
    }

    public function getXpDropAmount(): int
    {
        return $this->xpDrops;
    }

    public function setXpDropAmount(int $amount): void
    {
        if ($amount < 0) {
            throw new \InvalidArgumentException("Amount must be at least zero");
        }
        $this->xpDrops = $amount;
    }

}