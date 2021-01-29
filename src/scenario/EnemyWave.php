<?php
class EnemyWave extends PlayerRegion {
    public $roundNum;
    // time until this round plays
    public $time;
    // payment from this round
    public $payment;
    // the units of the round. note: unit is of type [[unitId, unitCount]]
    public $units;
    public $nextTime;
    public $nextUnits;

    private $swordsMen = [U_MILITIA, U_MAN_AT_ARMS, U_CHAMPION];
    private $archers = [U_ARCHER, U_CROSSBOWMAN, U_ARBALEST];
    private $lightCavalry = [U_SCOUT_CAVALRY, U_LIGHT_CAVALRY, U_HUSSAR];

    function getUnitStat($unitId) {
        // $stat = new UnitStats(0);
        // if (in_array($unitId, $swordsMen)) {
        //     $stat = new UnitStats(60, 20);
        // } else if (in_array($unitId, $archers)) {
        //     $stat = new UnitStats(0, 25, 45);
        // } else if (in_array($unitId, $lightCavalry)) {
        //     $stat = new UnitStats(90, 0);
        // }
        // print($stat);
        // return $stat;
    }

    function getUnitStats() {
        $stats = array();
        foreach((array)$this->units as $unit) {
            $unitId = $unit[0];
            $unitSize = $unit[1];
            array_push($stats, $this->getUnitStat($unit));
        }
        //print_r($stats);
    }

    function evaluateDifficulty() {
        $swordLine = new UnitStats(60, 20);
        $knightLine = new UnitStats(60, 75);
        $archerLine = new UnitStats(0, 45, 25);
    }

    function __construct(
        $playerId, 
        $width, 
        $depth,  
        $roundNum, 
        $time, 
        $payment, 
        $units
    ) {
        parent::__construct($playerId, TERRAIN_SNOW, $width, $depth);
        $this->roundNum = $roundNum;
        $this->time = $time;
        $this->payment = $payment;
        $this->units = $units;
    }

    function renderWave() {
        $relicName = nameFromUnit($this->units);
        $this->trig($relicName, 1, 0);
            Cond_Timer($this->time);
        // give the play goodies
        Efft_Give($this->playerId, $this->payment, STONE);
        $this->chat("<GREEN> {$this->payment} stone for round advancement");
        $this->chat("<RED> Round {$this->roundNum}: {$relicName}");
        // spawn the waves units
        foreach((array)$this->units as $i => $unit) {
            $unitId = $unit[0];
            $unitSize = $unit[1];
            while($unitSize > 0) {
                $spawnArea = AreaAdvanced(
                    $this->origin->offset(-$this->depth + 2 + $i)->asArr(), 
                    $this->orientation, 
                    $unitSize % $this->width,
                    1
                );
                $this->createInArea($unitId, $spawnArea, $this->getEnemyId());
                $unitSize -= $this->width;
                $i++;
            }
        }
        // display future round info if it exists
        if ($this->nextTime != null) {
            $nextMin = floor($this->nextTime / 60);
            $nextSec = $this->nextTime % 60;
            $nextMin = $nextMin < 10 ? "0{$nextMin}" : $nextMin;
            $nextSec = $nextSec < 10 ? "0{$nextSec}" : $nextSec;
            $roundDiff = $this->nextTime - $this->time;
            $nextName = nameFromUnit($this->nextUnits);
            $nextNum = $this->roundNum + 1;
            Efft_Display($roundDiff, 0, 
                "<RED> Round $nextNum " . 
                "begins at $nextMin:$nextSec " .
                "in $roundDiff seconds. \n\n Next: $nextName"
            );
        }
        $this->getUnitStats();
    }
} 
?>