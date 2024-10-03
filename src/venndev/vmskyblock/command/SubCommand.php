<?php

declare(strict_types=1);

namespace venndev\vmskyblock\command;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use venndev\vmskyblock\data\ConfigPaths;
use venndev\vmskyblock\manager\MessageManager;

abstract class SubCommand
{

    private bool|string $permission;

    public function __construct(
        private readonly string $name,
        private readonly string $description,
        private readonly string $usage,
        private readonly array  $aliases,
        private readonly bool   $isPlayerOnly = true
    )
    {
        $this->permission = true;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getUsage(): string
    {
        return $this->usage;
    }

    public function getAliases(): array
    {
        return $this->aliases;
    }

    public function getPermission(): bool|string
    {
        return $this->permission;
    }

    public function setPermission(bool|string $permission): void
    {
        $this->permission = $permission;
    }

    public function checkCommand(CommandSender $sender, array $args): bool
    {
        if (isset($args[0]) && ($args[0] === $this->name || in_array($args[0], $this->aliases))) {
            if ($this->isPlayerOnly && $sender instanceof Player && $this->permission !== true && !$sender->hasPermission($this->permission)) {
                $sender->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_NO_PERMISSION));
                return false;
            }
            unset($args[0]);
            $args = array_values($args);
            $this->execute($sender, $args);
            return true;
        }
        return false;
    }

    protected function execute(CommandSender $sender, array $args): void
    {
        // TODO: Implement execute() method.
    }

}