<?php
class EnemySpawnZone extends PlayerRegion {
    private $ENEMY_TC_OFFSET = -4;

    function render() {
        parent::render();
        $this->killZoneTriggers();
        $time = 0;
        $waves = array();
        $this->trig("Enemy Town Center");
        Efft_RemoveO($this->getEnemyId());
        $this->create(
            U_TOWN_CENTER, 
            $this->origin->offset(2),
            $this->getEnemyId()
        );
        $spawnCenter = $this->origin;

        $spawnRounds = $GLOBALS['UNITS_MODEL'];

        $roundName = $spawnRounds[0]->getRoundName();
        $totalTime = 0;
        $this->trig("1: $roundName");
        for ($roundIndex = 0; $roundIndex < count($spawnRounds); $roundIndex++) {
            $roundNumber = $roundIndex + 1;
            $spawnRound = $spawnRounds[$roundIndex];
            $spawnCenter = $spawnCenter->offset(-$i);

            $totalTime += $spawnRound->roundTime;
            $roundName = $spawnRound->getRoundName();
            //$this->trig($roundName, 1, 0);
            // make the time +1 so the time alligns properly
            Cond_Timer($spawnRound->roundTime + 1);
            // give the play goodies
            Efft_Give($this->playerId, $spawnRound->payment, STONE);
            $this->chat("<GREEN> {$spawnRound->payment} stone for round advancement");
            // spawn the rounds units
            foreach($spawnRound->unitSpawnList as $i => $unitSpawn) {
                $unitSize = $unitSpawn->unitCount;
                while($unitSize > 0) {
                    $spawnArea = AreaAdvanced(
                        $this->origin->offset(-$this->depth + 2 + $i)->asArr(), 
                        $this->orientation, 
                        $unitSize % $this->width,
                        1
                    );
                    $this->createInArea($unitSpawn->unitId, $spawnArea, $this->getEnemyId());
                    $unitSize -= $this->width;
                    $i++;
                }
            }
            $nextRound = $spawnRounds[$roundIndex + 1];
            if ($nextRound) {
                $nextRoundTime = $totalTime + $nextRound->roundTime;
                $timeString = $this->getTimeString($nextRoundTime);
                $nextName = $nextRound->getRoundName();
                $nextNum = $roundNumber + 1;
                Efft_Display($nextRoundTime, 0, 
                    "<RED>Round {$roundNumber}: {$roundName}\n\n" .
                    "Round $nextNum begins at $timeString " .
                    "in $nextRound->roundTime seconds.\n\nNext: $nextName"
                );
                $nextTriggerName = "$nextNum: $nextName";
                $this->act($nextTriggerName);
                $this->trig($nextTriggerName, 0);
                // age up the age if the age is different
                if ($spawnRound->age != $nextRound->age) {
                    $eId = $this->getEnemyId();
                    $pId = $this->playerId;
                    Efft_Research($pId, $nextRound->age);
                    Efft_Research($eId, $nextRound->age);
                }
            }
        }
    }

    private function getTimeString($roundSeconds) {
        $nextMin = floor($roundSeconds / 60);
        $nextSec = $roundSeconds % 60;
        $nextMin = $nextMin < 10 ? "0{$nextMin}" : $nextMin;
        $nextSec = $nextSec < 10 ? "0{$nextSec}" : $nextSec;
        return "$nextMin:$nextSec";
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