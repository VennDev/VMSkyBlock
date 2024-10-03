<?php

namespace venndev\vmskyblock\api\manager;

use Exception;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use Throwable;
use venndev\vmskyblock\player\VMSBPlayer;
use venndev\vmskyblock\VMSkyBlock;

interface IPlayerManager
{

    /**
     * @param Player $player
     * @return VMSBPlayer
     *
     * This method should return the VMSBPlayer instance for the given player.
     */
    public function getSkyblockPlayer(Player $player): VMSBPlayer;

    /**
     * @param Player $player
     *
     * This method should delete the VMSBPlayer instance for the given player.
     */
    public function deleteSkyblockPlayer(Player $player): void;

    /**
     * @param string<TypeIsland::ISLAND_NETHER|TypeIsland::ISLAND_THE_END> $type
     * @return Item
     * @throws Exception
     */
    public function itemCreatePortal(string $type): Item;

    /**
     * @throws Exception
     * @throws Throwable
     */
    public function giveItemCreatePortal(Player $player, string $type, int $count): void;

    public function getConfigIsland(): Config;

    public function getPlugin(): VMSkyBlock;
}