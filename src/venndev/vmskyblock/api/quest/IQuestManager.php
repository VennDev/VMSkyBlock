<?php

namespace venndev\vmskyblock\api\quest;

use pocketmine\event\Event;
use pocketmine\player\Player;
use venndev\vmskyblock\data\DataQuest;
use venndev\vmskyblock\VMSkyBlock;
use vennv\vapm\Async;
use Throwable;

interface IQuestManager
{
    /**
     * @return Async<array|string>
     * @throws Throwable
     */
    public function getNextQuests(Player $player, bool $limit = false, bool $returnString = false): Async;

    /**
     * @throws Throwable
     * @return Async<DataQuest[]>
     */
    public function getHistoryQuests(Player $player): Async;

    /**
     * @param int $eventType
     * @param Player $player
     * @param Event $event
     * @return Async
     * @throws Throwable
     */
    public function doQuestEvent(int $eventType, Player $player, Event $event): Async;

    /**
     * @throws Throwable
     */
    public function updateNewQuests(Player $player): Async;

    public function getPlugin(): VMSkyBlock;
}