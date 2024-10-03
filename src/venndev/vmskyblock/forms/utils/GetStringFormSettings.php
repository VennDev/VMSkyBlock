<?php

declare(strict_types=1);

namespace venndev\vmskyblock\forms\utils;

use Override;
use pocketmine\utils\TextFormat;
use RuntimeException;
use venndev\vformoopapi\results\VResultString;
use venndev\vmskyblock\data\ConfigPaths;
use venndev\vmskyblock\VMSkyBlock;

final class GetStringFormSettings extends VResultString
{

    public function __construct(string $input)
    {
        parent::__construct($input);
    }

    #[Override] public function getResult(): string
    {
        $dataForm = VMSkyBlock::getInstance()->getProvider()->getConfigProvider()->getForms()->getNested(ConfigPaths::FORMS_SETTINGS_SKYBLOCK_FORM);
        if ($dataForm === null) throw new RuntimeException("Form data not found");
        $result = "";
        foreach (explode(".", $this->getInput()) as $key) {
            if (isset($dataForm[$key])) {
                $result = $dataForm[$key];
                $dataForm = $dataForm[$key];
            }
        }
        return TextFormat::colorize($result);
    }

}