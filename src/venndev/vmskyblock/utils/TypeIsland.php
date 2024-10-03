<?php

declare(strict_types=1);

namespace venndev\vmskyblock\utils;

final class TypeIsland
{

    public const ISLAND_NORMAL = "normal";
    public const ISLAND_NETHER = "nether";
    public const ISLAND_THE_END = "the-end";

    public static function getIslandTypes(): array
    {
        return [
            self::ISLAND_NORMAL,
            self::ISLAND_NETHER,
            self::ISLAND_THE_END
        ];
    }

    public static function isIslandType(string $type): bool
    {
        return in_array($type, self::getIslandTypes());
    }

}