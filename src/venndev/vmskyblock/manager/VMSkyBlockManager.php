<?php

declare(strict_types=1);

namespace venndev\vmskyblock\manager;

use Exception;
use Throwable;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\World;
use venndev\vapmdatabase\database\ResultQuery;
use venndev\vdatastoragesystems\handler\DataStorage;
use venndev\vmskyblock\api\manager\IVMSkyBlockManager;
use venndev\vmskyblock\data\ConfigPaths;
use venndev\vmskyblock\data\DataIslandPlayer;
use venndev\vmskyblock\data\Permissions;
use venndev\vmskyblock\event\VMSBJoinIslandEvent;
use venndev\vmskyblock\utils\ErrorLogger;
use venndev\vmskyblock\utils\ItemUtil;
use venndev\vmskyblock\utils\MathUtil;
use venndev\vmskyblock\utils\WorldUtil;
use venndev\vmskyblock\VMSkyBlock;
use venndev\vplayerdatasaver\VPlayerDataSaver;
use vennv\vapm\Async;
use vennv\vapm\FiberManager;
use vennv\vapm\Promise;

final class VMSkyBlockManager implements IVMSkyBlockManager
{

    private const KEY_SKYBLOCK = "_vmskyblock";
    public const KEY_ISLAND = "_island" . self::KEY_SKYBLOCK;
    public const KEY_NETHER = "_nether" . self::KEY_SKYBLOCK;
    public const KEY_END = "_end" . self::KEY_SKYBLOCK;

    public function __construct(
        private readonly VMSkyBlock $plugin
    )
    {
        //TODO: Implement constructor
    }

    public function getIslandDataByOwner(Player $player): null|array
    {
        $data = iterator_to_array($this->getDataIslands()->getAll());
        $nameIsland = $this->generateNameNewIsland($player);
        return $data[$nameIsland] ?? null;
    }

    public function getIslandDataByNameIsland(string $nameIsland): null|array
    {
        $nameIsland = $this->generateNameSpecialIsland($nameIsland);
        $data = iterator_to_array($this->getDataIslands()->getAll());
        return $data[$nameIsland] ?? null;
    }

    public function setDataIsland(string $nameIsland, array $data): void
    {
        $nameIsland = $this->generateNameSpecialIsland($nameIsland);
        $this->getDataIslands()->set($nameIsland, $data);
    }

    public function getIslandDataByMember(string $playerXuid): Promise
    {
        return new Promise(function ($resolve, $reject) use ($playerXuid) {
            try {
                foreach ($this->getDataIslands()->getAll() as $value) {
                    $data = DataIslandPlayer::fromArray($value);
                    if (in_array($playerXuid, $data->getMembers())) {
                        $resolve(DataIslandPlayer::fromArray($value));
                    }
                    FiberManager::wait();
                }
                $resolve(null);
            } catch (Throwable $e) {
                ErrorLogger::logThrowable($e); // Log error
                $reject($e);
            }
        });
    }

    public function getNameIslandByPlayer(Player $player): Async
    {
        return new Async(function () use ($player): ?string {
            $data = Async::await($this->getIslandDataByMember($player->getXuid()));
            return $data?->getIsland() ?? null;
        });
    }

    public function generateNameNewIsland(Player $player): string
    {
        return strtolower($player->getName() . "_" . $player->getXuid() . self::KEY_ISLAND);
    }

    public function generateNameNewNetherIsland(Player $player): string
    {
        return strtolower($player->getName() . "_" . $player->getXuid() . self::KEY_NETHER);
    }

    public function generateNameNewEndIsland(Player $player): string
    {
        return strtolower($player->getName() . "_" . $player->getXuid() . self::KEY_END);
    }

    public function hasPermissionIsland(Player $player, string $nameIsland, string $permission): bool
    {
        return $player->hasPermission($permission) ||
            $this->plugin->getServer()->isOp($player->getName()) ||
            $this->plugin->getManager()->isIsland($nameIsland) ||
            $this->plugin->getManager()->isMemberIsland($player, $nameIsland);
    }

