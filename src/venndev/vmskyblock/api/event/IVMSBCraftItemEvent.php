<?php

namespace venndev\vmskyblock\api\event;

use pocketmine\crafting\CraftingRecipe;
use pocketmine\inventory\transaction\CraftingTransaction;
use pocketmine\item\Item;
use pocketmine\player\Player;

interface IVMSBCraftItemEvent
{
    /**
     * Returns the inventory transaction involved in this crafting event.
     */
    public function getTransaction(): CraftingTransaction;

    /**
     * Returns the recipe crafted.
     */
    public function getRecipe(): CraftingRecipe;

    /**
     * Returns the number of times the recipe was crafted. This is usually 1, but might be more in the case of recipe
     * book shift-clicks (which craft lots of items in a batch).
     */
    public function getRepetitions(): int;

    /**
     * Returns a list of items destroyed as ingredients of the recipe.
     *
     * @return Item[]
     */
    public function getInputs(): array;

    /**
     * Returns a list of items created by crafting the recipe.
     *
     * @return Item[]
     */
    public function getOutputs(): array;

    public function getPlayer(): Player;
}