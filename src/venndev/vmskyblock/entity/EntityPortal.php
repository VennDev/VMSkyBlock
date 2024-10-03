<?php

declare(strict_types=1);

namespace venndev\vmskyblock\entity;

use Throwable;
use Generator;
use RuntimeException;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Zombie;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use venndev\vmskyblock\data\ConfigPaths;
use venndev\vmskyblock\data\DataIslandPlayer;
use venndev\vmskyblock\manager\MessageManager;
use venndev\vmskyblock\utils\TypeIsland;
use venndev\vmskyblock\utils\WorldUtil;
use venndev\vmskyblock\VMSkyBlock;
use vennv\vapm\Async;
use vennv\vapm\CoroutineGen;

final class EntityPortal extends Zombie
{

    private ?string $portal = null;
    private string $name = "";

    public function __construct(
        Location     $location,
        ?CompoundTag $nbt = null,
    )
    {
        parent::__construct($location, $nbt);
    }

    public function getNamePortal(): string
    {
        return $this->name;
    }

    public function setPortal(?string $portal): void
    {
        $this->portal = $portal;
    }

    public function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(2.0, 5.0);
    }

    public function saveNBT(): CompoundTag
    {
        if ($this->portal === null) {
            throw new RuntimeException("Portal or world is null");
        }
        $nbt = parent::saveNBT();
        $nbt->setString("portal", $this->portal);
        $nbt->setString("name", $this->name);
        return $nbt;
    }

    /**
     * @throws Throwable
     */
    public function initEntity(CompoundTag $nbt): void
    {
        parent::initEntity($nbt);
        if ($nbt->getTag("portal") === null) {
            if ($this->portal === null) {
                $this->flagForDespawn();
                return;
            }
            $nbt->setString("portal", $this->portal);
        }
        if ($nbt->getTag("name") === null) {
            $nbt->setString("name", $this->name);
        }
        $this->portal = $nbt->getString("portal");
        $this->name = $nbt->getString("name");
        $this->setNameTag($this->name);
        $this->setNameTagVisible();
        $this->setNameTagAlwaysVisible();
        $this->setScale(0.01);
    }

    /**
     * @throws Throwable
     */
    public function onUpdate(int $currentTick): bool
    {
        $this->setMotion($this->getMotion()->withComponents(0, 0, 0));
        $this->setGravity(0.0);

        $data = $this->getDataPortal();

        CoroutineGen::runNonBlocking(function () use ($data): Generator {
            $height = $data["height"] / 2;
            $width = $data["width"] / 2;

            $nearestEntities = yield from WorldUtil::getNearbyEntities(
                world: $this->getWorld(),
                bb: $this->getBoundingBox()->expandedCopy($width, $height, $width),
                entity: $this
            )->await();

            if (empty($nearestEntities)) return;

            $nearestEntitiesFixTag = yield from WorldUtil::getNearbyEntities(
                world: $this->getWorld(),
                bb: $this->getBoundingBox()->expandedCopy($width * 2, $height * 2, $width * 2),
                entity: $this
            )->await();

            $islandConfig = VMSkyBlock::getInstance()->getProvider()->getConfigProvider()->getIsland();

            $worldName = $this->getWorld()->getFolderName();
            if (!VMSkyBlock::getInstance()->getManager()->isIsland($worldName)) return;
            if ($this->portal == TypeIsland::ISLAND_NETHER) {
                $enabled = $islandConfig->getNested(ConfigPaths::ISLAND_NETHER_LEVEL_SETTINGS_ENABLED);
                $enabled ? $levelRequired =
                    $islandConfig->getNested(ConfigPaths::ISLAND_NETHER_LEVEL_SETTINGS_LEVEL)
                    : $levelRequired = 0;
                $island = VMSkyBlock::getInstance()->getManager()->convertToNetherIsland($worldName);
            } elseif ($this->portal == TypeIsland::ISLAND_THE_END) {
                $enabled = $islandConfig->getNested(ConfigPaths::ISLAND_THE_END_LEVEL_SETTINGS_ENABLED);
                $enabled ? $levelRequired =
                    $islandConfig->getNested(ConfigPaths::ISLAND_THE_END_LEVEL_SETTINGS_LEVEL)
                    : $levelRequired = 0;
                $island = VMSkyBlock::getInstance()->getManager()->convertToEndIsland($worldName);
            } else {
                return;
            }

            $nameReal = VMSkyBlock::getInstance()->getManager()->convertToNormalIsland($worldName);

            $worldIsland = $island;
            WorldUtil::checkLoadWorld($worldIsland);
            $world = VMSkyBlock::getInstance()->getServer()->getWorldManager()->getWorldByName($worldIsland);
            if ($world === null) return;

            $dataIsland = VMSkyBlock::getInstance()->getManager()->getIslandDataByNameIsland($nameReal);
            $dataIsland = DataIslandPlayer::fromArray($dataIsland);
            $level = VMSkyBlock::getInstance()->getManager()->getLevelIsland($nameReal);

            $qualifiedLevel = $level >= $levelRequired;

            if (!$qualifiedLevel) {
                foreach ($nearestEntitiesFixTag as $entity) {
                    if (!$entity instanceof Player) continue;
                    if (in_array($entity->getXuid(), $dataIsland->getMembers())) {
                        $nameTag = $this->getNamePortal() . "\n" .
                            MessageManager::getNested(ConfigPaths::MESSAGE_PORTAL_REQUIRED_LEVEL, ["%level%" => $levelRequired], false);
                        if ($nameTag !== $this->getNameTag()) {
                            $this->setNameTag($nameTag);
                        }
                    }
                    yield;
                }
            } else {
                foreach ($nearestEntities as $entity) {
                    if (!$entity instanceof Player) continue;
                    $entity->teleport($world->getSpawnLocation());
                    yield;
                }
            }
        });

        return parent::onUpdate($currentTick);
    }

    /**
     * @throws Throwable
     */
    public function onInteract(Player $player, Vector3 $clickPos): bool
    {
        $world = $player->getWorld();
        $worldName = $world->getFolderName();
        if (VMSkyBlock::getInstance()->getManager()->isIsland($worldName)) {
            new Async(function () use ($player, $worldName) {
                $dataIslandPlayer = Async::await(VMSkyBlock::getInstance()->getManager()->getIslandDataByMember($player->getName()));
                if ($dataIslandPlayer instanceof DataIslandPlayer) {
                    $nameIsland = $dataIslandPlayer->getIsland();
                    if ($nameIsland === $worldName) {
                        $world = VMSkyBlock::getInstance()->getServer()->getWorldManager()->getWorldByName($nameIsland);
                        $player->teleport($world->getSpawnLocation());
                    }
                }
            });
        }
        return parent::onInteract($player, $clickPos);
    }

    private function getDataPortal(): ?array
    {
        $islandConfig = VMSkyBlock::getInstance()->getProvider()->getConfigProvider()->getIsland();
        if ($this->portal === TypeIsland::ISLAND_NETHER) {
            $data = $islandConfig->getNested(ConfigPaths::ISLAND_NETHER_PORTAL_SETTINGS);
        } elseif ($this->portal === TypeIsland::ISLAND_THE_END) {
            $data = $islandConfig->getNested(ConfigPaths::ISLAND_THE_END_PORTAL_SETTINGS);
        } else {
            return null;
        }
        return $data;
    }

    /**
     * @throws Throwable
     */
    public function removePortal(): void
    {
        $data = $this->getDataPortal();
        $height = $data["height"];
        $width = $data["width"];
        WorldUtil::createPortal(
            entity: $this,
            border: VanillaBlocks::AIR(),
            inside: VanillaBlocks::AIR(),
            pos: $this->getLocation()->asVector3()->add(0, -2, 0),
            height: $height,
            width: $width,
            portalType: $this->portal,
            spawnEntity: false
        );
    }

}