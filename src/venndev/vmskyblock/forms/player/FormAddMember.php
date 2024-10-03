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
use venndev\vmskyblock\forms\FormPlayer;
use venndev\vmskyblock\VMSkyBlock;

#[VForm(
    title: "Skyblock Add Member Form",
    type: TypeForm::CUSTOM_FORM,
    content: "-",
)]
final class FormAddMember extends Form
{

    private VMSkyBlock $plugin;
    private ?array $dataForm;

    public function __construct(Player $player)
    {
        $this->plugin = VMSkyBlock::getInstance();
        $this->dataForm = $this->plugin->getProvider()->getConfigProvider()->getForms()->getNested(ConfigPaths::FORMS_ADD_MEMBER_SKYBLOCK_FORM);
        if ($this->dataForm === null) throw new RuntimeException("Form data not found");
        $this->setTitle(TextFormat::colorize($this->dataForm["title"]));
        $this->setContent(TextFormat::colorize($this->dataForm["content"]));

        parent::__construct(
            player: $player,
            middleWare: function () {
                $this->setIndexContent(0, new VInput(
                    text: TextFormat::colorize($this->dataForm["input-player-name"]["text"]),
                    placeholder: TextFormat::colorize($this->dataForm["input-player-name"]["place-holder"]),
                    default: "",
                    label: "input-player-name"
                ));
            });
    }

    #[VInput(
        text: "Add member",
        placeholder: "Enter player name",
        default: "",
        label: "input-player-name"
    )]
    public function addMember(Player $player, mixed $data): void
    {
        // TODO: Implement addMember() method.
    }

    public function onCompletion(Player $player, mixed $data): void
    {
        $config = VMSkyBlock::getInstance()->getProvider()->getConfigProvider()->getConfig();
        $nameCmd = $config->getNested(ConfigPaths::PLUGIN_SETTINGS_COMMAND_NAME);

        $inputName = $data["input-player-name"];
        VMSkyBlock::getInstance()->getServer()->dispatchCommand($player, $nameCmd . " add $inputName");
    }

    /**
     * @throws Throwable
     */
    public function onClose(Player $player): void
    {
        FormPlayer::getInstance($player)->sendForm();
    }

}