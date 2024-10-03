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

final class UnbanItem extends SubCommand
{

    public function __construct()
    {
        parent::__construct(
            "unban-item",
            "Unban an item from being used in your island",
            "unban-item",
            [
                "ubi",
            ]
        );

        $this->setPermission(Permissions::COMMAND_UNBAN_ITEM);
    }

    /**
     * @throws Throwable
     */
    protected function execute(CommandSender $sender, array $args): void
    {
        if ($sender instanceof Player) {
            $item = clone $sender->getInventory()->getItemInHand();
            if (!$item->isNull()) {
                VMSkyBlock::getInstance()->getManager()->unbanItemIsland($sender, $item);
            } else {
                $sender->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_HAND_EMPTY));
            }
        }
    }

}