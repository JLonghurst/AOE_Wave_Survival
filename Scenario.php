<?php
include('Data/wave_data.php');

function Scenario($serial) {
    global $DEBUG;
    $DEBUG = true;
    $tech_data = [
        new Tech(
            "Infandry Upgrade 1 (Feudal Upgrades)", 
            U_MAN_AT_ARMS,
            [200],  
            [T_FEUDAL_AGE],
            [T_MAN_AT_ARMS, T_SCALE_MAIL_ARMOR],
        ),
        new Tech(
            "Infandry Upgrade 2 (Castle Upgrades + 1 Barracks)", 
            U_LONG_SWORDSMAN,
            [400],  
            [T_CASTLE_AGE, T_SCALE_MAIL_ARMOR],
            [T_LONG_SWORDSMAN, T_PIKEMAN, T_EAGLE_WARRIOR, T_SCALE_MAIL_ARMOR],
        ),
        new Tech(
            "Infandry Upgrade 3 (Imperial Upgrades + 1 Barracks)", 
            U_CHAMPION,
            [800],  
            [T_IMPERIAL_AGE, T_SCALE_MAIL_ARMOR],
            [T_CHAMPION, T_TWO_HANDED_SWORDSMAN, T_SCALE_BARDING_ARMOR],
        ),
        new Tech(
            "Archer Upgrade 1 (Feudal Upgrades)", 
            U_ARCHER,
            [200],  
            [T_FEUDAL_AGE],
            [T_FLETCHING, T_LEATHER_ARCHER_ARMOR],
        ),
        new Tech(
            "Archer Upgrade 2 (Castle Upgrades + 1 Range)", 
            U_CROSSBOWMAN,
            [400],  
            [T_CASTLE_AGE, T_LEATHER_ARCHER_ARMOR],
            [T_BODKIN_ARROW, T_PADDED_ARCHER_ARMOR, T_CROSSBOWMAN, T_ELITE_SKIRMISHER],
        ),
        new Tech(
            "Archer Upgrade 3 (Imperial Upgrades + 1 Range)", 
            U_ARBALEST,
            [800],  
            [T_IMPERIAL_AGE, T_PADDED_ARCHER_ARMOR],
            [T_CAVALRY_ARCHER_A, T_ARBALEST, T_RING_ARCHER_ARMOR],
        ),
        new Tech(
            "Cavalry Upgrade 1 (Feudal Upgrades)", 
            U_SCOUT_CAVALRY,
            [200],  
            [T_FEUDAL_AGE],
            [T_FORGING, T_BLOODLINES, T_SCALE_BARDING_ARMOR ],
        ),
        new Tech(
            "Cavalry Upgrade 2 (Castle Upgrades + 1 Stable)", 
            U_KNIGHT,
            [400],  
            [T_CASTLE_AGE, T_LEATHER_ARCHER_ARMOR, T_SCALE_BARDING_ARMOR],
            [T_FORGING,  T_PLATE_BARDING_ARMOR],
        ),
        new Tech(
            "Cavalry Upgrade 3 (Imperial Upgrades + 1 Stable)", 
            U_PALADIN,
            [1000],  
            [T_IMPERIAL_AGE, T_PLATE_MAIL_ARMOR],
            [T_CAVALIER, T_PALADIN, T_HUSSAR, T_HEAVY_CAMEL, T_PLATE_BARDING_ARMOR],
        ),
    ];
    
    function placeObjectInArea($p, $area, $objectId) {
        foreach (AreaPts($area) as $point) {
            Efft_Create($p, $objectId, $point);
            setCell($point, TERRAIN_FARM);
        }
    }
    class Round {
        public $roundNum;
        // time until this round plays
        public $time;
        // payment from this round
        public $payment;
        // the units of the round. note: unit is of type [[unitId, unitCount]]
        public $units;
        // a pointer to the next round, or null if this is the last round
        public $nextPtr;

        function __construct($roundNum, $time, $payment, $units, $nextPtr) {
            $this->roundNum = $roundNum;
            $this->time = $time;
            $this->payment = $payment;
            $this->units = $units;
            $this->nextPtr = $nextPtr;
        }

        function placeRoundForPlayer($p, $enemyId, $point) {
            //Trig(uniqid(), 1, 0);
            $id = uniqid();
            $name = nameFromUnit($this->units);
            Trig("{$id} {$name}", 1, 0);
            Cond_Timer($this->time);
            // spawn the units of the round
            foreach((array)$this->units as $i => $unit) {
                $unitId = $unit[0];
                $unitSize = $unit[1] - 1;
                placeObjectInArea($enemyId, Area(
                    $point[0]  + $i, 
                    $point[1] - $unitSize / 2, 
                    $point[0]  + $i, 
                    $point[1] + $unitSize / 2
                ), $unitId);
            }
            // give the play goodies
            Efft_Give($p, $this->payment, STONE);
            Efft_Chat($p, "<GREEN> {$this->payment} stone for round advancement");
            Efft_Chat($p, "<RED> Round {$this->roundNum}: {$name}");
            // display future round info if it exists
            if ($this->nextPtr != null)
            {
                $nextMin = floor($this->nextPtr->time / 60);
                $nextSec = $this->nextPtr->time % 60;
                if ($nextMin < 10)  $nextMin =  "0{$nextMin}"; 
                if ($nextSec < 10)  $nextSec =  "0{$nextSec}";
                $roundDiff = $this->nextPtr->time - $this->time;
                $nextName = nameFromUnit($this->nextPtr->units);
                Efft_Display($roundDiff, 0, 
                    "<RED> Round {$this->nextPtr->roundNum} " . 
                    "begins at $nextMin:$nextSec " .
                    "in {$roundDiff} seconds. \n\n Next: {$nextName}"
                );
            }
        }
    }

    class Tech {
        public $origin;
        public $name; 
        public  $costs; 
        public  $requirements; 
        public  $unitId; 
        // nullable, can make a trigger optionally
        public $triggerNames;
        
        function __construct($name, $unitId, $costs, $requirements, $triggerNames) {
            $this->name = $name;
            $this->origin = $origin;
            $this->unitId = $unitId;
            $this->costs = $costs;
            $this->requirements = $requirements;
            $this->triggerNames= $triggerNames;
        }

        function setOrigin($origin) {
            $this->origin = $origin;
        }

        function placeAtLocation($p) {
            if ($this->origin == null) {
                print("FUCK U NEED AN ORIGIN ");
            }
            // give meaningful names to data array
            // x is offset by 2 on map
            // lan    $Length_Xs are 2
            $headLocation = $this->origin;
            $relicLocation = array($headLocation[0] - 2, $headLocation[1]);
            $unitLocation = array($headLocation[0] - 1, $headLocation[1]);
            $killLocation = array($headLocation[0] + 1, $headLocation[1]);

            $size = count($this->costs);
            // one time event
            Trig(uniqid(), 1, 0);
                Efft_Create(0, U_RELIC, $relicLocation);
                /// intiially its locked   
                Efft_Create(0, U_OLD_STONE_HEAD, $headLocation); 
            foreach($this->costs as $i => $cost) {
                Efft_Create($p, $this->unitId, $unitLocation);
                Efft_NameU(0, U_RELIC, "{$this->name} ($cost stone)", $headLocation);

                Trig("P:$p {$this->name} $i", 1, 0);
                Cond_Timer(2); // debounce the last purchase
                Cond_Accumulate($p, $cost, R_STONE_STORAGE);
                Cond_InAreaU($p, 1, $this->unitId, $killLocation);
                Efft_KillU($p, $this->unitId, $killLocation);
                Efft_Tribute($p, $cost, R_STONE_STORAGE, 0);
                foreach ($this->triggerNames as $trigName) 
                    Efft_Act("$trigName");
                // place down for another round if it exists
                if ($i != $size - 1) {
                    $next = $i + 1;
                    Efft_Act("P:$p {$this->name} $next");
                }
            }
            Trig("{$p} Block Kill Trigger {$this->name}");
                foreach ($this->requirements as $req) 
                Cond_Researched($p, $req);
                Efft_RemoveU(0, U_OLD_STONE_HEAD, $headLocation);
        }
    }

    class Zone {
        public $playerId;
        public $terrainId;
        public $origin;
        public $yOffset;
        public $xOffset;

        public function __construct($playerId, $origin, $xOffset, $yOffset, $terrainId = TERRAIN_DESERT) {
            $this->playerId = $playerId;
            $this->origin = $origin;
            $this->xOffset = $xOffset;
            $this->yOffset = $yOffset;
            $this->terrainId = $terrainId;
        }

        public function getArea() {
            return AreaAdvanced($this->origin, 'N', $this->yOffset, $this->xOffset);
        }

        public function trig($name, $on = 1) {
            $name = "{$this->playerId} $name";
            Trig($name, $on);
            return $name;
        }

        public function getEnemyId() {
            return $this->playerId + 4;
        }

        public function getCenter() {
            return array($this->origin[0] - round($this->xOffset / 2), $this->origin[1]);
        }

        // renders this zone for a player
        public function run() {
            foreach(AreaPts($this->getArea()) as $pt) 
                setCell($pt, $this->terrainId);
            return [$this->xOffset, $this->yOffset];
        }
    }

    class WalledZone extends Zone {
        public function __construct($playerId, $origin, $xOffset, $yOffset, $tId = TERRAIN_ROCK_ROAD) {
            parent::__construct($playerId, $origin, $xOffset, $yOffset, $tId);
        }
        public function run() {
            $this->placeWall();
            return parent::run();
        }
        public function placeWall() {
            Trig(uniqid() . ' Surround Walls', 1, 0);
                placeObjectInArea(0, offAreaYUp($this->getArea(), 1), U_OLD_STONE_HEAD);
                placeObjectInArea(0, offAreaYDown($this->getArea(), 1), U_OLD_STONE_HEAD);
        }
    }

    class EnemySpawnZone extends WalledZone {
        function __construct($p, $pArea, $terrainId = TERRAIN_SNOW) {
            parent::__construct($p, $pArea, 20, 31, $terrainId);
        }
        
        function run() {
            $this->killZoneTriggers();
            $rounds = $this->getRounds();
            foreach($rounds as $round) {
                $round->placeRoundForPlayer($this->playerId, $this->getEnemyId(), $this->getCenter());
            }
            return parent::run();
        }

        private function killZoneTriggers() {
            // Creates Kill Zone in the area where enemyId buildings spawn
            Trig("{$this->playerId} Kill Zone", 1, 1);
            Cond_InAreaY($this->playerId, Y_MILITARY, 1, $this->getArea());
            Efft_Chat($this->playerId, "<RED> No player units are not allowed in the enemyId spawning area");
            Efft_KillY($this->playerId, Y_MILITARY, $this->getArea());
            if ($GLOBALS['DEBUG']) {
                Trig("{$this->playerId} Kill Zone Enemy", 1, 1);
                    Cond_InAreaY($this->playerId, 1, Y_MILITARY, $this->getArea());
                    Efft_KillY($this->playerId, Y_MILITARY, $this->getArea());
            }
        }
        
        public function placeWall() {
            parent::placeWall();
            placeObjectInArea(0, offAreaXRight($this->getArea(), 1), U_OLD_STONE_HEAD);
        }

        public function getRounds() {
            $time = 15;
            $rounds = array();
            foreach($GLOBALS['UNITS'] as $i => $unit) {
                $round = new Round($i + 1, $time, $time, $unit, null);
                array_push($rounds, $round);
                $time += 45;
            }
            for ($i = 0; $i < count($rounds) - 2; $i++)
                $rounds[$i]->nextPtr = $rounds[$i+1];
            return $rounds;
        }
    }

    class EnemyPathZone extends WalledZone {
        function __construct($p, $pArea, $terrainId = TERRAIN_DIRT_1) {
            parent::__construct($p, $pArea, 30, 31, $terrainId);
        }
    }

    class TowerZone extends WalledZone {
        function __construct($p, $area) {
            parent::__construct($p, $area, 21, 21, TERRAIN_ROAD_BROKEN);
        }
        function run() {
            $this->placeTower($this->getCenter());
            return parent::run();
        }
        function placeTower($point) {
            setCell($point, TERRAIN_SNOW);
            $e = 5;
            for ($i = 1; $i < $e; $i++)
                foreach (AreaPts(AreaSet($point, $e + 2 - $i)) as $p) 
                    setElevation($p, $i);
            
            Trig("{$this->playerId} Town Center");
                Efft_RemoveO($this->getEnemyId());
                Efft_Create($this->getEnemyId(), U_TOWN_CENTER, [$point[0], $point[1] + 9]);
            
            Trig("{$this->playerId} Tower Placement", 1, 0);
                Efft_Create($this->playerId, U_WATCH_TOWER, $point);
                Efft_Act("{$this->playerId} Tower Death");

                // Town Center is indestrucable
            Trig("{$this->playerId} EnemyId Town Center Invunerable", 1, 1);
                Efft_InvincibleU($this->getEnemyId(), U_TOWN_CENTER);

            Trig("{$this->playerId} Tower Death", 0, 0, 1, "111", "Do not let your tower be destroyed by the enemyId buildings");
                Cond_NotOwnU($this->playerId, 1, U_WATCH_TOWER);
                Efft_Chat($this->playerId, "<RED> You lost your tower! gg fam");
                Efft_Display(10, 0, "<RED> You lost your tower! gg fam");
                Efft_Display(10, 1, "<RED> You lost your tower! gg fam");
                Efft_Display(10, 2, "<RED> You lost your tower! gg fam");
                Efft_Act("{$this->playerId} End Game Chat 1");
            
            Trig("{$this->playerId} Game Over", 0, 0);
                Cond_Timer(6);
                Efft_DeclareVictory($this->getEnemyId());
                
            Trig("{$this->playerId} End Game Chat 2", 0, 0);
                Cond_Timer(5);
                Efft_Chat($this->playerId, 27);
                Efft_Act("{$this->playerId} Game Over");
            
            Trig("{$this->playerId} End Game Chat 1", 0, 0);
                Cond_Timer(3);
                Efft_Chat($this->playerId, 26);
                Efft_Act("{$this->playerId} End Game Chat 2");
                

            Trig("{$this->playerId} Tower Health Regain", 1, 1);
                Cond_Timer(1);
                Efft_DamageY($this->playerId, -1, Y_BUILDING, $point);
            // Trig("{$this->playerId} Tower Health Regain x10", 0, 1);
            //     Cond_Timer(1);
            //     Efft_DamageY($this->playerId, -10, Y_BUILDING, $point);
        }
    }

    class CombatBuildingZone extends WalledZone {
        function __construct($p, $origin, $tId = TERRAIN_ROAD) {
            parent::__construct($p, $origin, 21, 21, $tId);
        }
        function run() {
            $filteredPoints = array();
            foreach (AreaPts($this->getArea()) as $point) {
                if (abs($point[0] - $this->getCenter()[0]) % 6 == 0 && abs($point[1] - $this->getCenter()[1]) % 6 == 0) {
                    setCell($point, TERRAIN_SNOW);
                    array_push($filteredPoints, $point);
                } 
            }
            Trig(uniqid() . " PLACE", 1, 0);
            foreach($filteredPoints as $pt) {
                if ($pt[0] == $this->getCenter()[0]) {
            // middle
            Efft_Create($this->playerId, U_BARRACKS, $pt);
                } else if ($pt[0] < $this->getCenter()[0]) {
            // left
            Efft_Create($this->playerId, U_STABLE, $pt);
                } else {
            // right
            Efft_Create($this->playerId, U_ARCHERY_RANGE, $pt);
                }
            }
            return parent::run();
        }
        public function placeWall() {
            parent::placeWall();
            placeObjectInArea(0, offAreaYDown($this->getArea(), 1), U_OLD_STONE_HEAD);
        }
    }

    class StoreZone extends WalledZone {
        function __construct($p, $origin, $tId = TERRAIN_ROAD_BROKEN) {
            parent::__construct($p, $origin, 9, 35, $tId);
        }
        function run() {
            $name = $this->trig("YAH BOI");
                Efft_Chat($this->playerId, "FUUUUCK");
            $tech = new Tech(
                "My Name Yeet",
                $this->origin,
                U_MILITIA,
                [100, 200, 300],
                [T_FEUDAL_AGE],
                [$name]
            );
            $tech->origin = $this->origin;
            $tech->placeAtLocation($this->playerId);
            return parent::run();
        }
        public function placeWall() {
            parent::placeWall();
            placeObjectInArea(0, offAreaYDown($this->getArea(), 1), U_OLD_STONE_HEAD);
        }
    }

    class HouseZone extends WalledZone {
        function __construct($p, $origin, $tId = TERRAIN_DESERT) {
            parent::__construct($p, $origin, 9, 30, $tId);
        }
        function run() {
            $this->trig("House Place");
                placeObjectInArea($this->playerId, $this->getArea(), U_HOUSE);
            return parent::run();
        }
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
        SetPlayerStartStone(1, 200);
    }
    SetAllTech(true);
    SetPlayersCount(8);
    $MAP_OFFSET = 15;
    $o = array(GetMapSize() - $MAP_OFFSET, $MAP_OFFSET);
    $X_FIXED = $o[0];
    $Y_LENGTH = 51;
    $o[1] += round($Y_LENGTH / 2); 

    foreach ([1, 2, 3, 4] as $i => $playerId) {
    }
    $PLAYERS = [1, 2, 3, 4];
    $PLAYERS = [1];
    foreach ($PLAYERS as $i => $playerId) {
        SetPlayerMaxPop($playerId, 200);
        SetPlayerStartAge($playerId, "Imperial");
        SetPlayerDisabilitybuildingList($playerId, $GLOBALS['BANNED_BUILDINGS']);
        $enemyZone = new EnemySpawnZone($playerId, $o);
        $o[0] -= $enemyZone->run()[0];
        $pathZone = new EnemyPathZone($playerId, $o);
        $o[0] -= $pathZone->run()[0];
        $towerZone = new TowerZone($playerId, $o);
        $o[0] -= $towerZone->run()[0];
        $combatBuildingZone = new CombatBuildingZone($playerId, $o);
        $o[0] -= $combatBuildingZone->run()[0];
        $StoreZone = new StoreZone($playerId, $o);
        $o[0] -= $StoreZone->run()[0];
        $HouseZone = new HouseZone($playerId, $o);
        $o[0] -= $HouseZone->run()[0];
        $o[1] += $Y_LENGTH;
        $o[0] = $X_FIXED;
    }
    //$lastRound = null;

    
    // $techs->placeAtLocation(1, array(125, 125));
    // Trig("Intro Prompt", 1, 0);
    //     //Efft_Research(1, T_TOWN_WATCH);
    //     Efft_ChangeView(1, array(37, 27));
    //     Efft_Display(25, 0, "Welcome to AOE2 Wave Survival. The Game will begin at 00:30, prepare for carnage");        
    //     Efft_Display(25, 1, "You have until 00:25 to choose your difficulty level before the default of Hero mode is selected");      
    //     Efft_Display(25, 2, "Task an infandry building in between the flags to select your difficulty level");
    
    // place global map revealers
    Trig('Map Revealers');
    foreach (AreaPts(Area(0, 0, GetMapSize(), GetMapSize())) as $point) {
        $i = $point[0];
        $j = $point[1];
        if ($i % 5 == 1 && $j % 5 == 1) 
        {
        Efft_Create(1, U_MAP_REVEALER, array($i, $j));
        } 
    }


   
   
   
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
    //     Trig($mode["name"] . " Mode Starter");
    //         //Cond_InAreaU(1, 1, $game_buildings[$i], $mode_choice_area);
    //         Efft_Chat(1, $chats[$i]);
    //         //Efft_KillY(1, Y_MILITARY, $mode_select_area);
    //         //Efft_KillO(0, $mode_select_area_o);
    //         Efft_Deact("Default Mode Starter");  
    //         foreach ($game_modes as $mode) 
    //         {
    //             if ($mode != $mode) 
    //             {
    //         Efft_Deact("Start " . $mode["name"] . " Round 0");
    //         Efft_Deact($mode["name"] . " Mode Starter");
    //             }
    //         }

    // }


    


    // function storeTriggers() {
    //     global $tower_location;
    //     Trig("Vil Spawn 5", 0, 0);
    //     //Efft_ChangeView(1, array(37, 12));
    //     $i = 0;
    //     foreach(Spawn_Box(array(38, 27), array(42, 27)) as $spawn) {
    //         ($i % 2 == 0) ? $building =  U_VILLAGER_M : $building =  U_VILLAGER_F;
    //         Efft_Create(1, $building, $spawn);
    //         $i++;
    //     }         
        
    //     Trig("Bomb Area", 0, 0);
    //         Efft_KillO(2, [array(6, 7), array(10, 11)]);
    //         Efft_ChangeView(1, $tower_location);
    //         foreach (Spawn_Box(array(6, 7), array(10, 11)) as $spawn) {
    //             Efft_Create(0,U_MACAW, $spawn);
    //             Efft_KillU(0,U_MACAW, $spawn);
    //         }
            
    //     Trig("Castle Creation", 0, 0);
    //         // remove stone heads
    //         Efft_RemoveO(0, [array(9, 0), array(12, 3)]);
    //         // place castle
    //         Efft_Create(1, U_CASTLE, array(11, 2));
            
    //     Trig("Tower Upgrade 1", 0, 0);
    //         Efft_Deact("Tower Health Regain");
    //         Efft_Act("Tower Health Regain x4");
    //         Efft_Research(1, T_MURDER_HOLES);
    //         Efft_RangeY(1, 2, Y_BUILDING, $tower_location);
    //         Efft_APY(1, 5, Y_BUILDING, $tower_location);
           
    //     Trig("Tower Upgrade 2", 0, 0);   
    //         Efft_Deact("Tower Health Regain x4");
    //         Efft_Act("Tower Health Regain x10");
    //         Efft_RangeY(1, 5, Y_BUILDING, $tower_location);
    //         Efft_APY(1, 5, Y_BUILDING, $tower_location); 
       
    //     Trig("Tower Upgrade 3", 0, 0);   
    //         Efft_Deact("Tower Health Regain x4");
    //         Efft_Act("Tower Health Regain x10");
    //         Efft_RangeY(1, 5, Y_BUILDING, $tower_location);
    //         Efft_APY(1, 5, Y_BUILDING, $tower_location);   

    //     Trig("Tower Health Regain x4", 0, 1);
    //         Cond_Timer(1);
    //         Efft_DamageY(1, -4, Y_BUILDING, $tower_location); 
            
    //     Trig("Tower Health Regain x10", 0, 1);
    //         Cond_Timer(1);
    //         Efft_DamageY(1, -10, Y_BUILDING, $tower_location);  
            
    //     Trig("Add 1000 Health", 0, 0);
    //         Efft_HPY(1, 1000, Y_BUILDING, $tower_location);
    // }
    // storeTriggers();

    // Hay Stack Effect Triggers

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
    //     $spawn_trig_names = array();
    //     foreach ($unique_spawn_data as $unique_data) {
    //         array_push($spawn_trig_names, $unique_data[0]);
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
    //                 Efft_Act($spawn_trig_names[$count]);
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