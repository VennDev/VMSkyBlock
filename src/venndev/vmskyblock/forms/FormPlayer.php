<?php

declare(strict_types=1);

namespace venndev\vmskyblock\forms;

use Throwable;
use RuntimeException;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use venndev\vapmdatabase\database\ResultQuery;
use venndev\vformoopapi\attributes\custom\VDropDown;
use venndev\vformoopapi\attributes\custom\VInput;
use venndev\vformoopapi\attributes\custom\VLabel;
use venndev\vformoopapi\attributes\normal\VButton;
use venndev\vformoopapi\attributes\VForm;
use venndev\vformoopapi\Form;
use venndev\vformoopapi\FormSample;
use venndev\vformoopapi\utils\TypeForm;
use venndev\vmskyblock\data\ConfigPaths;
use venndev\vmskyblock\data\DataIslandPlayer;
use venndev\vmskyblock\forms\player\FormAddMember;
use venndev\vmskyblock\forms\player\FormBanPlayer;
use venndev\vmskyblock\forms\player\FormDelete;
use venndev\vmskyblock\forms\player\FormKickPlayer;
use venndev\vmskyblock\forms\player\FormQuestManager;
use venndev\vmskyblock\forms\player\FormRemoveMember;
use venndev\vmskyblock\forms\player\FormSetName;
use venndev\vmskyblock\forms\player\FormSettings;
use venndev\vmskyblock\forms\player\FormUnbanPlayer;
use venndev\vmskyblock\forms\utils\GetStringFormPlayer;
use venndev\vmskyblock\manager\MessageManager;
use venndev\vmskyblock\VMSkyBlock;
use venndev\vplayerdatasaver\VPlayerDataSaver;
use vennv\vapm\Async;
use vennv\vapm\FiberManager;

#[VForm(
    title: "Skyblock Form",
    type: TypeForm::NORMAL_FORM,
    content: "-",
)]
final class FormPlayer extends Form
{

    private VMSkyBlock $plugin;
    private ?array $dataForm;

    public function __construct(Player $player)
    {
        $this->plugin = VMSkyBlock::getInstance();
        $this->dataForm = $this->plugin->getProvider()->getConfigProvider()->getForms()->getNested(ConfigPaths::FORMS_SKYBLOCK_FORM);
        if ($this->dataForm === null) throw new RuntimeException("Form data not found");
        $this->setTitle(TextFormat::colorize($this->dataForm["title"]));
        $this->setContent(TextFormat::colorize($this->dataForm["content"]));
        parent::__construct($player);
    }

