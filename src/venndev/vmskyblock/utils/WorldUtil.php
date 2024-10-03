<?php

declare(strict_types=1);

namespace venndev\vmskyblock\utils;

use Generator;
use pocketmine\utils\TextFormat;
use Throwable;
use pocketmine\block\NetherPortal;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Axis;
use pocketmine\math\AxisAlignedBB;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\format\Chunk;
use pocketmine\entity\Entity;
use pocketmine\math\Vector3 as PMVector3;
use pocketmine\block\Block;
use pocketmine\world\World;
use venndev\vmskyblock\api\utils\IWorldUtil;
use venndev\vmskyblock\data\ConfigPaths;
use venndev\vmskyblock\entity\EntityPortal;
use venndev\vmskyblock\VMSkyBlock;
use vennv\vapm\Async;
use vennv\vapm\Deferred;
use vennv\vapm\FiberManager;
use vennv\vapm\Promise;

final class WorldUtil implements IWorldUtil
{

    public static function getNearbyEntities(World $world, AxisAlignedBB $bb, ?Entity $entity = null): Deferred
    {
        return new Deferred(function () use ($world, $bb, $entity): Generator {
            $minX = ((int)floor($bb->minX - 2)) >> Chunk::COORD_BIT_SIZE;
            $maxX = ((int)floor($bb->maxX + 2)) >> Chunk::COORD_BIT_SIZE;
            $minZ = ((int)floor($bb->minZ - 2)) >> Chunk::COORD_BIT_SIZE;
            $maxZ = ((int)floor($bb->maxZ + 2)) >> Chunk::COORD_BIT_SIZE;

            $entities = [];
            for ($x = $minX; $x <= $maxX; ++$x) {
                for ($z = $minZ; $z <= $maxZ; ++$z) {
                    foreach ($world->getChunkEntities($x, $z) as $ent) {
                        if ($ent !== $entity && $ent->boundingBox->intersectsWith($bb)) {
                            yield $entities[] = $ent;
                        }
                    }
                }
            }

            return $entities;
        });
    }

    public static function getListWorldSkyBlock(): Deferred
    {
        return new Deferred(function (): Generator {
            $pathWorld = VMSkyBlock::getInstance()->getProvider()->getWorldsFolder();
            if (is_dir($pathWorld)) {
                foreach (scandir($pathWorld) as $file) {
                    if ($file === "." || $file === "..") continue;
                    if (is_dir($pathWorld . $file)) yield $file;
                }
            }
        });
    }

    public static function generateWorldSkyBlock(string $worldName): Async
    {
        return FolderUtil::copy(VMSkyBlock::getInstance()->getProvider()->getPathWorldOrigin(), VMSkyBlock::getInstance()->getProvider()->getWorldsFolder() . DIRECTORY_SEPARATOR . $worldName);
    }

    public static function generateWorldNetherSkyBlock(string $worldName): Async
    {
        return FolderUtil::copy(VMSkyBlock::getInstance()->getProvider()->getPathWorldNetherOrigin(), VMSkyBlock::getInstance()->getProvider()->getWorldsFolder() . DIRECTORY_SEPARATOR . $worldName);
    }

    public static function generateWorldEndSkyBlock(string $worldName): Async
    {
        return FolderUtil::copy(VMSkyBlock::getInstance()->getProvider()->getPathWorldEndOrigin(), VMSkyBlock::getInstance()->getProvider()->getWorldsFolder() . DIRECTORY_SEPARATOR . $worldName);
    }

    public static function removeWorldSkyBlock(string $worldName): Async
    {
        return FolderUtil::delete(VMSkyBlock::getInstance()->getProvider()->getWorldsFolder() . DIRECTORY_SEPARATOR . $worldName);
    }

    public static function checkLoadWorld(string $worldName): void
    {
        if (!VMSkyBlock::getInstance()->getServer()->getWorldManager()->isWorldLoaded($worldName)) {
            VMSkyBlock::getInstance()->getLogger()->debug("Loading world: " . $worldName);
            VMSkyBlock::getInstance()->getServer()->getWorldManager()->loadWorld($worldName);
        }
    }

    public static function checkUnloadWorld(string $worldName): void
    {
        if (VMSkyBlock::getInstance()->getServer()->getWorldManager()->isWorldLoaded($worldName)) {
            $world = VMSkyBlock::getInstance()->getServer()->getWorldManager()->getWorldByName($worldName);
            VMSkyBlock::getInstance()->getServer()->getWorldManager()->unloadWorld($world);
        }
    }

