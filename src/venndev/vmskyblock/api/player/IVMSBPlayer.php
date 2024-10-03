<?php

namespace venndev\vmskyblock\api\player;

use pocketmine\player\Player;

interface IVMSBPlayer
{
    public function getPlayer(): Player;

    public function isChangingDimension(): bool;

    public function onBeginDimensionChange(): void;

    public function onEndDimensionChange(): void;
}