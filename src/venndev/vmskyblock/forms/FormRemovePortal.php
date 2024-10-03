<?php

declare(strict_types=1);

namespace venndev\vmskyblock\forms;

use Throwable;
use RuntimeException;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use venndev\vformoopapi\attributes\modal\VButton;
use venndev\vformoopapi\attributes\VForm;
use venndev\vformoopapi\Form;
use venndev\vformoopapi\utils\TypeForm;
use venndev\vmskyblock\data\ConfigPaths;
use venndev\vmskyblock\entity\EntityPortal;
use venndev\vmskyblock\forms\utils\GetStringFormPlayer;
use venndev\vmskyblock\manager\MessageManager;
use venndev\vmskyblock\VMSkyBlock;

#[VForm(
    title: "Remove Portal Form",
    type: TypeForm::MODAL_FORM,
    content: "-",
)]
final class FormRemovePortal extends Form
{

    private VMSkyBlock $plugin;
    private ?array $dataForm;

    public function __construct(Player $player, private readonly EntityPortal $entityPortal)
    {
        $this->plugin = VMSkyBlock::getInstance();
        $this->dataForm = $this->plugin->getProvider()->getConfigProvider()->getForms()->getNested(ConfigPaths::FORMS_REMOVE_PORTAL_FORM);
        if ($this->dataForm === null) throw new RuntimeException("Form data not found");
        $this->setTitle(TextFormat::colorize($this->dataForm["title"]));
        $this->setContent(TextFormat::colorize($this->dataForm["content"]));
        parent::__construct($player);
    }

    /**
     * @throws Throwable
     */
    #[VButton(
        text: new GetStringFormPlayer("skyblock-remove-portal-form.button-yes.text"),
    )]
    public function yes(Player $player, mixed $data): void
    {
        if (!$this->entityPortal->isFlaggedForDespawn() || $this->entityPortal->isAlive()) {
            $this->entityPortal->removePortal();
            $this->entityPortal->flagForDespawn();
        }
        $player->sendMessage(TextFormat::colorize(MessageManager::getNested(ConfigPaths::MESSAGE_PORTAL_REMOVED)));
    }

    #[VButton(
        text: new GetStringFormPlayer("skyblock-remove-portal-form.button-no.text"),
    )]
    public function no(Player $player, mixed $data): void
    {
        $player->sendMessage(TextFormat::colorize(MessageManager::getNested(ConfigPaths::MESSAGE_WAITING)));
    }

}