<?php

declare(strict_types=1);

namespace venndev\vmskyblock\forms;

use Throwable;
use RuntimeException;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use venndev\vformoopapi\attributes\normal\VButton;
use venndev\vformoopapi\attributes\VForm;
use venndev\vformoopapi\Form;
use venndev\vformoopapi\utils\TypeForm;
use venndev\vmskyblock\data\ConfigPaths;
use venndev\vmskyblock\data\DataIslandPlayer;
use venndev\vmskyblock\data\DataQuest;
use venndev\vmskyblock\data\DataQuestPlayer;
use venndev\vmskyblock\forms\player\FormQuestManager;
use venndev\vmskyblock\forms\player\FormViewQuest;
use venndev\vmskyblock\VMSkyBlock;

#[VForm(
    title: "Quest Form",
    type: TypeForm::NORMAL_FORM,
    content: "-",
)]
final class FormQuest extends Form
{

    private VMSkyBlock $plugin;
    private ?array $dataForm;

    public function __construct(Player $player, DataIslandPlayer $dataIslandPlayer)
    {
        $this->plugin = VMSkyBlock::getInstance();
        $this->dataForm = $this->plugin->getProvider()->getConfigProvider()->getForms()->getNested(ConfigPaths::FORMS_QUEST_SKYBLOCK_FORM);
        if ($this->dataForm === null) throw new RuntimeException("Form data not found");
        $this->setTitle(TextFormat::colorize($this->dataForm["title"]));
        $this->setContent(TextFormat::colorize($this->dataForm["content"]));

        $numberOfQuestsAssigned = $this->plugin->getProvider()->getConfigProvider()->getQuestSettings()->get(ConfigPaths::QUESTS_NUMBER_OF_QUESTS_ASSIGNED);
        $i = 0;
        foreach ($dataIslandPlayer->getQuestGenerator() as $idQuest => $data) {
            if ($i >= $numberOfQuestsAssigned) break;
            $dataQuestPlayer = DataQuestPlayer::fromArray($data);
            if (!$dataQuestPlayer->isCompleted()) {
                // DataQuest
                $dataQuest = DataQuest::fromArray($this->plugin->getProvider()->getConfigProvider()->getQuests()[$idQuest]);
                $replaceList = ["%quest-name%" => $dataQuest->getName(),];
                $this->addContent(new VButton(
                    text: TextFormat::colorize(str_replace(array_keys($replaceList), array_values($replaceList), $this->dataForm["button-quest"]["text"])),
                    image: str_replace(array_keys($replaceList), array_values($replaceList), $this->dataForm["button-quest"]["image-path"]),
                ), function (Player $player, mixed $data) use ($dataQuestPlayer): void {
                    $formViewQuest = new FormViewQuest($player, $dataQuestPlayer);
                    $formViewQuest->sendForm();
                });
                $i++;
            }
        }
        parent::__construct($player);
    }

    /**
     * @throws Throwable
     */
    public function onClose(Player $player): void
    {
        FormQuestManager::getInstance($player)->sendForm();
    }

}