    public function hasPermissionMemberIsland(Player $player, string $nameIsland, string $permission): bool
    {
        return $player->hasPermission($permission) && $this->plugin->getManager()->isMemberIsland($player, $nameIsland);
    }

    public function isMemberIsland(Player $player, string $nameIsland): bool
    {
        $nameIsland = $this->generateNameSpecialIsland($nameIsland);
        $data = $this->getDataIslands()->get($nameIsland, []);
        if (!empty($data)) {
            $members = $data["members"];
            $nameMember = $player->getXuid();
            return in_array($nameMember, $members);
        }
        return false;
    }

    public function isIsland(string $nameIsland): bool
    {
        $nameIsland = $this->generateNameSpecialIsland($nameIsland);
        return $this->getDataIslands()->get($nameIsland) !== null;
    }

    private function generateNameSpecialIsland(string $nameWorld): string
    {
        return str_replace([self::KEY_NETHER, self::KEY_END], self::KEY_ISLAND, $nameWorld);
    }

    public function isIslandNormal(string $nameIsland): bool
    {
        return str_contains($nameIsland, self::KEY_ISLAND);
    }

    public function isIslandNether(string $nameIsland): bool
    {
        return str_contains($nameIsland, self::KEY_NETHER);
    }

    public function isIslandEnd(string $nameIsland): bool
    {
        return str_contains($nameIsland, self::KEY_END);
    }

    public function convertToNormalIsland(string $nameIsland): string
    {
        return str_replace([self::KEY_NETHER, self::KEY_END], self::KEY_ISLAND, $nameIsland);
    }

    public function convertToNetherIsland(string $nameIsland): string
    {
        return str_replace([self::KEY_ISLAND, self::KEY_END], self::KEY_NETHER, $nameIsland);
    }

    public function convertToEndIsland(string $nameIsland): string
    {
        return str_replace([self::KEY_ISLAND, self::KEY_NETHER], self::KEY_END, $nameIsland);
    }

    public function getItemBannedIsland(string $nameIsland): array
    {
        $data = $this->getDataIslands()->get($nameIsland, []);
        return $data[$nameIsland]["items_banned"] ?? [];
    }

    private function calculateLevelIsland(float $xp): int
    {
        return (int)($xp / 1000);
    }

    public function getLevelIsland(string $nameIsland): int
    {
        $data = $this->getDataIslands()->get($nameIsland, []);
        return $this->calculateLevelIsland($data["xp"] ?? 0);
    }

    public function addXpIsland(string $nameIsland, float $xp): void
    {
        $nameIsland = $this->generateNameSpecialIsland($nameIsland);
        $data = $this->getDataIslands()->get($nameIsland, []);
        $data["xp"] += $xp;
        $this->getDataIslands()->set($nameIsland, $data);
    }

    /**
     * @param Player $player
     * @return Async<array>
     * @throws Throwable
     */
    public function getQuests(Player $player): Async
    {
        return new Async(function () use ($player): array {
            /** @var DataIslandPlayer|null $data */
            $data = Async::await($this->getIslandDataByMember($player->getXuid()));
            return $data?->getQuests() ?? [];
        });
    }

    public function getQuestById(Player $player, string $id): Async
    {
        return new Async(function () use ($player, $id): ?array {
            $quests = Async::await($this->getQuests($player));
            return $quests[$id] ?? null;
        });
    }

    public function setQuests(Player $player, array $quests): Async
    {
        return new Async(function () use ($player, $quests): void {
            /** @var DataIslandPlayer|null $data */
            $data = Async::await($this->getIslandDataByMember($player->getXuid()));
            if ($data !== null) {
                $data->setQuests($quests);
                $this->setDataIsland($data->getIsland(), $data->toArray());
            }
        });
    }

