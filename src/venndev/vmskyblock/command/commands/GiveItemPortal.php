<?php

declare(strict_types=1);

namespace venndev\vmskyblock\command\commands;

use Throwable;
use pocketmine\utils\TextFormat;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use venndev\vmskyblock\command\SubCommand;
use venndev\vmskyblock\data\Permissions;
use venndev\vmskyblock\utils\TypeIsland;
use venndev\vmskyblock\VMSkyBlock;

final class GiveItemPortal extends SubCommand
{

    public function __construct()
    {
        parent::__construct(
            "giveitemportal",
            "Give a portal item",
            "giveitemportal",
            [
                "gip"
            ]
        );

        $this->setPermission(Permissions::COMMAND_GIVE_ITEM_PORTAL);
    }

    /**
     * @throws Throwable
     */
    protected function execute(CommandSender $sender, array $args): void
    {
        if ($sender instanceof Player) {
            !isset($args[1]) ? $count = 1 : $count = $args[1];
            if (!is_numeric($count)) $sender->sendMessage(TextFormat::RED . "Count must be a number");
            if (!isset($args[0])) {
                $sender->sendMessage(TextFormat::RED . "Usage: /vmsb giveitemportal <type(nether/the-end)> [count]");
                return;
            }
            if ($args[0] === "nether") {
                VMSkyBlock::getInstance()->getPlayerManager()->giveItemCreatePortal($sender, TypeIsland::ISLAND_NETHER, (int)$count);
            } elseif ($args[0] === "the-end") {
                VMSkyBlock::getInstance()->getPlayerManager()->giveItemCreatePortal($sender, TypeIsland::ISLAND_THE_END, (int)$count);
            } else {
                $sender->sendMessage(TextFormat::RED . "Invalid type");
            }
        }
    }

}