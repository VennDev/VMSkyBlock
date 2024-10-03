<?php

namespace venndev\vmskyblock\api\event;

use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

interface IVMSBBlockPlaceEvent
{
    /**
     * Returns the player who is placing the block.
     */
    public function getPlayer(): Player;

    /**
     * Gets the item in hand
     */
    public function getItem(): Item;

    /**
     * Returns a BlockTransaction object containing all the block positions that will be changed by this event, and the
     * states they will be changed to.
     *
     * This will usually contain only one block, but may contain more if the block being placed is a multi-block
     * structure such as a door or bed.
     */
    public function getTransaction(): BlockTransaction;

    public function getBlockAgainst(): Block;
}