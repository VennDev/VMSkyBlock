<?php

declare(strict_types=1);

$main = function (\pocketmine\player\Player $player) : void {
    $player->sendMessage("You have received a reward!");
};