    /**
     * @throws Throwable
     */
    #[VButton(
        text: new GetStringFormPlayer("button-join.text"),
        image: new GetStringFormPlayer("button-join.image-path"),
        label: "button-join"
    )]
    public function join(Player $player, mixed $data): void
    {
        $this->plugin->getManager()->join($player);
    }

    /**
     * @throws Throwable
     */
    #[VButton(
        text: new GetStringFormPlayer("button-visit.text"),
        image: new GetStringFormPlayer("button-visit.image-path"),
        label: "button-visit"
    )]
    public function visit(Player $player, mixed $data): Async
    {
        return new Async(function () use ($player) {
            $dataFormVisit = $this->dataForm["child-forms"]["visit-form"] ?? null;
            if ($dataFormVisit === null) throw new RuntimeException("Form data not found");
            $formVisit = new FormSample($player);
            $formVisit->setType(TypeForm::CUSTOM_FORM);
            $formVisit->setTitle(TextFormat::colorize($dataFormVisit["title"]));
            $formVisit->setContent(TextFormat::colorize($dataFormVisit["content"]));
            $playersList = [];
            foreach ($this->plugin->getServer()->getOnlinePlayers() as $playerOnline) {
                if ($playerOnline->getXuid() === $player->getXuid()) continue;
                $playersList[] = $playerOnline->getName();
                FiberManager::wait();
            }
            $formVisit->addContent(new VDropDown(
                text: TextFormat::colorize($dataFormVisit["drop-down-islands"]["text"]),
                options: $playersList,
                default: -1,
                label: "drop-down-islands"
            ), function (Player $player, mixed $data): Async {
                return new Async(function () use ($player, $data) {
                    if ($data !== null) {
                        $playerVisit = $this->plugin->getServer()->getPlayerExact($data);
                        if ($playerVisit === null) return;
                        $nameIsland = Async::await($this->plugin->getManager()->getNameIslandByPlayer($playerVisit));
                        if ($nameIsland !== null) $this->plugin->getManager()->visit($player, $nameIsland);
                    }
                });
            });
            $formVisit->setFormClose(function (Player $player): void {
                $this->sendForm(); // Reopen form
            });
            $formVisit->sendForm();
        });
    }

    /**
     * @throws Throwable
     */
    #[VButton(
        text: new GetStringFormPlayer("button-top.text"),
        image: new GetStringFormPlayer("button-top.image-path"),
        label: "button-top"
    )]
    public function top(Player $player, mixed $data): Async
    {
        return new Async(function () use ($player) {
            $dataFormTop = $this->dataForm["child-forms"]["top-form"] ?? null;
            if ($dataFormTop === null) throw new RuntimeException("Form data not found");
            $formTop = new FormSample($player);
            $formTop->setType(TypeForm::CUSTOM_FORM);
            $formTop->setTitle(TextFormat::colorize($dataFormTop["title"]));
            $formTop->setContent(TextFormat::colorize($dataFormTop["content"]));
            $formTop->addContent(new VInput(
                text: TextFormat::colorize($dataFormTop["input-limit"]["text"]),
                placeholder: TextFormat::colorize($dataFormTop["input-limit"]["place-holder"]),
                default: $dataFormTop["input-limit"]["default"],
                label: "input-limit"
            ), fn() => null);
            $formTop->setFormSubmit(function (Player $player, mixed $data): void {
                new Async(function () use ($player, $data) {
                    if ($data !== null) {
                        $dataInput = $data["input-limit"] ?? null;
                        if (!is_numeric($dataInput)) {
                            $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_MUST_IS_NUMBER));
                            return;
                        }
                        $limit = (int)$dataInput;
                        $top = Async::await($this->plugin->getManager()->getTopIslands($limit));
                        $message = MessageManager::getNested(ConfigPaths::MESSAGE_TOP_ISLANDS, [
                            "%limit%" => $limit
                        ]);
                        foreach ($top as $index => $island) {
                            $message .= "\n" . MessageManager::getNested(ConfigPaths::MESSAGE_TOP_ISLANDS_FORMAT, [
                                    "%rank%" => $index + 1,
                                    "%owner%" => $island["owner"],
                                    "%level%" => $island["level"],
                                    "%xp%" => $island["xp"],
                                ]);
                        }
                        $player->sendMessage($message);
                    }
                });
            });
            $formTop->sendForm();
        });
    }

    /**
     * @throws Throwable
     */
    #[VButton(
        text: new GetStringFormPlayer("button-manager.text"),
        image: new GetStringFormPlayer("button-manager.image-path"),
        label: "button-manager"
    )]
    public function manager(Player $player, mixed $data): void
    {
        $dataFormManager = $this->dataForm["child-forms"]["manager-form"] ?? null;
        if ($dataFormManager === null) throw new RuntimeException("Form data not found");

        $formManager = new FormSample($player);
        $formManager->setType(TypeForm::NORMAL_FORM);
        $formManager->setTitle(TextFormat::colorize($dataFormManager["title"]));
        $formManager->setContent(TextFormat::colorize($dataFormManager["content"]));

        $config = VMSkyBlock::getInstance()->getProvider()->getConfigProvider()->getConfig();
        $nameCmd = $config->getNested(ConfigPaths::PLUGIN_SETTINGS_COMMAND_NAME);

        $listNameButtons = [
            "button-settings" => function (Player $player, mixed $data): Async {
                return new Async(function () use ($player) {
                    /** @var DataIslandPlayer|null $dataIsland */
                    $dataIsland = Async::await($this->plugin->getManager()->getIslandDataByMember($player->getXuid()));
                    if ($dataIsland === null) return;
                    $form = new FormSettings($player, $dataIsland);
                    $form->sendForm();
                });
            },
            "button-set-name" => function (Player $player, mixed $data): Async {
                return new Async(function () use ($player) {
                    /** @var DataIslandPlayer|null $dataIsland */
                    $dataIsland = Async::await($this->plugin->getManager()->getIslandDataByMember($player->getXuid()));
                    if ($dataIsland === null) return;
                    $form = new FormSetName($player, $dataIsland);
                    $form->sendForm();
                });
            },
            "button-set-spawn" => function (Player $player, mixed $data) use ($nameCmd): void {
                VMSkyBlock::getInstance()->getServer()->dispatchCommand($player, $nameCmd . " setspawn");
            },
            "button-add-member" => function (Player $player, mixed $data): void {
                FormAddMember::getInstance($player)->sendForm();
            },
            "button-remove-member" => function (Player $player, mixed $data): Async {
                return new Async(function () use ($player) {
                    /** @var DataIslandPlayer|null $dataIsland */
                    $dataIsland = Async::await($this->plugin->getManager()->getIslandDataByMember($player->getXuid()));
                    if ($dataIsland === null) return;

                    $listPlayers = $dataIsland->getBannedPlayers();
                    foreach ($listPlayers as $case => $xuid) {
                        $player = Async::await(Async::await(VPlayerDataSaver::getDataByXuid($xuid)));
                        if ($player instanceof ResultQuery) $player = $player->getResult();
                        $listPlayers[$case] = $player["name"];
                        FiberManager::wait();
                    }

                    $form = new FormRemoveMember($player, $listPlayers);
                    $form->sendForm();
                });
            },
            "button-members" => function (Player $player, mixed $data) use ($nameCmd): void {
                VMSkyBlock::getInstance()->getServer()->dispatchCommand($player, $nameCmd . " members");
            },
            "button-ban-item" => function (Player $player, mixed $data) use ($nameCmd): void {
                VMSkyBlock::getInstance()->getServer()->dispatchCommand($player, $nameCmd . " ban-item");
            },
            "button-unban-item" => function (Player $player, mixed $data) use ($nameCmd): void {
                VMSkyBlock::getInstance()->getServer()->dispatchCommand($player, $nameCmd . " unban-item");
            },
            "button-ban" => function (Player $player, mixed $data): void {
                FormBanPlayer::getInstance($player)->sendForm();
            },
            "button-unban" => function (Player $player, mixed $data): Async {
                return new Async(function () use ($player) {
                    /** @var DataIslandPlayer|null $dataIsland */
                    $dataIsland = Async::await($this->plugin->getManager()->getIslandDataByMember($player->getXuid()));
                    if ($dataIsland === null) return;

                    $listPlayersBanned = $dataIsland->getBannedPlayers();
                    foreach ($listPlayersBanned as $case => $xuid) {
                        $playerBanned = Async::await(Async::await(VPlayerDataSaver::getDataByXuid($xuid)));
                        if ($playerBanned instanceof ResultQuery) $playerBanned = $playerBanned->getResult();
                        $listPlayersBanned[$case] = $playerBanned["name"];
                        FiberManager::wait();
                    }

                    $form = new FormUnbanPlayer($player, $listPlayersBanned);
                    $form->sendForm();
                });
            },
            "button-kick" => function (Player $player, mixed $data): Async {
                return new Async(function () use ($player) {
                    /** @var DataIslandPlayer|null $dataIsland */
                    $dataIsland = Async::await($this->plugin->getManager()->getIslandDataByMember($player->getXuid()));
                    if ($dataIsland === null) return;

                    $world = $player->getWorld();
                    if ($world->getFolderName() !== $dataIsland->getIsland()) {
                        $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_PLEASE_STAND_ON_YOUR_ISLAND));
                        return;
                    }

                    $listPlayers = $world->getPlayers();
                    foreach ($listPlayers as $case => $player) {
                        $listPlayers[$case] = $player->getName();
                        FiberManager::wait();
                    }

                    $form = new FormKickPlayer($player, $listPlayers);
                    $form->sendForm();
                });
            },
            "button-delete" => function (Player $player, mixed $data): Async {
                return new Async(function () use ($player) {
                    /** @var DataIslandPlayer|null $dataIsland */
                    $dataIsland = Async::await($this->plugin->getManager()->getIslandDataByMember($player->getXuid()));
                    if ($dataIsland === null) return;

                    if ($player->getWorld()->getFolderName() === $dataIsland->getIsland()) {
                        $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_PLEASE_DONT_STAND_ON_YOUR_ISLAND));
                        return;
                    }

                    $form = new FormDelete($player, $dataIsland);
                    $form->sendForm();
                });
            },
        ];

        foreach ($listNameButtons as $nameButton => $callback) {
            $dataButton = $dataFormManager[$nameButton] ?? null;
            if ($dataButton === null) throw new RuntimeException("Form data not found");
            $formManager->addContent(new VButton(
                text: TextFormat::colorize($dataButton["text"]),
                image: TextFormat::colorize($dataButton["image-path"]),
                label: $nameButton
            ), $callback);
        }

        $formManager->setFormClose(function (Player $player): void {
            $this->sendForm(); // Reopen form
        });

        $formManager->sendForm();
    }

    /**
     * @throws Throwable
     */
    #[VButton(
        text: new GetStringFormPlayer("button-info.text"),
        image: new GetStringFormPlayer("button-info.image-path"),
        label: "button-info"
    )]
    public function info(Player $player, mixed $data): Async
    {
        $player->sendMessage(MessageManager::getNested(ConfigPaths::MESSAGE_WAITING));
        return new Async(function () use ($player) {
            $dataFormInfo = $this->dataForm["child-forms"]["info-form"] ?? null;
            if ($dataFormInfo === null) throw new RuntimeException("Form data not found");
            $dataIsland = Async::await($this->plugin->getManager()->getIslandDataByMember($player->getXuid()));
            if ($dataIsland === null) return;
            $formInfo = new FormSample($player);
            $formInfo->setType(TypeForm::CUSTOM_FORM);
            $formInfo->setTitle(TextFormat::colorize($dataFormInfo["title"]));

            $statusContent = VMSkyBlock::getInstance()->getProvider()->getConfigProvider()->getForms()->getNested(ConfigPaths::FORMS_STATUS);
            $enabled = TextFormat::colorize($statusContent["enabled"]);
            $disabled = TextFormat::colorize($statusContent["disabled"]);

            $members = $dataIsland->getMembers();
            foreach ($members as $case => $xuid) {
                $playerMember = Async::await(Async::await(VPlayerDataSaver::getDataByXuid($xuid)));
                if ($playerMember instanceof ResultQuery) $playerMember = $playerMember->getResult();
                $members[$case] = $playerMember["name"];
                FiberManager::wait();
            }

            $owner = Async::await(Async::await(VPlayerDataSaver::getDataByXuid($dataIsland->getOwner())));
            if ($owner instanceof ResultQuery) $owner = $owner->getResult();
            $owner = $owner["name"] ?? "Unknown";

            $nameIsland = $dataIsland->getName();
            $replaceList = [
                "%island-name%" => $nameIsland,
                "%island-owner%" => $owner,
                "%island-size%" => $dataIsland->getMaxSize(),
                "%island-level%" => $this->plugin->getManager()->getLevelIsland($nameIsland),
                "%island-members%" => implode(", ", $members),
                "%island-xp%" => $dataIsland->getXp(),
                "%island-pvp%" => $dataIsland->getPvp() ? $enabled : $disabled,
                "%island-drop-items%" => $dataIsland->getDroppedItem() ? $enabled : $disabled,
                "%island-pickup-items%" => $dataIsland->getPickUpItem() ? $enabled : $disabled,
                "%island-allow-visitors%" => $dataIsland->getAllowVisitors() ? $enabled : $disabled,
                "%island-created%" => $dataIsland->getTimeCreate(),
            ];

            $contentForm = $dataFormInfo["content"];
            if (!is_array($contentForm)) throw new RuntimeException("Content form must be an array");

            $content = [];
            foreach ($contentForm as $value) {
                $content[] = str_replace(array_keys($replaceList), array_values($replaceList), TextFormat::colorize($value));
            }

            $formInfo->addContent(new VLabel(
                text: implode("\n", $content),
                label: "label-info"
            ), fn() => null);
            $formInfo->setFormClose(function (Player $player): void {
                $this->sendForm(); // Reopen form
            });
            $formInfo->sendForm();
        });
    }

    /**
     * @throws Throwable
     */
    #[VButton(
        text: new GetStringFormPlayer("button-quest.text"),
        image: new GetStringFormPlayer("button-quest.image-path"),
        label: "button-quest"
    )]
    public function questManager(Player $player, mixed $data): void
    {
        FormQuestManager::getInstance($player)->sendForm();
    }

}