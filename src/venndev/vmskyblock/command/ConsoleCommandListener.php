<?php

declare(strict_types=1);

namespace venndev\vmskyblock\command;

use Throwable;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use venndev\vmskyblock\data\ConfigPaths;
use venndev\vmskyblock\manager\MessageManager;
use venndev\vmskyblock\utils\TypeIsland;
use venndev\vmskyblock\VMSkyBlock;

final readonly class ConsoleCommandListener
{

    public function __construct(private VMSkyBlock $plugin)
    {
        $this->plugin->getLogger()->notice(
            "Usage: /vmsbconsole to view all available commands for Console!"
        );
    }

    /**
     * @throws Throwable
     */
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if ($sender instanceof Player) return true;
        if ($command->getName() === "vmsbconsole") {
            if (isset($args[0])) {
                switch ($args[0]) {
                    case "giveitemportal":
                        if (!isset($args[1])) {
                            $sender->sendMessage(TextFormat::RED . "Usage: /vmsbconsole giveitemportal <player> <type(nether/the-end)> [count]");
                            return true;
                        }
                        $player = VMSkyBlock::getInstance()->getServer()->getPlayerExact($args[1]);
                        if ($player === null) {
                            $sender->sendMessage(TextFormat::RED . "Player not found");
                            return true;
                        }
                        !isset($args[3]) ? $count = 1 : $count = $args[3];
                        if (!is_numeric($count)) $sender->sendMessage(TextFormat::RED . "Count must be a number");
                        if ($args[2] === "nether") {
                            VMSkyBlock::getInstance()->getPlayerManager()->giveItemCreatePortal($player, TypeIsland::ISLAND_NETHER, (int)$count);
                        } elseif ($args[2] === "the-end") {
                            VMSkyBlock::getInstance()->getPlayerManager()->giveItemCreatePortal($player, TypeIsland::ISLAND_THE_END, (int)$count);
                        } else {
                            $sender->sendMessage(TextFormat::RED . "Invalid type");
                        }
                        break;
                    case "setsizeisland":
                        if (isset($args[1]) && isset($args[2]) && is_numeric($args[2])) {
                            $size = (int)$args[2];

                            if ($size < 1) {
                                $sender->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_ISLAND_SIZE_NOT_VALID));
                                return true;
                            }

                            VMSkyBlock::getInstance()->getManager()->changeSizeIsland($args[1], $size);
                        } else {
                            $sender->sendMessage("Usage: /vmsbconsole setsizeisland <name_island> <number>");
                        }
                        break;
                        default:
                            $sender->sendMessage(TextFormat::RED . "Invalid subcommand");
                            break;
                }
            } else {
                $sender->sendMessage(TextFormat::RED . "Usage: /vmsbconsole giveitemportal <player> <type(nether/the-end)> [count]");
                $sender->sendMessage(TextFormat::RED . "Usage: /vmsbconsole setsizeisland <name_island> <number>");
            }
            return true;
        }
        return false;
    }

    public function getPlugin(): VMSkyBlock
    {
        return $this->plugin;
    }

}