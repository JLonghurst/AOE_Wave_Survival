<?php
class EnemySpawnZone extends PlayerRegion {
    private $ENEMY_TC_OFFSET = -4;

    function render() {
        parent::render();
        $this->killZoneTriggers();
        $time = 0;
        $this->trig("Enemy Town Center");
            Efft_RemoveO($this->getEnemyId());
            $this->create(
                U_TOWN_CENTER, 
                $this->origin->offset($this->ENEMY_TC_OFFSET), 
                $this->getEnemyId()
            );
        // $this->trig("Enemy Control", 1, 1);
        //     Efft_PatrolO($this->getEnemyId(), $this->getArea(), $this->origin->offset(50)->asArr());
        
        $waves = array();
        foreach($GLOBALS['UNITS'] as $i => $roundUnits) {
            if (is_array($roundUnits)) {
                $time += $roundUnits[0];
                array_push($waves, 
                    new EnemyWave(
                        $this->playerId, 
                        $this->width, 
                        $this->depth,
                        $i + 1, 
                        $time, 
                        75, 
                        $roundUnits[1]
                    )
                );
            } else {
                // age up break time
                $time += 120;
                $eId = $this->getEnemyId();
                $pId = $this->playerId;
                $age = null;
                $this->trig($roundUnits);
                Cond_Timer($time);
                switch ($roundUnits) {
                    case "Feudal Upgrade":
                        $age = T_FEUDAL_AGE;
                        // upgrade enemy units
                        foreach ([
                            $age,
                            T_FLETCHING,
                            T_PADDED_ARCHER_ARMOR,
                            T_SCALE_MAIL_ARMOR
                        ] as $r) Efft_Research($eId, $r);
                    break;
                    case "Castle Upgrade":
                        $age = T_CASTLE_AGE;
                        foreach ([
                            $age,
                        ] as $r) Efft_Research($eId, $r);
                    break;
                    case "Imperial Upgrade":
                        $age = T_IMPERIAL_AGE;
                        foreach ([
                            $age,
                        ] as $r) Efft_Research($eId, $r);
                    break;
                }
                Efft_Research($pId, $age);
            }
        }
        foreach($waves as $i => $wave)
            $wave->setOrigin($this->origin->offset(-$i));
        for ($i = 0; $i < count($waves) - 2; $i++) {
            $waves[$i]->nextTime = $waves[$i+1]->time;
            $waves[$i]->nextUnits = $waves[$i+1]->units;
        }
        foreach($waves as $wave) {
            $wave->renderWave();
        }
    }

    private function killZoneTriggers() {
        $a = $this->getArea();
        // Creates Kill Zone in the area where enemyId buildings spawn
        $this->trig("Kill Zone", 1, 1);
        Cond_InAreaY($this->playerId, Y_MILITARY, 1, $a);
        $this->chat("<RED> No player units are not allowed in the enemyId spawning area");
        Efft_KillY($this->playerId, Y_MILITARY, $a);
        if ($GLOBALS['DEBUG']) {
            $this->trig("Kill Zone Enemy", 1, 1);
                Cond_InAreaY($this->playerId, 1, Y_MILITARY, $a);
                Efft_KillY($this->playerId, Y_MILITARY, $a);
        }
    }
}
?>