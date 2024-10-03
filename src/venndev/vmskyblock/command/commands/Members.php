<?php

declare(strict_types=1);

namespace venndev\vmskyblock\command\commands;

use Throwable;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use venndev\vapmdatabase\database\ResultQuery;
use venndev\vmskyblock\command\SubCommand;
use venndev\vmskyblock\data\ConfigPaths;
use venndev\vmskyblock\data\Permissions;
use venndev\vmskyblock\manager\MessageManager;
use venndev\vmskyblock\VMSkyBlock;
use venndev\vplayerdatasaver\VPlayerDataSaver;
use vennv\vapm\Async;

final class Members extends SubCommand
{

    public function __construct()
    {
        parent::__construct(
            "members",
            "List all members of your island",
            "members",
            [
                "mbrs"
            ]
        );

        $this->setPermission(Permissions::COMMAND_MEMBERS);
    }

    /**
     * @throws Throwable
     */
    protected function execute(CommandSender $sender, array $args): void
    {
        if ($sender instanceof Player) {
            new Async(function () use ($sender) {
                $members = Async::await(VMSkyBlock::getInstance()->getManager()->getMembersByPlayer($sender));
                foreach ($members as $key => $member) {
                    /** @var ResultQuery|array $data */
                    $data = Async::await(Async::await(VPlayerDataSaver::getDataByXuid($member)));
                    if ($data instanceof ResultQuery) $data = $data->getResult();
                    $members[$key] = $data["name"];
                }
                $members = implode(", ", $members);
                $sender->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_MEMBERS_LIST, ["%members%" => $members]));
            });
        }
    }

}