<?php

declare(strict_types=1);

namespace venndev\vmskyblock\forms\player;

use Throwable;
use RuntimeException;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use venndev\vformoopapi\attributes\normal\VButton;
use venndev\vformoopapi\attributes\VForm;
use venndev\vformoopapi\Form;
use venndev\vformoopapi\utils\TypeForm;
use venndev\vmskyblock\data\ConfigPaths;
use venndev\vmskyblock\forms\FormHistoryQuest;
use venndev\vmskyblock\forms\FormPlayer;
use venndev\vmskyblock\forms\FormQuest;
use venndev\vmskyblock\forms\utils\GetStringFormPlayer;
use venndev\vmskyblock\VMSkyBlock;
use vennv\vapm\Async;

#[VForm(
    title: "Skyblock Quest Manager Form",
    type: TypeForm::NORMAL_FORM,
    content: "-",
)]
final class FormQuestManager extends Form
{

    private VMSkyBlock $plugin;
    private ?array $dataForm;

    public function __construct(Player $player)
    {
        $this->plugin = VMSkyBlock::getInstance();
        $this->dataForm = $this->plugin->getProvider()->getConfigProvider()->getForms()->getNested(ConfigPaths::FORMS_QUEST_MANAGER_FORM);
        if ($this->dataForm === null) throw new RuntimeException("Form data not found");
        $this->setTitle(TextFormat::colorize($this->dataForm["title"]));
        $this->setContent(TextFormat::colorize($this->dataForm["content"]));
        parent::__construct($player);
    }

    /**
     * @throws Throwable
     */
    #[VButton(
        text: new GetStringFormPlayer("skyblock-quest-manager-form.button-quest.text"),
        image: new GetStringFormPlayer("skyblock-quest-manager-form.button-quest.image-path"),
        label: "button-quest"
    )]
    public function buttonQuest(Player $player, mixed $data): Async
    {
        return new Async(function () use ($player): void {
            $dataIslandPlayer = Async::await($this->plugin->getManager()->getIslandDataByMember($player->getXuid()));
            if ($dataIslandPlayer === null) return;
            $formQuest = new FormQuest($player, $dataIslandPlayer);
            $formQuest->sendForm();
        });
    }

    /**
     * @throws Throwable
     */
    #[VButton(
        text: new GetStringFormPlayer("skyblock-quest-manager-form.button-history.text"),
        image: new GetStringFormPlayer("skyblock-quest-manager-form.button-history.image-path"),
        label: "button-history"
    )]
    public function buttonHistory(Player $player, mixed $data): Async
    {
        return new Async(function () use ($player): void {
            $dataIslandPlayer = Async::await($this->plugin->getManager()->getIslandDataByMember($player->getXuid()));
            if ($dataIslandPlayer === null) return;
            $listQuestsCompleted = Async::await($this->plugin->getQuestManager()->getHistoryQuests($player));
            $formHistoryQuest = new FormHistoryQuest($player, $listQuestsCompleted, $dataIslandPlayer);
            $formHistoryQuest->sendForm();
        });
    }

    /**
     * @throws Throwable
     */
    public function onClose(Player $player): void
    {
        FormPlayer::getInstance($player)->sendForm();
    }

}