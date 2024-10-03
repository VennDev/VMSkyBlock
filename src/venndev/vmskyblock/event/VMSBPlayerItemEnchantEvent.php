<?php

declare(strict_types=1);

namespace venndev\vmskyblock\event;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\player\PlayerEvent;
use pocketmine\inventory\transaction\EnchantingTransaction;
use pocketmine\item\enchantment\EnchantingOption;
use pocketmine\item\Item;
use pocketmine\player\Player;
use venndev\vmskyblock\api\event\IVMSBPlayerItemEnchantEvent;

final class VMSBPlayerItemEnchantEvent extends PlayerEvent implements Cancellable, IVMSBPlayerItemEnchantEvent
{
    use CancellableTrait;

    public function __construct(
        Player                                 $player,
        private readonly EnchantingTransaction $transaction,
        private readonly EnchantingOption      $option,
        private readonly Item                  $inputItem,
        private readonly Item                  $outputItem,
        private readonly int                   $cost
    )
    {
        $this->player = $player;
    }

    public function getTransaction(): EnchantingTransaction
    {
        return $this->transaction;
    }

    public function getOption(): EnchantingOption
    {
        return $this->option;
    }

    public function getInputItem(): Item
    {
        return clone $this->inputItem;
    }

    public function getOutputItem(): Item
    {
        return clone $this->outputItem;
    }

    public function getCost(): int
    {
        return $this->cost;
    }

}