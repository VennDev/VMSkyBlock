<?php

declare(strict_types=1);

namespace venndev\vmskyblock\event;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\world\Position;

final class VMSBCreatePortal extends Event implements Cancellable
{
    use CancellableTrait;

    public function __construct(
        private readonly Player   $player,
        private readonly Position $position,
        private readonly string   $type
    )
    {
        // TODO: Implement __construct() method.
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getPosition(): Position
    {
        return $this->position;
    }

    public function getType(): string
    {
        return $this->type;
    }

}