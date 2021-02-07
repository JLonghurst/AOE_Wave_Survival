<?php
class TowerZone extends PlayerRegion {
    private $TOWER_HILL_OFFSET = 5;
    private $UNIT_OFFSET = 1;

    /**
     * Heals the enemies building 
     *
     * @param Point $p a point to heal
     * @return void
     */
    function healEnemyBuildingAt($point) {
        Efft_HPY($this->getEnemyId(), 3, Y_BUILDING, $point->asArr());
    }

    function render() {
        parent::render();
        $tcLoc = $this->getCenter()->offset(0, 9);
        $unitLoc = $this->getCenter()->offset($UNIT_OFFSET)->asArr();
        $this->trig("Enemy Placement");
            Efft_Create($this->getEnemyId(), U_MILITIA, $unitLoc);
        $this->trig("Enemy Kill");
            Cond_Timer(2);
            Efft_KillU($this->getEnemyId(), U_MILITIA, $unitLoc);

        $this->setTowerElevation();
        $this->trig("Enemy Town Center Invincible", 1, 1);
            Cond_Timer(2);
            $this->healEnemyBuildingAt($tcLoc);

        $this->trig("Tower Placement");
            $this->create(U_WATCH_TOWER, $this->getCenter());
            Efft_ChangeView($this->playerId, $this->getCenter()->asArr());
            $this->act("Tower Death");

        $this->trig("Enemy Town Center");
            Efft_RemoveO($this->getEnemyId());
            $this->create(
                U_TOWN_CENTER, 
                $this->getCenter()->offset(-10), 
                $this->getEnemyId()
            );

        $this->trig("Tower Death",  0, 0, 1, "111", "Do not let your tower be destroyed by the enemyId buildings");
            Cond_Timer(3);
            Cond_NotOwnU($this->playerId, 1, U_WATCH_TOWER);
            $this->chat("<RED> You lost your tower! gg fam");
            Efft_Display(10, 0, "<RED> You lost your tower! gg fam");
            Efft_Display(10, 1, "<RED> You lost your tower! gg fam");
            Efft_Display(10, 2, "<RED> You lost your tower! gg fam");
            $this->runTrigger("End Game Chat 1", function() {
                Cond_Timer(5);
                $this->runTrigger("End Game Chat 2", function () {
                    Cond_Timer(5);
                    $this->chat('27');
                    $this->runTrigger("Game Over", function() {
                        Cond_Timer(6);
                        Efft_DeclareVictory($this->getEnemyId());
                    });
                });
            });
    }

    private function setTowerElevation() {
        for ($i = 1; $i < $this->TOWER_HILL_OFFSET; $i++)
            foreach (AreaPts(AreaSet(
                $this->getCenter()->asArr(), 
                $this->TOWER_HILL_OFFSET + 2 - $i
            )) as $p)
                setElevation($p, $i);
    }

    private function towerExplosionTrigger($triggerName) {
        Trig($triggerName, 0);
        Efft_ChangeView(1, $this->getCenter()->asArr());
        $this->runTrigger("Tower Explode Trigger", function() {
            $center = $this->getCenter()->asArr();
            for ($i = 1; $i < $this->TOWER_HILL_OFFSET - 1; $i++) {
                foreach (AreaPts(AreaSet($center, $i)) as $pt) {
                    // kill all enemy units
                    Efft_KillO($this->getEnemyId(), $pt);
                    // explosion animation
                    Efft_Create(0, U_MACAW, $pt);
                    Efft_KillU(0, U_MACAW, $pt);
                }
            }
        }, 2);
    }

    public function placeStoreTriggersAt($storeOrigin) {
        $towerDestroyTrigger = $this->getName("Tower Explosion");
        $towerDestroyTech = new Tech(
            $towerDestroyTrigger,
            U_PETARD,
            [100, 400, 800]
        );
        $towerDestroyTech->setPlayerId($this->playerId);
        $towerDestroyTech->setTriggerName($towerDestroyTrigger);
        $this->towerExplosionTrigger($towerDestroyTrigger);
        $towerDestroyTech->placeAtLocation($storeOrigin->offset(-9, 4), $towerDestroyTrigger);

        $createCastleName = $this->getName("Castle Creation");
        $castleTech = new Tech(
            $createCastleName,
            U_PETARD,
            [500]
        );
        $castlePoint = $this->origin->offset(0, $this->width / 2);
        Trig($createCastleName, 0);
            // remove stone heads
            Efft_RemoveO(0, $castlePoint->areaFromOffset(-4, -4));
            // place castle
            $this->create(U_CASTLE, $castlePoint->offset(-2, -1));
        $castleTech->setPlayerId($this->playerId);
        $castleTech->setTriggerName($createCastleName);
        $castleTech->placeAtLocation($storeOrigin->offset(-9, 7), $createCastleName);
        $this->updateTriggers($storeOrigin);
    }

    public function updateTriggers($storeOrigin) {
        $towerLocation = $this->getCenter()->asArr();
        $addHealth = $this->getName("Add 1000 Health");
        $towerUpgrade1 = $this->getName("Tower Upgrade 1");
        $towerUpgrade2 = $this->getName("Tower Upgrade 2");
        $towerUpgrade3 = $this->getName("Tower Upgrade 3");
        $regain = $this->regainTrigger(1, 1);
        $regain4 = $this->regainTrigger(4);
        $regain10 = $this->regainTrigger(10);
        $regain15 = $this->regainTrigger(15);

        Trig($addHealth, 0, 0);
            Efft_HPY(1, 1000, Y_BUILDING, $towerLocation);
        Trig($towerUpgrade1, 0, 0);
            Efft_Deact($regain);
            Efft_Act($regain4);
            Efft_Research(1, T_MURDER_HOLES);
            Efft_RangeY(1, 2, Y_BUILDING, $towerLocation);
            Efft_APY(1, 5, Y_BUILDING, $towerLocation);
        Trig($towerUpgrade2, 0, 0);   
            Efft_Deact($regain4);
            Efft_Act($regain10);
            Efft_RangeY(1, 5, Y_BUILDING, $towerLocation);
            Efft_APY(1, 5, Y_BUILDING, $towerLocation); 
        Trig($towerUpgrade3, 0, 0);   
            Efft_Deact($regain10);
            Efft_Act($regain15);
            Efft_RangeY(1, 5, Y_BUILDING, $towerLocation);
            Efft_APY(1, 5, Y_BUILDING, $towerLocation);

        $techs = [
            new Tech($addHealth, U_MONK, [100, 200, 300, 400, 500]),
            new Tech($towerUpgrade1, U_SKIRMISHER, [200]),
            new Tech($towerUpgrade2, U_ELITE_SKIRMISHER, [400]),
            new Tech($towerUpgrade3, U_ARBALEST, [800]),
        ];
        foreach($techs as $i => $t) {
            $t->setPlayerId($this->playerId);
            $t->placeAtLocation($storeOrigin->offset(-9, -2 - 2*$i), $t->relicName);
        }
    }

    private function regainTrigger($ammount, $startState = 0) {
        $name = $this->getName("Tower Health Regain x$ammount");
        Trig($name, $startState, 1);
            Cond_Timer(1);
            Efft_DamageY($this->playerId, -$ammount, Y_BUILDING, $this->getCenter()->asArr());
        return $name;
    }
}
?>