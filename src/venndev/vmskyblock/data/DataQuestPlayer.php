<?php

declare(strict_types=1);

namespace venndev\vmskyblock\data;

use venndev\vmskyblock\api\data\IData;

final class DataQuestPlayer implements IData
{

    private float $progress = 0.0;
    private bool $completed = false;
    private ?string $completedAt = null;
    private array $requirements = [];

    public function __construct(
        private readonly string $id,
        private readonly int    $event,
    )
    {
        // TODO: Implement constructor
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEvent(): int
    {
        return $this->event;
    }

    public function getProgress(): float
    {
        return $this->progress;
    }

    public function setProgress(float $progress): void
    {
        $this->progress = $progress;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    public function setCompleted(bool $completed): void
    {
        $this->completed = $completed;
    }

    /**
     * @return string|null
     *
     * Returns the date and time (Y-m-d H:i:s) the quest was completed.
     */
    public function getCompletedAt(): ?string
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?string $completedAt): void
    {
        $this->completedAt = $completedAt;
    }

    public function getRequirements(): array
    {
        return $this->requirements;
    }

    public function setRequirements(array $requirements): void
    {
        $this->requirements = $requirements;
    }

    public function toArray(): array
    {
        return [
            "id" => $this->id,
            "event" => $this->event,
            "progress" => $this->progress,
            "completed" => $this->completed,
            "completedAt" => $this->completedAt,
            "requirements" => $this->requirements,
        ];
    }

    public static function fromArray(array $data): DataQuestPlayer
    {
        $questPlayer = new DataQuestPlayer(
            id: $data["id"],
            event: $data["event"],
        );
        $questPlayer->setProgress($data["progress"]);
        $questPlayer->setCompleted($data["completed"]);
        $questPlayer->setCompletedAt($data["completedAt"]);
        $questPlayer->setRequirements($data["requirements"]);
        return $questPlayer;
    }

}