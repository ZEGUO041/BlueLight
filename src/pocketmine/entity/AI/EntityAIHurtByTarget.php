<?php
namespace pocketmine\entity\AI;

use pocketmine\math\AxisAlignedBB;

class EntityAIHurtByTarget extends EntityAITarget{

	private $entityCallsForHelp;
	private $revengeTimerOld;
	private $targetClasses;

	public function __construct($creatureIn, $entityCallsForHelpIn, $targetClassesIn){
		parent::__construct($creatureIn, false);
		$this->entityCallsForHelp = $entityCallsForHelpIn;
		$this->targetClasses = $targetClassesIn;
		$this->setMutexBits(1);
	}

	public function shouldExecute(){
		$i = $this->taskOwner->getRevengeTimer();
		return $i != $this->revengeTimerOld && $this->isSuitableTarget($this->taskOwner->getAITarget(), false);
	}

	public function startExecuting(){
		$this->taskOwner->setAttackTarget($this->taskOwner->getAITarget());
		$this->revengeTimerOld = $this->taskOwner->getRevengeTimer();

		if ($this->entityCallsForHelp){
			$d0 = $this->getTargetDistance();

			$bb = new AxisAlignedBB($this->taskOwner->x, $this->taskOwner->y, $this->taskOwner->z, $this->taskOwner->x + 1.0, $this->taskOwner->y + 1.0, $this->taskOwner->z + 1.0);
			$l = $this->taskOwner->level->getCollidingEntities($bb->expand($d0, 10.0, $d0), $this->taskOwner);
			foreach ($l as $entitycreture){
				if ($this->taskOwner != $entitycreature && $entitycreature->getAttackTarget() == null/* && !$entitycreature->isOnSameTeam($this->taskOwner->getAITarget())*/){
					$flag = false;

					foreach ($this->targetClasses as $targetClass){
						if ($targetClass == get_class($entitycreature)){
							$flag = true;
							break;
						}
					}

					if (!$flag){
						$this->setEntityAttackTarget($entitycreature, $this->taskOwner->getAITarget());
					}
				}
			}
		}

		parent::startExecuting();
	}

	protected function setEntityAttackTarget($creatureIn, $entityLivingBaseIn){
		$creatureIn->setAttackTarget($entityLivingBaseIn);
	}
}