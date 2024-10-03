<?php

declare(strict_types=1);

namespace venndev\vmskyblock\event;

use pocketmine\crafting\CraftingRecipe;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;
use pocketmine\inventory\transaction\CraftingTransaction;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\utils\Utils;
use venndev\vmskyblock\api\event\IVMSBCraftItemEvent;

final class VMSBCraftItemEvent extends Event implements Cancellable, IVMSBCraftItemEvent
{
    use CancellableTrait;

    /**
     * @param Item[] $inputs
     * @param Item[] $outputs
     */
    public function __construct(
        private readonly CraftingTransaction $transaction,
        private readonly CraftingRecipe      $recipe,
        private readonly int                 $repetitions,
        private readonly array               $inputs,
        private readonly array $outputs
    )
    {
        // TODO: Implement constructor
    }

    public function getTransaction(): CraftingTransaction
    {
        return $this->transaction;
    }

    public function getRecipe(): CraftingRecipe
    {
        return $this->recipe;
    }

    public function getRepetitions(): int
    {
        return $this->repetitions;
    }

    public function getInputs(): array
    {
        return Utils::cloneObjectArray($this->inputs);
    }

    public function getOutputs(): array
    {
        return Utils::cloneObjectArray($this->outputs);
    }

    public function getPlayer(): Player
    {
        return $this->transaction->getSource();
    }

}