<?php

declare(strict_types=1);

namespace venndev\vmskyblock\player;

use pocketmine\player\Player;
use ReflectionProperty;
use venndev\vmskyblock\api\player\IVMSBPlayer;

final class VMSBPlayer implements IVMSBPlayer
{

    private readonly ReflectionProperty $_chunksPerTick;
    private int $chunksPerTickBeforeChange;
    private bool $changingDimension = false;

    public function __construct(private readonly Player $player)
    {
        static $_chunksPerTick = null;
        $_chunksPerTick ??= new ReflectionProperty(Player::class, "chunksPerTick");
        $this->_chunksPerTick = $_chunksPerTick;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function isChangingDimension(): bool
    {
        return $this->changingDimension;
    }

    public function onBeginDimensionChange(): void
    {
        $this->changingDimension = true;
        $this->chunksPerTickBeforeChange = $this->_chunksPerTick->getValue($this->player);
        if ($this->chunksPerTickBeforeChange < 40) {
            $this->_chunksPerTick->setValue($this->player, 40);
        }
    }

    public function onEndDimensionChange(): void
    {
        $this->changingDimension = false;
        $this->_chunksPerTick->setValue($this->player, $this->chunksPerTickBeforeChange);
    }

}