<?php

declare(strict_types=1);

namespace venndev\vmskyblock\event;

use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageEvent;
use venndev\vmskyblock\api\event\IVMSBEntityDamageByEntityEvent;

final class VMSBEntityDamageByEntityEvent extends EntityDamageEvent implements IVMSBEntityDamageByEntityEvent
{
    private int $attackerEntityId;

    /**
     * @param float[] $modifiers
     */
    public function __construct(
        Entity        $attacker,
        Entity        $entity,
        int           $cause,
        float         $damage,
        array         $modifiers = [],
        private float $knockBack = Living::DEFAULT_KNOCKBACK_FORCE,
        private float $verticalKnockBackLimit = Living::DEFAULT_KNOCKBACK_VERTICAL_LIMIT
    )
    {
        $this->attackerEntityId = $attacker->getId();
        parent::__construct($entity, $cause, $damage, $modifiers);
        $this->addAttackerModifiers($attacker);
    }

    protected function addAttackerModifiers(Entity $attacker): void
    {
        if ($attacker instanceof Living) { //TODO: move this to entity classes
            $effects = $attacker->getEffects();
            if (($strength = $effects->get(VanillaEffects::STRENGTH())) !== null) {
                $this->setModifier($this->getBaseDamage() * 0.3 * $strength->getEffectLevel(), self::MODIFIER_STRENGTH);
            }
            if (($weakness = $effects->get(VanillaEffects::WEAKNESS())) !== null) {
                $this->setModifier(-($this->getBaseDamage() * 0.2 * $weakness->getEffectLevel()), self::MODIFIER_WEAKNESS);
            }
        }
    }

    public function getAttacker(): ?Entity
    {
        return $this->getEntity()->getWorld()->getServer()->getWorldManager()->findEntity($this->attackerEntityId);
    }

    public function getKnockBack(): float
    {
        return $this->knockBack;
    }

    public function setKnockBack(float $knockBack): void
    {
        $this->knockBack = $knockBack;
    }

    public function getVerticalKnockBackLimit(): float
    {
        return $this->verticalKnockBackLimit;
    }

    public function setVerticalKnockBackLimit(float $verticalKnockBackLimit): void
    {
        $this->verticalKnockBackLimit = $verticalKnockBackLimit;
    }

}