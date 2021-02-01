<?php
class SpawnRound {
    public $roundTime;
    public array $unitSpawnList;

    public function __construct($raw) {
        $this->roundTime = $raw[0];
        $this ->unitSpawnList = array_map(function($unitRaw) {
            $unitId = $unitRaw[0];
            $unitCount = $unitRaw[1];
            return new UnitSpawn($unitId, $unitCount);
        }, (array)$raw[1]);
    }

    public function getRoundName() {
        $roundName = '';
        foreach($this->unitSpawnList as $i => $unitSpawn) {
            if ($i != 0) $roundName .= ', ';
            $roundName .= $unitSpawn->getName();
        }
        return $roundName;
    }

    public function getTotalComputedVillagerTime() {
        $total = 0;
        foreach($this->unitSpawnList as $unitSpawn) {
            $total += 
                $unitSpawn->unitStats->getComputedVillagerTime() 
                * $unitSpawn->unitCount;
        }
        return $total;
    }

    public function getProductionTime() {
        $totalProductionTime = 0;
        foreach($this->unitSpawnList as $unitSpawn) {
            $totalProductionTime += 
                $unitSpawn->unitStats->createTime
                * $unitSpawn->unitCount;
        }
        return $totalProductionTime;
    }

    public function getStats() {
        $total_vil_time = $this->getTotalComputedVillagerTime(); 
        $total_production_time = $this->getProductionTime();
        $result = array(
            "Round Time" => $this->roundTime,
            "Units" => $this->getRoundName(),
            "Total Vil Time" => $total_vil_time,
            "Total Production Times" => $total_production_time,
            "Vil Time Per Second" => $total_vil_time / $this->roundTime,
            "Production Time Per Second" => $total_production_time / $this->roundTime,
            // "UnitName" => $stat->unitName,
            // "UnitCount" => $stat->unitCount,
        );
        //print_r($result);
        //print("\n");
        return $result;
    }
}
?>