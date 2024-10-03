<?php

declare(strict_types=1);

namespace venndev\vmskyblock\utils;

use Throwable;
use venndev\vmskyblock\VMSkyBlock;
use vennv\vapm\Async;

final class FolderUtil
{

  /**
   * @throws Throwable
   */
  public static function copy(string $source, string $dest): Async
  {
    return new Async(function () use ($source, $dest) {
      try {
        if (is_dir($source)) {
          @mkdir($dest);
          foreach (scandir($source) as $file) {
            if ($file === "." || $file === "..") continue;
            Async::await(self::copy($source . DIRECTORY_SEPARATOR . $file, $dest . DIRECTORY_SEPARATOR . $file));
          }
        } else {
          @copy($source, $dest);
        }
        VMSkyBlock::getInstance()->getLogger()->debug("Copy $source to $dest");
        return true;
      } catch (Throwable $e) {
        VMSkyBlock::getInstance()->getLogger()->error($e->getMessage());
        return false;
      }
    });
  }

  /**
   * @throws Throwable
   */
  public static function delete(string $string): Async
  {
    return new Async(function () use ($string) {
      try {
        if (is_dir($string)) {
          foreach (scandir($string) as $file) {
            if ($file === "." || $file === "..") continue;
            Async::await(self::delete($string . DIRECTORY_SEPARATOR . $file));
          }
          @rmdir($string);
        } else {
          @unlink($string);
        }
      } catch (Throwable $e) {
        VMSkyBlock::getInstance()->getLogger()->error($e->getMessage());
      }
    });
  }

  public static function checkExists(string $path): bool
  {
    return is_dir($path) || file_exists($path);
  }
}

