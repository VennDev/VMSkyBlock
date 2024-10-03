<?php

declare(strict_types=1);

namespace venndev\vmskyblock\command\commands;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use venndev\vmskyblock\command\SubCommand;
use venndev\vmskyblock\data\Permissions;
use venndev\vmskyblock\VMSkyBlock;

final class About extends SubCommand
{

    public function __construct()
    {
        parent::__construct(
            "about",
            "About VMSkyBlock",
            "about",
            [
                "abt"
            ]
        );

        $this->setPermission(Permissions::COMMAND_ABOUT);
    }

    protected function execute(CommandSender $sender, array $args): void
    {
        if ($sender instanceof Player) {
            $sender->sendMessage(TextFormat::GREEN . "\n\nVMSkyBlock v" . VMSkyBlock::getInstance()->getDescription()->getVersion());
            $sender->sendMessage(TextFormat::GREEN . "Developed by VennDev");
            $sender->sendMessage(TextFormat::GREEN . "Github: https://github.com/VennDev");
            $sender->sendMessage(TextFormat::GREEN . "Email: pnam5005@gmail.com");
        }
    }

}