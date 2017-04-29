<?php
namespace pocketmine\entity\AI;

use pocketmine\entity\Entity;
use pocketmine\entity\Human;

class EntityAINearestAttackableTarget extends EntityAITarget{

	protected $targetClass;
	private $targetChance;
	protected $targetEntity;

	public function __construct($creature, $classTarget, $checkSight, $chance = 10, $onlyNearby = false, $targetSelector = null){
		parent::__construct($creature, $checkSight, $onlyNearby);
		$this->targetClass = $classTarget;
		$this->targetChance = $chance;
		$this->setMutexBits(1);
	}

	public function shouldExecute(){
		if ($this->targetChance > 0 && rand(0, $this->targetChance - 1) != 0){
			return false;
		}else{
			$d0 = $this->getTargetDistance();
			$bb = clone $this->taskOwner->getBoundingBox();
			$list = $this->taskOwner->level->getCollidingEntities($bb->expand($d0, 4.0, $d0), $this->taskOwner);
			foreach ($list as $index => $entity){
				if(!(get_class($entity) != $this->targetClass)) unset($list[$index]);
			}
			if(count($list) == 0){
				return false;
			}else{
				$target = $this->getNearestAttackableTarget($list);
				if($target instanceof Entity){
					$this->targetEntity = $target;
					return true;
				}
				return false;
			}
		}
	}

	public function startExecuting(){
		$this->taskOwner->setAttackTarget($this->targetEntity);
		parent::startExecuting();
	}

	public function getNearestAttackableTarget($list){
		$result = null;
		$distance = null;
		$owner = $this->taskOwner->getPosition();
		foreach ($list as $entity){
			$d = $entity->distance($owner);
			if($distance == null || $distance > $d){
				$distance = $d;
				$result = $entity;
			}
		}
		return $result;
	}
}