<?php

declare(strict_types=1);

namespace venndev\vmskyblock\manager;

use Generator;
use Throwable;
use pocketmine\utils\TextFormat;
use muqsit\dimensionfix\VDimensionFix;
use pocketmine\network\mcpe\protocol\types\DimensionIds;
use venndev\vmskyblock\api\manager\IWorldManager;
use venndev\vmskyblock\VMSkyBlock;
use vennv\vapm\CoroutineGen;
use vennv\vapm\Deferred;

final class WorldManager implements IWorldManager
{

    private array $islandNether = [];
    private array $islandEnd = [];
    private array $overWorlds = [];

    public function __construct(private readonly VMSkyBlock $plugin)
    {
        // TODO: Implement __construct() method.
    }

    public function init(): void
    {
        CoroutineGen::runNonBlocking(function (): Generator {
            $i = yield from $this->loadWorlds()->await();
            $this->getPlugin()->getLogger()->info(TextFormat::GREEN . "Loaded $i worlds");
        });
    }

    /**
     * @return Deferred<int>
     * @throws Throwable
     */
    public function loadWorlds(): Deferred
    {
        return new Deferred(function (): Generator {
            $i = 0;
            foreach (scandir($this->plugin->getServer()->getDataPath() . "worlds") as $world) {
                if ($world === "." || $world === "..") continue;
                if ($this->plugin->getManager()->isIslandNether($world)) {
                    $i++;
                    $this->applyIslandNether($world);
                } elseif ($this->plugin->getManager()->isIslandEnd($world)) {
                    $i++;
                    $this->applyIslandEnd($world);
                } else {
                    $this->applyIslandOverworld($world);
                }
                yield;
            }
            return $i;
        });
    }

    /**
     * @throws Throwable
     */
    public function applyIslandNether(string $world): void
    {
        VDimensionFix::getInstance()->applyToWorld($world, DimensionIds::NETHER);
        $this->islandNether[] = $world;
    }

    /**
     * @throws Throwable
     */
    public function applyIslandEnd(string $world): void
    {
        VDimensionFix::getInstance()->applyToWorld($world, DimensionIds::THE_END);
        $this->islandEnd[] = $world;
    }

    public function applyIslandOverworld(string $world): void
    {
        $this->overWorlds[] = $world;
    }

    public function getIslandNether(): array
    {
        return $this->islandNether;
    }

    public function getIslandEnd(): array
    {
        return $this->islandEnd;
    }

    public function getOverworld(): array
    {
        return $this->overWorlds;
    }

    public function getWorldDimension(string $world): ?int
    {
        if (in_array($world, $this->islandNether)) return DimensionIds::NETHER;
        elseif (in_array($world, $this->islandEnd)) return DimensionIds::THE_END;
        elseif (in_array($world, $this->overWorlds)) return DimensionIds::OVERWORLD;
        return null;
    }

    public function getPlugin(): VMSkyBlock
    {
        return $this->plugin;
    }

}