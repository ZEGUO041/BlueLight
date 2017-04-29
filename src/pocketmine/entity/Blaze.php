<?php

/*
 *
 *  _____   _____   __   _   _   _____  __    __  _____
 * /  ___| | ____| |  \ | | | | /  ___/ \ \  / / /  ___/
 * | |     | |__   |   \| | | | | |___   \ \/ /  | |___
 * | |  _  |  __|  | |\   | | | \___  \   \  /   \___  \
 * | |_| | | |___  | | \  | | |  ___| |   / /     ___| |
 * \_____/ |_____| |_|  \_| |_| /_____/  /_/     /_____/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author iTX Technologies
 * @link https://itxtech.org
 *
 */

namespace pocketmine\entity;

use pocketmine\entity\Attribute;
use pocketmine\entity\AI\EntityAIMoveTowardsRestriction;
use pocketmine\entity\AI\EntityAIWatchClosest;
use pocketmine\entity\AI\EntityAIHurtByTarget;
use pocketmine\entity\AI\EntityAILookIdle;
use pocketmine\entity\AI\EntityAIWander;
use pocketmine\entity\AI\EntityAINearestAttackableTarget;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item as ItemItem;

class Blaze extends Monster{
	const NETWORK_ID = 43;

	public $width = 0.3;
	public $length = 0.9;
	public $height = 1.8;
	public $maxhealth = 20;

	private $heightOffset = 0.5;
	private $heightOffsetUpdateTime;

	public function initEntity(){
		$this->tasks->addTask(5, new EntityAIMoveTowardsRestriction($this, 1.0));
		$this->tasks->addTask(7, new EntityAIWander($this, 1.0));
		$this->tasks->addTask(8, new EntityAIWatchClosest($this, "pocketmine\Player", 8.0));
		$this->tasks->addTask(8, new EntityAILookIdle($this));
		//$this->targetTasks->addTask(1, new EntityAIHurtByTarget($this, true, ""));
		$this->targetTasks->addTask(2, new EntityAINearestAttackableTarget($this, "pocketmine\Player", true));
		$this->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED)->setValue(0.23000000417232513);
	}

	public function getName() : string{
		return "Blaze";
	}
	
	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->eid = $this->getId();
		$pk->type = self::NETWORK_ID;
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->speedX = $this->motionX;
		$pk->speedY = $this->motionY;
		$pk->speedZ = $this->motionZ;
		$pk->yaw = $this->yaw;
		$pk->pitch = $this->pitch;
		$pk->metadata = $this->dataProperties;
		$player->dataPacket($pk);
		parent::spawnTo($player);
	}

	public function onUpdate($currentTick){
		if($this->closed){
			return false;
		}


		$tickDiff = $currentTick - $this->lastUpdate;
		if($tickDiff <= 0 and !$this->justCreated){
			return true;
		}
		$this->lastUpdate = $currentTick;

		$hasUpdate = $this->entityBaseTick($tickDiff);
		if (!$this->onGround && $this->motionY < 0.0){
			$this->motionY *= 0.6;
		}
		$this->updateMovement();
		return true;
	}

	public function updateAITasks(){
		if ($this->isInsideOfWater()){
			//1.0Damage
		}

		--$this->heightOffsetUpdateTime;

		if ($this->heightOffsetUpdateTime <= 0){
			$this->heightOffsetUpdateTime = 100;
			$this->heightOffset = 0.5 + (rand(0, 100) / 100) * 3.0;
		}

		$entitylivingbase = $this->getAttackTarget();

		if ($entitylivingbase != null && $entitylivingbase->y + $entitylivingbase->getEyeHeight() > $this->y + $this->getEyeHeight() + $this->heightOffset){
			$this->motionY += (0.30000001192092896 - $this->motionY) * 0.30000001192092896;
		}

		parent::updateAITasks();
	}

	public function getDrops(){
		$cause = $this->lastDamageCause;
		if($cause instanceof EntityDamageByEntityEvent and $cause->getDamager() instanceof Player){
			$lootingL = $cause->getDamager()->getItemInHand()->getEnchantmentLevel(Enchantment::TYPE_WEAPON_LOOTING);
			$drops = array(ItemItem::get(ItemItem::BLAZE_ROD, 0, mt_rand(0, 1 + $lootingL)));
			return $drops;
		}
		return [];
	}
}