<?php

namespace venndev\vmskyblock\api\utils;

use Throwable;
use vennv\vapm\Promise;

interface IArrayPHP
{
    /**
     * @throws Throwable
     */
    public static function noDuplicate(array $array, string $columnName): Promise;

    /**
     * @throws Throwable
     */
    public static function mergeUniqueByKey($arrays, $key): Promise;

    /**
     * @throws Throwable
     */
    public static function array_column(array $array, string $columnName): Promise;
}