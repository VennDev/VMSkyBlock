<?php

declare(strict_types=1);

namespace venndev\vmskyblock\data;

use Generator;
use venndev\vmskyblock\api\data\IData;

final class DataIslandPlayer implements IData
{

    private array $banned_players = [];
    private array $quests = [];
    private string $origin_spawn = "";
    private string $origin_spawn_nether = "";
    private string $origin_spawn_end = "";

    public function __construct(
        private readonly string $island,
        private readonly string $name,
        private string          $owner,
        private readonly string $timeCreate,
        private int             $max_size,
        private float           $xp,
        private array           $members,
        private array           $items_banned,
        private bool            $pvp,
        private bool            $dropped_item,
        private bool            $pick_up_item,
        private bool            $allow_visitors,
    )
    {
        //TODO: Implement constructor
    }

    public function getIsland(): string
    {
        return $this->island;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOwner(): string
    {
        return $this->owner;
    }

    public function getTimeCreate(): string
    {
        return $this->timeCreate;
    }

    public function getMaxSize(): int
    {
        return $this->max_size;
    }

    public function getXp(): float
    {
        return $this->xp;
    }

    public function getMembers(): array
    {
        return $this->members;
    }

    public function getItemsBanned(): array
    {
        return $this->items_banned;
    }

    public function getPvp(): bool
    {
        return $this->pvp;
    }

    public function getDroppedItem(): bool
    {
        return $this->dropped_item;
    }

    public function getPickUpItem(): bool
    {
        return $this->pick_up_item;
    }

    public function getAllowVisitors(): bool
    {
        return $this->allow_visitors;
    }

    public function setOwner(string $owner): void
    {
        $this->owner = $owner;
    }

    public function setMaxSize(int $max_size): void
    {
        $this->max_size = $max_size;
    }

    public function setXp(float $xp): void
    {
        $this->xp = $xp;
    }

    public function setMembers(array $members): void
    {
        $this->members = $members;
    }

    public function setItemsBanned(array $items_banned): void
    {
        $this->items_banned = $items_banned;
    }

    public function setPvp(bool $pvp): void
    {
        $this->pvp = $pvp;
    }

    public function setDroppedItem(bool $dropped_item): void
    {
        $this->dropped_item = $dropped_item;
    }

    public function setPickUpItem(bool $pick_up_item): void
    {
        $this->pick_up_item = $pick_up_item;
    }

    public function setAllowVisitors(bool $allow_visitors): void
    {
        $this->allow_visitors = $allow_visitors;
    }

    public function getBannedPlayers(): array
    {
        return $this->banned_players;
    }

    public function setBannedPlayers(array $banned_players): void
    {
        $this->banned_players = $banned_players;
    }

    public function getQuests(): array
    {
        return $this->quests;
    }

    public function getQuestGenerator(): Generator
    {
        foreach ($this->quests as $key => $quest) {
            yield $key => $quest;
        }
    }

    public function setQuests(array $quests): void
    {
        $this->quests = $quests;
    }

    public function getOriginSpawn(): string
    {
        return $this->origin_spawn;
    }

    public function setOriginSpawn(string $origin_spawn): void
    {
        $this->origin_spawn = $origin_spawn;
    }

    public function getOriginSpawnNether(): string
    {
        return $this->origin_spawn_nether;
    }

    public function setOriginSpawnNether(string $origin_spawn_nether): void
    {
        $this->origin_spawn_nether = $origin_spawn_nether;
    }

    public function getOriginSpawnEnd(): string
    {
        return $this->origin_spawn_end;
    }

    public function setOriginSpawnEnd(string $origin_spawn_end): void
    {
        $this->origin_spawn_end = $origin_spawn_end;
    }

    public function toArray(): array
    {
        return [
            "island" => $this->island,
            "name" => $this->name,
            "owner" => $this->owner,
            "time_create" => $this->timeCreate,
            "max_size" => $this->max_size,
            "xp" => $this->xp,
            "members" => $this->members,
            "items_banned" => $this->items_banned,
            "pvp" => $this->pvp,
            "dropped_item" => $this->dropped_item,
            "pick_up_item" => $this->pick_up_item,
            "allow_visitor" => $this->allow_visitors,
            "banned_players" => $this->banned_players,
            "quests" => $this->quests,
            "origin_spawn" => $this->origin_spawn,
            "origin_spawn_nether" => $this->origin_spawn_nether,
            "origin_spawn_end" => $this->origin_spawn_end,
        ];
    }

    public static function fromArray(array $data): DataIslandPlayer
    {
        $instance = new self(
            $data["island"],
            $data["name"],
            $data["owner"],
            $data["time_create"],
            $data["max_size"],
            $data["xp"],
            $data["members"],
            $data["items_banned"],
            $data["pvp"],
            $data["dropped_item"],
            $data["pick_up_item"],
            $data["allow_visitor"],
        );
        $instance->setBannedPlayers($data["banned_players"]);
        $instance->setQuests($data["quests"]);
        $instance->setOriginSpawn($data["origin_spawn"]);
        $instance->setOriginSpawnNether($data["origin_spawn_nether"]);
        $instance->setOriginSpawnEnd($data["origin_spawn_end"]);
        return $instance;
    }

}