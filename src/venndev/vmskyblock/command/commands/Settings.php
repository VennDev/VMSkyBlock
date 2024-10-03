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

final class Settings extends SubCommand
{

    private const SUB_COMMANDS = [
        "pvp" => Permissions::COMMAND_SET_PVP,
        "allow_visitor" => Permissions::COMMAND_SET_ALLOW_VISITOR,
        "dropped_item" => Permissions::COMMAND_SET_ALLOW_DROP_ITEM,
        "pick_up_item" => Permissions::COMMAND_SET_ALLOW_PICK_UP_ITEM,
    ];

    public function __construct()
    {
        parent::__construct(
            "settings",
            "Settings of the island",
            "settings <your_setting> <on|off>",
            [
                "sttgs",
            ]
        );

        $this->setPermission(Permissions::COMMAND_SETTINGS);
    }

    private function listSubCommands(CommandSender $sender): void
    {
        $sender->sendMessage("Available settings:");
        foreach (self::SUB_COMMANDS as $subCommand => $permission) {
            $sender->sendMessage("- $subCommand");
        }
    }

    protected function execute(CommandSender $sender, array $args): void
    {
        if ($sender instanceof Player) {
            if (
                isset($args[0]) && isset($args[1]) &&
                in_array($args[0], array_keys(self::SUB_COMMANDS)) &&
                in_array($args[1], ["on", "off"])
            ) {
                if (!$sender->hasPermission(self::SUB_COMMANDS[$args[0]])) {
                    $sender->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_NO_PERMISSION));
                    return;
                }

                VMSkyBlock::getInstance()->getManager()->setData($sender, $args[0], $args[1] === "on");
                $sender->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_SETTINGS_ISLAND_UPDATED));
            } else {
                $sender->sendMessage("Usage: settings <your_setting> <on|off>");
                $this->listSubCommands($sender);
            }
        }
    }

}