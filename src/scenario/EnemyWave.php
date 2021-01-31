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
    }
} 
?>