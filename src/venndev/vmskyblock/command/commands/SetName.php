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

final class SetName extends SubCommand
{

    public function __construct()
    {
        parent::__construct(
            "setname",
            "Set the name of your island",
            "setname <name>",
            [
                "snm"
            ]
        );

        $this->setPermission(Permissions::COMMAND_SET_NAME);
    }

    protected function execute(CommandSender $sender, array $args): void
    {
        if ($sender instanceof Player) {
            if (isset($args[0])) {
                VMSkyBlock::getInstance()->getManager()->setNamedIsland($sender, implode(" ", $args));
                $sender->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_NAME_HAS_BEEN_CHANGED));
            } else {
                $sender->sendMessage("Usage: setname <name>");
            }
        }
    }

}