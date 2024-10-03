<?php

declare(strict_types=1);

namespace venndev\vmskyblock\data;

final class ConfigPaths
{

    # Messages
    public const MESSAGE_OUT_OF_RANGE_ISLAND = "out-of-range-island";
    public const MESSAGE_PVP_DISABLED = "pvp-disabled";
    public const MESSAGE_SET_NAME_SUCCESS = "set-name-success";
    public const MESSAGE_NO_ISLAND = "no-island";
    public const MESSAGE_ITEM_BANNED = "item-banned";
    public const MESSAGE_BANNED_BY_OWNER = "banned-by-owner";
    public const MESSAGE_DO_WITH_YOURSELF = "do-with-yourself";
    public const MESSAGE_BAN_PLAYER_SUCCESS = "ban-player-success";
    public const MESSAGE_UNBAN_PLAYER_SUCCESS = "unban-player-success";
    public const MESSAGE_PLAYER_BANNED_EXISTS = "player-banned-exists";
    public const MESSAGE_PLAYER_NOT_BANNED = "player-not-banned";
    public const MESSAGE_WAITING_FOR_ISLAND_TO_BE_CREATED = "waiting-for-island-to-be-created";
    public const MESSAGE_ALREADY_HAS_ISLAND = "already-has-island";
    public const MESSAGE_ISLAND_ORIGIN_NOT_WORKING = "island-origin-not-working";
    public const MESSAGE_ISLAND_CREATED = "island-created";
    public const MESSAGE_ISLAND_NOT_FOUND = "island-not-found";
    public const MESSAGE_WAITING_FOR_ISLAND_TO_BE_DELETED = "waiting-for-island-to-be-deleted";
    public const MESSAGE_ISLAND_DELETED = "island-deleted";
    public const MESSAGE_ADD_MEMBER_SUCCESS = "add-member-success";
    public const MESSAGE_HAS_BEEN_ADDED = "has-been-added";
    public const MESSAGE_MEMBER_EXISTS = "member-exists";
    public const MESSAGE_REMOVE_MEMBER_SUCCESS = "remove-member-success";
    public const MESSAGE_MEMBER_NOT_EXISTS = "member-not-exists";
    public const MESSAGE_KICKED_FROM_ISLAND = "kicked-from-island";
    public const MESSAGE_ADD_ITEM_BAN_SUCCESS = "add-item-ban-success";
    public const MESSAGE_ITEM_BANNED_EXISTS = "item-banned-exists";
    public const MESSAGE_UNBAN_ITEM_SUCCESS = "unban-item-success";
    public const MESSAGE_ITEM_NOT_BANNED = "item-not-banned";
    public const MESSAGE_PLAYER_NOT_FOUND = "player-not-found";
    public const MESSAGE_HAND_EMPTY = "hand-empty";
    public const MESSAGE_KICK_PLAYER_SUCCESS = "kick-player-success";
    public const MESSAGE_MEMBERS_LIST = "members-list";
    public const MESSAGE_ISLAND_SIZE_NOT_VALID = "island-size-not-valid";
    public const MESSAGE_SET_SPAWN_SUCCESS = "set-spawn-success";
    public const MESSAGE_PLEASE_STAND_ON_YOUR_ISLAND = "please-stand-on-your-island";
    public const MESSAGE_PLEASE_DONT_STAND_ON_YOUR_ISLAND = "please-dont-stand-on-your-island";
    public const MESSAGE_NO_PERMISSION = "no-permission";
    public const MESSAGE_SETTINGS_ISLAND_UPDATED = "setting-island-updated";
    public const MESSAGE_TOP_ISLANDS = "top-islands";
    public const MESSAGE_TOP_ISLANDS_FORMAT = "top-islands-format";
    public const MESSAGE_VISIT_NOT_ALLOWED = "visit-not-allowed";
    public const MESSAGE_PLAYER_ONLY = "player-only";
    public const MESSAGE_COMPLETE_QUEST = "complete-quest";
    public const MESSAGE_MUST_IS_NUMBER = "must-is-number";
    public const MESSAGE_NAME_HAS_BEEN_CHANGED = "name-has-been-changed";
    public const MESSAGE_CANNOT_DO_THIS_WITH_THE_PLAYER_HAS_PERMISSION = "cant-do-this-with-the-player-has-permission";
    public const MESSAGE_IS_NOT_AN_OWNER_OF_THE_ISLAND = "is-not-an-owner-of-the-island";
    public const MESSAGE_WAITING = "waiting";
    public const MESSAGE_PORTAL_REMOVED = "portal-has-been-removed";
    public const MESSAGE_PORTAL_REQUIRED_LEVEL = "portal-required-level";

