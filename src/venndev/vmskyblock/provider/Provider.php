<?php

declare(strict_types=1);

namespace venndev\vmskyblock\provider;

use pocketmine\plugin\PluginBase;
use venndev\vmskyblock\VMSkyBlock;
use Throwable;

final class Provider
{

    private const NAME_FOLDER_ISLAND_ORIGIN = "island_origin";
    private const NAME_FOLDER_NETHER_ORIGIN = "nether_origin";
    private const NAME_FOLDER_END_ORIGIN = "end_origin";

    private readonly ConfigProvider $configProvider;

    private bool $isLoaded;

    /**
     * @throws Throwable
     */
    public function __construct(
        PluginBase $plugin,
        private readonly string $dataFolder,
        private readonly string $dataPath
    )
    {
        $this->configProvider = new ConfigProvider($plugin, $this->dataFolder);

        if (!$this->configProvider->isLoaded()) {
            VMSkyBlock::getInstance()->getLogger()->warning("Failed to load config provider!");
            $this->isLoaded = false;
            return;
        }

        if (!is_dir($this->dataFolder . self::NAME_FOLDER_ISLAND_ORIGIN)) {
            @mkdir($this->dataFolder . self::NAME_FOLDER_ISLAND_ORIGIN);
        }
        if (!is_dir($this->dataFolder . self::NAME_FOLDER_NETHER_ORIGIN)) {
            @mkdir($this->dataFolder . self::NAME_FOLDER_NETHER_ORIGIN);
        }
        if (!is_dir($this->dataFolder . self::NAME_FOLDER_END_ORIGIN)) {
            @mkdir($this->dataFolder . self::NAME_FOLDER_END_ORIGIN);
        }

        // Check folder origin island have any file
        if (!$this->islandOriginIsWorking()) {
            VMSkyBlock::getInstance()->getLogger()->warning("Folder origin `island` is empty! Please add some island origin file to this folder.");
        }

        // Check folder origin nether have any file
        if (!$this->netherOriginIsWorking()) {
            VMSkyBlock::getInstance()->getLogger()->warning("Folder origin `nether` is empty! Please add some nether origin file to this folder.");
        }

        // Check folder origin end have any file
        if (!$this->endOriginIsWorking()) {
            VMSkyBlock::getInstance()->getLogger()->warning("Folder origin `end` is empty! Please add some end origin file to this folder.");
        }

        $this->isLoaded = true;
    }

    public function isLoaded(): bool
    {
        return $this->isLoaded;
    }

    public function getDataFolder(): string
    {
        return $this->dataFolder;
    }

    public function getDataPath(): string
    {
        return $this->dataPath;
    }

    public function getWorldsFolder(): string
    {
        return $this->dataPath . "worlds";
    }

    public function getConfigProvider(): ConfigProvider
    {
        return $this->configProvider;
    }

    public function getPathWorldOrigin(): string
    {
        return $this->dataFolder . self::NAME_FOLDER_ISLAND_ORIGIN;
    }

    public function getPathWorldNetherOrigin(): string
    {
        return $this->dataFolder . self::NAME_FOLDER_NETHER_ORIGIN;
    }

    public function getPathWorldEndOrigin(): string
    {
        return $this->dataFolder . self::NAME_FOLDER_END_ORIGIN;
    }

    public function islandOriginIsWorking(): bool
    {
        return count(scandir($this->getPathWorldOrigin())) > 2;
    }

    public function netherOriginIsWorking(): bool
    {
        return count(scandir($this->getPathWorldNetherOrigin())) > 2;
    }

    public function endOriginIsWorking(): bool
    {
        return count(scandir($this->getPathWorldEndOrigin())) > 2;
    }

}