    /**
     * @param World $world
     * @param Block $block
     * @param PMVector3 $pos1
     * @param PMVector3 $pos2
     * @return Promise
     * @throws Throwable
     *
     * This method is used to fill a block in a specific area.
     */
    public static function setFillBlock(
        World $world, Block $block, PMVector3 $pos1, PMVector3 $pos2
    ): Promise
    {
        return new Promise(function($resolve, $reject) use ($world, $block, $pos1, $pos2) {
            try {
                MathUtil::calculateMinAndMaxValues(
                    $pos1, $pos2, false, $minX, $maxX, $minY, $maxY, $minZ, $maxZ
                );
                for ($x = $minX; $x <= $maxX; ++$x) {
                    for ($z = $minZ; $z <= $maxZ; ++$z) {
                        for ($y = $minY; $y <= $maxY; ++$y) {
                            if (($x !== $minX && $x !== $maxX) && ($y !== $minY && $y !== $maxY) && ($z !== $minZ && $z !== $maxZ)) {
                                continue;
                            }
                            $world->setBlockAt($x, $y, $z, $block);
                            FiberManager::wait();
                        }
                    }
                }
                $resolve();
            } catch (Throwable $e) {
                $reject($e);
            }
        });
    }

    /**
     * @param Entity $entity
     * @param Block $border
     * @param Block $inside
     * @param PMVector3 $pos
     * @param int $height
     * @param int $width
     * @param string $portalType
     * @param bool $spawnEntity
     * @return Async
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
    ): Async
    {
        return new Async(function() use (
            $entity, $border, $inside, $pos, $height, $width, $portalType, $spawnEntity
        ) {
            $islandConfig = VMSkyBlock::getInstance()->getProvider()->getConfigProvider()->getIsland();
            if ($portalType === TypeIsland::ISLAND_NETHER) {
                $data = $islandConfig->getNested(ConfigPaths::ISLAND_NETHER_PORTAL_SETTINGS);
            } elseif ($portalType === TypeIsland::ISLAND_THE_END) {
                $data = $islandConfig->getNested(ConfigPaths::ISLAND_THE_END_PORTAL_SETTINGS);
            } else {
                return;
            }

            $checkAxisNetherPortal = function (Block $block, int $horizontalFacing): void {
                if ($block->getTypeId() === VanillaBlocks::NETHER_PORTAL()->getTypeId()) {
                    $axis = Vector3::getAxisByHorizontalFacing($horizontalFacing);
                    if ($axis === null) return;
                    /** @var NetherPortal $block */
                    if ($axis === Axis::X) {
                        $block->setAxis(Axis::X);
                    } elseif ($axis === Axis::Z) {
                        $block->setAxis(Axis::Z);
                    }
                }
            };

            $checkAxisNetherPortal($border, $entity->getHorizontalFacing());
            $checkAxisNetherPortal($inside, $entity->getHorizontalFacing());

            $pos = $pos->add(0, 1, 0);
            $world = $entity->getWorld();

            $vectorBorder = Vector3::getTwoPointsHorizontal(
                $entity, $pos, $width, $height
            );
            $vectorInside = Vector3::getTwoPointsHorizontal(
                $entity, $pos->add(0, 2, 0), $width - 1, $height - 3
            );
            if ($vectorBorder === null || $vectorInside === null) {
                return;
            }

            // Create the border of the portal.
            $vectorRight = $vectorBorder[0];
            $vectorLeft = $vectorBorder[1];
            Async::await(self::setFillBlock($world, $border, $pos, $vectorLeft));
            Async::await(self::setFillBlock($world, $border, $pos, $vectorRight));

            // Create the inside of the portal.
            $vectorRight = $vectorInside[0];
            $vectorLeft = $vectorInside[1];
            Async::await(self::setFillBlock($world, $inside, $pos, $vectorLeft));
            Async::await(self::setFillBlock($world, $inside, $pos, $vectorRight));

            if (!$spawnEntity) return; // If the entity is not to be spawned, the method ends here.

            $name = $data["name"];
            $location = $entity->getLocation();
            $nbt = CompoundTag::create()
                ->setString("portal", $portalType)
                ->setString("name", TextFormat::colorize($name));
            $entityPortal = new EntityPortal($location, $nbt);
            $entityPortal->setPortal($portalType);
            $entityPortal->spawnToAll();
            $entityPortal->teleport($pos->add(0.5, 1, 0.5));
        });
    }

}