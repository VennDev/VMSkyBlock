<?php

declare(strict_types=1);

namespace venndev\vmskyblock\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat;
use venndev\vmskyblock\command\commands\About;
use venndev\vmskyblock\command\commands\Add;
use venndev\vmskyblock\command\commands\Ban;
use venndev\vmskyblock\command\commands\BanItem;
use venndev\vmskyblock\command\commands\Create;
use venndev\vmskyblock\command\commands\Delete;
use venndev\vmskyblock\command\commands\Form;
use venndev\vmskyblock\command\commands\GiveItemPortal;
use venndev\vmskyblock\command\commands\Join;
use venndev\vmskyblock\command\commands\Kick;
use venndev\vmskyblock\command\commands\Members;
use venndev\vmskyblock\command\commands\Remove;
use venndev\vmskyblock\command\commands\SetName;
use venndev\vmskyblock\command\commands\SetSizeIsland;
use venndev\vmskyblock\command\commands\SetSpawn;
use venndev\vmskyblock\command\commands\Settings;
use venndev\vmskyblock\command\commands\Top;
use venndev\vmskyblock\command\commands\Unban;
use venndev\vmskyblock\command\commands\UnbanItem;
use venndev\vmskyblock\command\commands\Visit;
use venndev\vmskyblock\data\ConfigPaths;
use venndev\vmskyblock\data\Permissions;
use venndev\vmskyblock\manager\MessageManager;
use venndev\vmskyblock\provider\ConfigProvider;
use venndev\vmskyblock\VMSkyBlock;

final class VMSkyBlockCommand extends Command implements PluginOwned
{

    public function __construct(
        private readonly VMSkyBlock $plugin
    )
    {
        $config = $plugin->getProvider()->getConfigProvider()->getConfig();
        $nameCmd = $config->getNested(ConfigPaths::PLUGIN_SETTINGS_COMMAND_NAME);
        $aliasesCmd = $config->getNested(ConfigPaths::PLUGIN_SETTINGS_COMMAND_ALIASES);

        parent::__construct(
            $nameCmd,
            "VMSkyBlock command",
            "/" . $nameCmd,
            $aliasesCmd
        );

        $this->setPermission(Permissions::COMMAND);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_PLAYER_ONLY));
            return false;
        }

        $subCommands = [
            new Join(),
            new Create(),
            new Delete(),
            new Add(),
            new Remove(),
            new Members(),
            new Ban(),
            new Unban(),
            new Kick(),
            new Visit(),
            new About(),
            new Settings(),
            new SetSizeIsland(),
            new BanItem(),
            new UnbanItem(),
            new SetSpawn(),
            new Top(),
            new SetName(),
            new Form(),
            new GiveItemPortal(),
        ];

        $pages = [];
        $pageCount = 0;
        foreach ($subCommands as $subCommand) {
            $pages[$pageCount][] = "- " . TextFormat::GREEN . $subCommand->getUsage() . TextFormat::WHITE . " - " . TextFormat::GRAY . $subCommand->getDescription();
            if (count($pages[$pageCount]) === 7) $pageCount++;
        }

        if (count($args) === 0) {
            $sender->sendMessage("\n\n" . TextFormat::AQUA . "VMSkyblock(" . $this->plugin->getDescription()->getVersion() . ") Commands:");

            foreach ($pages[0] as $line) $sender->sendMessage($line);
            $sender->sendMessage(TextFormat::AQUA . "--- (Page " . TextFormat::WHITE . "1" . TextFormat::AQUA . " of " . TextFormat::RED . count($pages) . TextFormat::AQUA . ") ---");

            if (count($pages) > 1) $sender->sendMessage(TextFormat::AQUA . "Type /vmsb <page> to view more commands.");
        } else {
            if (is_numeric($args[0])) {
                $page = (int)$args[0];
                if (!isset($pages[$page - 1])) {
                    $sender->sendMessage(TextFormat::RED . "Page $page not found.");
                    return false;
                }

                $sender->sendMessage("\n\n" . TextFormat::AQUA . "VMSkyblock(" . $this->plugin->getDescription()->getVersion() . ") Commands:");
                foreach ($pages[$page - 1] as $line) $sender->sendMessage($line);
                $sender->sendMessage(TextFormat::AQUA . "--- (Page " . TextFormat::WHITE . "$page" . TextFormat::AQUA .  " of " . TextFormat::RED . count($pages) . TextFormat::AQUA . ") ---");
            } else {
                /** @var SubCommand $subCommand */
                foreach ($subCommands as $subCommand) {
                    if ($subCommand->checkCommand($sender, $args)) return true;
                }
            }
        }
        return false;
    }

    private function getConfigProvider(): ConfigProvider
    {
        return $this->plugin->getProvider()->getConfigProvider();
    }

    public function getOwningPlugin(): Plugin
    {
        return $this->plugin;
    }

}