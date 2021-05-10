<?php
class UnitDatabase {
    private $CSV_NAME = "Data/unitJsonData.csv"; 
    private $STATS_CSV_NAME = "Data/structureData.csv"; 
    private $GATHERING_CSV = "Data/gatheringData.csv"; 

    private $STATS_URL = "https://aoe2stats.net/stats/dlc_units.json?1.0.1&_=1612509091453";
    private $STRUCTURE_URL = "https://aoe2stats.net/stats/dlc_structures.json?1.0.1&_=1615687802601";
    private $GATHERING_URL = "https://aoe2stats.net/stats/dlc_gathering.json?1.0.1&_=1615687802604";

    function getStatMap() {
        $csvData = file_get_contents($this->CSV_NAME);
        $lines = explode("\n", $csvData);
        $map = array();
        foreach ($lines as $line) {
            $raw = str_getcsv($line);
            $name = $raw[0];
            $foodCost = $raw[1];
            $woodCost = $raw[2];
            $goldCost = $raw[3];
            $createTime = $raw[4];
            if ($name) {
                $map[$name] = new UnitStats( 
                    $foodCost,
                    $woodCost, 
                    $goldCost, 
                    $createTime
                );
            }

        }
        return $map;
    }

    function refreshStructUrl() {
        $unitsHttpRespose = file_get_contents($this->STRUCTURE_URL);
        $unitData = json_decode($unitsHttpRespose)->data;
        $file = fopen($this->STATS_CSV_NAME, 'w');
        foreach ($unitData as $unit) {
            //print_r($unitData);
            if ($unit->cost != '-') {
                fputcsv($file, 
                [
                    $unit->name,
                    $this->parseCostString($unit->cost, "F"),
                    $this->parseCostString($unit->cost, "W"),
                    $this->parseCostString($unit->cost, "G"),
                    $this->parseCostString($unit->cost, "S"),
                ]);
            }
        }
        fclose($file);
    }

    function refreshGatheringUrl() {
        $gathering = file_get_contents($this->GATHERING_URL);
        $gatheringData = json_decode ($gathering)->data;
        $file = fopen($this->GATHERING_CSV, 'w');
        foreach ($gatheringData as $gatheringStat) {
            print_r($gatheringStat);
            fputcsv($file, 
            [
                $gatheringStat->type,
                $gatheringStat->source,
                $gatheringStat->speed,
                $gatheringStat->note
                // $this->parseCostString($gatheringStat->cost, "F"),
                // $this->parseCostString($gatheringStat->cost, "W"),
                // $this->parseCostString($gatheringStat->cost, "G"),
            ]);
        }
        fclose($file);
    }

    function refreshStats() {
        $unitsHttpRespose = file_get_contents($this->STATS_URL);
        $unitData = json_decode ($unitsHttpRespose)->data;
        $file = fopen($this->CSV_NAME, 'w');
        foreach ($unitData as $unit) {
            if ($unit->cost != '-') {
                fputcsv($file, 
                [
                    $unit->name,
                    $this->parseCostString($unit->cost, "F"),
                    $this->parseCostString($unit->cost, "W"),
                    $this->parseBuildTime($unit->bt),
                ]);
            }
        }
        fclose($file);
    }

    private function parseCostString(string $rawCosts, string $resourceFilter) {
        foreach (explode(" ", $rawCosts) as $cost) {
            if (strpos($cost, $resourceFilter)) {
                return rtrim($cost, $resourceFilter);
            }
        }
        return "0";
    }

    private function parseBuildTime(string $buildTime) {
        if (!$buildTime) return 0;
        $min_sec = explode(":", $buildTime);
        return $min_sec[0]*60 + $min_sec[1];
    }
}
?>