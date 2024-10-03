<?php

namespace venndev\vmskyblock\api\event;

use pocketmine\item\Item;
use pocketmine\player\Player;

interface IVMSBBlockBreakEvent
{
    /**
     * Returns the player who is destroying the block.
     */
    public function getPlayer(): Player;

    /**
     * Returns the item used to destroy the block.
     */
    public function getItem(): Item;

    /**
     * Returns whether the block may be broken in less than the amount of time calculated. This is usually true for
     * creative players.
     */
    public function getInstaBreak(): bool;

    public function setInstaBreak(bool $instaBreak): void;

    /**
     * @return Item[]
     */
    public function getDrops(): array;

    /**
     * @param Item[] $drops
     */
    public function setDrops(array $drops): void;

    /**
     * Variadic hack for easy array member type enforcement.
     */
    public function setDropsVariadic(Item ...$drops): void;

    /**
     * Returns how much XP will be dropped by breaking this block.
     */
    public function getXpDropAmount(): int;

    /**
     * Sets how much XP will be dropped by breaking this block.
     */
    public function setXpDropAmount(int $amount): void;
}