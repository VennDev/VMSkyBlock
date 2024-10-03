<?php

declare(strict_types=1);

namespace venndev\vmskyblock\data;

use venndev\vmskyblock\api\data\IData;

final class DataQuest implements IData
{

    private ?string $pathDirectory = null;

    public function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly array $description,
        private readonly int    $event,
        private array           $requirements,
        private readonly array  $rewards,
    )
    {
        // TODO: Implement constructor
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return implode("\n", $this->description);
    }

    public function getEvent(): int
    {
        return $this->event;
    }

    public function getRequirements(): array
    {
        return $this->requirements;
    }

    public function setRequirements(array $requirements): void
    {
        $this->requirements = $requirements;
    }

    public function getRewards(): array
    {
        return $this->rewards;
    }

    public function getPathDirectory(): ?string
    {
        return $this->pathDirectory;
    }

    public function setPathDirectory(?string $pathDirectory): void
    {
        $this->pathDirectory = $pathDirectory;
    }

    public function toArray(): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "description" => $this->description,
            "event" => $this->event,
            "requirements" => $this->requirements,
            "rewards" => $this->rewards,
            "path" => $this->pathDirectory,
        ];
    }

    public static function fromArray(array $data): DataQuest
    {
        $instance = new DataQuest(
            $data["id"],
            $data["name"],
            $data["description"],
            $data["event"],
            $data["requirements"],
            $data["rewards"],
        );
        $instance->setPathDirectory($data["path"] ?? null);
        return $instance;
    }

}