<?php

declare(strict_types=1);

namespace venndev\vmskyblock\data;

final class Permissions
{

    // Commands
    public const COMMAND = "vmskyblock.command";
    public const COMMAND_JOIN = "vmskyblock.command.join";
    public const COMMAND_CREATE = "vmskyblock.command.create";
    public const COMMAND_ADD = "vmskyblock.command.add";
    public const COMMAND_REMOVE = "vmskyblock.command.remove";
    public const COMMAND_DELETE = "vmskyblock.command.delete";
    public const COMMAND_VISIT = "vmskyblock.command.visit";
    public const COMMAND_MEMBERS = "vmskyblock.command.members";
    public const COMMAND_ABOUT = "vmskyblock.command.about";
    public const COMMAND_BAN = "vmskyblock.command.ban";
    public const COMMAND_UNBAN = "vmskyblock.command.unban";
    public const COMMAND_KICK = "vmskyblock.command.kick";
    public const COMMAND_SETTINGS = "vmskyblock.command.settings";
    public const COMMAND_SET_PVP = "vmskyblock.command.set.pvp";
    public const COMMAND_SET_ALLOW_VISITOR = "vmskyblock.command.set.allow.visitor";
    public const COMMAND_SET_ALLOW_DROP_ITEM = "vmskyblock.command.set.allow.drop.item";
    public const COMMAND_SET_ALLOW_PICK_UP_ITEM = "vmskyblock.command.set.allow.pick.up.item";
    public const COMMAND_SET_SIZE_ISLAND = "vmskyblock.command.set.size.island";
    public const COMMAND_BAN_ITEM = "vmskyblock.command.ban.item";
    public const COMMAND_UNBAN_ITEM = "vmskyblock.command.unban.item";
    public const COMMAND_SET_SPAWN = "vmskyblock.command.set.spawn";
    public const COMMAND_TOP = "vmskyblock.command.top";
    public const COMMAND_SET_NAME = "vmskyblock.command.set.name";
    public const COMMAND_FORM = "vmskyblock.command.form";
    public const COMMAND_GIVE_ITEM_PORTAL = "vmskyblock.command.give.item.portal";

    // Events
    public const BREAK_BLOCK = "vmskyblock.break.block";
    public const PLACE_BLOCK = "vmskyblock.place.block";
    public const INTERACT_BLOCK = "vmskyblock.interact.block";
    public const DROP_ITEM = "vmskyblock.drop.item";
    public const PICK_UP_ITEM = "vmskyblock.pick.up.item";
    public const USE_ITEM = "vmskyblock.use.item";
    public const PVP = "vmskyblock.pvp";

    // Islands
    public const ISLAND_BAN = "vmskyblock.island.ban";
    public const ISLAND_UNBAN = "vmskyblock.island.unban";
    public const ISLAND_KICK = "vmskyblock.island.kick";
    public const ISLAND_BAN_ITEM = "vmskyblock.island.ban.item";
    public const ISLAND_UNBAN_ITEM = "vmskyblock.island.unban.item";
    public const ISLAND_SET_PVP = "vmskyblock.island.set.pvp";
    public const ISLAND_SET_ALLOW_VISITOR = "vmskyblock.island.set.allow.visitor";
    public const ISLAND_SET_ALLOW_DROP_ITEM = "vmskyblock.island.set.allow.drop.item";
    public const ISLAND_SET_ALLOW_PICK_UP_ITEM = "vmskyblock.island.set.allow.pick.up.item";
    public const ISLAND_SET_SPAWN = "vmskyblock.island.set.spawn";
    public const ISLAND_ADD_MEMBER = "vmskyblock.island.add"; // Add player to island
    public const ISLAND_REMOVE_MEMBER = "vmskyblock.island.remove"; // Remove player from island

    // Others
    public const CANT_GOT_KICKED = "vmskyblock.command.cant.got.kicked";

    public static function getArray(): array
    {
        return [
            // Commands
            self::COMMAND => "vmskyblock command",
            self::COMMAND_JOIN => "vmskyblock command join",
            self::COMMAND_CREATE => "vmskyblock command create",
            self::COMMAND_ADD => "vmskyblock command add",
            self::COMMAND_REMOVE => "vmskyblock command remove",
            self::COMMAND_DELETE => "vmskyblock command delete",
            self::COMMAND_VISIT => "vmskyblock command visit",
            self::COMMAND_MEMBERS => "vmskyblock command members",
            self::COMMAND_ABOUT => "vmskyblock command about",
            self::COMMAND_BAN => "vmskyblock command ban",
            self::COMMAND_UNBAN => "vmskyblock command unban",
            self::COMMAND_KICK => "vmskyblock command kick",
            self::COMMAND_SETTINGS => "vmskyblock command settings",
            self::COMMAND_SET_PVP => "vmskyblock command set pvp",
            self::COMMAND_SET_ALLOW_VISITOR => "vmskyblock command set allow visitor",
            self::COMMAND_SET_ALLOW_DROP_ITEM => "vmskyblock command set allow drop item",
            self::COMMAND_SET_ALLOW_PICK_UP_ITEM => "vmskyblock command set allow pick up item",
            self::COMMAND_SET_SIZE_ISLAND => "vmskyblock command set size island",
            self::COMMAND_BAN_ITEM => "vmskyblock command ban item",
            self::COMMAND_UNBAN_ITEM => "vmskyblock command unban item",
            self::COMMAND_SET_SPAWN => "vmskyblock command set spawn",
            self::COMMAND_TOP => "vmskyblock command top",
            self::COMMAND_SET_NAME => "vmskyblock command set name",
            self::COMMAND_FORM => "vmskyblock command form",
            self::COMMAND_GIVE_ITEM_PORTAL => "vmskyblock command give item portal",
            // Events
            self::BREAK_BLOCK => "vmskyblock break block",
            self::PLACE_BLOCK => "vmskyblock place block",
            self::INTERACT_BLOCK => "vmskyblock interact block",
            self::DROP_ITEM => "vmskyblock drop item",
            self::PICK_UP_ITEM => "vmskyblock pick up item",
            self::USE_ITEM => "vmskyblock use item",
            self::PVP => "vmskyblock pvp",
            // Islands
            self::ISLAND_BAN => "vmskyblock island ban",
            self::ISLAND_UNBAN => "vmskyblock island unban",
            self::ISLAND_KICK => "vmskyblock island kick",
            self::ISLAND_BAN_ITEM => "vmskyblock island ban item",
            self::ISLAND_UNBAN_ITEM => "vmskyblock island unban item",
            self::ISLAND_SET_PVP => "vmskyblock island set pvp",
            self::ISLAND_SET_ALLOW_VISITOR => "vmskyblock island set allow visitor",
            self::ISLAND_SET_ALLOW_DROP_ITEM => "vmskyblock island set allow drop item",
            self::ISLAND_SET_ALLOW_PICK_UP_ITEM => "vmskyblock island set allow pick up item",
            self::ISLAND_SET_SPAWN => "vmskyblock island set spawn",
            self::ISLAND_ADD_MEMBER => "vmskyblock island add", // Add player to island
            self::ISLAND_REMOVE_MEMBER => "vmskyblock island remove", // Remove player from island
            // Others
            self::CANT_GOT_KICKED => "vmskyblock command cant got kicked",
        ];
    }

}