<?php

declare(strict_types=1);

namespace venndev\vmskyblock\api\data;

interface IData
{
    public function toArray(): array;

    public static function fromArray(array $data): object;
}