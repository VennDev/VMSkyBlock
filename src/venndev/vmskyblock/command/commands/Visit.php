<?php

declare(strict_types=1);

namespace venndev\vmskyblock\command\commands;

use Throwable;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use venndev\vmskyblock\command\SubCommand;
use venndev\vmskyblock\data\ConfigPaths;
use venndev\vmskyblock\data\DataIslandPlayer;
use venndev\vmskyblock\data\Permissions;
use venndev\vmskyblock\manager\MessageManager;
use venndev\vmskyblock\VMSkyBlock;

final class Visit extends SubCommand
{

    public function __construct()
    {
        parent::__construct(
            "visit",
            "Visit a SkyBlock island",
            "visit <player>",
            [
                "vst"
            ]
        );

        $this->setPermission(Permissions::COMMAND_VISIT);
    }

    /**
     * @throws Throwable
     */
    protected function execute(CommandSender $sender, array $args): void
    {
        if ($sender instanceof Player) {
            if (isset($args[0])) {
                $player = VMSkyBlock::getInstance()->getServer()->getPlayerExact($args[0]);
                if ($player) {
                    VMSkyBlock::getInstance()->getManager()->getIslandDataByMember($player->getXuid())->then(function ($islandData) use ($sender) {
                        /** @var DataIslandPlayer|null $islandData */
                        if ($islandData !== null && $islandData->getAllowVisitors() === true) {
                            VMSkyBlock::getInstance()->getManager()->visit($sender, $islandData->getIsland());
                        } else {
                            $sender->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_VISIT_NOT_ALLOWED));
                        }
                    });
                }
            } else {
                $sender->sendMessage("Usage: visit <player>");
            }
        }
    }

}