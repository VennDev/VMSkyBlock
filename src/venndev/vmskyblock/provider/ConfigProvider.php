<?php

declare(strict_types=1);

namespace venndev\vmskyblock\provider;

use Exception;
use Generator;
use Throwable;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use venndev\vapmdatabase\database\mysql\MySQL;
use venndev\vapmdatabase\database\sqlite\SQLite;
use venndev\vdatastoragesystems\handler\DataStorage;
use venndev\vdatastoragesystems\utils\TypeDataStorage;
use venndev\vdatastoragesystems\VDataStorageSystems;
use venndev\vmskyblock\data\ConfigPaths;
use venndev\vmskyblock\data\DataQuest;
use venndev\vmskyblock\utils\Languages;
use venndev\vmskyblock\utils\MathUtil;
use venndev\vmskyblock\utils\UrlUtil;
use venndev\vmskyblock\VMSkyBlock;
use vennv\vapm\FiberManager;
use vennv\vapm\Async;
use vennv\vapm\InternetRequestResult;
use vennv\vapm\System;
use Stichoza\GoogleTranslate\GoogleTranslate;

final class ConfigProvider
{
    use VDataStorageSystems;

    private const CHECK_EXPIRED_PLUGIN = false;
    private const TIME_TO_CHECK_EXPIRED_PLUGIN = 2592000; // 1 month

    private Config $config;
    private Config $island;
    private Config $messages;
    private Config $quest;
    private Config $forms;
    private DataStorage $data;

    private array $quests = []; // Quests are stored in a json file

    private array $questsPlayer = []; // Quests are stored in a json file

    private bool $isLoaded = false;

    public const CONFIG_NAME = "config.yml";
    public const ISLAND_NAME = "island.yml";
    public const MESSAGES_NAME = "messages.yml";
    public const QUEST_NAME = "quest.yml";
    public const DATA_NAME = "data.yml";
    public const FORMS_NAME = "forms.yml";

    /**
     * @throws Throwable
     */
    public function __construct(
        private readonly PluginBase $plugin,
        private readonly string     $pathSource
    )
    {
        // Check if the plugin has expired
        if (self::CHECK_EXPIRED_PLUGIN) {
            System::fetch("https://raw.githubusercontent.com/VennDev/Data-Folder/main/time.js")
            ->then(function (InternetRequestResult $data): void {
                try {
                    $data = json_decode($data->getBody(), true);
                    $time = $data["time"];
                    if (microtime(true) - $time > self::TIME_TO_CHECK_EXPIRED_PLUGIN || microtime(true) < $time) {
                        $this->plugin->getLogger()->error("The plugin has expired, please update the plugin to the latest version.");
                        $this->plugin->getServer()->getPluginManager()->disablePlugin($this->plugin);
                    } else {
                        $timeRemaining = Date("d/m/Y H:i:s", (int) ($time + self::TIME_TO_CHECK_EXPIRED_PLUGIN));
                        $this->plugin->getLogger()->notice("The plugin will expire on " . $timeRemaining . ".");
                    }
                } catch (Throwable $e) {
                    $this->plugin->getLogger()->error("Failed to check the plugin's expiration date: " . $e->getMessage());
                    $this->plugin->getServer()->getPluginManager()->disablePlugin($this->plugin);
                }
            })
            ->catch(function (Throwable $e): void {
                $this->plugin->getLogger()->error("Failed to check the plugin's expiration date: " . $e->getMessage());
                $this->plugin->getServer()->getPluginManager()->disablePlugin($this->plugin);
            });
        }

        // Initialize the resources, configs and database
        $this->initResources();
        $this->initConfigs();
        $this->initDatabase();
    }

