<?php
spl_autoload_register(function ($class_name) {
    include $class_name . '.php';
});
function Scenario($serial) {
    global $DEBUG;
    $DEBUG = true;

    function wallAreas($areas) {
        $maxAtX = [];
        $minAtX = [];
        $allPts = [];
        foreach ($areas as $a) {
            foreach (AreaPts($a) as $p) {
                $p = Point::fromArr($p);
                if (!array_key_exists($p->x, $maxAtX)) 
                    $maxAtX[$p->x] = new Point(0, 0);
                
                if (!array_key_exists($p->x, $minAtX)) 
                    $minAtX[$p->x] = new Point(500, 500);
                $curMax = $maxAtX[$p->x];
                $curMin = $minAtX[$p->x];
                if ($p->y > $curMax->y) 
                        $maxAtX[$p->x] = $p;
                if ($p->y < $curMin->y) 
                        $minAtX[$p->x] = $p;
                
                array_push($allPts, $p);
            }
        }
        sort($maxAtX);
        sort($minAtX);
        $lastMax = $maxAtX[0];
        $diffIndex = [];
        foreach($maxAtX as $i => $max) {
            $diff = $max->y - $lastMax->y;
            if ($diff != 0) $diffIndex[$i] = $diff;
            $lastMax = $max;
        }
        $xDiffs = [];
        foreach ($diffIndex as $x => $diff) {
            $aMax = null;
            $aMin = null;
            if ($diff < 0) {
                $aMax = AreaPts($maxAtX[$x]->offset(0, 1)->areaFromOffset(0, -$diff));
                $aMin = AreaPts($minAtX[$x]->offset(0, -1)->areaFromOffset(0, $diff));
            } else {
                $aMax = AreaPts($maxAtX[$x - 1]->offset(0, 1)->areaFromOffset(0, $diff));
    
                $aMin = AreaPts($minAtX[$x - 1]->offset(0, -1)->areaFromOffset(0, -$diff));
            }
            $xDiffs = array_merge($xDiffs, $aMax);
            $xDiffs = array_merge($xDiffs, $aMin);
        }
        $res = array_merge(
            array_map(function($pt) {
                return $pt->offset(0, 1)->asArr();
            }, $maxAtX), 
            array_map(function($pt) {
                return $pt->offset(0, -1)->asArr();
            }, $minAtX),
            $xDiffs
        );
        foreach($res as $pt) {
            Efft_Create(0, U_OLD_STONE_HEAD, $pt);
            setCell($pt, TERRAIN_SHALLOWS);
        }
        return $res;
    }

    /**
     * 
     * 
     * MAIN
     * 
     * 
     **/
    if ($DEBUG) {
        SetPlayerStartFood(1, 10000);
        SetPlayerStartWood(1, 10000);
        SetPlayerStartGold(1, 10000);
        SetPlayerStartStone(1, 10000);
    } else {
        SetPlayerStartFood(1, 200);
        SetPlayerStartWood(1, 200);
        SetPlayerStartGold(1, 200);
        SetPlayerStartStone(1, 100);
    }
    SetAllTech(true);
    // terrain, width, height
    $regions = [
        new EnemySpawnZone(0, TERRAIN_SNOW, 31, 15),
        new PlayerRegion(0, TERRAIN_ROAD_FUNGUS, 31, 15),
        new TowerZone(0, TERRAIN_ROAD_BROKEN, 21, 21),
        new CombatBuildingZone(0, TERRAIN_ROAD, 21, 21),
        new StoreZone(0, TERRAIN_ROAD_BROKEN, 35, 12),
        new EcoZone(0, TERRAIN_GRASS_2, 30, 25),
        new HouseZone(0, TERRAIN_ROAD_BROKEN, 38, 4),
    ];
    $offsets = [0];
    foreach ($regions as $i => $region) 
        array_push($offsets, $offsets[$i] - $region->depth);
    $STORE_OFFSET = $offsets[4];

    $Y_LENGTH = 40;
    $MAP_OFFSET = 20;
    $corner = new Point(GetMapSize() - $MAP_OFFSET, $MAP_OFFSET);
    $origin = $corner->offset(0, round($Y_LENGTH / 2));
    $PLAYERS = [1, 3, 5, 7];
    $PLAYERS = [1];
    SetPlayersCount(2*count($PLAYERS));
    //Trig(uniqid()); foreach($PLAYERS as $p) Efft_RemoveO($p);
    foreach ($PLAYERS as $i => $playerId) {
        SetPlayerMaxPop($playerId, 200);
        SetPlayerStartAge($playerId, "Dark");
        SetPlayerDisabilitybuildingList($playerId, $GLOBALS['BANNED_BUILDINGS']);
        $storeOrigin = $origin->offset($STORE_OFFSET);
        $areas = [];
        foreach ($regions as $i => $region) {
            $region->setPlayerId($playerId);
            $region->setOrigin($origin->offset($offsets[$i]));
            $region->placeStoreTriggersAt($storeOrigin);
            $region->render();
            array_push($areas, $region->getArea());
        }

        $topRegion = $regions[0];
        // place walls
        $topA = $regions[0]->createAreaRow(1);
        $bottomA = $regions[count($regions) - 1]->createAreaRow(-1);
        Trig(uniqid());
            $topRegion->createInArea(U_OLD_STONE_HEAD, $topA, 0);
            $topRegion->createInArea(U_OLD_STONE_HEAD, $bottomA, 0);
            wallAreas($areas);
        $origin = $origin->offset(0, $Y_LENGTH);
    }
    // place global map revealers
    Trig('Map Revealers');
    foreach (AreaPts(Area(0, 0, GetMapSize(), GetMapSize())) as $p) {
        $i = $p[0];
        $j = $p[1];
        if ($i % 5 == 1 && $j % 5 == 1) 
        {
        Efft_Create(1, U_MAP_REVEALER, array($i, $j));
        } 
    }
    // Trig("Intro Prompt", 1, 0);
    //     //Efft_Research(1, T_TOWN_WATCH);
    //     Efft_ChangeView(1, array(37, 27));
    //     Efft_Display(25, 0, "Welcome to AOE2 Wave Survival. The Game will begin at 00:30, prepare for carnage");        
    //     Efft_Display(25, 1, "You have until 00:25 to choose your difficulty level before the default of Hero mode is selected");      
    //     Efft_Display(25, 2, "Task an infandry building in between the flags to select your difficulty level");
    // $chats = [
    //     "<GREEN> Noob mode selected. Very cute. We all start somewhere :)",
    //     "<GREEN> Easy Mode selected. Maybe one day you will be worth somethings",
    //     "<GREEN> Hero Mode Selected. Prepare for a true challange of your skills!"
    // ];
    // $game_buildings = [U_MILITIA, U_LONG_SWORDSMAN, U_CHAMPION];
    // $mode_choice_area = [array(37, 27), array(43, 27)];
    // $mode_select_area = [array(36, 22), array(44, 27)];
    // $mode_select_area_o = [array(36, 22), array(44, 26)];
    // foreach($game_modes as $mode) {
    //     //$time = 30; // current game time (30 for time to select game mode)
    //     $time = 0;
    //     $round_data_iter = new NeighborIterator(new ArrayIterator($ROUND_DATA));
    //     $i = 0;
    //     Trig($mode["relicName"] . " Mode Starter");
    //         //Cond_InAreaU(1, 1, $game_buildings[$i], $mode_choice_area);
    //         Efft_Chat(1, $chats[$i]);
    //         //Efft_KillY(1, Y_MILITARY, $mode_select_area);
    //         //Efft_KillO(0, $mode_select_area_o);
    //         Efft_Deact("Default Mode Starter");  
    //         foreach ($game_modes as $mode) 
    //         {
    //             if ($mode != $mode) 
    //             {
    //         Efft_Deact("Start " . $mode["relicName"] . " Round 0");
    //         Efft_Deact($mode["relicName"] . " Mode Starter");
    //             }
    //         }
    // }
    // Trig("Default Mode Starter", 1, 0);
    //     Cond_Timer(25);
    //     Efft_Display(10, 0, 
    //         "<RED> You didn't pick in time, so Hero mode was selected"
    //     );
    //     Efft_KillY(1, Y_MILITARY, $mode_select_area);
    //     Efft_KillO(0, $mode_select_area_o); 
    //     Efft_Deact("Start Noob Mode");
    //     Efft_Deact("Start Easy Mode");
    // Trig("Start $mode Mode");
    //     Cond_Timer(30);
    //     Efft_Chat(1, "$mode started");
    //     Efft_Act("$mode Round 0");
    //     Efft_Act("$mode Start Game");
    // // final round
    // Trig("$mode Round 40", 0, 0);
    //     Efft_Act("Round 40 Spawn");
    //     Efft_Give(1, $round["money"], STONE);
    //     Efft_Display(100, 3, "Round 40 final round");
    //     Efft_Act("Endless Starter");
    // Trig("Endless Starter", 0, 0);
    //     Cond_NotInAreaY(2, 1, Y_MILITARY, [array(0, 0), array(17, 90)]);
    //     Efft_Display(60, 0, "You have survived all 40 rounds! congrats");
    //     Efft_Display(1000, 1, "You are now entering endless mode.");
    //     Efft_Display(1000, 2, "Random sets of unique buildings will spawn until you die");
    //     Efft_Act("Initiate1");
    //     Efft_Act("Initiate2");
    // function End_Game_Sandbox() {
    //     $unique_spawn_data = $GLOBALS["unique_spawn_data"];

    //     Trig("Ship Gaia Convert");
    //         Efft_ChangeOwnerO(1, [array(85, 100), array(111, 118)], 0);

    //     $center1_X = 103;
    //     $center1_Y = 110;
    //     $spawns_1 = [array($center1_X + 1, $center1_Y), array($center1_X - 1, $center1_Y), 
    //             array($center1_X, $center1_Y - 1), array($center1_X, $center1_Y + 1)]; 

    //     $center2_X = 106;
    //     $center2_Y = 113;
    //     $spawns_2 = [array($center2_X + 1, $center2_Y), array($center2_X - 1, $center2_Y), 
    //             array($center2_X, $center2_Y - 1), array($center2_X, $center2_Y + 1)]; 

    //     Trig("Initiate1", 0, 1);
    //         Cond_Timer(60);
    //         Efft_RemoveO(0, array($center1_X - 1, $center1_Y - 1));
    //         Efft_Create(0, U_ARCHER, array($center1_X - 1, $center1_Y - 1));
    //         Efft_TaskO(0, array($center1_X - 1, $center1_Y - 1), array($center1_X, $center1_Y));
    //         Efft_UnloadO(0, array($center1_X, $center1_Y), array($center1_X, $center1_Y));
    //         Efft_Create(0, U_HAY_STACK, array($center1_X - 1, $center1_Y - 1)); 

    //     Trig("Initiate2", 0, 1);
    //         Cond_Timer(60);
    //         Efft_RemoveO(0, array($center2_X - 1, $center2_Y - 1));
    //         Efft_Create(0, U_ARCHER, array($center2_X - 1, $center2_Y - 1));
    //         Efft_TaskO(0, array($center2_X - 1, $center2_Y - 1), array($center2_X, $center2_Y));
    //         Efft_UnloadO(0, array($center2_X, $center2_Y), array($center2_X, $center2_Y));
    //         Efft_Create(0, U_HAY_STACK, array($center2_X - 1, $center2_Y - 1));
    //     $spawn_trig_relicNames = array();
    //     foreach ($unique_spawn_data as $unique_data) {
    //         array_push($spawn_trig_relicNames, $unique_data[0]);
    //         Trig($unique_data[0], 0, 0);
    //         Efft_Display(60, 0, $unique_data[0]);
    //             foreach($unique_data[2] as $spawn) {
    //                 Efft_Create(2, $unique_data[1], $spawn);
    //             }
    //     }
    //     $count = 0;
    //     for($i = 0; $i < 4; $i++) {
    //         for($j = 0; $j < 4; $j++) {
    //             Trig("Random $count", 1, 1);
    //                 Cond_InAreaO(0, 1, $spawns_1[$i]);
    //                 Cond_InAreaO(0, 1, $spawns_2[$j]);
    //                 Cond_Timer(3);
    //                 Efft_Act("Killer");
    //                 Efft_Act("Random $count Spawn");
    //             Trig("Random $count Spawn", 0, 0);
    //                 Efft_Chat(1, "trig $count"); 
    //                 Efft_Act($spawn_trig_relicNames[$count]);
    //             $count++;
    //         }
    //     }

    //     Trig("Killer", 0, 0);
    //         foreach(array_merge($spawns_1, $spawns_2) as $spawn) { 
    //             Efft_KillO(0, $spawn);
    //         }  
    // }
    // End_Game_Sandbox();
}
?>