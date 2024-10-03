<?php

declare(strict_types=1);

namespace venndev\vmskyblock\utils;

use Throwable;
use venndev\vmskyblock\api\utils\IArrayPHP;
use venndev\vmskyblock\VMSkyBlock;
use vennv\vapm\FiberManager;
use vennv\vapm\Promise;

final class ArrayPHP implements IArrayPHP
{

  public static function noDuplicate(array $array, string $columnName): Promise
  {
    return new Promise(function ($resolve, $reject) use ($array, $columnName): void {
      try {
        $result = [];
        foreach ($array as $value) {
          if (!in_array($value[$columnName], $result)) $result[] = $value;
          FiberManager::wait();
        }
        $resolve($result);
      } catch (Throwable $e) {
        VMSkyBlock::getInstance()->getLogger()->error($e->getMessage());
        $reject($array);
      }
    });
  }

  public static function mergeUniqueByKey($arrays, $key): Promise
  {
    return new Promise(function ($resolve, $reject) use ($arrays, $key): void {
      try {
        $result = [];
        foreach ($arrays as $array) {
          foreach ($array as $item) {
            $case = $item[$key];
            if (!isset($result[$case]) || count($result[$case]) < count($item)) $result[$case] = $item;
            FiberManager::wait();
          }
          FiberManager::wait();
        }
        $resolve(array_values($result));
      } catch (Throwable $e) {
        VMSkyBlock::getInstance()->getLogger()->error($e->getMessage());
        $reject($arrays);
      }
    });
  }

  public static function array_column(array $array, string $columnName): Promise
  {
    return new Promise(function ($resolve, $reject) use ($array, $columnName): void {
      try {
        $result = [];
        foreach ($array as $value) {
          $result[] = $value[$columnName];
          FiberManager::wait();
        }
        $resolve($result);
      } catch (Throwable $e) {
        VMSkyBlock::getInstance()->getLogger()->error($e->getMessage());
        $reject($array);
      }
    });
  }
}

