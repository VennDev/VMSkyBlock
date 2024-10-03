<?php

declare(strict_types=1);

namespace venndev\vmskyblock\listeners;

use Generator;
use Throwable;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\types\SpawnSettings;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\block\Block;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\ChangeDimensionPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\PlayerAction;
use pocketmine\player\Player;
use venndev\vmskyblock\data\ConfigPaths;
use venndev\vmskyblock\data\DataIslandPlayer;
use venndev\vmskyblock\data\Permissions;
use venndev\vmskyblock\entity\EntityPortal;
use venndev\vmskyblock\event\VMSBBlockBreakEvent;
use venndev\vmskyblock\event\VMSBBlockPlaceEvent;
use venndev\vmskyblock\event\VMSBCraftItemEvent;
use venndev\vmskyblock\event\VMSBCreatePortal;
use venndev\vmskyblock\event\VMSBEntityDamageByEntityEvent;
use venndev\vmskyblock\event\VMSBEntityItemPickupEvent;
use venndev\vmskyblock\event\VMSBLeaveIslandEvent;
use venndev\vmskyblock\event\VMSBPlayerDeathEvent;
use venndev\vmskyblock\event\VMSBPlayerDropItemEvent;
use venndev\vmskyblock\event\VMSBPlayerInteractEvent;
use venndev\vmskyblock\forms\FormRemovePortal;
use venndev\vmskyblock\manager\MessageManager;
use venndev\vmskyblock\manager\VMSkyBlockManager;
use venndev\vmskyblock\utils\ArrayPHP;
use venndev\vmskyblock\utils\ItemUtil;
use venndev\vmskyblock\utils\TagSpecial;
use venndev\vmskyblock\utils\TypeIsland;
use venndev\vmskyblock\utils\WorldUtil;
use venndev\vmskyblock\VMSkyBlock;
use vennv\vapm\Async;
use vennv\vapm\CoroutineGen;
use vennv\vapm\System;

