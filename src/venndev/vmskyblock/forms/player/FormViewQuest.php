<?php

declare(strict_types=1);

namespace venndev\vmskyblock\forms\player;

use Throwable;
use RuntimeException;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use venndev\vformoopapi\attributes\custom\VLabel;
use venndev\vformoopapi\attributes\VForm;
use venndev\vformoopapi\Form;
use venndev\vformoopapi\utils\TypeForm;
use venndev\vmskyblock\data\ConfigPaths;
use venndev\vmskyblock\data\DataQuest;
use venndev\vmskyblock\data\DataQuestPlayer;
use venndev\vmskyblock\forms\FormQuest;
use venndev\vmskyblock\VMSkyBlock;
use vennv\vapm\Async;

#[VForm(
    title: "Skyblock View Quest Form",
    type: TypeForm::CUSTOM_FORM,
    content: "-",
)]
final class FormViewQuest extends Form
{

    private VMSkyBlock $plugin;
    private ?array $dataForm;

    public function __construct(Player $player, DataQuestPlayer $dataQuestPlayer)
    {
        $this->plugin = VMSkyBlock::getInstance();
        $this->dataForm = $this->plugin->getProvider()->getConfigProvider()->getForms()->getNested(ConfigPaths::FORMS_VIEW_QUEST_SKYBLOCK_FORM);
        if ($this->dataForm === null) throw new RuntimeException("Form data not found");
        $this->setTitle(TextFormat::colorize($this->dataForm["title"]));

        $dataQuest = DataQuest::fromArray($this->plugin->getProvider()->getConfigProvider()->getQuests()[$dataQuestPlayer->getId()]);

        $replaceList = [
            "%quest-name%" => $dataQuest->getName(),
            "%quest-description%" => $dataQuest->getDescription(),
            "%quest-progress%" => $dataQuestPlayer->getProgress(),
        ];

        $contentForm = $this->dataForm["content"];
        if (!is_array($contentForm)) throw new RuntimeException("Content form must be an array");
        foreach ($contentForm as $case => $data) {
            $contentForm[$case] = TextFormat::colorize(str_replace(array_keys($replaceList), array_values($replaceList), $data));
        }
        $contentForm = implode("\n", $contentForm);
        $this->addContent(new VLabel(
            text: $contentForm,
            label: "label-quest",
        ), fn() => null);
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