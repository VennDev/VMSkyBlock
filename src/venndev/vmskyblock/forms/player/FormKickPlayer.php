<?php

declare(strict_types=1);

namespace venndev\vmskyblock\forms\player;

use Throwable;
use RuntimeException;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use venndev\vformoopapi\attributes\custom\VDropDown;
use venndev\vformoopapi\attributes\custom\VInput;
use venndev\vformoopapi\attributes\VForm;
use venndev\vformoopapi\Form;
use venndev\vformoopapi\utils\TypeForm;
use venndev\vmskyblock\data\ConfigPaths;
use venndev\vmskyblock\forms\FormPlayer;
use venndev\vmskyblock\VMSkyBlock;

#[VForm(
    title: "Skyblock Kick Player Form",
    type: TypeForm::CUSTOM_FORM,
    content: "-",
)]
final class FormKickPlayer extends Form
{

    private VMSkyBlock $plugin;
    private ?array $dataForm;

    public function __construct(Player $player, array $playersInIslandList)
    {
        $this->plugin = VMSkyBlock::getInstance();
        $this->dataForm = $this->plugin->getProvider()->getConfigProvider()->getForms()->getNested(ConfigPaths::FORMS_KICK_PLAYER_SKYBLOCK_FORM);
        if ($this->dataForm === null) throw new RuntimeException("Form data not found");
        $this->setTitle(TextFormat::colorize($this->dataForm["title"]));
        $this->setContent(TextFormat::colorize($this->dataForm["content"]));

        parent::__construct(
            player: $player,
            middleWare: function () use ($playersInIslandList) {
                $this->setIndexContent(0, new VDropDown(
                    text: TextFormat::colorize($this->dataForm["drop-down-players"]["text"]),
                    options: $playersInIslandList,
                    default: -1,
                    label: "drop-down-players"
                ));
            });
    }

    #[VDropDown(
        text: "Kick Player",
        options: [],
        default: -1,
        label: "drop-down-players"
    )]
    public function kickPlayer(Player $player, mixed $data): void
    {
        // TODO: Implement addMember() method.
    }

    public function onCompletion(Player $player, mixed $data): void
    {
        $config = VMSkyBlock::getInstance()->getProvider()->getConfigProvider()->getConfig();
        $nameCmd = $config->getNested(ConfigPaths::PLUGIN_SETTINGS_COMMAND_NAME);

        $inputName = $data["drop-down-players"];
        VMSkyBlock::getInstance()->getServer()->dispatchCommand($player, $nameCmd . " kick $inputName");
    }

    /**
     * @throws Throwable
     */
    public function onClose(Player $player): void
    {
        FormPlayer::getInstance($player)->sendForm();
    }

}