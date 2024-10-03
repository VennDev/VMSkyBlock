<?php

declare(strict_types=1);

namespace venndev\vmskyblock\forms\player;

use Throwable;
use RuntimeException;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use venndev\vformoopapi\attributes\modal\VButton;
use venndev\vformoopapi\attributes\VForm;
use venndev\vformoopapi\Form;
use venndev\vformoopapi\utils\TypeForm;
use venndev\vmskyblock\data\ConfigPaths;
use venndev\vmskyblock\data\DataIslandPlayer;
use venndev\vmskyblock\forms\FormPlayer;
use venndev\vmskyblock\forms\utils\GetStringFormPlayer;
use venndev\vmskyblock\VMSkyBlock;

#[VForm(
    title: "Skyblock Delete Island Form",
    type: TypeForm::MODAL_FORM,
    content: "-",
)]
final class FormDelete extends Form
{

    private VMSkyBlock $plugin;
    private ?array $dataForm;

    public function __construct(Player $player, private readonly DataIslandPlayer $dataIslandPlayer)
    {
        $this->plugin = VMSkyBlock::getInstance();
        $this->dataForm = $this->plugin->getProvider()->getConfigProvider()->getForms()->getNested(ConfigPaths::FORMS_DELETE_ISLAND_SKYBLOCK_FORM);
        if ($this->dataForm === null) throw new RuntimeException("Form data not found");
        $this->setTitle(TextFormat::colorize($this->dataForm["title"]));
        $this->setContent(TextFormat::colorize($this->dataForm["content"]));
        parent::__construct($player);
    }

    /**
     * @throws Throwable
     */
    #[VButton(
        text: new GetStringFormPlayer("skyblock-delete-island-form.button-yes.text"),
    )]
    public function yesButton(Player $player): void
    {
        $islandDataOwner = $this->plugin->getManager()->getIslandDataByOwner($player);
        if ($islandDataOwner === null) {
            $player->sendMessage(TextFormat::colorize($this->plugin->getProvider()->getConfigProvider()->getMessages()->getNested(ConfigPaths::MESSAGE_NO_ISLAND)));
            return;
        }
        if ($this->dataIslandPlayer->getName() === DataIslandPlayer::fromArray($islandDataOwner)->getName()) {
            $this->plugin->getManager()->delete($player);
            $player->sendMessage(TextFormat::colorize($this->plugin->getProvider()->getConfigProvider()->getMessages()->getNested(ConfigPaths::MESSAGE_ISLAND_DELETED)));
        } else {
            $player->sendMessage(TextFormat::colorize($this->plugin->getProvider()->getConfigProvider()->getMessages()->getNested(ConfigPaths::MESSAGE_IS_NOT_AN_OWNER_OF_THE_ISLAND)));
        }
    }

    /**
     * @throws Throwable
     */
    #[VButton(
        text: new GetStringFormPlayer("skyblock-delete-island-form.button-no.text"),
    )]
    public function noButton(Player $player): void
    {
        FormPlayer::getInstance($player)->sendForm();
    }

    /**
     * @throws Throwable
     */
    public function onClose(Player $player): void
    {
        FormPlayer::getInstance($player)->sendForm();
    }

}