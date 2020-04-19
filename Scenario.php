<?php
include('Data/wave_data.php');

function Scenario($serial) {
    global $DEBUG;
    $DEBUG = true;

    class PlayerContext {
        public $playerId;

        function __construct($playerId) {
            $this->playerId = $playerId;
        }

        public function setPlayerId($playerId) {
            $this->playerId = $playerId;
        }

        public function getName($name) {
            return "{$this->playerId}: $name";
        }

        public function trig($name, $S = 1, $L = 0, $P = 0, $E = 0, $D = '', $R = '') {
            $name = $this->getName($name);
            Trig($name, $S, $L, $P, $E, $D, $R);
            return $name;
        }

        public function getEnemyId() {
            return $this->playerId + 1;
        }

        public function act($name) {
            Efft_Act($this->getName($name));
        }

        public function chat($text) {
            Efft_Chat($this->playerId, $text);
        }

        // pt is of type pt
        public function create($objectId, $pt, $playerId = NULL) {
            if (!$playerId) $playerId = $this->playerId;
            Efft_Create($playerId, $objectId, $pt->asArr());
            setCell($pt->asArr(), TERRAIN_SNOW_DIRT_BUILDING_RESIDUE);
        }

        // pt is of type pt
        public function createGaia($objectId, $pt) {
            Efft_Create(0, $objectId, $pt->asArr());
            setCell($pt->asArr(), TERRAIN_SNOW_DIRT_BUILDING_RESIDUE);
        }

        function createInArea($objectId, $area, $playerId = NULL) {
            foreach (AreaPts($area) as $pt) 
                if ($playerId === 0) 
                    $this->createGaia($objectId, new Point($pt[0], $pt[1]));
                else 
                    $this->create($objectId, new Point($pt[0], $pt[1]), $playerId);
        }
    }
    
    class Point {
        public $x;
        public $y;

        function __construct($x, $y) {
            $this->x = $x;
            $this->y = $y;
        }

        function offset($dx, $dy = 0) {
            return new Point($this->x + $dx, $this->y + $dy);
        }

        function asArr() {
            return array($this->x, $this->y);
        }

        /**
         * Returns the area created by the bounding box
         * of the two points
         *
         * @param Point $p2
         * @return Area
         */
        public function areaFromP2($p2) {
            return Area($this->x, $this->y, $p2->x, $p2->y);
        }
        
        public function areaFromOffset($dx, $dy = 0) {
            return $this->areaFromP2($this->offset($dx, $dy));
        }
    }

    class PlayerRegion extends PlayerContext {
        public $orientation = 'N';
        public $terrainId;
        /**
         * @var Point $origin: the origin of the areaRegion
         */
        public $origin;
        public $width;
        public $depth;
        /**
         * Creates a new PlayerRegion
         *
         * @param int $playerId - the playerId of the region
         * @param int $terrainId - the terrain of the region
         * @param int $width - the width of the region
         * @param int $depth
         */
        function __construct($terrainId, $width, $depth) {
            $this->terrainId = $terrainId;
            $this->width = $width;
            $this->depth = $depth;
        }
        
        public function setOrigin($oPt) {
            $this->origin = $oPt;
        }

        public function setWidth($width) {
            $this->width = $width;
        }
    
        public function setDepth($depth) {
            $this->depth = $depth;
        }

        public function getArea() {
            return AreaAdvanced($this->origin->asArr(), $this->orientation, $this->width, $this->depth);
        }

        public function getAreaWithWidth($width) {
            return AreaAdvanced($this->origin->asArr(), $this->orientation, $width, $this->depth);
        }

        public function getCenter() {
            return $this->origin->offset(round(-$this->depth / 2) + 1);
        }
        
        public function getZoneEnd() {
            return $this->origin->offset(-$this->depth);
        }

        public function createAreaRow($offsetX, $origin = null, $width = null) {
            $origin = $origin != null ? $origin : $this->origin;
            $origin = $origin->offset($offsetX);
            $width = $width != null ? $width : $this->width;
            return AreaAdvanced($origin->asArr(), $this->orientation, $width, 1);
        }

        // renders this zone for a player
        public function run() {
            foreach(AreaPts($this->getArea()) as $pt) 
                setCell($pt, $this->terrainId);
        }
    }

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
        function __construct($playerId, $roundNum, $time, $payment, $units) {
            parent::__construct($playerId, null, 0, 0);
            $this->roundNum = $roundNum;
            $this->time = $time;
            $this->payment = $payment;
            $this->units = $units;
        }

        function runWave() {
            $name = nameFromUnit($this->units);
            $this->trig($name, 1, 0);
                Cond_Timer($this->time);
            // give the play goodies
            Efft_Give($this->playerId, $this->payment, STONE);
            $this->chat("<GREEN> {$this->payment} stone for round advancement");
            $this->chat("<RED> Round {$this->roundNum}: {$name}");
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

    class EnemySpawnZone extends PlayerRegion {
        function run() {
            parent::run();
            $this->killZoneTriggers();
            $time = 5;
            $waves = array();
            foreach($GLOBALS['UNITS'] as $i => $unit) {
                $wave = new EnemyWave($this->playerId, $i + 1, $time, $time, $unit);
                array_push($waves, $wave);
                $time += 5;
            }
            for ($i = 0; $i < count($waves) - 2; $i++) {
                $waves[$i]->nextTime = $waves[$i+1]->time;
                $waves[$i]->nextUnits = $waves[$i+1]->units;
            }
            foreach($waves as $wave) {
                $wave->origin = $this->origin;
                $wave->setWidth($this->width);
                $wave->setDepth($this->depth);
                $wave->setPlayerId($this->playerId);
                $wave->runWave();
            }
        }

        private function killZoneTriggers() {
            // Creates Kill Zone in the area where enemyId buildings spawn
            $this->trig("Kill Zone", 1, 1);
            Cond_InAreaY($this->playerId, Y_MILITARY, 1, $this->getArea());
            $this->chat("<RED> No player units are not allowed in the enemyId spawning area");
            Efft_KillY($this->playerId, Y_MILITARY, $this->getArea());
            if ($GLOBALS['DEBUG']) {
                $this->trig("Kill Zone Enemy", 1, 1);
                    Cond_InAreaY($this->playerId, 1, Y_MILITARY, $this->getArea());
                    Efft_KillY($this->playerId, Y_MILITARY, $this->getArea());
            }
        }
    }

    class TowerZone extends PlayerRegion {
        function run() {
            parent::run();
            $this->setTowerElevation();

            $this->trig("Enemy Town Center");
                Efft_RemoveO($this->getEnemyId());
                $this->create(U_TOWN_CENTER, $this->getCenter()->offset(0, 9), $this->getEnemyId());
                Efft_InvincibleU($this->getEnemyId(), U_TOWN_CENTER);
            $this->trig("Tower Placement", 1, 0);
                $this->create(U_WATCH_TOWER, $this->getCenter());
                $this->act("Tower Death");
            $this->trig("Tower Death", 0, 0, 1, "111", "Do not let your tower be destroyed by the enemyId buildings");
                Cond_Timer(3);
                Cond_NotOwnU($this->playerId, 1, U_WATCH_TOWER);
                $this->chat("<RED> You lost your tower! gg fam");
                Efft_Display(10, 0, "<RED> You lost your tower! gg fam");
                Efft_Display(10, 1, "<RED> You lost your tower! gg fam");
                Efft_Display(10, 2, "<RED> You lost your tower! gg fam");
                $this->act("End Game Chat 1");
                $this->act("End Game Chat 2");
            $this->trig("End Game Chat 1", 0);
                Cond_Timer(5);
                $this->chat(26);
            $this->trig("End Game Chat 2", 0);
                Cond_Timer(5);
                $this->chat(27);
                $this->act("Game Over");
            $this->trig("Game Over", 0);
                Cond_Timer(6);
                Efft_DeclareVictory($this->getEnemyId());
            $this->trig("Tower Health Regain", 1, 1);
                Cond_Timer(1);
                Efft_DamageY($this->playerId, -1, Y_BUILDING, $this->getCenter()->asArr());
        }

        private function setTowerElevation() {
            $e = 5;
            for ($i = 1; $i < $e; $i++)
                foreach (AreaPts(AreaSet($this->getCenter()->asArr(), $e + 2 - $i)) as $p) 
                    setElevation($p, $i);
        }
    }

    class CombatBuildingZone extends PlayerRegion {
        private $DISTANCE = 6;
        function run() {
            parent::run();
            $this->trig("Initial Placement");
                $this->create(U_ARCHERY_RANGE, $this->origin->offset(-$this->DISTANCE + 1));
                $this->create(U_BARRACKS, $this->origin->offset(-$this->DISTANCE + 1, -$this->DISTANCE));
                $this->create(U_STABLE, $this->origin->offset(-$this->DISTANCE + 1, $this->DISTANCE));
        }

        function getStoreTriggers($techData) {
        }
    }

    class StoreZone extends PlayerRegion {
        function run() {
            $name = $this->trig("YAH BOI");
                $this->chat("FUUUUCK");
            $tech = new Tech(
                "My Name Yeet",
                $this->origin,
                U_MILITIA,
                [100, 200, 300],
                [T_FEUDAL_AGE],
                [$name]
            );
            $tech->origin = $this->origin;
            //$tech->placeAtLocation($this->playerId);
            parent::run();
        }
    }

    class HouseZone extends PlayerRegion {
        function run() {
            $this->trig("House Place");
                $this->createInArea(U_HOUSE, $this->getArea());
            parent::run();
        }
    }

    class EcoZone extends PlayerRegion {
        private $goldOffset = 10;

        function run() {
            parent::run();
            $goldOffset = $this->getCenter()->offset(0, -$this->goldOffset);
            $lumberCamp = $this->getZoneEnd()->offset(4);
            $treeArea = $this->createAreaRow(1, $this->getZoneEnd());
            $goldArea = AreaSet($goldOffset->asArr());
            $this->trig("Initial Eco Placement");
                $this->create(U_TOWN_CENTER, $this->getCenter());
                $this->create(U_MINING_CAMP, $goldOffset->offset(-3));
                $this->create(U_LUMBER_CAMP, $lumberCamp);
                $this->createInArea(U_OLD_STONE_HEAD, $this->createAreaRow(0), 0);
                $this->act("Create 5 Vils");
            $this->trig("Gold Placement", 1, 1);
                $this->createInArea(U_GOLD_MINE, $goldArea, 0);
            $this->trig("Tree Placement", 1, 1);
                $this->createInArea(U_TREE_A, $treeArea, 0);
            
            $this->getStoreTriggers();
        }

        function getStoreTriggers() {
            $vilSpawnArea = $this->createAreaRow(1, $this->origin->offset(-3), 5);
            $createVilTech = new Tech(
                "Create 5 Vils",
                U_VILLAGER_M,
                [100, 200, 400, 800],
                null,
                $this->trig("Create 5 Vils"),
                null
            );
                $this->createInArea(U_VILLAGER_M, $vilSpawnArea);
            $createVilTech->setOrigin($this->origin->offset(3));
            $createVilTech->placeAtLocation($this->playerId);
        }
    }

    class Tech extends PlayerRegion {
        public $name; 
        public $costs; 
        public $requirements; 
        public $unitId; 
        public $buildingId;
        // nullable, can make a trigger optionally
        public $triggerName;
        
        public $WALL_MATERIAL = U_OLD_STONE_HEAD;

        function __construct($name, $unitId, $costs, $requirements, $triggerName, $buildingId) {
            $this->name = $name;
            $this->unitId = $unitId;
            $this->costs = $costs;
            $this->requirements = $requirements;
            $this->triggerName = $triggerName;
            $this->buildingId = $buildingId;
        }

        function placeAtLocation($p) {
            $this->setPlayerId($p);
            if ($this->origin == null) {
                print("FUCK U NEED AN ORIGIN ");
            }
            // give meaningful names to data array
            // x is offset by 2 on map
            // lan    $Length_Xs are 2
            $headLocation = $this->origin;
            $relicLocation = $this->origin->offset(-2);
            $unitLocation = $this->origin->offset(-1);
            $killLocation = $this->origin->offset(1);

            $size = count($this->costs);
            // one time event
            Trig(uniqid(), 1, 0);
                Efft_Act("P:$p {$this->name} 0");
                $this->createGaia(U_RELIC, $relicLocation);
            foreach($this->costs as $i => $cost) {
                $this->create($this->unitId, $unitLocation);
                Efft_NameU(0, U_RELIC, "{$this->name} ($cost stone)", $headLocation->asArr());

                Trig("P:$p {$this->name} $i", 0, 0);
                    Cond_Timer(2); // debounce the last purchase
                    Cond_Accumulate($p, $cost, R_STONE_STORAGE);
                    Cond_InAreaU($p, 1, $this->unitId, $killLocation->asArr());
                    Efft_KillU($p, $this->unitId, $killLocation->asArr());
                    Efft_Tribute($p, $cost, R_STONE_STORAGE, 0);
                    Efft_Act($this->triggerName);
                // place down for another round if it exists
                if ($i != $size - 1) {
                    $next = $i + 1;
                    Efft_Act("P:$p {$this->name} $next");
                }
            }
            $this->trig(uniqId());
                /// will place wall over blocked location
                $this->createInArea($this->WALL_MATERIAL, AreaSet($unitLocation->asArr()), 0);
                $this->createInArea($this->WALL_MATERIAL, AreaSet($killLocation->asArr()), 0);
                Efft_RemoveO(0, $killLocation->asArr());
            Trig("{$p} Block Kill Trigger {$this->name}");
                if (is_array($this->requirements))
                    foreach ($this->requirements as $req) 
                        Cond_Researched($p, $req);
                Efft_RemoveU(0, U_OLD_STONE_HEAD, $headLocation->asArr());
        }
    }
    $TECH_DATA = [
        new Tech(
            "Infandry Upgrade 1 (Feudal Upgrades)", 
            U_MAN_AT_ARMS,
            [200],  
            [T_FEUDAL_AGE],
            [T_MAN_AT_ARMS, T_SCALE_MAIL_ARMOR],
            U_BARRACKS
        ),
        new Tech(
            "Infandry Upgrade 2 (Castle Upgrades + 1 Barracks)", 
            U_LONG_SWORDSMAN,
            [400],  
            [T_CASTLE_AGE, T_SCALE_MAIL_ARMOR],
            [T_LONG_SWORDSMAN, T_PIKEMAN, T_EAGLE_WARRIOR, T_SCALE_MAIL_ARMOR],
            U_BARRACKS
        ),
        new Tech(
            "Infandry Upgrade 3 (Imperial Upgrades + 1 Barracks)", 
            U_CHAMPION,
            [800],  
            [T_IMPERIAL_AGE, T_SCALE_MAIL_ARMOR],
            [T_CHAMPION, T_TWO_HANDED_SWORDSMAN, T_SCALE_BARDING_ARMOR],
            U_BARRACKS
        ),
        new Tech(
            "Archer Upgrade 1 (Feudal Upgrades)", 
            U_ARCHER,
            [200],  
            [T_FEUDAL_AGE],
            [T_FLETCHING, T_LEATHER_ARCHER_ARMOR],
            U_ARCHERY_RANGE
        ),
        new Tech(
            "Archer Upgrade 2 (Castle Upgrades + 1 Range)", 
            U_CROSSBOWMAN,
            [400],  
            [T_CASTLE_AGE, T_LEATHER_ARCHER_ARMOR],
            [T_BODKIN_ARROW, T_PADDED_ARCHER_ARMOR, T_CROSSBOWMAN, T_ELITE_SKIRMISHER],
            U_ARCHERY_RANGE
        ),
        new Tech(
            "Archer Upgrade 3 (Imperial Upgrades + 1 Range)", 
            U_ARBALEST,
            [800],  
            [T_IMPERIAL_AGE, T_PADDED_ARCHER_ARMOR],
            [T_CAVALRY_ARCHER_A, T_ARBALEST, T_RING_ARCHER_ARMOR],
            U_ARCHERY_RANGE
        ),
        new Tech(
            "Cavalry Upgrade 1 (Feudal Upgrades)", 
            U_SCOUT_CAVALRY,
            [200],  
            [T_FEUDAL_AGE],
            [T_FORGING, T_BLOODLINES, T_SCALE_BARDING_ARMOR ],
            U_STABLE
        ),
        new Tech(
            "Cavalry Upgrade 2 (Castle Upgrades + 1 Stable)", 
            U_KNIGHT,
            [400],  
            [T_CASTLE_AGE, T_LEATHER_ARCHER_ARMOR, T_SCALE_BARDING_ARMOR],
            [T_FORGING,  T_PLATE_BARDING_ARMOR],
            U_STABLE
        ),
        new Tech(
            "Cavalry Upgrade 3 (Imperial Upgrades + 1 Stable)", 
            U_PALADIN,
            [1000],  
            [T_IMPERIAL_AGE, T_PLATE_MAIL_ARMOR],
            [T_CAVALIER, T_PALADIN, T_HUSSAR, T_HEAVY_CAMEL, T_PLATE_BARDING_ARMOR],
            U_STABLE
        ),
    ];

    Trig(uniqid());
        Efft_RemoveO(1);
        Efft_RemoveO(2);

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
    // terrain, width, height
    $regions = [
        new EnemySpawnZone(TERRAIN_SNOW, 31, 15),
        new PlayerRegion(TERRAIN_ROAD_FUNGUS, 31, 30),
        new TowerZone(TERRAIN_ROAD_BROKEN, 21, 21),
        new CombatBuildingZone(TERRAIN_ROAD, 21, 21),
        new StoreZone(TERRAIN_ROAD_BROKEN, 35, 12),
        new EcoZone(TERRAIN_GRASS_2, 30, 25),
        new HouseZone(TERRAIN_ROAD_BROKEN, 38, 4),
    ];
    $offsets = [0];
    foreach ($regions as $i => $region) 
        array_push($offsets, $offsets[$i] - $region->depth);
    $Y_LENGTH = 40;
    $MAP_OFFSET = 20;
    $corner = new Point(GetMapSize() - $MAP_OFFSET, $MAP_OFFSET);
    $origin = $corner->offset(0, round($Y_LENGTH / 2));
    $PLAYERS = [1, 3, 5, 7];
    $PLAYERS = [1];
    SetPlayersCount(2*count($PLAYERS));
    foreach ($PLAYERS as $i => $playerId) {
        SetPlayerMaxPop($playerId, 200);
        SetPlayerStartAge($playerId, "Imperial");
        SetPlayerDisabilitybuildingList($playerId, $GLOBALS['BANNED_BUILDINGS']);
        $areas = [];
        foreach ($regions as $i => $region) {
            $region->setPlayerId($playerId);
            $region->setOrigin($origin->offset($offsets[$i]));
            $region->run();
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
    //$lastRound = null;

    
    // Trig("Intro Prompt", 1, 0);
    //     //Efft_Research(1, T_TOWN_WATCH);
    //     Efft_ChangeView(1, array(37, 27));
    //     Efft_Display(25, 0, "Welcome to AOE2 Wave Survival. The Game will begin at 00:30, prepare for carnage");        
    //     Efft_Display(25, 1, "You have until 00:25 to choose your difficulty level before the default of Hero mode is selected");      
    //     Efft_Display(25, 2, "Task an infandry building in between the flags to select your difficulty level");
    
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