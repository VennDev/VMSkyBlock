<?php

declare(strict_types=1);

namespace venndev\vmskyblock\quest;

final class QuestEventType
{

    public const PLAYER_JOIN = 0;
    public const PLAYER_LEAVE = 1;
    public const PLAYER_DEATH = 2;
    public const PLAYER_KILL = 3;
    public const PLAYER_BREAK_BLOCK = 4;
    public const PLAYER_PLACE_BLOCK = 5;
    public const PLAYER_INTERACT_BLOCK = 6;
    public const PLAYER_CRAFT_ITEM = 7;
    public const PLAYER_ENCHANT_ITEM = 8;
    public const PLAYER_EAT_FOOD = 9;
    public const PLAYER_THROW_ITEM = 10;
    public const PLAYER_HOLD_ITEM = 11;

}