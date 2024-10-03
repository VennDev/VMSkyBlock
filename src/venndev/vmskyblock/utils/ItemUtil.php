<?php

declare(strict_types=1);

namespace venndev\vmskyblock\utils;

use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;

final class ItemUtil
{

  public static function getItem(string $item): Item
  {
    return StringToItemParser::getInstance()->parse($item);
  }

  public static function encodeItem(Item $item): string
  {
    $itemToJson = self::itemToJson($item);
    return base64_encode(gzcompress($itemToJson));
  }

  public static function decodeItem(string $item): Item
  {
    $itemFromJson = gzuncompress(base64_decode($item));
    return self::jsonToItem($itemFromJson);
  }

  public static function itemToJson(Item $item): string
  {
    $cloneItem = clone $item;
    $itemNBT = $cloneItem->nbtSerialize();
    return base64_encode(serialize($itemNBT));
  }

  public static function jsonToItem(string $json): Item
  {
    $itemNBT = unserialize(base64_decode($json));
    return Item::nbtDeserialize($itemNBT);
  }

  /**
   * @param Item $item1
   * @param Item $item2
   * @return bool
   *
   * This method don't compare the count of items
   */
  public static function equalItems(Item $item1, Item $item2): bool
  {
    return $item1->equals($item2) &&
      json_encode($item1->getEnchantments()) == json_encode($item2->getEnchantments()) &&
      $item1->getCustomName() == $item2->getCustomName();
  }

  public static function toBlock(Item $item): Block
  {
    return $item->getBlock();
  }
}

