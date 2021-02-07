<?php
class SpawnRound {
    public $age;
    public $roundTime;
    public $payment;
    public array $unitSpawnList;

    public function __construct($age, $roundTime, $payment, $unitSpawnList) {
        $this->age = $age;
        $this->payment = $payment;
        $this->roundTime = $roundTime;
        $this->unitSpawnList = $unitSpawnList;
    }

    public function getRoundName() {
        $roundName = '';
        foreach($this->unitSpawnList as $i => $unitSpawn) {
            if ($i != 0) $roundName .= ', ';
            $roundName .= $unitSpawn->getName();
        }
        if ($roundName == '') {
            $roundName = 'feudal';
        }
        return $roundName;
    }

    public function getTotalVillagerTime() {
        $total = 0;
        foreach($this->unitSpawnList as $unitSpawn) {
            $total += 
                $unitSpawn->unitStats->getComputedVillagerTime() 
                * $unitSpawn->unitCount;
        }
        return $total;
    }

    public function getVilTimePerSecond() {
        return $this->getTotalVillagerTime() / $this->roundTime;
    }

    public function getTotalProductionTime() {
        $totalProductionTime = 0;
        foreach($this->unitSpawnList as $unitSpawn) {
            $totalProductionTime += 
                $unitSpawn->unitStats->createTime
                * $unitSpawn->unitCount;
        }
        return $totalProductionTime;
    }

    public function getProductionTimePerSecond() {
        return $this->getTotalProductionTime() / $this->roundTime;
    }
 

    public function getStats($i) {
        $result = array(
            "Round Number" => $i + 1, 
            "Round Time" => $this->roundTime,
            "Age" => $this->age,
            "Units" => $this->getRoundName(),
            "Total Vil Time" => $this->getTotalVillagerTime(),
            "Total Production Times" => $this->getTotalProductionTime(),
            //"Vil Time Per Second" => $this->getVilTimePerSecond(),
            //"Production Time Per Second" => $this->getProductionTimePerSecond(),
            // "UnitName" => $stat->unitName,
            // "UnitCount" => $stat->unitCount,
        );
        for ($i = 1; $i < 4; $i++) {
            $result["Prod Deficit $i"] = $this->getTotalProductionTime() - $this->roundTime*$i;
        }
        //print_r($result);
        //print("\n");
        return $result;
    }
}
?>