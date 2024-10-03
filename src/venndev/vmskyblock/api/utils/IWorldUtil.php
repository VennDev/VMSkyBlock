<?php

namespace venndev\vmskyblock\api\utils;

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3 as PMVector3;
use pocketmine\world\World;
use Throwable;
use venndev\vmskyblock\utils\Vector3;
use vennv\vapm\Async;
use vennv\vapm\Deferred;
use vennv\vapm\Promise;

interface IWorldUtil
{

    /**
     * @param World $world
     * @param AxisAlignedBB $bb
     * @param Entity|null $entity
     * @return Deferred
     * @throws Throwable
     *
     * This method is used to get nearby entities in a specific area.
     */
    public static function getNearbyEntities(World $world, AxisAlignedBB $bb, ?Entity $entity = null): Deferred;

    /**
     * @throws Throwable
     */
    public static function getListWorldSkyBlock(): Deferred;

    /**
     * @throws Throwable
     */
    public static function generateWorldSkyBlock(string $worldName): Async;

    /**
     * @throws Throwable
     */
    public static function generateWorldNetherSkyBlock(string $worldName): Async;

    /**
     * @throws Throwable
     */
    public static function generateWorldEndSkyBlock(string $worldName): Async;

    /**
     * @throws Throwable
     */
    public static function removeWorldSkyBlock(string $worldName): Async;

    public static function checkLoadWorld(string $worldName): void;

    public static function checkUnloadWorld(string $worldName): void;

    /**
     * @param World $world
     * @param Block $block
     * @param Vector3 $pos1
     * @param Vector3 $pos2
     * @return Promise
     * @throws Throwable
     *
     * This method is used to fill a block in a specific area.
     */
    public static function setFillBlock(
        World $world, Block $block, Vector3 $pos1, Vector3 $pos2
    ): Promise;

    /**
     * @throws Throwable
     */
    public static function createPortal(
        Entity $entity,
        Block $border,
        Block $inside,
        PMVector3 $pos,
        int $height,
        int $width,
        string $portalType,
        bool $spawnEntity = true
    ): Async;
}