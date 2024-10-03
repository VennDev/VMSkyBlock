<?php

namespace venndev\vmskyblock\api\manager;

use Throwable;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\World;
use venndev\vdatastoragesystems\handler\DataStorage;
use venndev\vmskyblock\data\DataIslandPlayer;
use vennv\vapm\Async;
use vennv\vapm\Promise;

interface IVMSkyBlockManager
{
    public function getIslandDataByOwner(Player $player): null|array;

    public function getIslandDataByNameIsland(string $nameIsland): null|array;

    public function setDataIsland(string $nameIsland, array $data): void;

    /**
     * @return Promise<DataIslandPlayer|null>
     * @throws Throwable
     */
    public function getIslandDataByMember(string $playerXuid): Promise;

    /**
     * @return Async<DataIslandPlayer|null>
     * @throws Throwable
     */
    public function getNameIslandByPlayer(Player $player): Async;

    public function generateNameNewIsland(Player $player): string;

    public function generateNameNewNetherIsland(Player $player): string;

    public function generateNameNewEndIsland(Player $player): string;

    public function hasPermissionIsland(Player $player, string $nameIsland, string $permission): bool;

    public function hasPermissionMemberIsland(Player $player, string $nameIsland, string $permission): bool;

    public function isMemberIsland(Player $player, string $nameIsland): bool;

    public function isIsland(string $nameIsland): bool;

    public function isIslandNormal(string $nameIsland): bool;

    public function isIslandNether(string $nameIsland): bool;

    public function isIslandEnd(string $nameIsland): bool;

    public function convertToNormalIsland(string $nameIsland): string;

    public function convertToNetherIsland(string $nameIsland): string;

    public function convertToEndIsland(string $nameIsland): string;

    public function getItemBannedIsland(string $nameIsland): array;

    public function getLevelIsland(string $nameIsland): int;

    public function addXpIsland(string $nameIsland, float $xp): void;

    /**
     * @param Player $player
     * @return Async<array>
     * @throws Throwable
     */
    public function getQuests(Player $player): Async;

    /**
     * @param Player $player
     * @param string $id
     * @return Async<array|null>
     * @throws Throwable
     */
    public function getQuestById(Player $player, string $id): Async;

    /**
     * @param Player $player
     * @param array $quests
     * @return Async
     * @throws Throwable
     */
    public function setQuests(Player $player, array $quests): Async;

    /**
     * @throws Throwable
     */
    public function setQuestById(Player $player, string $id, array $quest): Async;

    public function setNamedIsland(Player $player, string $nameIsland): void;

    /**
     * @throws Throwable
     */
    public function getTopIslands(int $limit = 10): Async;

    /**
     * @param string $nameIsland
     * @param Item $item
     * @param Player|null $player
     * @return bool
     *
     * Check if the item is banned on the island. If the player is not null, it will send a message to the player.
     */
    public function isItemBannedIsland(string $nameIsland, Item $item, ?Player $player = null): bool;

    /**
     * @param string $nameIsland
     * @return string|null
     *
     * @return string|null - It will return xuid of owner island
     */
    public function getOwnerByNameIsland(string $nameIsland): ?string;

    public function getMembersByNameIsland(string $nameIsland): array;

    /**
     * @throws Throwable
     */
    public function getMembersByPlayer(Player $player): Async;

    public function getIslandSizeByNameIsland(string $nameIsland): int;

    public function getOriginSpawnByNameIsland(string $nameIsland): Vector3;

    public function getOriginSpawnNetherByNameIsland(string $nameIsland): Vector3;

    public function getOriginSpawnEndByNameIsland(string $nameIsland): Vector3;

    public function isAllowVisitorByNameIsland(string $nameIsland): bool;

    public function isBannedByNameIsland(Player $player, string $nameIsland): bool;

    public function checkBanPlayer(Player $player, string $nameIsland): bool;

    /**
     * @return Async<bool>
     * @throws Throwable
     */
    public function haveIsland(string $playerXuid): Async;

    public function isPVPByNameIsland(string $nameIsland): bool;

    /**
     * @return Promise<string|null|Throwable>
     * @throws Throwable
     *
     */
    public function getIslandByNamePlayer(Player $player): Promise;

    public function setData(Player $player, string $setting, bool $allow): void;

    /**
     * @param Player $player
     * @param string $namePlayer
     * @return Async
     * @throws Throwable
     */
    public function banPlayer(Player $player, string $namePlayer): Async;

    /**
     * @param Player $player
     * @param string $namePlayer
     * @return Async
     * @throws Throwable
     */
    public function unbanPlayer(Player $player, string $namePlayer): Async;

    /**
     * @param Player $player
     * @return Async
     * @throws Throwable
     */
    public function create(Player $player): Async;

    /**
     * @param Player $player
     * @return Async
     * @throws Throwable
     */
    public function join(Player $player): Async;

    /**
     * @param Player $player
     * @param string $nameIsland
     * @return void
     *
     * This function will change in the future :)
     */
    public function visit(Player $player, string $nameIsland): void;

    /**
     * @param Player $player
     * @return Async
     * @throws Throwable
     */
    public function delete(Player $player): Async;

    /**
     * @param Player $player
     * @param string $nameMember
     * @return Async
     * @throws Throwable
     */
    public function addMember(Player $player, string $nameMember): Async;

    /**
     * @param Player $player
     * @param string $nameMember
     * @return Async
     * @throws Throwable
     */
    public function removeMember(Player $player, string $nameMember): Async;

    /**
     * @param Player $owner
     * @param Player $player
     * @return Async
     * @throws Throwable
     */
    public function kick(Player $owner, Player $player): Async;

    public function setOriginSpawnIsland(string $nameIsland, Vector3 $position): void;

    public function setOriginSpawnNetherIsland(string $nameIsland, Vector3 $position): void;

    public function setOriginSpawnEndIsland(string $nameIsland, Vector3 $position): void;

    public function checkOutSizeIsland(string $nameIsland, Vector3 $pos, float $sizeAdd = 0): ?bool;

    public function changeSizeIsland(string $nameIsland, int $size): void;

    /**
     * @param Player $player
     * @param Item $item
     * @return Async
     * @throws Throwable
     */
    public function addItemBanIsland(Player $player, Item $item): Async;

    /**
     * @param Player $player
     * @param Item $item
     * @return Async
     * @throws Throwable
     */
    public function unbanItemIsland(Player $player, Item $item): Async;

    /**
     * @param Player $player
     * @return Async
     * @throws Throwable
     */
    public function setSpawnIsland(Player $player): Async;

    /**
     * This function will be called when the player leaves the world.
     * If the world is empty, it will be unloaded.
     *
     * Optimized for performance.
     */
    public function freeWorldIsland(World $world): void;

    public function getDataIslands(): DataStorage;
}