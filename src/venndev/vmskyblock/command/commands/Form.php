<?php

declare(strict_types=1);

namespace venndev\vmskyblock\command\commands;

use Throwable;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use venndev\vmskyblock\command\SubCommand;
use venndev\vmskyblock\data\ConfigPaths;
use venndev\vmskyblock\data\Permissions;
use venndev\vmskyblock\forms\FormPlayer;
use venndev\vmskyblock\manager\MessageManager;
use venndev\vmskyblock\VMSkyBlock;
use vennv\vapm\Async;

final class Form extends SubCommand
{

    public function __construct()
    {
        parent::__construct(
            "form",
            "Open the form",
            "form",
            [
                "frm"
            ]
        );

        $this->setPermission(Permissions::COMMAND_FORM);
    }

    /**
     * @throws Throwable
     */
    protected function execute(CommandSender $sender, array $args): void
    {
        if ($sender instanceof Player) {
            new Async(function () use ($sender) {
                $dataIsland = Async::await(VMSkyBlock::getInstance()->getManager()->haveIsland($sender->getXuid()));
                if ($dataIsland) {
                    FormPlayer::getInstance($sender)->sendForm();
                } else {
                    $sender->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_NO_ISLAND));
                }
            });
        }
    }

}