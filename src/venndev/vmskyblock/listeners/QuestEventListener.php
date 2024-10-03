<?php

declare(strict_types=1);

namespace venndev\vmskyblock\listeners;

use pocketmine\event\Listener;
use pocketmine\player\Player;
use venndev\vmskyblock\event\VMSBBlockBreakEvent;
use venndev\vmskyblock\event\VMSBBlockPlaceEvent;
use venndev\vmskyblock\event\VMSBCraftItemEvent;
use venndev\vmskyblock\event\VMSBEntityDamageByEntityEvent;
use venndev\vmskyblock\event\VMSBJoinIslandEvent;
use venndev\vmskyblock\event\VMSBLeaveIslandEvent;
use venndev\vmskyblock\event\VMSBPlayerDeathEvent;
use venndev\vmskyblock\event\VMSBPlayerDropItemEvent;
use venndev\vmskyblock\event\VMSBPlayerInteractEvent;
use venndev\vmskyblock\event\VMSBPlayerItemConsumeEvent;
use venndev\vmskyblock\event\VMSBPlayerItemEnchantEvent;
use venndev\vmskyblock\event\VMSBPlayerItemHeldEvent;
use venndev\vmskyblock\quest\QuestEventType;
use venndev\vmskyblock\VMSkyBlock;
use Throwable;

final readonly class QuestEventListener implements Listener
{

    public function __construct(
        private VMSkyBlock $plugin
    ) {
        // TODO: Implement __construct() method.
    }

    /**
     * @throws Throwable
     */
    public function onPlayerJoinIsland(VMSBJoinIslandEvent $event): void
    {
        $this->getPlugin()->getQuestManager()->doQuestEvent(QuestEventType::PLAYER_JOIN, ($player = $event->getPlayer()), $event);
    }

    /**
     * @throws Throwable
     */
    public function onPlayerLeaveIsland(VMSBLeaveIslandEvent $event): void
    {
        $this->getPlugin()->getQuestManager()->doQuestEvent(QuestEventType::PLAYER_LEAVE, ($player = $event->getPlayer()), $event);
    }

    /**
     * @throws Throwable
     */
    public function onPlayerDeath(VMSBPlayerDeathEvent $event): void
    {
        $this->getPlugin()->getQuestManager()->doQuestEvent(QuestEventType::PLAYER_DEATH, ($player = $event->getPlayer()), $event);
    }

    /**
     * @throws Throwable
     */
    public function onEntityDamageByEntity(VMSBEntityDamageByEntityEvent $event): void
    {
        $attacker = $event->getAttacker();
        if ($attacker instanceof Player)
            $this->getPlugin()->getQuestManager()->doQuestEvent(QuestEventType::PLAYER_KILL, $attacker, $event);
    }

    /**
     * @throws Throwable
     */
    public function onBlockBreakEvent(VMSBBlockBreakEvent $event): void
    {
        $this->getPlugin()->getQuestManager()->doQuestEvent(QuestEventType::PLAYER_BREAK_BLOCK, ($player = $event->getPlayer()), $event);
    }

    /**
     * @throws Throwable
     */
    public function onBlockPlaceEvent(VMSBBlockPlaceEvent $event): void
    {
        $this->getPlugin()->getQuestManager()->doQuestEvent(QuestEventType::PLAYER_PLACE_BLOCK, ($player = $event->getPlayer()), $event);
    }

    /**
     * @throws Throwable
     */
    public function onPlayerInteract(VMSBPlayerInteractEvent $event): void
    {
        $this->getPlugin()->getQuestManager()->doQuestEvent(QuestEventType::PLAYER_INTERACT_BLOCK, ($player = $event->getPlayer()), $event);
    }

    /**
     * @throws Throwable
     */
    public function onCraftItem(VMSBCraftItemEvent $event): void
    {
        $this->getPlugin()->getQuestManager()->doQuestEvent(QuestEventType::PLAYER_CRAFT_ITEM, ($player = $event->getPlayer()), $event);
    }

    /**
     * @throws Throwable
     */
    public function onPlayerItemEnchant(VMSBPlayerItemEnchantEvent $event): void
    {
        $this->getPlugin()->getQuestManager()->doQuestEvent(QuestEventType::PLAYER_ENCHANT_ITEM, ($player = $event->getPlayer()), $event);
    }

    /**
     * @throws Throwable
     */
    public function onPlayerItemConsume(VMSBPlayerItemConsumeEvent $event): void
    {
        $this->getPlugin()->getQuestManager()->doQuestEvent(QuestEventType::PLAYER_EAT_FOOD, ($player = $event->getPlayer()), $event);
    }

    /**
     * @throws Throwable
     */
    public function onPlayerDropItem(VMSBPlayerDropItemEvent $event): void
    {
        $this->getPlugin()->getQuestManager()->doQuestEvent(QuestEventType::PLAYER_THROW_ITEM, ($player = $event->getPlayer()), $event);
    }

    /**
     * @throws Throwable
     */
    public function onPlayerItemHeld(VMSBPlayerItemHeldEvent $event): void
    {
        $this->getPlugin()->getQuestManager()->doQuestEvent(QuestEventType::PLAYER_HOLD_ITEM, ($player = $event->getPlayer()), $event);
    }

    public function getPlugin(): VMSkyBlock
    {
        return $this->plugin;
    }

}