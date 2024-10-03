<?php

declare(strict_types=1);

namespace venndev\vmskyblock\command\commands;

use Throwable;
use pocketmine\utils\TextFormat;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use venndev\vmskyblock\command\SubCommand;
use venndev\vmskyblock\data\ConfigPaths;
use venndev\vmskyblock\data\Permissions;
use venndev\vmskyblock\manager\MessageManager;
use venndev\vmskyblock\VMSkyBlock;
use vennv\vapm\Async;

final class Top extends SubCommand
{

    public function __construct()
    {
        parent::__construct(
            "top",
            "Top SkyBlock islands",
            "top <limit>",
            [
                "tt"
            ]
        );

        $this->setPermission(Permissions::COMMAND_TOP);
    }

    /**
     * @throws Throwable
     */
    protected function execute(CommandSender $sender, array $args): void
    {
        if ($sender instanceof Player) {
            if (isset($args[0])) {
                if (!is_numeric($args[0])) {
                    $sender->sendMessage(TextFormat::RED . "Limit must be a number");
                    return;
                }

                $sender->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_WAITING));

                new Async(function () use ($sender, $args) {
                    $limit = (int)$args[0];
                    $top = Async::await(VMSkyBlock::getInstance()->getManager()->getTopIslands($limit));
                    $message = MessageManager::getNested(ConfigPaths::MESSAGE_TOP_ISLANDS, [
                        "%limit%" => $limit
                    ]);

                    foreach ($top as $index => $island) {
                        $message .= "\n" . MessageManager::getNested(ConfigPaths::MESSAGE_TOP_ISLANDS_FORMAT, [
                            "%rank%" => $index + 1,
                            "%owner%" => $island["owner"],
                            "%level%" => $island["level"],
                            "%xp%" => $island["xp"],
                        ]);
                    }

                    $sender->sendMessage($message);
                });
            } else {
                $sender->sendMessage("Usage: top <limit>");
            }
        }
    }

}