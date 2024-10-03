<?php

namespace venndev\vmskyblock\api\manager;

use Throwable;
use venndev\vmskyblock\VMSkyBlock;
use vennv\vapm\Deferred;

interface IWorldManager
{
    /**
     * @throws Throwable
     */
    public function init(): void;

    /**
     * @return Deferred<int>
     * @throws Throwable
     *
     * This method is used to load the worlds.
     */
    public function loadWorlds(): Deferred;

    /**
     * @throws Throwable
     * @param string $world
     * @return void
     *
     * This method is used to apply the island nether world.
     */
    public function applyIslandNether(string $world): void;

    /**
     * @throws Throwable
     * @param string $world
     * @return void
     *
     * This method is used to apply the island end world.
     */
    public function applyIslandEnd(string $world): void;

    /**
     * @return array
     *
     * This method is used to get the island nether world.
     */
    public function getIslandNether(): array;

    /**
     * @return array
     *
     * This method is used to get the island end world.
     */
    public function getIslandEnd(): array;

    /**
     * @param string $world
     * @return int|null
     *
     * This method is used to get the dimension of the world.
     */
    public function getWorldDimension(string $world): ?int;

    public function getPlugin(): VMSkyBlock;
}