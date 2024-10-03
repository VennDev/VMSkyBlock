<?php

declare(strict_types=1);

namespace venndev\vmskyblock\forms\player;

use Throwable;
use RuntimeException;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use venndev\vformoopapi\attributes\custom\VInput;
use venndev\vformoopapi\attributes\VForm;
use venndev\vformoopapi\Form;
use venndev\vformoopapi\utils\TypeForm;
use venndev\vmskyblock\data\ConfigPaths;
use venndev\vmskyblock\data\DataIslandPlayer;
use venndev\vmskyblock\forms\FormPlayer;
use venndev\vmskyblock\manager\MessageManager;
use venndev\vmskyblock\VMSkyBlock;

#[VForm(
    title: "Skyblock Set Name Form",
    type: TypeForm::CUSTOM_FORM,
    content: "-",
)]
final class FormSetName extends Form
{

    private VMSkyBlock $plugin;
    private ?array $dataForm;

    public function __construct(Player $player, private readonly DataIslandPlayer $dataIslandPlayer)
    {
        $this->plugin = VMSkyBlock::getInstance();
        $this->dataForm = $this->plugin->getProvider()->getConfigProvider()->getForms()->getNested(ConfigPaths::FORMS_SET_NAME_SKYBLOCK_FORM);
        if ($this->dataForm === null) throw new RuntimeException("Form data not found");
        $this->setTitle(TextFormat::colorize($this->dataForm["title"]));
        $this->setContent(TextFormat::colorize($this->dataForm["content"]));

        parent::__construct(
            player: $player,
            middleWare: function () {
                $this->setIndexContent(0, new VInput(
                    text: TextFormat::colorize($this->dataForm["input-name"]["text"]),
                    placeholder: TextFormat::colorize($this->dataForm["input-name"]["place-holder"]),
                    default: $this->dataIslandPlayer->getName(),
                    label: "input-name"
                ));
            });
    }

    #[VInput(
        text: "Set name",
        placeholder: "Enter name",
        default: "",
        label: "input-name"
    )]
    public function setName(Player $player, mixed $data): void
    {
        // TODO: Implement setName() method.
    }

    public function onCompletion(Player $player, mixed $data): void
    {
        $inputName = $data["input-name"];
        VMSkyBlock::getInstance()->getManager()->setNamedIsland($player, $inputName);
        $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_NAME_HAS_BEEN_CHANGED));
    }

    /**
     * @throws Throwable
     */
    public function onClose(Player $player): void
    {
        FormPlayer::getInstance($player)->sendForm();
    }

}