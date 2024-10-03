<?php

declare(strict_types=1);

namespace venndev\vmskyblock\quest;

use pocketmine\entity\Entity;
use pocketmine\event\Event;
use pocketmine\item\Item;
use pocketmine\player\Player;
use Throwable;
use venndev\vmskyblock\api\quest\IQuestManager;
use venndev\vmskyblock\data\ConfigPaths;
use venndev\vmskyblock\data\DataIslandPlayer;
use venndev\vmskyblock\data\DataQuest;
use venndev\vmskyblock\data\DataQuestPlayer;
use venndev\vmskyblock\data\QuestRequirementStruct;
use venndev\vmskyblock\event\VMSBBlockBreakEvent;
use venndev\vmskyblock\event\VMSBBlockPlaceEvent;
use venndev\vmskyblock\event\VMSBCraftItemEvent;
use venndev\vmskyblock\event\VMSBEntityDamageByEntityEvent;
use venndev\vmskyblock\event\VMSBEntityItemPickupEvent;
use venndev\vmskyblock\event\VMSBPlayerDropItemEvent;
use venndev\vmskyblock\event\VMSBPlayerInteractEvent;
use venndev\vmskyblock\event\VMSBPlayerItemConsumeEvent;
use venndev\vmskyblock\event\VMSBPlayerItemEnchantEvent;
use venndev\vmskyblock\event\VMSBPlayerItemHeldEvent;
use venndev\vmskyblock\event\VMSBPlayerItemUseEvent;
use venndev\vmskyblock\manager\MessageManager;
use venndev\vmskyblock\utils\ErrorLogger;
use venndev\vmskyblock\utils\ItemUtil;
use venndev\vmskyblock\VMSkyBlock;
use vennv\vapm\Async;
use vennv\vapm\FiberManager;
use vennv\vapm\PHPUtils;

