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

    private $unitStatMap = null;
    private function getUnitStatMap() {
        if ($this->unitStatMap == null) {
            $this->unitStatMap = $this->parseUnitStatMap();
        } 
        return $this->unitStatMap;
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

    function parseUnitStatMap() {
        $csvData = file_get_contents('C:\Code\AOE_Wave_Survival\Data\unitStats.csv');
        $lines = explode(PHP_EOL, $csvData);
        $unitStatMap = array();
        foreach ($lines as $line) {
            $raw = str_getcsv($line);

            $name = $raw[1];
            $createTime = $raw[2];
            $foodCost = floatval($raw[5]);
            $woodCost = floatval($raw[6]);
            $goldCost = floatval($raw[7]);
            
            $unitStat = new UnitStats($foodCost, $woodCost, $goldCost, $createTime);
            $unitStat->productionTime = $createTime;
            $unitStat->unitName = $name;

            $unitStatMap[$name] = $unitStat;
        }
        return $unitStatMap;
    }

    function getUnitStat($unitId) {
        $unitName = unitNameById($unitId);
        $dataMap = $this->getUnitStatMap();
        $stat = $dataMap[$unitName];
        if ($stat == null) {
            print("no stat for name: {$unitName}\n");
        }
        return $stat;
    }    
    
    function getUnitStats() {
        $stats = array();
        foreach((array)$this->units as $unit) {
            $unitId = $unit[0];
            $unitCount = $unit[1];
            array_push($stats, $this->getUnitStat($unitId));
        }
        // REQUIRED 
        foreach($stats as $stat) {
            $vilTime = $stat->getComputedVillagerTime();
            $count = 
            $result = array(
                "Total villager time" => $this->nextTime - $this->time,
                "Required Villager Time" => $vilTime,
                "UnitName" => $stat->unitName,
            );
            print_r($result);
            print("\n");
            if ($vilTime > 0) {
            }
        }
    }
} 
?>