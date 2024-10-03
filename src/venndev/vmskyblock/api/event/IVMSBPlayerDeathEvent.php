<?php

namespace venndev\vmskyblock\api\event;

use pocketmine\lang\Translatable;
use pocketmine\player\Player;

interface IVMSBPlayerDeathEvent
{
    /**
     * @return Player
     */
    public function getEntity(): Player;

    public function getPlayer(): Player;

    public function getDeathMessage(): Translatable|string;

    public function setDeathMessage(Translatable|string $deathMessage): void;

    public function getDeathScreenMessage(): Translatable|string;

    public function setDeathScreenMessage(Translatable|string $deathScreenMessage): void;

    public function getKeepInventory(): bool;

    public function setKeepInventory(bool $keepInventory): void;

    public function getKeepXp(): bool;

    public function setKeepXp(bool $keepXp): void;
}