    # Island Settings
    public const ISLAND_SETTINGS_VALUABLE_BLOCKS = "valuable-blocks";
    public const ISLAND_SETTINGS_VOID = "void";
    public const ISLAND_NETHER_LEVEL_SETTINGS_ENABLED = "island-nether.level-settings.enabled";
    public const ISLAND_NETHER_LEVEL_SETTINGS_LEVEL = "island-nether.level-settings.level";
    public const ISLAND_THE_END_LEVEL_SETTINGS_ENABLED = "island-the-end.level-settings.enabled";
    public const ISLAND_THE_END_LEVEL_SETTINGS_LEVEL = "island-the-end.level-settings.level";
    public const ISLAND_THE_END_ITEM_CREATE_PORTAL = "island-the-end.item-create-portal";
    public const ISLAND_NETHER_ITEM_CREATE_PORTAL = "island-nether.item-create-portal";
    public const ISLAND_THE_END_PORTAL_SETTINGS = "island-the-end.portal-settings";
    public const ISLAND_NETHER_PORTAL_SETTINGS = "island-nether.portal-settings";

    # Plugin Settings
    public const PLUGIN_SETTINGS_PLUGIN_PREFIX = "vmskyblock.prefix";
    public const PLUGIN_SETTINGS_AUTO_UNLOAD_WORLDS = "vmskyblock.settings.auto-unload-worlds";
    public const PLUGIN_SETTINGS_COMMAND_NAME = "vmskyblock.settings.command.name";
    public const PLUGIN_SETTINGS_COMMAND_ALIASES = "vmskyblock.settings.command.aliases";
    public const PLUGIN_SETTINGS_TIME_TO_SAVE = "vmskyblock.settings.time-to-save";
    public const PLUGIN_SETTINGS_DATABASE_ENABLED = "vmskyblock.database-settings.enabled";
    public const PLUGIN_SETTINGS_DATABASE_TYPE = "vmskyblock.database-settings.type";
    public const PLUGIN_SETTINGS_DATABASE = "vmskyblock.database-settings.database";
    public const PLUGIN_SETTINGS_DATABASE_HOST = "vmskyblock.database-settings.host";
    public const PLUGIN_SETTINGS_DATABASE_PORT = "vmskyblock.database-settings.port";
    public const PLUGIN_SETTINGS_DATABASE_USERNAME = "vmskyblock.database-settings.username";
    public const PLUGIN_SETTINGS_DATABASE_PASSWORD = "vmskyblock.database-settings.password";

    # Quests
    public const QUESTS_PATH_OF_THE_FACILITY = "path-of-the-facility";
    public const QUESTS_NUMBER_OF_QUESTS_ASSIGNED = "number-of-quests-assigned";
    public const QUESTS_DATE_TIME_FORMAT = "date-time-format";

    # Forms Settings
    public const FORMS_STATUS = "forms.status";
    public const FORMS_SKYBLOCK_FORM = "forms.skyblock-form";
    public const FORMS_SETTINGS_SKYBLOCK_FORM = self::FORMS_SKYBLOCK_FORM . ".skyblock-settings-form";
    public const FORMS_SET_NAME_SKYBLOCK_FORM = self::FORMS_SKYBLOCK_FORM . ".skyblock-set-name-form";
    public const FORMS_ADD_MEMBER_SKYBLOCK_FORM = self::FORMS_SKYBLOCK_FORM . ".skyblock-add-member-form";
    public const FORMS_BAN_PLAYER_SKYBLOCK_FORM = self::FORMS_SKYBLOCK_FORM . ".skyblock-ban-player-form";
    public const FORMS_UNBAN_PLAYER_SKYBLOCK_FORM = self::FORMS_SKYBLOCK_FORM . ".skyblock-unban-player-form";
    public const FORMS_KICK_PLAYER_SKYBLOCK_FORM = self::FORMS_SKYBLOCK_FORM . ".skyblock-kick-player-form";
    public const FORMS_REMOVE_MEMBER_SKYBLOCK_FORM = self::FORMS_SKYBLOCK_FORM . ".skyblock-remove-member-form";
    public const FORMS_DELETE_ISLAND_SKYBLOCK_FORM = self::FORMS_SKYBLOCK_FORM . ".skyblock-delete-island-form";
    public const FORMS_QUEST_SKYBLOCK_FORM = self::FORMS_SKYBLOCK_FORM . ".skyblock-quest-form";
    public const FORMS_VIEW_QUEST_SKYBLOCK_FORM = self::FORMS_SKYBLOCK_FORM . ".skyblock-view-quest-form";
    public const FORMS_HISTORY_QUEST_FORM = self::FORMS_SKYBLOCK_FORM . ".skyblock-history-quest-form";
    public const FORMS_QUEST_MANAGER_FORM = self::FORMS_SKYBLOCK_FORM . ".skyblock-quest-manager-form";
    public const FORMS_REMOVE_PORTAL_FORM = self::FORMS_SKYBLOCK_FORM . ".skyblock-remove-portal-form";

}