final readonly class QuestManager implements IQuestManager
{

    public function __construct(private VMSkyBlock $plugin)
    {
        // TODO: Implement constructor
    }

    /**
     * @param Player $player
     * @param bool $limit
     * @param bool $returnString
     * @return Async<array|string>
     * @throws Throwable
     */
    public function getNextQuests(Player $player, bool $limit = false, bool $returnString = false): Async
    {
        return new Async(function () use ($player, $limit, $returnString): array|string {
            $quests = [];
            /** @var DataIslandPlayer|null $dataIsland */
            $dataIsland = Async::await($this->plugin->getManager()->getIslandDataByMember($player->getXuid()));
            if ($dataIsland !== null) {
                $numberOfQuestsAssigned = $this->plugin->getProvider()->getConfigProvider()->getQuestSettings()->get(ConfigPaths::QUESTS_NUMBER_OF_QUESTS_ASSIGNED);
                $i = 0;
                foreach ($dataIsland->getQuestGenerator() as $idQuest => $data) {
                    if ($limit && $i >= $numberOfQuestsAssigned) break;
                    // DataQuestPlayer
                    $dataQuestPlayer = DataQuestPlayer::fromArray($data);
                    if (!$dataQuestPlayer->isCompleted()) {
                        // DataQuest
                        $dataQuest = DataQuest::fromArray($this->plugin->getProvider()->getConfigProvider()->getQuests()[$idQuest]);
                        $quests[$idQuest] = $dataQuest->toArray();
                        $i++;
                    }
                    FiberManager::wait();
                }
            }
            return $returnString ? implode("\n", array_column($quests, "name")) : $quests;
        });
    }

    /**
     * @throws Throwable
     * @return Async<array<int, DataQuest>>
     */
    public function getHistoryQuests(Player $player): Async
    {
        return new Async(function () use ($player): array {
            $quests = [];
            $dataIsland = Async::await($this->plugin->getManager()->getIslandDataByMember($player->getXuid()));
            if ($dataIsland !== null) {
                foreach ($dataIsland->getQuestGenerator() as $idQuest => $data) {
                    $dataQuestPlayer = DataQuestPlayer::fromArray($data);
                    if ($dataQuestPlayer->isCompleted()) {
                        $quests[$idQuest] = DataQuest::fromArray($this->plugin->getProvider()->getConfigProvider()->getQuests()[$idQuest]);
                    }
                    FiberManager::wait();
                }
            }
            return $quests;
        });
    }

    public function doQuestEvent(int $eventType, Player $player, Event $event): Async
    {
        return new Async(function () use ($eventType, $player, $event): void {
            foreach (Async::await($this->getNextQuests($player, true)) as $data) {
                $dataQuestPlayer = Async::await($this->plugin->getManager()->getQuestById($player, $data["id"]));
                $dataQuest = DataQuestPlayer::fromArray($dataQuestPlayer);
                Async::await($this->processDataQuest($dataQuest, $eventType, $player, $event));
            }
        });
    }

    /**
     * @return Async<DataQuest[]>
     * @throws Throwable
     */
    private function processRequirements(array $requirements, Player $player, mixed $comparisonItem): Async
    {
        return new Async(function () use ($requirements, $player, $comparisonItem): array {
            return Async::await(PHPUtils::arrayMap($requirements, function (int $case, array $requirement) use ($player, $comparisonItem): array {
                $requirementStruct = QuestRequirementStruct::fromArray($requirement);
                $item = $requirementStruct->getItem();
                if ($item !== null && $comparisonItem instanceof Item && $requirementStruct->getType() === QuestRequirementType::REQUIREMENT_ITEM) {
                    try {
                        $item = ItemUtil::decodeItem($item);
                        if (!ItemUtil::equalItems($item, $comparisonItem)) return $requirement;
                        $requirementStruct->setAmount($requirementStruct->getAmount() - $comparisonItem->getCount());
                    } catch (Throwable $e) {
                        ErrorLogger::logThrowable($e); // Error handling
                    }
                } elseif (
                    ($requirementStruct->getType() === QuestRequirementType::REQUIREMENT_KILL &&
                        $comparisonItem instanceof Entity &&
                        $comparisonItem->getNameTag() === $item) ||
                    $requirementStruct->getType() === QuestRequirementType::REQUIREMENT_NORMAL
                ) {
                    $requirementStruct->setAmount($requirementStruct->getAmount() - 1);
                }
                return $requirementStruct->getAmount() <= 0 ? [] : $requirement;
            }));
        });
    }

    /**
     * @return Async<DataQuestPlayer>
     * @throws Throwable
     */
    private function processDataQuest(DataQuestPlayer $dataQuestPlayer, int $eventType, Player $player, Event $event): Async
    {
        return new Async(function () use ($dataQuestPlayer, $eventType, $player, $event): DataQuestPlayer {
            if ($dataQuestPlayer->getEvent() === $eventType) {
                $item = null;
                try {
                    if ($event instanceof VMSBPlayerItemUseEvent && !$event->isCancelled()) $item = $event->getItem();
                    elseif ($event instanceof VMSBPlayerItemHeldEvent && !$event->isCancelled()) $item = $event->getItem();
                    elseif ($event instanceof VMSBBlockBreakEvent && !$event->isCancelled()) $item = $event->getBlock()->asItem();
                    elseif ($event instanceof VMSBBlockPlaceEvent && !$event->isCancelled()) $item = $event->getBlockAgainst()->asItem();
                    elseif ($event instanceof VMSBPlayerInteractEvent && !$event->isCancelled()) $item = $event->getBlock()->asItem();
                    elseif ($event instanceof VMSBEntityItemPickupEvent && $event->getEntity() instanceof Player && !$event->isCancelled()) $item = $event->getItem();
                    elseif ($event instanceof VMSBCraftItemEvent && !$event->isCancelled()) $item = $event->getOutputs();
                    elseif ($event instanceof VMSBPlayerItemEnchantEvent && !$event->isCancelled()) $item = $event->getOutputItem();
                    elseif ($event instanceof VMSBPlayerItemConsumeEvent && !$event->isCancelled()) $item = $event->getItem();
                    elseif ($event instanceof VMSBPlayerDropItemEvent && !$event->isCancelled()) $item = $event->getItem();
                    elseif ($event instanceof VMSBEntityDamageByEntityEvent && !$event->isCancelled() && $event->getFinalDamage() >= $event->getEntity()->getHealth()) $item = $event->getEntity();
                } catch (Throwable $e) {
                    ErrorLogger::logThrowable($e); // Error handling
                }
                $requirementsLast = $dataQuestPlayer->getRequirements();
                $requirements = Async::await($this->processRequirements($requirementsLast, $player, $item));
                $requirements = Async::await(PHPUtils::arrayFilter($requirements, function (int $case, array $requirement): bool {
                    return !empty($requirement);
                }));
                $dataQuestPlayer->setRequirements($requirements);
                $countRequirements = count($requirements);
                $countRequirementsLast = count($requirementsLast);

                if ($countRequirements > 0 && $countRequirementsLast > 0) $percentage = ($countRequirements / $countRequirementsLast * 100);
                elseif ($countRequirements === 0 && $countRequirementsLast > 0) $percentage = 100.0;
                elseif ($countRequirements === 0 && $countRequirementsLast === 0) $percentage = 100.0;
                else $percentage = 0.0;

                $dataQuestPlayer->setProgress($percentage); // Update progress
                if (empty($requirements)) {
                    // No spam cache
                    $this->plugin->getCache()->doNoSpamCache(
                        key: [$dataQuestPlayer, $player->getXuid()],
                        get: fn() => null,
                        callback: function () use ($player, $dataQuestPlayer): void {
                            $dataQuestServer = DataQuest::fromArray($this->plugin->getProvider()->getConfigProvider()->getDetailsQuest($dataQuestPlayer->getId()));
                            $dataQuestPlayer->setCompleted(true);
                            $dataQuestPlayer->setCompletedAt(
                                completedAt: date($this->plugin->getProvider()->getConfigProvider()->getQuestSettings()->get(ConfigPaths::QUESTS_DATE_TIME_FORMAT))
                            );
                            $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_COMPLETE_QUEST, [
                                "%quest%" => $dataQuestServer->getName()
                            ]));
                            $pathQuest = $dataQuestServer->getPathDirectory();
                            if ($pathQuest !== null) {
                                $rewards = array_column($dataQuestServer->getRewards(), "reward");

                                /**
                                 * Example data:
                                 * "rewards" => {
                                 *     "reward" => "give %player% diamond 1",
                                 *     "reward" => ".\\quests\\quest1",
                                 *      ...
                                 */
                                $this->plugin->getDoPlayer()->doPlayer(
                                    player: $player,
                                    path: $pathQuest,
                                    data: $rewards
                                );
                            }

                            $this->plugin->getManager()->setQuestById($player, $dataQuestPlayer->getId(), $dataQuestPlayer->toArray());
                        });
                }
            }
            return $dataQuestPlayer;
        });
    }

    public function updateNewQuests(Player $player): Async
    {
        return new Async(function () use ($player): void {
            $dataIsland = Async::await($this->plugin->getManager()->getIslandDataByMember($player->getXuid()));
            if ($dataIsland !== null) {
                $questsServer = $this->plugin->getProvider()->getConfigProvider()->getPrepareQuestsPlayer();
                $questsPlayer = Async::await($this->plugin->getManager()->getQuests($player)) ?? [];
                $questsData = array_merge($questsServer, $questsPlayer);
                Async::await($this->plugin->getManager()->setQuests($player, $questsData));
            }
        });
    }

    public function getPlugin(): VMSkyBlock
    {
        return $this->plugin;
    }

}