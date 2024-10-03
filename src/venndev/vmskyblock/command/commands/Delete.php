<?php

declare(strict_types=1);

namespace venndev\vmskyblock\command\commands;

use Throwable;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use venndev\vmskyblock\command\SubCommand;
use venndev\vmskyblock\data\Permissions;
use venndev\vmskyblock\VMSkyBlock;

final class Delete extends SubCommand
{

    public function __construct()
    {
        parent::__construct(
            "delete",
            "Delete a SkyBlock island",
            "delete",
            [
                "dlt"
            ]
        );

        $this->setPermission(Permissions::COMMAND_DELETE);
    }

    /**
     * @throws Throwable
     */
    protected function execute(CommandSender $sender, array $args): void
    {
        if ($sender instanceof Player) VMSkyBlock::getInstance()->getManager()->delete($sender);
    }

}