    public function setQuestById(Player $player, string $id, array $quest): Async
    {
        return new Async(function () use ($player, $id, $quest): void {
            $quests = Async::await($this->getQuests($player));
            $quests[$id] = $quest;
            Async::await($this->setQuests($player, $quests));
        });
    }

    public function setNamedIsland(Player $player, string $nameIsland): void
    {
        $data = $this->getIslandDataByOwner($player);
        if ($data !== null) {
            $island = $data["island"];
            $data["name"] = $nameIsland;
            $this->getDataIslands()->set($island, $data);
            $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_SET_NAME_SUCCESS));
        } else {
            $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_NO_ISLAND));
        }
    }

    public function getTopIslands(int $limit = 10): Async
    {
        return new Async(function () use ($limit) {
            try {
                $data = iterator_to_array($this->getDataIslands()->getAll());
                usort($data, function ($a, $b) {
                    return $b["xp"] <=> $a["xp"];
                });
                $i = 0;
                $top = [];
                foreach ($data as $value) {
                    if ($i >= $limit) break;
                    $ownerData = Async::await(Async::await(VPlayerDataSaver::getDataByXuid($value["owner"])));
                    if ($ownerData instanceof ResultQuery) $ownerData = $ownerData->getResult();
                    $top[] = [
                        "owner" => $ownerData->getResult()["name"] ?? "Unknown",
                        "level" => $this->calculateLevelIsland($value["xp"]),
                        "xp" => $value["xp"],
                    ];
                    $i++;
                }
                return $top;
            } catch (Throwable $error) {
                ErrorLogger::logThrowable($error);
                return [];
            }
        });
    }

    public function isItemBannedIsland(string $nameIsland, Item $item, ?Player $player = null): bool
    {
        if ($item->isNull()) return false;
        $item = clone $item;
        $item->setCount(1); // Prevents the item from being banned with a quantity greater than 1
        $data = $this->getDataIslands()->get($nameIsland, []);
        $itemString = ItemUtil::encodeItem($item);
        $result = in_array($itemString, $data["items_banned"] ?? []);
        if ($player !== null && $result) $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_ITEM_BANNED));
        return $result;
    }

    public function getOwnerByNameIsland(string $nameIsland): ?string
    {
        $data = $this->getDataIslands()->get($nameIsland, []);
        return $data["owner"] ?? null;
    }

    public function getMembersByNameIsland(string $nameIsland): array
    {
        $data = $this->getDataIslands()->get($nameIsland, []);
        return $data["members"] ?? [];
    }

    public function getMembersByPlayer(Player $player): Async
    {
        return new Async(function () use ($player) {
            /** @var DataIslandPlayer|null $data */
            $data = Async::await($this->getIslandDataByMember($player->getXuid()));
            return $data->getMembers() ?? [];
        });
    }

    public function getIslandSizeByNameIsland(string $nameIsland): int
    {
        $data = $this->getDataIslands()->get($nameIsland, []);
        return $data["max_size"] ?? 0;
    }

    private function getOriginSpawn(string $nameIsland, string $nameData): Vector3
    {
        $data = $this->getDataIslands()->get($nameIsland, []);
        $stringVector = $data[$nameData] ?? "0:0:0";
        $arrayVector = explode(":", $stringVector);
        $x = (int)$arrayVector[0];
        $y = (int)$arrayVector[1];
        $z = (int)$arrayVector[2];
        return new Vector3($x, $y, $z);
    }

    public function getOriginSpawnByNameIsland(string $nameIsland): Vector3
    {
        return $this->getOriginSpawn($nameIsland, "origin_spawn");
    }

    public function getOriginSpawnNetherByNameIsland(string $nameIsland): Vector3
    {
        return $this->getOriginSpawn($nameIsland, "origin_spawn_nether");
    }

    public function getOriginSpawnEndByNameIsland(string $nameIsland): Vector3
    {
        return $this->getOriginSpawn($nameIsland, "origin_spawn_end");
    }

    public function isAllowVisitorByNameIsland(string $nameIsland): bool
    {
        $data = $this->getDataIslands()->get($nameIsland, []);
        return $data["allow_visitor"] ?? false;
    }

    public function isBannedByNameIsland(Player $player, string $nameIsland): bool
    {
        $data = $this->getDataIslands()->get($nameIsland, []);
        return in_array($player->getXuid(), $data["banned_players"] ?? []);
    }

    public function checkBanPlayer(Player $player, string $nameIsland): bool
    {
        if ($this->isBannedByNameIsland($player, $nameIsland)) {
            $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_BANNED_BY_OWNER));
            return true;
        }
        return false;
    }

    public function haveIsland(string $playerXuid): Async
    {
        return new Async(function () use ($playerXuid) {
            $data = Async::await($this->getIslandDataByMember($playerXuid));
            return $data !== null;
        });
    }

    public function isPVPByNameIsland(string $nameIsland): bool
    {
        $nameIsland = $this->generateNameSpecialIsland($nameIsland);
        $data = $this->getDataIslands()->get($nameIsland, []);
        return $data["pvp"] ?? false;
    }

    public function getIslandByNamePlayer(Player $player): Promise
    {
        return new Promise(function ($resolve, $reject) use ($player) {
            try {
                $playerXuid = $player->getXuid();
                foreach ($this->getDataIslands()->getAll() as $nameIsland => $value) {
                    if ($value["owner"] === $playerXuid) $resolve($nameIsland);
                    $members = $value["members"];
                    foreach ($members as $member) {
                        if ($member === $playerXuid) $resolve($nameIsland);
                        FiberManager::wait();
                    }
                    FiberManager::wait();
                }
                $resolve(null);
            } catch (Throwable $e) {
                ErrorLogger::logThrowable($e); // Log error
                $reject($e);
            }
        });
    }

    public function setData(Player $player, string $setting, bool $allow): void
    {
        $data = $this->getIslandDataByOwner($player);
        if ($data !== null) {
            $nameIsland = $data["island"];
            $data[$setting] = $allow;
            $this->getDataIslands()->set($nameIsland, $data);
        } else {
            $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_NO_ISLAND));
        }
    }

    public function banPlayer(Player $player, string $namePlayer): Async
    {
        return new Async(function () use ($player, $namePlayer): void {
            if ($player->getXuid() === $namePlayer) {
                $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_DO_WITH_YOURSELF));
                return;
            }
            /** @var DataIslandPlayer|null $data */
            $data = Async::await($this->getIslandDataByMember($player->getXuid()));
            if ($data !== null && $this->hasPermissionMemberIsland($player, $data->getIsland(), Permissions::ISLAND_BAN)) {
                $nameIsland = $data->getIsland();
                $bannedPlayers = $data->getBannedPlayers();
                if (!in_array($namePlayer, $bannedPlayers)) {
                    $bannedPlayers[] = $namePlayer;
                    $data->setBannedPlayers($bannedPlayers);
                    $this->getDataIslands()->set($nameIsland, $data->toArray());
                    $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_BAN_PLAYER_SUCCESS));
                } else {
                    $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_PLAYER_BANNED_EXISTS));
                }
            } else {
                $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_NO_ISLAND));
            }
        });
    }

    public function unbanPlayer(Player $player, string $namePlayer): Async
    {
        return new Async(function () use ($player, $namePlayer): void {
            if ($player->getXuid() === $namePlayer) {
                $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_DO_WITH_YOURSELF));
                return;
            }
            /** @var DataIslandPlayer|null $data */
            $data = Async::await($this->getIslandDataByMember($player->getXuid()));
            if ($data !== null && $this->hasPermissionMemberIsland($player, $data->getIsland(), Permissions::ISLAND_UNBAN)) {
                $bannedPlayers = $data->getBannedPlayers();
                if (in_array($namePlayer, $bannedPlayers)) {
                    $nameIsland = $data->getIsland();
                    $bannedPlayers = array_diff($bannedPlayers, [$namePlayer]);
                    $data->setBannedPlayers($bannedPlayers);
                    $this->getDataIslands()->set($nameIsland, $data->toArray());
                    $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_UNBAN_PLAYER_SUCCESS));
                } else {
                    $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_PLAYER_NOT_BANNED));
                }
            } else {
                $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_NO_ISLAND));
            }
        });
    }

    public function create(Player $player): Async
    {
        $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_WAITING_FOR_ISLAND_TO_BE_CREATED));
        return new Async(function () use ($player): void {
            $haveIsland = Async::await($this->haveIsland($player->getXuid()));
            if ($haveIsland) {
                $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_ALREADY_HAS_ISLAND));
                return;
            }
            if (!$this->plugin->getProvider()->islandOriginIsWorking()) {
                $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_ISLAND_ORIGIN_NOT_WORKING));
                return;
            }
            $xuidPlayer = $player->getXuid();
            if ($this->getIslandDataByOwner($player) === null) {
                $namePlayer = $player->getName();
                $nameIsland = $this->generateNameNewIsland($player);
                $prepareData = new DataIslandPlayer(
                    island: $nameIsland,
                    name: $namePlayer,
                    owner: $xuidPlayer,
                    timeCreate: MathUtil::getTimeNowString(),
                    max_size: 100,
                    xp: 0.0,
                    members: [
                        $player->getXuid()
                    ],
                    items_banned: [],
                    pvp: false,
                    dropped_item: false,
                    pick_up_item: false,
                    allow_visitors: true
                );
                try {
                    $nameNether = $this->generateNameNewNetherIsland($player);
                    $nameEnd = $this->generateNameNewEndIsland($player);
                    if (
                        Async::await(WorldUtil::generateWorldSkyBlock($nameIsland)) &&
                        Async::await(WorldUtil::generateWorldNetherSkyBlock($nameNether)) &&
                        Async::await(WorldUtil::generateWorldEndSkyBlock($nameEnd))
                    ) {
                        WorldUtil::checkLoadWorld($nameIsland);
                        WorldUtil::checkLoadWorld($nameNether);
                        WorldUtil::checkLoadWorld($nameEnd);
                        $worldIsland = $this->plugin->getServer()->getWorldManager()->getWorldByName($nameIsland);
                        $worldNether = $this->plugin->getServer()->getWorldManager()->getWorldByName($nameNether);
                        $worldEnd = $this->plugin->getServer()->getWorldManager()->getWorldByName($nameEnd);

                        // Apply island nether and end
                        $this->plugin->getWorldManager()->applyIslandNether($nameNether);
                        $this->plugin->getWorldManager()->applyIslandEnd($nameEnd);

                        // Set origin spawn
                        $prepareData->setOriginSpawn(MathUtil::vector3ToString($worldIsland->getSpawnLocation()->asVector3()));
                        $prepareData->setOriginSpawnNether(MathUtil::vector3ToString($worldNether->getSpawnLocation()->asVector3()));
                        $prepareData->setOriginSpawnEnd(MathUtil::vector3ToString($worldEnd->getSpawnLocation()->asVector3()));
                    }
                    $this->getDataIslands()->set($nameIsland, $prepareData->toArray());
                    $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_ISLAND_CREATED));
                    Async::await($this->join($player));
                } catch (Throwable $e) {
                    ErrorLogger::logThrowable($e); // Log error
                }
            }
        });
    }

    public function join(Player $player): Async
    {
        return new Async(function () use ($player): void {
            /** @var DataIslandPlayer|null $data */
            $data = Async::await($this->getIslandDataByMember($player->getXuid()));
            if ($data !== null) {
                try {
                    $eventInstance = new VMSBJoinIslandEvent($player, $data->getIsland());
                    $eventInstance->call(); // Call event
                    WorldUtil::checkLoadWorld($data->getIsland());
                    $world = $this->plugin->getServer()->getWorldManager()->getWorldByName($data->getIsland());
                    $player->teleport($world->getSpawnLocation());
                } catch (Throwable $e) {
                    ErrorLogger::logThrowable($e); // Log error
                }
            } else {
                $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_NO_ISLAND));
            }
        });
    }

    public function visit(Player $player, string $nameIsland): void
    {
        $data = $this->getDataIslands()->get($nameIsland, []);
        if (!empty($data) && $data["allow_visitor"]) {
            WorldUtil::checkLoadWorld($nameIsland);
            $world = $this->plugin->getServer()->getWorldManager()->getWorldByName($nameIsland);
            if ($world !== null) {
                // Check if the player is banned
                if (!$this->checkBanPlayer($player, $nameIsland)) {
                    $player->teleport($world->getSpawnLocation());
                } else {
                    $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_BANNED_BY_OWNER));
                }
            } else {
                $this->plugin->getLogger()->error("Error loading world for player " . $player->getName());
            }
        } else {
            $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_ISLAND_NOT_FOUND));
        }
    }

    public function delete(Player $player): Async
    {
        $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_WAITING_FOR_ISLAND_TO_BE_DELETED));
        return new Async(function () use ($player): void {
            $data = $this->getIslandDataByOwner($player);
            if ($data !== null) {
                $nameIsland = $data["island"];
                $world = $this->plugin->getServer()->getWorldManager()->getWorldByName($nameIsland);
                try {
                    if ($world !== null) {
                        WorldUtil::checkUnloadWorld($nameIsland);
                        Async::await(WorldUtil::removeWorldSkyBlock($nameIsland));
                    } else {
                        $pathWorld = $this->plugin->getProvider()->getWorldsFolder() . DIRECTORY_SEPARATOR . $nameIsland;
                        if (is_dir($pathWorld)) {
                            $this->plugin->getLogger()->debug("Deleting world for player " . $player->getName());
                            Async::await(WorldUtil::removeWorldSkyBlock($nameIsland));
                        }
                    }
                } catch (Throwable $e) {
                    ErrorLogger::logThrowable($e); // Log error
                }
                $this->getDataIslands()->remove($nameIsland);
                $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_ISLAND_DELETED));
            } else {
                $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_NO_ISLAND));
            }
        });
    }

    public function addMember(Player $player, string $nameMember): Async
    {
        return new Async(function () use ($player, $nameMember): void {
            if ($player->getXuid() === $nameMember) {
                $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_DO_WITH_YOURSELF));
                return;
            }
            if (Async::await($this->haveIsland($nameMember))) {
                $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_ALREADY_HAS_ISLAND));
                return;
            }
            /** @var DataIslandPlayer|null $data */
            $data = Async::await($this->getIslandDataByMember($player->getXuid()));
            if ($data !== null && $this->hasPermissionMemberIsland($player, $data->getIsland(), Permissions::ISLAND_ADD_MEMBER)) {
                $members = $data->getMembers();
                if (!in_array($nameMember, $members)) {
                    $nameIsland = $data->getIsland();
                    $members[] = $nameMember;
                    $data->setMembers($members);
                    $this->getDataIslands()->set($nameIsland, $data);
                    try {
                        $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_ADD_MEMBER_SUCCESS));

                        // Future update: proxy send message to player member

                        /** @var ResultQuery|Exception $dataPlayer */
                        $dataPlayer = async::await(async::await(VPlayerDataSaver::getDataByXuid($nameMember)));
                        if ($dataPlayer instanceof ResultQuery) $dataPlayer = $dataPlayer->getResult();
                        $playerMember = $this->plugin->getServer()->getPlayerExact($dataPlayer["name"]);
                        $playerMember?->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_HAS_BEEN_ADDED));
                    } catch (Throwable $e) {
                        ErrorLogger::logThrowable($e); // Log error
                    }
                } else {
                    $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_MEMBER_EXISTS));
                }
            } else {
                $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_NO_ISLAND));
            }
        });
    }

    public function removeMember(Player $player, string $nameMember): Async
    {
        return new Async(function () use ($player, $nameMember): void {
            if ($player->getXuid() === $nameMember) {
                $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_DO_WITH_YOURSELF));
                return;
            }
            $data = Async::await($this->getIslandDataByMember($player->getXuid()));
            if ($data !== null && $this->hasPermissionMemberIsland($player, $data->getIsland(), Permissions::ISLAND_REMOVE_MEMBER)) {
                $members = $data["members"];
                if (in_array($nameMember, $members)) {
                    $nameIsland = $data["island"];
                    $members = array_diff($members, [$nameMember]);
                    $data["members"] = $members;
                    $this->getDataIslands()->set($nameIsland, $data);
                    $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_REMOVE_MEMBER_SUCCESS));
                } else {
                    $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_MEMBER_NOT_EXISTS));
                }
            } else {
                $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_NO_ISLAND));
            }
        });
    }

    public function kick(Player $owner, Player $player): Async
    {
        return new Async(function () use ($owner, $player): void {
            if ($player->hasPermission(Permissions::CANT_GOT_KICKED)) {
                $owner->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_CANNOT_DO_THIS_WITH_THE_PLAYER_HAS_PERMISSION));
                return;
            }
            if ($owner->getXuid() === $player->getXuid()) {
                $owner->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_DO_WITH_YOURSELF));
                return;
            }
            /** @var DataIslandPlayer|null $data */
            $data = Async::await($this->getIslandDataByMember($owner->getXuid()));
            if ($data !== null && $this->hasPermissionMemberIsland($owner, $data->getIsland(), Permissions::ISLAND_KICK)) {
                $nameIsland = $data->getIsland();
                $world = $this->plugin->getServer()->getWorldManager()->getWorldByName($nameIsland);
                if ($world !== null) {
                    if ($player->getWorld() === $world) {
                        $player->teleport($this->plugin->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
                        $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_KICKED_FROM_ISLAND, ["%player%" => $owner->getName()]));
                        $owner->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_KICK_PLAYER_SUCCESS, ["%player%" => $player->getName()]));
                    }
                }
            } else {
                $owner->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_NO_ISLAND));
            }
        });
    }

    /**
     * @param string $nameIsland
     * @param string $nameData
     * @param Vector3 $position
     *
     * This is function skip check `$nameIsland = $this->generateNameSpecialIsland($nameIsland);`
     */
    private function setOriginSpawn(string $nameIsland, string $nameData, Vector3 $position): void
    {
        $data = $this->getDataIslands()->get($nameIsland, []);
        if (!empty($data)) {
            $x = $position->getX();
            $y = $position->getY();
            $z = $position->getZ();
            $stringVector = $x . ":" . $y . ":" . $z;
            $data[$nameData] = $stringVector;
            $this->getDataIslands()->set($nameIsland, $data);
        }
    }

    public function setOriginSpawnIsland(string $nameIsland, Vector3 $position): void
    {
        $this->setOriginSpawn($nameIsland, "origin_spawn", $position);
    }

    public function setOriginSpawnNetherIsland(string $nameIsland, Vector3 $position): void
    {
        $this->setOriginSpawn($nameIsland, "origin_spawn_nether", $position);
    }

    public function setOriginSpawnEndIsland(string $nameIsland, Vector3 $position): void
    {
        $this->setOriginSpawn($nameIsland, "origin_spawn_end", $position);
    }

    public function checkOutSizeIsland(string $nameIsland, Vector3 $pos, float $sizeAdd = 0): ?bool
    {
        $replaceName = $this->generateNameSpecialIsland($nameIsland);
        $isNether = $this->isIslandNether($replaceName);
        $isEnd = $this->isIslandEnd($replaceName);
        $islandSize = $this->getIslandSizeByNameIsland($nameIsland);
        if ($islandSize > 0) {
            if ($isNether) {
                $originSpawn = $this->getOriginSpawnNetherByNameIsland($replaceName);
            } elseif ($isEnd) {
                $originSpawn = $this->getOriginSpawnEndByNameIsland($replaceName);
            } else {
                $originSpawn = $this->getOriginSpawnByNameIsland($replaceName);
            }
            $distance = MathUtil::calculateXZDistance($originSpawn, $pos);
            return $distance > ($islandSize + $sizeAdd);
        }
        return null;
    }

    public function changeSizeIsland(string $nameIsland, int $size): void
    {
        $nameIsland = $this->generateNameSpecialIsland($nameIsland);
        $data = $this->getDataIslands()->get($nameIsland, []);
        if (!empty($data)) {
            $data["size"] = $size;
            $this->getDataIslands()->set($nameIsland, $data[$nameIsland]);
        } else {
            $this->plugin->getLogger()->warning("Error when changing size island " . $nameIsland);
        }
    }

    public function addItemBanIsland(Player $player, Item $item): Async
    {
        return new Async(function () use ($player, $item): void {
            $item->setCount(1); // Prevents the item from being banned with a quantity greater than 1
            /** @var DataIslandPlayer|null $data */
            $data = Async::await($this->getIslandDataByMember($player->getXuid()));
            if ($data !== null && $this->hasPermissionMemberIsland($player, $data->getIsland(), Permissions::ISLAND_BAN_ITEM)) {
                $itemsBanned = $data->getItemsBanned();
                $itemString = ItemUtil::encodeItem($item);
                if (!in_array($itemString, $itemsBanned)) {
                    $nameIsland = $data->getIsland();
                    $itemsBanned[] = $itemString;
                    $data->setItemsBanned($itemsBanned);
                    $this->getDataIslands()->set($nameIsland, $data->toArray());
                    $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_ADD_ITEM_BAN_SUCCESS));
                } else {
                    $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_ITEM_BANNED_EXISTS));
                }
            } else {
                $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_NO_ISLAND));
            }
        });
    }

    public function unbanItemIsland(Player $player, Item $item): Async
    {
        return new Async(function () use ($player, $item): void {
            /** @var DataIslandPlayer|null $data */
            $data = Async::await($this->getIslandDataByMember($player->getXuid()));
            if ($data !== null && $this->hasPermissionMemberIsland($player, $data->getIsland(), Permissions::ISLAND_UNBAN_ITEM)) {
                $itemsBanned = $data->getItemsBanned();
                $itemString = ItemUtil::encodeItem($item);
                if (in_array($itemString, $itemsBanned)) {
                    $nameIsland = $data->getIsland();
                    $itemsBanned = array_diff($itemsBanned, [$itemString]);
                    $data->setItemsBanned($itemsBanned);
                    $this->getDataIslands()->set($nameIsland, $data->toArray());
                    $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_UNBAN_ITEM_SUCCESS));
                } else {
                    $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_ITEM_NOT_BANNED));
                }
            } else {
                $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_NO_ISLAND));
            }
        });
    }

    public function setSpawnIsland(Player $player): Async
    {
        return new Async(function () use ($player): void {
            /** @var DataIslandPlayer|null $dataIsland */
            $dataIsland = Async::await(VMSkyBlock::getInstance()->getManager()->getIslandDataByMember($player->getXuid()));
            if ($dataIsland !== null && $this->hasPermissionMemberIsland($player, $dataIsland->getIsland(), Permissions::ISLAND_SET_SPAWN)) {
                $world = $player->getWorld();
                if ($dataIsland->getIsland() === $world->getFolderName()) {
                    $world->setSpawnLocation($player->getPosition());
                    $world->save(); // Save the world
                    $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_SET_SPAWN_SUCCESS));
                } else {
                    $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_PLEASE_STAND_ON_YOUR_ISLAND));
                }
            } else {
                $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_ISLAND_NOT_FOUND));
            }
        });
    }

    public function freeWorldIsland(World $world): void
    {
        if (empty($world->getPlayers())) WorldUtil::checkUnloadWorld($world->getFolderName());
    }

    public function getDataIslands(): DataStorage
    {
        return $this->plugin->getProvider()->getConfigProvider()->getData();
    }

}