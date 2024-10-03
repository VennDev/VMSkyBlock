<?php

declare(strict_types=1);

namespace venndev\vmskyblock\manager;

use Exception;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use venndev\vmskyblock\api\manager\IPlayerManager;
use venndev\vmskyblock\data\ConfigPaths;
use venndev\vmskyblock\player\VMSBPlayer;
use venndev\vmskyblock\utils\ItemUtil;
use venndev\vmskyblock\utils\TagSpecial;
use venndev\vmskyblock\utils\TypeIsland;
use venndev\vmskyblock\VMSkyBlock;
use vennv\vapm\CoroutineGen;

final class PlayerManager implements IPlayerManager
{

    /** @var array<string, VMSBPlayer> */
    private array $skyblockPlayers = [];

    public function __construct(private readonly VMSkyBlock $plugin)
    {
        // TODO: Implement __construct() method.
    }

    public function getSkyblockPlayer(Player $player): VMSBPlayer
    {
        return $this->skyblockPlayers[$player->getName()] ??= new VMSBPlayer($player);
    }

    public function deleteSkyblockPlayer(Player $player): void
    {
        if (isset($this->skyblockPlayers[$player->getName()])) {
            unset($this->skyblockPlayers[$player->getName()]);
        }
    }

    public function itemCreatePortal(string $type): Item
    {
        if ($type === TypeIsland::ISLAND_NETHER) {
            $typeConfig = ConfigPaths::ISLAND_NETHER_ITEM_CREATE_PORTAL;
        } elseif ($type === TypeIsland::ISLAND_THE_END) {
            $typeConfig = ConfigPaths::ISLAND_THE_END_ITEM_CREATE_PORTAL;
        } else {
            throw new Exception("Invalid type");
        }

        $dataItem = $this->getConfigIsland()->getNested($typeConfig);
        $item = ItemUtil::getItem($dataItem["item"]);
        $item->setCustomName($dataItem["name"]);
        $item->setLore($dataItem["lore"]);
        $item->getNamedTag()->setString(TagSpecial::TAG_ITEM_CREATE_PORTAL, $type);
        return $item;
    }

    public function giveItemCreatePortal(Player $player, string $type, int $count): void
    {
        $item = $this->itemCreatePortal($type);
        CoroutineGen::runNonBlocking(function () use ($player, $item, $count) {
            for ($i = 0; $i < $count; $i++) {
                $player->getInventory()->addItem($item);
                yield;
            }
        });
    }

    public function getConfigIsland(): Config
    {
        return $this->plugin->getProvider()->getConfigProvider()->getIsland();
    }

    public function getPlugin(): VMSkyBlock
    {
        return $this->plugin;
    }

}