<?php

declare(strict_types=1);

namespace venndev\vmskyblock\utils;

use Throwable;
use venndev\vmskyblock\VMSkyBlock;

final class ErrorLogger
{

  public static function logThrowable(Throwable $throwable, bool $isError = true): void
  {
    $message = $throwable->getMessage();
    $file = $throwable->getFile();
    $line = $throwable->getLine();
    $trace = $throwable->getTraceAsString();
    $error = "An error occurred in file $file on line $line: $message\n$trace";
    if ($isError) VMSkyBlock::getInstance()->getLogger()->error($error);
    else VMSkyBlock::getInstance()->getLogger()->warning($error);
  }
}

