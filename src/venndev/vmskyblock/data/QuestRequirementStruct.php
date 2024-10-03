<?php

declare(strict_types=1);

namespace venndev\vmskyblock\data;

use venndev\vmskyblock\api\data\IData;

final class QuestRequirementStruct implements IData
{

    private ?string $item = null;

    public function __construct(
        private int   $type,
        private float $amount,
    )
    {
        // TODO: Implement constructor
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setType(int $type): void
    {
        $this->type = $type;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    public function getItem(): ?string
    {
        return $this->item;
    }

    public function setItem(?string $item): void
    {
        $this->item = $item;
    }

    public function toArray(): array
    {
        return [
            "type" => $this->type,
            "amount" => $this->amount,
            "item" => $this->item,
        ];
    }

    public static function fromArray(array $data): QuestRequirementStruct
    {
        $instance = new QuestRequirementStruct(
            $data["type"],
            $data["amount"],
        );
        $instance->setItem($data["item"]);
        return $instance;
    }

}