<?php

declare(strict_types=1);

namespace venndev\vmskyblock\manager;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use venndev\vmskyblock\data\ConfigPaths;
use venndev\vmskyblock\provider\Provider;
use venndev\vmskyblock\VMSkyBlock;

final class MessageManager
{

    public static function getNested(string $key, array $replaces = [], bool $prefix = true): string
    {
        $prefix = $prefix ? VMSkyBlock::getInstance()->getProvider()->getConfigProvider()->getConfig()->getNested(ConfigPaths::PLUGIN_SETTINGS_PLUGIN_PREFIX) : "";
        return TextFormat::colorize($prefix . str_replace(array_keys($replaces), array_values($replaces), self::getMessages()->getNested($key, "")));
    }

    private static function getMessages(): Config
    {
        return VMSkyBlock::getInstance()->getProvider()->getConfigProvider()->getMessages();
    }

}