final readonly class EventListener implements Listener
{

    public function __construct(
        private VMSkyBlock $plugin
    )
    {
        // Register events here
    }

    private function isOutRange(string $worldName, Player $player, Block $block, string $permission): bool
    {
        if ($this->plugin->getManager()->isIsland($worldName)) {
            if (!$this->plugin->getManager()->hasPermissionIsland($player, $worldName, $permission)) return true;
            if ($this->plugin->getManager()->checkOutSizeIsland($worldName, $block->getPosition()->asVector3())) {
                $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_OUT_OF_RANGE_ISLAND));
                return true;
            }
        }
        return false;
    }

    public function onPlayerJoin(PlayerJoinEvent $event): void
    {
        $player = $event->getPlayer();
        $this->plugin->getPlayerManager()->getSkyblockPlayer($player); // Initialize the player data
    }

    public function onPlayerQuit(PlayerQuitEvent $event): void
    {
        $player = $event->getPlayer();
        $this->plugin->getPlayerManager()->deleteSkyblockPlayer($player);
    }

    public function onDataPacketSend(DataPacketSendEvent $event): void
    {
        // Cancel the event if the configuration is not loaded
        if (!$this->plugin->getProvider()->getConfigProvider()->isLoaded()) $event->cancel();

        // Send the dimension change packet
        foreach ($event->getPackets() as $packet) {
            foreach ($event->getTargets() as $target) {
                $player = $target->getPlayer();
                if ($player !== null) {
                    if ($packet instanceof StartGamePacket) {
                        if (($dimensionId = $this->plugin->getWorldManager()->getWorldDimension($player->getWorld()->getFolderName())) === null) {
                            continue;
                        }
                        $pk = clone $packet;
                        $pk->levelSettings->spawnSettings = new SpawnSettings(
                            $packet->levelSettings->spawnSettings->getBiomeType(),
                            $packet->levelSettings->spawnSettings->getBiomeName(),
                            $dimensionId
                        );
                        System::setTimeout(function () use ($target, $pk): void {
                            $target->sendDataPacket($pk);
                        }, 500);
                    } elseif ($packet instanceof MovePlayerPacket || $packet instanceof PlayerAuthInputPacket) {
                        if ($this->plugin->getPlayerManager()->getSkyblockPlayer($player)->isChangingDimension()) {
                            $event->cancel();
                        }
                    } elseif ($packet instanceof PlayerActionPacket) {
                        $packetType = $packet->action;
                        if ($packetType === PlayerAction::DIMENSION_CHANGE_ACK) {
                            $this->plugin->getPlayerManager()->getSkyblockPlayer($player)->onEndDimensionChange();
                        }
                    }
                }
            }
        }
    }

    /**
     * @throws Throwable
     */
    public function onBlockBreak(BlockBreakEvent $event): void
    {
        $player = $event->getPlayer();
        $block = clone $event->getBlock();
        $worldName = $player->getWorld()->getFolderName();
        if ($this->isOutRange($worldName, $player, $block, Permissions::BREAK_BLOCK)) {
            $event->cancel();
            return;
        }

        $item = $event->getItem();
        $instaBreak = $event->getInstaBreak();
        $drops = $event->getDrops();
        $xpDrop = $event->getXpDropAmount();
        if ($this->plugin->getManager()->isMemberIsland($player, $worldName)) {
            $eventInstance = new VMSBBlockBreakEvent($player, $block, $item, $instaBreak, $drops, $xpDrop);
            $eventInstance->call();

            $config = $this->plugin->getProvider()->getConfigProvider()->getIsland();
            $valuableBlocks = $config->getNested(ConfigPaths::ISLAND_SETTINGS_VALUABLE_BLOCKS);
            if ($valuableBlocks["enabled"]) {
                $replaceNameBlock = str_replace(" ", "_", strtolower($block->getName()));
                if (isset($valuableBlocks["blocks"][$replaceNameBlock])) {
                    $dataBlock = $valuableBlocks["blocks"][$replaceNameBlock];
                    $value = $dataBlock["value"];
                    $this->plugin->getManager()->addXpIsland($worldName, $value);

                    $specialIsland = null;
                    if ($this->plugin->getManager()->isIslandNether($worldName)) {
                        $specialIsland = TypeIsland::ISLAND_NETHER;
                    } elseif ($this->plugin->getManager()->isIslandEnd($worldName)) {
                        $specialIsland = TypeIsland::ISLAND_THE_END;
                    }
                    new Async(function () use ($player, $config, $dataBlock, $specialIsland): void {
                        if ($specialIsland !== null) {
                            $listRewards = Async::await(ArrayPHP::array_column($dataBlock[$specialIsland], "reward"));
                            if ($listRewards === false) return;
                            $this->plugin->getDoPlayer()->doPlayer($player, realpath($config->getPath()), $listRewards);
                        }
                    });
                }
            }
        }

        CoroutineGen::runNonBlocking(function () use ($player, $block): Generator {
            $position = $block->getPosition();
            foreach ($block->getCollisionBoxes() as $box) {
                foreach ($position->getWorld()->getNearbyEntities($box->expandedCopy(
                    3.0, 3.0, 3.0
                ), $player) as $entity) {
                    if ($entity instanceof EntityPortal) {
                        $formRemovePortal = new FormRemovePortal($player, $entity);
                        $formRemovePortal->sendForm();
                        System::setTimeout(function () use ($position, $block): void {
                            $position->getWorld()->setBlock($position, $block);
                        }, 100);
                        return;
                    }
                    yield;
                }
                yield;
            }
        });
    }

    public function onBlockPlace(BlockPlaceEvent $event): void
    {
        $player = $event->getPlayer();
        $block = $event->getBlockAgainst();
        $transaction = $event->getTransaction();
        $item = $event->getItem();
        $worldName = $player->getWorld()->getFolderName();
        if ($this->isOutRange($worldName, $player, $block, Permissions::PLACE_BLOCK)) $event->cancel();
        if ($this->plugin->getManager()->isItemBannedIsland($worldName, $player->getInventory()->getItemInHand(), $player)) {
            $event->cancel();
            return;
        }
        if ($this->plugin->getManager()->isMemberIsland($player, $worldName)) {
            $eventInstance = new VMSBBlockPlaceEvent($player, $transaction, $block, $item);
            $eventInstance->call();
        }
    }

    /**
     * @throws Throwable
     */
    public function onPlayerInteract(PlayerInteractEvent $event): void
    {
        $player = $event->getPlayer();
        $world = $player->getWorld();
        $worldName = $world->getFolderName();
        if ($this->plugin->getManager()->isIsland($worldName)) {
            $block = $event->getBlock();
            if ($this->isOutRange($worldName, $player, $block, Permissions::INTERACT_BLOCK)) $event->cancel();
            if ($this->plugin->getManager()->isItemBannedIsland($worldName, $player->getInventory()->getItemInHand(), $player)) {
                $event->cancel();
                return;
            }
            if ($this->plugin->getManager()->isMemberIsland($player, $worldName)) {
                $item = $event->getItem();
                $action = $event->getAction();
                $blockFace = $event->getFace();
                $touchVector = $event->getTouchVector();
                $eventInstance = new VMSBPlayerInteractEvent($player, $item, $block, $touchVector, $blockFace, $action);
                $eventInstance->call();
            }
        }

        // Check if the player is holding an item to create a portal
        $itemInHand = clone $player->getInventory()->getItemInHand();
        $tagPortal = $itemInHand->getNamedTag()->getTag(TagSpecial::TAG_ITEM_CREATE_PORTAL);
        if ($tagPortal !== null) {
            if (!$this->plugin->getManager()->isIsland($worldName)) {
                $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_PLEASE_STAND_ON_YOUR_ISLAND));
                return;
            }

            $this->plugin->getCache()->doNoSpamCache(
                key: $player->getXuid() . $tagPortal,
                get: fn() => null,
                callback: function () use ($itemInHand, $world, $player, $tagPortal, $event) {
                    new Async(function () use ($itemInHand, $world, $player, $tagPortal, $event) {
                        /** @var DataIslandPlayer|null $dataIslandPlayer */
                        $dataIslandPlayer = Async::await($this->plugin->getManager()->getIslandDataByMember($player->getXuid()));
                        if ($dataIslandPlayer === null) {
                            $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_NO_ISLAND));
                            return;
                        }
                        if ($dataIslandPlayer->getIsland() !== $world->getFolderName()) {
                            $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_PLEASE_STAND_ON_YOUR_ISLAND));
                            return;
                        }
                        $config = $this->plugin->getProvider()->getConfigProvider();
                        if ($tagPortal->getValue() === TypeIsland::ISLAND_NETHER) {
                            $data = $config->getIsland()->getNested(ConfigPaths::ISLAND_NETHER_PORTAL_SETTINGS);
                            $borderMaterial = $data["border-material"];
                            $bodyMaterial = $data["body-material"];
                        } elseif ($tagPortal->getValue() === TypeIsland::ISLAND_THE_END) {
                            $data = $config->getIsland()->getNested(ConfigPaths::ISLAND_THE_END_PORTAL_SETTINGS);
                            $borderMaterial = $data["border-material"];
                            $bodyMaterial = $data["body-material"];
                        } else {
                            return;
                        }

                        $height = $data["height"];
                        $width = $data["width"];

                        $position = $event->getBlock()->getPosition();
                        $eventCreatePortal = new VMSBCreatePortal($player, $position, $tagPortal->getValue());
                        $eventCreatePortal->call();

                        if (!$eventCreatePortal->isCancelled()) {
                            WorldUtil::createPortal(
                                entity: $player,
                                border: ItemUtil::toBlock(ItemUtil::getItem($borderMaterial)),
                                inside: ItemUtil::toBlock(ItemUtil::getItem($bodyMaterial)),
                                pos: $position->asVector3(),
                                height: $height,
                                width: $width,
                                portalType: $tagPortal->getValue()
                            );
                            $player->getInventory()->setItemInHand($itemInHand->setCount($itemInHand->getCount() - 1));
                        }
                    });
                }
            );
        }
    }

    public function onPlayerMove(PlayerMoveEvent $event): void
    {
        $player = $event->getPlayer();
        $world = $player->getWorld();
        $worldName = $world->getFolderName();
        if ($this->plugin->getManager()->isIsland($worldName)) {
            // $from = $event->getFrom();
            $to = $event->getTo();
            if (
                $this->plugin->getManager()->checkOutSizeIsland($worldName, $to->asVector3(), 5) ||
                $this->plugin->getManager()->checkOutSizeIsland($worldName, $to->asVector3(), 5, VMSkyBlockManager::KEY_NETHER) ||
                $this->plugin->getManager()->checkOutSizeIsland($worldName, $to->asVector3(), 5, VMSkyBlockManager::KEY_END)
            ) {
                $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_OUT_OF_RANGE_ISLAND));

                // Push the player back to the previous position
                // $player->setMotion($from->subtract($to->getX(), $to->getY(), $to->getZ())->multiply(2));
                $player->teleport($world->getSpawnLocation());
            }
            $config = $this->plugin->getProvider()->getConfigProvider()->getIsland();
            $void = $config->getNested(ConfigPaths::ISLAND_SETTINGS_VOID);
            if ($void["enabled"] && $to->getY() <= $void["y"]) $player->teleport($world->getSpawnLocation());
        }
    }

    public function onPlayerDropItem(PlayerDropItemEvent $event): void
    {
        $player = $event->getPlayer();
        $world = $player->getWorld();
        $worldName = $world->getFolderName();
        if ($this->plugin->getManager()->isIsland($worldName) && !$this->plugin->getManager()->hasPermissionIsland($player, $worldName, Permissions::DROP_ITEM)) {
            $event->cancel();
            return;
        }
        if ($this->plugin->getManager()->isMemberIsland($player, $worldName)) {
            $item = $event->getItem();
            $eventInstance = new VMSBPlayerDropItemEvent($player, $item);
            $eventInstance->call();
        }
    }

    public function onEntityItemPickup(EntityItemPickupEvent $event): void
    {
        $entity = $event->getEntity();
        if (!$entity instanceof Player) return;
        $origin = $event->getOrigin();
        $world = $origin->getWorld();
        $worldName = $world->getFolderName();
        if ($this->plugin->getManager()->isIsland($worldName) && !$this->plugin->getManager()->hasPermissionIsland($entity, $worldName, Permissions::PICK_UP_ITEM)) {
            $event->cancel();
            return;
        }
        if ($this->plugin->getManager()->isMemberIsland($entity, $worldName)) {
            $item = $event->getItem();
            $inventory = $event->getInventory();
            $eventInstance = new VMSBEntityItemPickupEvent($origin, $entity, $item, $inventory);
            $eventInstance->call();
        }
    }

    /**
     * @throws Throwable
     */
    public function onEntityDamageByEntity(EntityDamageByEntityEvent $event): void
    {
        $attacker = $event->getDamager();
        $world = $attacker->getWorld();
        $worldName = $world->getFolderName();
        if ($this->plugin->getManager()->isIsland($worldName)) {
            $entity = $event->getEntity();
            if ($attacker instanceof Player) {
                // Check if the attacker is attacking a portal
                if ($entity instanceof EntityPortal) {
                    $formRemovePortal = new FormRemovePortal($attacker, $entity);
                    $formRemovePortal->sendForm();
                    $event->cancel();
                    return;
                }

                // Call the event if the attacker is a member of the island
                if ($this->plugin->getManager()->isMemberIsland($attacker, $worldName)) {
                    $cause = $event->getCause();
                    $damage = $event->getBaseDamage();
                    $modifiers = $event->getModifiers();
                    $knockBack = $event->getKnockBack();
                    $verticalKnockBackLimit = $event->getVerticalKnockBackLimit();

                    $eventInstance = new VMSBEntityDamageByEntityEvent($attacker, $entity, $cause, $damage, $modifiers, $knockBack, $verticalKnockBackLimit);
                    $eventInstance->call();
                }

                $checkPermissionAttacker = $this->plugin->getManager()->hasPermissionIsland($attacker, $worldName, Permissions::PVP);
                if ($entity instanceof Player) {
                    $checkPermissionEntity = $this->plugin->getManager()->hasPermissionIsland($entity, $worldName, Permissions::PVP);

                    // If the attacker and the entity do not have permission, cancel the event
                    if (
                        ($checkPermissionAttacker && !$checkPermissionEntity) ||
                        (!$checkPermissionAttacker && $checkPermissionEntity) ||
                        (!$checkPermissionAttacker && !$checkPermissionEntity)
                    ) {
                        $event->cancel();
                        $attacker->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_PVP_DISABLED));
                    }

                    // If the attacker and the entity have permission, check if pvp is enabled
                    if ($checkPermissionAttacker && $checkPermissionEntity) {
                        if (!$this->plugin->getManager()->isPVPByNameIsland($worldName)) {
                            $event->cancel();
                            $attacker->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_PVP_DISABLED));
                        }
                    }
                } else {

                    // If the attacker does not have permission, cancel the event
                    if (!$checkPermissionAttacker) $event->cancel();
                }
            } else {
                if ($entity instanceof Player) {
                    $checkPermissionEntity = $this->plugin->getManager()->hasPermissionIsland($entity, $worldName, Permissions::PVP);

                    // If the entity does not have permission and the world is an island, cancel the event
                    if (!$checkPermissionEntity) $event->cancel();
                }
            }
        }
    }

    public function onPlayerItemUse(PlayerItemUseEvent $event): void
    {
        $player = $event->getPlayer();
        $world = $player->getWorld();
        $worldName = $world->getFolderName();
        if ($this->plugin->getManager()->isIsland($worldName)) {
            if (!$this->plugin->getManager()->hasPermissionIsland($player, $worldName, Permissions::USE_ITEM)) {
                $event->cancel();
            } else {
                if ($this->plugin->getManager()->isItemBannedIsland($worldName, $player->getInventory()->getItemInHand(), $player)) $event->cancel();
            }
        }
    }

    public function onCraftItem(CraftItemEvent $event): void
    {
        $player = $event->getPlayer();
        $world = $player->getWorld();
        $worldName = $world->getFolderName();
        if ($this->plugin->getManager()->isMemberIsland($player, $worldName)) {
            $craftingTransaction = $event->getTransaction();
            $recipe = $event->getRecipe();
            $repetitions = $event->getRepetitions();
            $inputs = $event->getInputs();
            $outputs = $event->getOutputs();
            $eventInstance = new VMSBCraftItemEvent($craftingTransaction, $recipe, $repetitions, $inputs, $outputs);
            $eventInstance->call();
        }
    }

    /**
     * @throws Throwable
     */
    public function onEntityTeleport(EntityTeleportEvent $event): void
    {
        $entity = $event->getEntity();
        if (!$entity instanceof Player) return;
        $from = $event->getFrom();
        $to = $event->getTo();
        $fromWorld = $from->getWorld();
        $toWorld = $to->getWorld();
        if (
            $fromWorld->getFolderName() !== $toWorld->getFolderName() &&
            $this->plugin->getManager()->isMemberIsland($entity, $fromWorld->getFolderName())
        ) {
            $eventInstance = new VMSBLeaveIslandEvent($entity, $fromWorld->getFolderName());
            $eventInstance->call();
        }
        $dimensionId = $this->plugin->getWorldManager()->getWorldDimension($toWorld->getFolderName());
        $dimensionIdFrom = $this->plugin->getWorldManager()->getWorldDimension($fromWorld->getFolderName());
        if ($dimensionId === null || $dimensionIdFrom === null) return;
        if ($dimensionId === $dimensionIdFrom) return;
        // Call the event when the player is teleporting to another dimension
        $this->plugin->getPlayerManager()->getSkyblockPlayer($entity)->onBeginDimensionChange();
        $entity->getNetworkSession()->sendDataPacket(ChangeDimensionPacket::create(
            $dimensionId,
            $to->asVector3(),
            !$entity->isAlive(),
            $entity->getScreenLineHeight()
        ));
        // Send the dimension change acknowledgment packet
        System::setTimeout(function () use ($entity) {
            if (!$entity->isConnected() && !$entity->isOnline()) return;
            $location = BlockPosition::fromVector3($entity->getLocation());
            $entity->getNetworkSession()->sendDataPacket(PlayerActionPacket::create(
                $entity->getId(),
                PlayerAction::DIMENSION_CHANGE_ACK,
                $location,
                $location, 0
            ));
        }, 500);
    }

    public function onPlayerDeath(PlayerDeathEvent $event): void
    {
        $player = $event->getPlayer();
        $world = $player->getWorld();
        $worldName = $world->getFolderName();
        if ($this->plugin->getManager()->isMemberIsland($player, $worldName)) {
            $drops = $event->getDrops();
            $xp = $event->getXpDropAmount();
            $deathMessage = $event->getDeathMessage();
            $eventInstance = new VMSBPlayerDeathEvent($player, $drops, $xp, $deathMessage);
            $eventInstance->call();
        }
    }

}