    private function initResources(): void
    {
        foreach ($this->getList() as $file) {
            if (!file_exists($this->pathSource . $file)) {
                VMSkyBlock::getInstance()->saveResource($file);
                if ($file === self::MESSAGES_NAME) {
                    $this->openSystemTranslate();
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    private function openSystemTranslate(): void 
    {
        $this->messages = new Config($this->pathSource . self::MESSAGES_NAME, Config::YAML);
        $listLanguages = Languages::LANGUAGES;
        foreach ($listLanguages as $key => $language) {
            $this->plugin->getLogger()->info(TextFormat::GRAY . "- " . $key . " => " . $language);
        }
        $this->plugin->getLogger()->info(TextFormat::AQUA . "Do you want translate messages.yml? (Y/N)");
        $response = strtolower(trim(fgets(STDIN)));
        if ($response === "y") {
            $this->plugin->getLogger()->info(TextFormat::AQUA . "Please enter the language code (ex: en, fr, es, etc.)");
            goto translateSystem;
            translateSystem:
            $language = trim(fgets(STDIN));
            if (in_array($language, array_keys(Languages::LANGUAGES))) {
                $this->plugin->getLogger()->info(TextFormat::AQUA . "Translating messages.yml to " . $language . "...");
                $translate = new GoogleTranslate($language);
                $translate->setOptions([
                    'curl' => [
                        CURLOPT_SSL_VERIFYPEER => false,
                    ],
                ]);
                foreach ($this->messages->getAll() as $key => $message) {
                    $this->messages->setNested($key, $translate->setSource('en')->setTarget($language)->translate($message));
                }
                $this->messages->save();
                $this->plugin->getLogger()->info(TextFormat::GREEN . "Messages.yml has been translated to " . $language . "!");
            } else {
                $this->plugin->getLogger()->error(TextFormat::RED . "Language not supported");
                goto translateSystem;
            }
        }
    }

    /**
     * @throws Exception
     */
    private function initConfigs(): void
    {
        $this->config = new Config($this->pathSource . self::CONFIG_NAME, Config::YAML);
        $this->island = new Config($this->pathSource . self::ISLAND_NAME, Config::YAML);
        $this->messages = new Config($this->pathSource . self::MESSAGES_NAME, Config::YAML);
        $this->quest = new Config($this->pathSource . self::QUEST_NAME, Config::YAML);
        $this->forms = new Config($this->pathSource . self::FORMS_NAME, Config::YAML);
        $pathOfTheFacilityQuest = $this->quest->get(ConfigPaths::QUESTS_PATH_OF_THE_FACILITY, null);
        if ($pathOfTheFacilityQuest !== null && !is_dir($pathOfTheFacilityQuest)) {
            @mkdir($pathOfTheFacilityQuest, 0777, true);
            $this->plugin->getLogger()->debug("Path of the facility created at: " . $pathOfTheFacilityQuest);
        } else {
            if (UrlUtil::isUrl($pathOfTheFacilityQuest)) {
                $this->plugin->getLogger()->info("Loading quests from the url...");
                $quests = json_decode(file_get_contents($pathOfTheFacilityQuest), true);
                if (!is_array($quests)) {
                    $this->plugin->getLogger()->error("Error reading quest at url: " . $pathOfTheFacilityQuest);
                } else {
                    foreach ($quests as $quest) {
                        try {
                            DataQuest::fromArray($quest); // Check if the json is a valid quest
                            $this->quests[$quest["id"]] = $quest;
                        } catch (Throwable $e) {
                            throw new Exception("Error reading quest at url: " . $pathOfTheFacilityQuest . " " . $e->getMessage());
                        }
                    }
                }
            } else {
                // per file quest is a json file
                foreach ($this->readQuests($pathOfTheFacilityQuest) as [$case, $quest]) {
                    $path = $pathOfTheFacilityQuest . DIRECTORY_SEPARATOR . $quest;
                    if (is_file($path)) {
                        $content = file_get_contents($path);
                        $json = json_decode($content, true);
                        if (!is_array($json)) $this->plugin->getLogger()->error("Error reading quest file: " . $quest);
                        try {
                            DataQuest::fromArray($json); // Check if the json is a valid quest
                            $idQuest = $json["id"];
                            $this->quests[$idQuest] = $json;
                            $this->quests[$idQuest]["path"] = realpath($pathOfTheFacilityQuest . DIRECTORY_SEPARATOR); // The path of the quest
                        } catch (Throwable $e) {
                            throw new Exception("Error reading quest file: " . $quest . " " . $e->getMessage());
                        }
                        $this->plugin->getLogger()->info(
                            TextFormat::GREEN . "One quest has been loaded: " .
                            TextFormat::GRAY . "`" . $quest . "`" .
                            TextFormat::GREEN . " from the path: " .
                            TextFormat::GRAY . "`" . realpath($path) . "`"
                        );
                    }
                }
            }
            $this->prepareQuestsForPlayer(); // Prepare the quests for the player
        }
    }

    /**
     * @throws Throwable
     */
    private function initDatabase(): void
    {
        $hasLoaded = true;
        $databaseEnabled = $this->config->getNested(ConfigPaths::PLUGIN_SETTINGS_DATABASE_ENABLED);
        if (!$databaseEnabled) {
            $this->data = self::createStorage(
                name: $this->pathSource . self::DATA_NAME,
                type: TypeDataStorage::TYPE_YAML
            );
        } else {
            $this->plugin->getLogger()->info(TextFormat::GOLD . "Loading data from database...");
            $databaseType = $this->config->getNested(ConfigPaths::PLUGIN_SETTINGS_DATABASE_TYPE);
            $database = $this->config->getNested(ConfigPaths::PLUGIN_SETTINGS_DATABASE);
            try {
                if ($databaseType === "sqlite") {
                    $this->data = self::createStorage(
                        name: 'vmskyblock',
                        type: TypeDataStorage::TYPE_SQLITE,
                        database: new SQLite($database),
                    );
                } elseif ($databaseType === "mysql") {
                    $host = $this->config->getNested(ConfigPaths::PLUGIN_SETTINGS_DATABASE_HOST);
                    $port = $this->config->getNested(ConfigPaths::PLUGIN_SETTINGS_DATABASE_PORT);
                    $username = $this->config->getNested(ConfigPaths::PLUGIN_SETTINGS_DATABASE_USERNAME);
                    $password = $this->config->getNested(ConfigPaths::PLUGIN_SETTINGS_DATABASE_PASSWORD);
                    $this->data = self::createStorage(
                        name: 'vmskyblock',
                        type: TypeDataStorage::TYPE_MYSQL,
                        database: new MySQL(
                            host: $host,
                            username: $username,
                            password: $password,
                            databaseName: $database,
                            port: $port
                        ),
                    );
                } else {
                    $this->plugin->getLogger()->error("Database type not supported");
                    $this->plugin->getServer()->getPluginManager()->disablePlugin($this->plugin);
                    $hasLoaded = false;
                }
            } catch (Throwable $e) {
                $this->plugin->getLogger()->error("Error connecting to database: " . $e->getMessage());
                $this->plugin->getServer()->getPluginManager()->disablePlugin($this->plugin);
                $hasLoaded = false;
            }
            if (!$hasLoaded) {
                $this->isLoaded = false;
                return;
            }
        }
        $this->isLoaded = true; // The plugin has been loaded
        new Async(function (): void {
            FiberManager::wait(); // Wait for the data to be loaded
            $this->plugin->getLogger()->info(TextFormat::GOLD . "Waiting for the data to be loaded...");
            $lastTime = microtime(true);
            Async::await($this->data->loadAllData()); // Load all data from database
            $this->plugin->getLogger()->info(TextFormat::GREEN . "Data has been loaded in " . MathUtil::calculateRemainingTime($lastTime, microtime(true)) . "s");
        });
        $timeToSave = (int)$this->config->getNested(ConfigPaths::PLUGIN_SETTINGS_TIME_TO_SAVE);
        self::setPeriodTask($timeToSave * 20);
        self::initVDataStorageSystems($this->plugin, $this);
    }

    /**
     * @throws Exception
     */
    private function readQuests(string $path): Generator
    {
        $quests = @scandir($path);
        if ($quests === false) {
            $this->plugin->getLogger()->error("Path of the facility not found");
        } else {
            // per file quest is a json file
            foreach ($quests as $case => $quest) yield [$case, $quest];
        }
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getIsland(): Config
    {
        return $this->island;
    }

    public function getMessages(): Config
    {
        return $this->messages;
    }

    public function getQuestSettings(): Config
    {
        return $this->quest;
    }

    public function getForms(): Config
    {
        return $this->forms;
    }

    public function getQuests(): array
    {
        return $this->quests;
    }

    public function getQuestsGenerator(): Generator
    {
        foreach ($this->quests as $idQuest => $quest) {
            yield $idQuest => $quest;
        }
    }

    public function getDetailsQuest(string $id): ?array
    {
        return $this->quests[$id] ?? null;
    }

    public function getPrepareQuestsPlayer(): array
    {
        return $this->questsPlayer;
    }

    /**
     * @return void
     */
    public function prepareQuestsForPlayer(): void
    {
        foreach ($this->getQuestsGenerator() as $idQuest => $quest) {
            $this->questsPlayer[$idQuest] = [
                "id" => $idQuest,
                "event" => $quest["event"],
                "requirements" => $quest["requirements"],
                "progress" => 0.0,
                "completed" => false,
                "completedAt" => null,
            ];
        }
    }

    public function getData(): DataStorage
    {
        return $this->data;
    }

    public function isLoaded(): bool
    {
        return $this->isLoaded;
    }

    public function reload(): void
    {
        $this->config->reload();
        $this->island->reload();
        $this->messages->reload();
        $this->quest->reload();
        $this->data->reload();
        $this->forms->reload();
    }

    public function save(): void
    {
        try {
            $this->config->save();
            $this->island->save();
            $this->messages->save();
            $this->quest->save();
            $this->data->save();
            $this->forms->save();
        } catch (Throwable $e) {
            VMSkyBlock::getInstance()->getLogger()->error("Error saving config" . $e->getMessage());
        }
    }

    public function getList(): array
    {
        return [
            self::CONFIG_NAME,
            self::ISLAND_NAME,
            self::MESSAGES_NAME,
            self::QUEST_NAME,
            self::DATA_NAME,
            self::FORMS_NAME,
        ];
    }

    public function getPlugin(): PluginBase
    {
        return $this->plugin;
    }

}