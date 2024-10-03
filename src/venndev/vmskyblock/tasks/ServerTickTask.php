<?php

declare(strict_types=1);

namespace venndev\vmskyblock\tasks;

use Generator;
use Throwable;
use pocketmine\scheduler\Task;
use venndev\vmskyblock\data\ConfigPaths;
use venndev\vmskyblock\data\Settings;
use venndev\vmskyblock\VMSkyBlock;
use vennv\vapm\CoroutineGen;
use vennv\vapm\FiberManager;
use vennv\vapm\Promise;

final class ServerTickTask extends Task
{

    private static ?Promise $promiseProcessWorld = null;
    private static ?Promise $promiseProcessPlayers = null;
    private static ?bool $doneLoadWorlds = null;

    private static float $lastTimeLoadWorlds = 0.0;

    public function __construct(
        private readonly VMSkyBlock $plugin
    )
    {
        self::$lastTimeLoadWorlds = microtime(true);
    }

    /**
     * @throws Throwable
     */
    public function onRun(): void
    {
        // Process worlds
        if (
            $this->plugin->getProvider()->getConfigProvider()->getConfig()->getNested(ConfigPaths::PLUGIN_SETTINGS_AUTO_UNLOAD_WORLDS) &&
            self::$promiseProcessWorld === null
        ) {
            self::$promiseProcessWorld = Promise::c(function ($resolve, $reject): void {
                try {
                    foreach ($this->plugin->getServer()->getWorldManager()->getWorlds() as $world) {
                        if ($this->plugin->getServer()->getWorldManager()->getDefaultWorld() === $world) continue;
                        if (!$this->plugin->getManager()->isIsland($world->getFolderName())) continue;
                        $this->plugin->getManager()->freeWorldIsland($world);
                        FiberManager::wait();
                    }
                    $resolve(true);
                } catch (Throwable $e) {
                    $reject($e);
                }
            })->catch(function (Throwable $e): void {
                $this->plugin->getLogger()->error($e->getMessage());
            })->finally(function (): void {
                self::$promiseProcessWorld = null;
            });
        }
        // Process players
        if (self::$promiseProcessPlayers === null) {
            self::$promiseProcessPlayers = Promise::c(function ($resolve, $reject): void {
                try {
                    foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
                        // Update new quests
                        $this->plugin->getQuestManager()->updateNewQuests($player);
                        FiberManager::wait();
                    }
                    $resolve(true);
                } catch (Throwable $e) {
                    $reject($e);
                }
            })->catch(function (Throwable $e): void {
                $this->plugin->getLogger()->error($e->getMessage());
            })->finally(function (): void {
                self::$promiseProcessPlayers = null;
            });
        }
        // Load worlds
        if (
            self::$doneLoadWorlds === null &&
            (microtime(true) - self::$lastTimeLoadWorlds) >= Settings::TIME_LOAD_WORLDS_PER_TICK
        ) {
            self::$lastTimeLoadWorlds = microtime(true);
            self::$doneLoadWorlds = true;
        } elseif (self::$doneLoadWorlds) {
            CoroutineGen::runNonBlocking(function (): Generator {
                yield from $this->plugin->getWorldManager()->loadWorlds()->await();
                self::$doneLoadWorlds = null;
            });
        }
    }

    public function getPlugin(): VMSkyBlock
    {
        return $this->plugin;
    }

}
