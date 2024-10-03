<?php

namespace venndev\vmskyblock\api\event;

use pocketmine\inventory\transaction\EnchantingTransaction;
use pocketmine\item\enchantment\EnchantingOption;
use pocketmine\item\Item;

interface IVMSBPlayerItemEnchantEvent
{
    /**
     * Returns the inventory transaction involved in this enchant event.
     */
    public function getTransaction(): EnchantingTransaction;

    /**
     * Returns the enchantment option used.
     */
    public function getOption(): EnchantingOption;

    /**
     * Returns the item to be enchanted.
     */
    public function getInputItem(): Item;

    /**
     * Returns the enchanted item.
     */
    public function getOutputItem(): Item;

    /**
     * Returns the number of XP levels and lapis that will be subtracted after enchanting
     * if the player is not in creative mode.
     */
    public function getCost(): int;
}