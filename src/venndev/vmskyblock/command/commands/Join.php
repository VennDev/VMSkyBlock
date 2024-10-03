<?php

declare(strict_types=1);

namespace venndev\vmskyblock\command\commands;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use venndev\vmskyblock\command\SubCommand;
use venndev\vmskyblock\data\Permissions;
use venndev\vmskyblock\VMSkyBlock;
use Throwable;

final class Join extends SubCommand
{

    public function __construct()
    {
        parent::__construct(
            "join",
            "Join a SkyBlock island",
            "join",
            [
                "jn"
            ]
        );

        $this->setPermission(Permissions::COMMAND_JOIN);
    }

    /**
     * @throws Throwable
     */
    protected function execute(CommandSender $sender, array $args): void
    {
        if ($sender instanceof Player) VMSkyBlock::getInstance()->getManager()->join($sender);
    }

}