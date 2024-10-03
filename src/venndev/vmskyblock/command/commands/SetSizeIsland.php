<?php

declare(strict_types=1);

namespace venndev\vmskyblock\command\commands;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use venndev\vmskyblock\command\SubCommand;
use venndev\vmskyblock\data\ConfigPaths;
use venndev\vmskyblock\data\Permissions;
use venndev\vmskyblock\manager\MessageManager;
use venndev\vmskyblock\VMSkyBlock;

final class SetSizeIsland extends SubCommand
{

    public function __construct()
    {
        parent::__construct(
            "set-size-island",
            "Set size of your island",
            "set-size-island <name_island> <number>",
            [
                "ssi"
            ]
        );

        $this->setPermission(Permissions::COMMAND_SET_SIZE_ISLAND);
    }

    protected function execute(CommandSender $sender, array $args): void
    {
        if ($sender instanceof Player) {
            if (isset($args[0]) && isset($args[1]) && is_numeric($args[1])) {
                $size = (int)$args[1];

                if ($size < 1) {
                    $sender->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_ISLAND_SIZE_NOT_VALID));
                    return;
                }

                VMSkyBlock::getInstance()->getManager()->changeSizeIsland($args[0], $size);
            } else {
                $sender->sendMessage("Usage: set-size-island <name_island> <number>");
            }
        }
    }

}