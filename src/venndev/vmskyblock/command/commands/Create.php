<?php

declare(strict_types=1);

namespace venndev\vmskyblock\command\commands;

use Throwable;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use venndev\vmskyblock\command\SubCommand;
use venndev\vmskyblock\data\Permissions;
use venndev\vmskyblock\VMSkyBlock;

final class Create extends SubCommand
{

    public function __construct()
    {
        parent::__construct(
            "create",
            "Create a SkyBlock island",
            "create",
            [
                "cr"
            ]
        );

        $this->setPermission(Permissions::COMMAND_CREATE);
    }

    /**
     * @throws Throwable
     */
    protected function execute(CommandSender $sender, array $args): void
    {
        if ($sender instanceof Player) VMSkyBlock::getInstance()->getManager()->create($sender);
    }

}