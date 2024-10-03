<?php

declare(strict_types=1);

namespace venndev\vmskyblock\utils;

use pocketmine\math\Vector3;
use pocketmine\world\World;

final class MathUtil
{

    public static function getTimeNowString(): string
    {
        return date("Y-m-d H:i:s");
    }

    public static function calculateXZDistance(Vector3 $point1, Vector3 $point2): float
    {
        return sqrt(
            pow($point2->x - $point1->x, 2) +
            pow($point2->z - $point1->z, 2)
        );
    }

    public static function vector3ToString(Vector3 $vector3): string
    {
        return $vector3->x . ":" . $vector3->y . ":" . $vector3->z;
    }

    public static function calculateRemainingTime(float $startTime, float $endTime): float
    {
        return round($endTime - $startTime, 2);
    }

    public static function calculateMinAndMaxValues(
        Vector3 $pos1, Vector3 $pos2, bool $clampY, ?int &$minX, ?int &$maxX, ?int &$minY, ?int &$maxY, ?int &$minZ, ?int &$maxZ
    ): void
    {
        $minX = (int)min($pos1->getX(), $pos2->getX());
        $maxX = (int)max($pos1->getX(), $pos2->getX());
        $minY = (int)min($pos1->getY(), $pos2->getY());
        $maxY = (int)max($pos1->getY(), $pos2->getY());
        $minZ = (int)min($pos1->getZ(), $pos2->getZ());
        $maxZ = (int)max($pos1->getZ(), $pos2->getZ());
        if (!$clampY) return;
        $minY = min(World::Y_MAX - 1, max(World::Y_MIN, $minY));
        $maxY = min(World::Y_MAX - 1, max(World::Y_MIN, $maxY));
    }

}