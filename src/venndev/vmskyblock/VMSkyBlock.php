<?php

declare(strict_types=1);

namespace venndev\vmskyblock;

// Import classes from vendor
require_once __DIR__ . "/../../../vendor/autoload.php";

use Throwable;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\event\Listener;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\World;
use venndev\vcache\VCache;
use venndev\vdoplayer\VDoPlayer;
use venndev\vformoopapi\VFormLoader;
use venndev\vmskyblock\command\ConsoleCommandListener;
use venndev\vmskyblock\command\VMSkyBlockCommand;
use venndev\vmskyblock\data\ConfigPaths;
use venndev\vmskyblock\data\Permissions;
use venndev\vmskyblock\entity\EntityPortal;
use venndev\vmskyblock\manager\PlayerManager;
use venndev\vmskyblock\manager\VMSkyBlockManager;
use venndev\vmskyblock\manager\WorldManager;
use venndev\vmskyblock\provider\Provider;
use venndev\vmskyblock\quest\QuestManager;
use venndev\vplaceholder\VPlaceHolder;
use vennv\vapm\FiberManager;
use vennv\vapm\Promise;
use vennv\vapm\VapmPMMP;

final class VMSkyBlock extends PluginBase implements Listener
{
    use SingletonTrait;

    private readonly Provider $provider;
    private WorldManager $worldManager;
    private VMSkyBlockManager $manager;
    private QuestManager $questManager;
    private PlayerManager $playerManager;
    private VCache $cache;
    private VDoPlayer $doPlayer;
    private ConsoleCommandListener $consoleCommandListener;

    protected function onLoad(): void
    {
        self::setInstance($this);
    }

    /**
     * @throws Throwable
     */
    protected function onEnable(): void
    {
        $this->initPlugin();
        $this->registerUtils();
    }

    /**
     * @throws Throwable
     */
    protected function onDisable(): void
    {
        try {
            $this->provider->getConfigProvider()->save();
        } catch (Throwable $e) {
            $this->getLogger()->error("Failed to save config provider: " . $e->getMessage());
        }
    }

    /**
     * @throws Throwable
     */
    protected function initPlugin(): void
    {
        // Register the plugin with VapmPMMP
        VapmPMMP::init($this);

        // Register the plugin with VPlaceHolder
        VPlaceHolder::init($this);

        // Register the plugin with VFormLoader
        VFormLoader::init($this);

        $this->registerPermissions();
        $this->registerEntities();
        $this->cache = new VCache($this);
        $this->doPlayer = new VDoPlayer($this);
        $this->provider = new Provider($this, $this->getDataFolder(), $this->getServer()->getDataPath());
        $this->worldManager = new WorldManager($this);
        $this->manager = new VMSkyBlockManager($this);
        $this->questManager = new QuestManager($this);
        $this->playerManager = new PlayerManager($this);
        $this->consoleCommandListener = new ConsoleCommandListener($this);
        $this->worldManager->init(); // Initialize the world manager
    }


    protected function registerUtils(): void
    {
        if (!$this->provider->isLoaded()) {
            return;
        }
        $this->getServer()->getPluginManager()->registerEvents(new listeners\EventListener($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new listeners\QuestEventListener($this), $this);
        $this->getServer()->getCommandMap()->register($this->getConfig()->getNested(ConfigPaths::PLUGIN_SETTINGS_COMMAND_NAME), new VMSkyBlockCommand($this));
        $this->getScheduler()->scheduleRepeatingTask(new tasks\ServerTickTask($this), 20);
    }

    /**
     * @throws Throwable
     */
    protected function registerPermissions(): Promise
    {
        return new Promise(function (): void {
            $permissions = Permissions::getArray();
            foreach ($permissions as $permission => $description) {
                PermissionManager::getInstance()->addPermission(new Permission($permission, $description));
                $this->getLogger()->debug("Registered permission: $permission");
                FiberManager::wait();
            }
        });
    }

    protected function registerEntities(): void
    {
        EntityFactory::getInstance()->register(EntityPortal::class, function (World $world, CompoundTag $nbt): Entity {
            return new EntityPortal(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, ["EntityPortal"]);
    }

    /**
     * @throws Throwable
     */
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        return $this->consoleCommandListener->onCommand($sender, $command, $label, $args);
    }

    public function getProvider(): Provider
    {
        return $this->provider;
    }

    public function getWorldManager(): WorldManager
    {
        return $this->worldManager;
    }

    public function getManager(): VMSkyBlockManager
    {
        return $this->manager;
    }

    public function getQuestManager(): QuestManager
    {
        return $this->questManager;
    }

    public function getPlayerManager(): PlayerManager
    {
        return $this->playerManager;
    }

    public function getCache(): VCache
    {
        return $this->cache;
    }

    public function getDoPlayer(): VDoPlayer
    {
        return $this->doPlayer;
    }
}
