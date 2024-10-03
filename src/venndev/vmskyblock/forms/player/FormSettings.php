<?php

declare(strict_types=1);

namespace venndev\vmskyblock\forms\player;

use Throwable;
use RuntimeException;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use venndev\vformoopapi\attributes\custom\VToggle;
use venndev\vformoopapi\attributes\VForm;
use venndev\vformoopapi\Form;
use venndev\vformoopapi\utils\TypeForm;
use venndev\vmskyblock\data\ConfigPaths;
use venndev\vmskyblock\data\DataIslandPlayer;
use venndev\vmskyblock\data\Permissions;
use venndev\vmskyblock\forms\FormPlayer;
use venndev\vmskyblock\forms\utils\GetStringFormSettings;
use venndev\vmskyblock\manager\MessageManager;
use venndev\vmskyblock\VMSkyBlock;

#[VForm(
    title: "Skyblock Settings Form",
    type: TypeForm::CUSTOM_FORM,
    content: "-",
)]
final class FormSettings extends Form
{

    private VMSkyBlock $plugin;
    private ?array $dataForm;

    public function __construct(Player $player, private readonly DataIslandPlayer $dataIslandPlayer)
    {
        $this->plugin = VMSkyBlock::getInstance();
        $this->dataForm = $this->plugin->getProvider()->getConfigProvider()->getForms()->getNested(ConfigPaths::FORMS_SETTINGS_SKYBLOCK_FORM);
        if ($this->dataForm === null) throw new RuntimeException("Form data not found");
        $this->setTitle(TextFormat::colorize($this->dataForm["title"]));
        $this->setContent(TextFormat::colorize($this->dataForm["content"]));

        parent::__construct(
            player: $player,
            middleWare: function (): void {
                $lists = [
                    "toggle-pvp" => $this->dataIslandPlayer->getPvp(),
                    "toggle-allow-visitors" => $this->dataIslandPlayer->getAllowVisitors(),
                    "toggle-drop-items" => $this->dataIslandPlayer->getDroppedItem(),
                    "toggle-pickup-items" => $this->dataIslandPlayer->getPickupItem()
                ];
                $i = 0;
                foreach ($lists as $name => $data) {
                    $this->setIndexContent($i, new VToggle(
                        text: TextFormat::colorize($this->dataForm[$name]["text"]),
                        default: $data,
                        label: $name
                    ));
                    $i++;
                }
            });
    }

    #[VToggle(
        text: new GetStringFormSettings("toggle-pvp.text"),
        default: false,
        label: "toggle-pvp"
    )]
    public function pvp(Player $player, mixed $data): void
    {
        // TODO: Implement pvp() method.
    }

    #[VToggle(
        text: new GetStringFormSettings("toggle-allow-visitors.text"),
        default: false,
        label: "toggle-allow-visitors"
    )]
    public function allowVisitors(Player $player, mixed $data): void
    {
        // TODO: Implement allowVisitors() method.
    }

    #[VToggle(
        text: new GetStringFormSettings("toggle-drop-items.text"),
        default: false,
        label: "toggle-drop-items"
    )]
    public function dropItems(Player $player, mixed $data): void
    {
        // TODO: Implement dropItems() method.
    }

    #[VToggle(
        text: new GetStringFormSettings("toggle-pickup-items.text"),
        default: false,
        label: "toggle-pickup-items"
    )]
    public function pickupItems(Player $player, mixed $data): void
    {
        // TODO: Implement pickupItems() method.
    }

    public function onCompletion(Player $player, mixed $data): void
    {
        $plugin = VMSkyBlock::getInstance();
        $pvp = $data["toggle-pvp"];
        $allowVisitors = $data["toggle-allow-visitors"];
        $dropItems = $data["toggle-drop-items"];
        $pickupItems = $data["toggle-pickup-items"];

        $nameIsland = $this->dataIslandPlayer->getName();

        if ($plugin->getManager()->hasPermissionMemberIsland($player, $nameIsland, Permissions::ISLAND_SET_PVP)) {
            $this->dataIslandPlayer->setPvp($pvp);
        }

        if ($plugin->getManager()->hasPermissionMemberIsland($player, $nameIsland, Permissions::ISLAND_SET_ALLOW_VISITOR)) {
            $this->dataIslandPlayer->setAllowVisitors($allowVisitors);
        }

        if ($plugin->getManager()->hasPermissionMemberIsland($player, $nameIsland, Permissions::ISLAND_SET_ALLOW_DROP_ITEM)) {
            $this->dataIslandPlayer->setDroppedItem($dropItems);
        }

        if ($plugin->getManager()->hasPermissionMemberIsland($player, $nameIsland, Permissions::ISLAND_SET_ALLOW_PICK_UP_ITEM)) {
            $this->dataIslandPlayer->setPickupItem($pickupItems);
        }

        $this->plugin->getManager()->setDataIsland($this->dataIslandPlayer->getName(), $this->dataIslandPlayer->toArray());
        $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_SETTINGS_ISLAND_UPDATED));
    }

    /**
     * @throws Throwable
     */
    public function onClose(Player $player): void
    {
        FormPlayer::getInstance($player)->sendForm();
    }

}