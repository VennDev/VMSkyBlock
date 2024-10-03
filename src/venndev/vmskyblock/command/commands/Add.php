<?php

declare(strict_types=1);

namespace venndev\vmskyblock\command\commands;

use Throwable;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use venndev\vmskyblock\command\SubCommand;
use venndev\vmskyblock\data\ConfigPaths;
use venndev\vmskyblock\data\Permissions;
use venndev\vmskyblock\manager\MessageManager;
use venndev\vmskyblock\VMSkyBlock;

final class Add extends SubCommand
{

    public function __construct()
    {
        parent::__construct(
            "add",
            "Add new player to your island",
            "add <player>",
            [
                "a"
            ]
        );

        $this->setPermission(Permissions::COMMAND_ADD);
    }

    /**
     * @throws Throwable
     */
    protected function execute(CommandSender $sender, array $args): void
    {
        if ($sender instanceof Player) {
            if (isset($args[0])) {
                $player = VMSkyBlock::getInstance()->getServer()->getPlayerExact(implode(" ", $args));
                if ($player) {
                    VMSkyBlock::getInstance()->getManager()->addMember($sender, $player->getXuid());
                } else {
                    $sender->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_PLAYER_NOT_FOUND));
                }
            } else {
                $sender->sendMessage("Usage: add <player>");
            }
        }
    }

}