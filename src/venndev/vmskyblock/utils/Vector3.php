<?php

declare(strict_types=1);

namespace venndev\vmskyblock\utils;

use pocketmine\entity\Entity;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use pocketmine\math\Vector3 as PMVector3;

final class Vector3 extends PMVector3
{

    /**
     * @param Entity $entity
     * @param PMVector3 $vector3
     * @param int $distance
     * @param int $height
     * @return array|null
     *
     * This method is used to get two points horizontally from the player's position.
     */
    public static function getTwoPointsHorizontal(
        Entity $entity, PMVector3 $vector3, int $distance = 2, int $height = 5
    ): ?array
    {
        $horizontalFacing = $entity->getHorizontalFacing();

        $x = $vector3->getFloorX();
        $y = $vector3->getFloorY() + $height;
        $z = $vector3->getFloorZ();

        if (self::getAxisByHorizontalFacing($horizontalFacing) === Axis::X) {
            $vectorRight = new PMVector3($x + $distance, $y, $z);
            $vectorLeft = new PMVector3($x - $distance, $y, $z);
        } elseif (self::getAxisByHorizontalFacing($horizontalFacing) === Axis::Z) {
            $vectorRight = new PMVector3($x, $y, $z + $distance);
            $vectorLeft = new PMVector3($x, $y, $z - $distance);
        } else {
            return null;
        }

        return [$vectorRight, $vectorLeft];
    }

    public static function getAxisByHorizontalFacing(int $horizontalFacing): ?int
    {
        if ($horizontalFacing === Facing::SOUTH || $horizontalFacing === Facing::NORTH) {
            return Axis::X;
        } elseif ($horizontalFacing === Facing::WEST || $horizontalFacing === Facing::EAST) {
            return Axis::Z;
        } else {
            return null;
        }
    }

    public static function fromString(string $string): PMVector3
    {
        $parts = explode(",", $string);
        return new PMVector3((float) $parts[0], (float) $parts[1], (float) $parts[2]);
    }

    public static function toString(PMVector3 $vector3): string
    {
        return $vector3->x . "," . $vector3->y . "," . $vector3->z;
    }

}