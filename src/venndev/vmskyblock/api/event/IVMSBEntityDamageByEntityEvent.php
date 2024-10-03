<?php

namespace venndev\vmskyblock\api\event;

use pocketmine\entity\Entity;

interface IVMSBEntityDamageByEntityEvent
{
    /**
     * Returns the attacking entity, or null if the attacker has been killed or closed.
     */
    public function getAttacker(): ?Entity;

    /**
     * Returns the force with which the victim will be knocked back from the attacking entity.
     *
     * @see Living::DEFAULT_KNOCKBACK_FORCE
     */
    public function getKnockBack(): float;

    /**
     * Sets the force with which the victim will be knocked back from the attacking entity.
     * Larger values will knock the victim back further.
     * Negative values will pull the victim towards the attacker.
     */
    public function setKnockBack(float $knockBack): void;

    /**
     * Returns the maximum upwards velocity the victim may have after being knocked back.
     * This ensures that the victim doesn't fly up into the sky when high levels of knock back are applied.
     *
     * @see Living::DEFAULT_KNOCKBACK_VERTICAL_LIMIT
     */
    public function getVerticalKnockBackLimit(): float;

    /**
     * Sets the maximum upwards velocity the victim may have after being knocked back.
     * Larger values will allow the victim to fly higher if the knock back force is also large.
     */
    public function setVerticalKnockBackLimit(float $verticalKnockBackLimit): void;
}