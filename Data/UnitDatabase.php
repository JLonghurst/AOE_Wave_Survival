<?php
class UnitDatabase {
    private $CSV_NAME = "Data/unitJsonData.csv"; 
    private $STATS_URL = "https://aoe2stats.net/stats/dlc_units.json?1.0.1&_=1612509091453";

    function getStatMap() {
        $csvData = fopen($CSV_NAME, 'w');
        $lines = explode(PHP_EOL, $csvData);
        $map = array();
        foreach ($lines as $line) {
            $raw = str_getcsv($line);
            $name = $raw[0];
            $cost = $raw[1];
            $foodCost = $raw[2];
            $woodCost = $raw[3];
            $goldCost = $raw[4];
    
            $map[$name] = new UnitStats(
                $foodCost, 
                $woodCost, 
                $goldCost, 
                $createTime
            );
        }
        return $map;
    }

    function refreshStats() {
        $unitsHttpRespose = file_get_contents($STATS_URL);
        $unitData = json_decode ($unitsHttpRespose)->data;
        $file = fopen($CSV_NAME, 'w');
        foreach ($unitData as $unit) {
            if ($unit->cost != '-') {
                $foodCost= parseCostString($unit->cost, "F");
                //print($foodCost);
                //$food = str_contains($costArray[0], "F");
                //print_r($food);
                //print_r($unit);
                fputcsv($file, 
                [
                    $unit->name,
                    parseCostString($unit->cost, "F"),
                    parseCostString($unit->cost, "W"),
                    parseCostString($unit->cost, "G"),
                    parseBuildTime($unit->bt),
                ]);
            }
        }
        fclose($file);
    }

    function parseCostString(string $rawCosts, string $resourceFilter) {
        foreach (explode(" ", $rawCosts) as $cost) {
            if (strpos($cost, $resourceFilter)) {
                return rtrim($cost, $resourceFilter);
            }
        }
        return 0;
    }

    function parseBuildTime(string $buildTime) {
        if (!$buildTime) return 0;
        $min_sec = explode(":", $buildTime);
        print_r($min_sec);
        return $min_sec[0]*60 + $min_sec[1];
    }
}
?>