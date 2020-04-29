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

        public function getName($objectName) {
            return "{$this->playerId}: $objectName";
        }

        public function trig($triggerName, $S = 1, $L = 0, $P = 0, $E = 0, $D = '', $R = '') {
            $triggerName = $this->getName($triggerName);
            Trig($triggerName, $S, $L, $P, $E, $D, $R);
            return $triggerName;
        }

        public function getEnemyId() {
            return $this->playerId + 1;
        }

        public function act($triggerName) {
            Efft_Act($this->getName($triggerName));
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

        public static function fromArr($arr) {
            return new Point($arr[0], $arr[1]);
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
        function __construct($playerId, $terrainId, $width, $depth) {
            parent::__construct($playerId);
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

        public function placeStoreTriggersAt($storeOrigin) { }

        public function createAreaRow($offsetX, $origin = null, $width = null) {
            $origin = $origin != null ? $origin : $this->origin;
            $origin = $origin->offset($offsetX);
            $width = $width != null ? $width : $this->width;
            return AreaAdvanced($origin->asArr(), $this->orientation, $width, 1);
        }

        // renders this zone for a player
        public function render() {
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

        function __construct($playerId, $width, $depth,  $roundNum, $time, $payment, $units) {
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

    class EnemySpawnZone extends PlayerRegion {
        function render() {
            parent::render();
            $this->killZoneTriggers();
            $time = 30;
            $waves = array();

            foreach($GLOBALS['UNITS'] as $i => $roundUnits) {
                if (is_array($roundUnits)) {
                    $time += $roundUnits[0];
                    array_push($waves, new EnemyWave(
                        $this->playerId, $this->width, $this->depth,
                        $i + 1, $time, 75, $roundUnits[1]
                    ));
                } else {
                    // age up break time
                    $time += 90;
                    $eId = $this->getEnemyId();
                    $pId = $this->playerId;
                    $age = null;
                    $this->trig($roundUnits);
                    Cond_Timer($time);
                    switch ($roundUnits) {
                        case "Feudal Upgrade":
                            $age = T_FEUDAL_AGE;
                            // upgrade enemy units
                            foreach ([
                                $age,
                                T_FLETCHING,
                                T_PADDED_ARCHER_ARMOR,
                                T_SCALE_MAIL_ARMOR
                            ] as $r) Efft_Research($eId, $r);
                        break;
                        case "Castle Upgrade":
                            $age = T_CASTLE_AGE;
                            foreach ([
                                $age,
                            ] as $r) Efft_Research($eId, $r);
                        break;
                        case "Imperial Upgrade":
                            $age = T_IMPERIAL_AGE;
                            foreach ([
                                $age,
                            ] as $r) Efft_Research($eId, $r);
                        break;
                    }
                    Efft_Research($pId, $age);
                }
            }
            foreach($waves as $i => $wave)
                $wave->setOrigin($this->origin->offset(-$i));
            for ($i = 0; $i < count($waves) - 2; $i++) {
                $waves[$i]->nextTime = $waves[$i+1]->time;
                $waves[$i]->nextUnits = $waves[$i+1]->units;
            }
            foreach($waves as $wave) {
                $wave->origin = $this->origin;
                $wave->renderWave();
            }
        }

        private function killZoneTriggers() {
            $a = $this->getArea();
            // Creates Kill Zone in the area where enemyId buildings spawn
            $this->trig("Kill Zone", 1, 1);
            Cond_InAreaY($this->playerId, Y_MILITARY, 1, $a);
            $this->chat("<RED> No player units are not allowed in the enemyId spawning area");
            Efft_KillY($this->playerId, Y_MILITARY, $a);
            if ($GLOBALS['DEBUG']) {
                $this->trig("Kill Zone Enemy", 1, 1);
                    Cond_InAreaY($this->playerId, 1, Y_MILITARY, $a);
                    Efft_KillY($this->playerId, Y_MILITARY, $a);
            }
        }
    }

    class TowerZone extends PlayerRegion {
        private $TOWER_HILL_OFFSET = 5;

        function render() {
            parent::render();
            $tcLoc = $this->getCenter()->offset(0, 9);

            $this->setTowerElevation();
            $this->trig("Enemy Town Center Invincible", 1, 1);
                Cond_Timer(2);
                Efft_HPY($this->getEnemyId(), 3, Y_BUILDING, $tcLoc->asArr());
            $this->trig("Enemy Town Center");
                Efft_RemoveO($this->getEnemyId());
                $this->create(U_TOWN_CENTER, $tcLoc, $this->getEnemyId());
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

        }

        private function setTowerElevation() {
            for ($i = 1; $i < $this->TOWER_HILL_OFFSET; $i++)
                foreach (AreaPts(AreaSet(
                    $this->getCenter()->asArr(), 
                    $this->TOWER_HILL_OFFSET + 2 - $i
                )) as $p)
                    setElevation($p, $i);
        }

        private function towerExplosionTrigger($triggerName) {
            Trig($triggerName, 0);
            for ($i = 1; $i < $this->TOWER_HILL_OFFSET - 1; $i++)
                foreach (AreaPts(AreaSet($this->getCenter()->asArr(), $i)) as $p) {
                    Efft_KillO($this->getEnemyId(), $p);
                    Efft_ChangeView(1, $this->getCenter()->asArr());
                    // explosion animation
                    Efft_Create(0, U_MACAW, $p);
                    Efft_KillU(0, U_MACAW, $p);
                }
        }

        public function placeStoreTriggersAt($storeOrigin) {
            $towerDestroyTrigger = $this->getName("Tower Explosion");
            $towerDestroyTech = new Tech(
                $towerDestroyTrigger,
                U_PETARD,
                [100, 400, 800]
            );
            $towerDestroyTech->setPlayerId($this->playerId);
            $towerDestroyTech->setTriggerName($towerDestroyTrigger);
            $this->towerExplosionTrigger($towerDestroyTrigger);
            $towerDestroyTech->placeAtLocation($storeOrigin->offset(-9, 4), $towerDestroyTrigger);

            $createCastleName = $this->getName("Castle Creation");
            $castleTech = new Tech(
                $createCastleName,
                U_PETARD,
                [500]
            );
            $castlePoint = $this->origin->offset(0, $this->width / 2);
            Trig($createCastleName, 0);
                // remove stone heads
                Efft_RemoveO(0, $castlePoint->areaFromOffset(-4, -4));
                // place castle
                $this->create(U_CASTLE, $castlePoint->offset(-2, -1));
            $castleTech->setPlayerId($this->playerId);
            $castleTech->setTriggerName($createCastleName);
            $castleTech->placeAtLocation($storeOrigin->offset(-9, 7), $createCastleName);
            $this->updateTriggers($storeOrigin);
        }

        public function updateTriggers($storeOrigin) {
            $towerLocation = $this->getCenter()->asArr();
            $addHealth = $this->getName("Add 1000 Health");
            $towerUpgrade1 = $this->getName("Tower Upgrade 1");
            $towerUpgrade2 = $this->getName("Tower Upgrade 2");
            $towerUpgrade3 = $this->getName("Tower Upgrade 3");
            $regain = $this->regainTrigger(1, 1);
            $regain4 = $this->regainTrigger(4);
            $regain10 = $this->regainTrigger(10);
            $regain15 = $this->regainTrigger(15);

            Trig($addHealth, 0, 0);
                Efft_HPY(1, 1000, Y_BUILDING, $towerLocation);
            Trig($towerUpgrade1, 0, 0);
                Efft_Deact($regain);
                Efft_Act($regain4);
                Efft_Research(1, T_MURDER_HOLES);
                Efft_RangeY(1, 2, Y_BUILDING, $towerLocation);
                Efft_APY(1, 5, Y_BUILDING, $towerLocation);
            Trig($towerUpgrade2, 0, 0);   
                Efft_Deact($regain4);
                Efft_Act($regain10);
                Efft_RangeY(1, 5, Y_BUILDING, $towerLocation);
                Efft_APY(1, 5, Y_BUILDING, $towerLocation); 
            Trig($towerUpgrade3, 0, 0);   
                Efft_Deact($regain10);
                Efft_Act($regain15);
                Efft_RangeY(1, 5, Y_BUILDING, $towerLocation);
                Efft_APY(1, 5, Y_BUILDING, $towerLocation);

            $techs = [
                new Tech($addHealth, U_MONK, [100, 200, 300, 400, 500]),
                new Tech($towerUpgrade1, U_SKIRMISHER, [200]),
                new Tech($towerUpgrade2, U_ELITE_SKIRMISHER, [400]),
                new Tech($towerUpgrade3, U_ARBALEST, [800]),
            ];
            foreach($techs as $i => $t) {
                $t->setPlayerId($this->playerId);
                $t->placeAtLocation($storeOrigin->offset(-9, -2 - 2*$i), $t->relicName);
            }
        }

        private function regainTrigger($ammount, $startState = 0) {
            $name = $this->getName("Tower Health Regain x$ammount");
            Trig($name, $startState, 1);
                Cond_Timer(1);
                Efft_DamageY($this->playerId, -$ammount, Y_BUILDING, $this->getCenter()->asArr());
            return $name;
        }
    }

    class CombatBuildingZone extends PlayerRegion {
        private $DISTANCE = 6;
        function render() {
            parent::render();
            $this->trig("Initial Placement");
                $this->create(U_BARRACKS, $this->getBaseOffsetForObject(U_BARRACKS));
            $this->trig("Feudal Placement");
                Cond_Researched($this->playerId, T_FEUDAL_AGE);
                foreach ([U_ARCHERY_RANGE, U_STABLE] as $buildingId)
                    $this->create($buildingId, $this->getBaseOffsetForObject($buildingId));
        }

        public function getBaseOffsetForObject($buildingId) {
            $barracksPoint = $this->origin->offset(-$this->DISTANCE + 1);
            switch($buildingId) {
                case U_STABLE:
                    return $barracksPoint->offset(0, $this->DISTANCE);
                case U_ARCHERY_RANGE:
                    return $barracksPoint->offset(0, -$this->DISTANCE);
                case U_BARRACKS:
                    return $barracksPoint;
            }
        }

        public function placeStoreTriggersAt($storeOrigin) {
            $base = $storeOrigin->offset(-4);
            foreach ($GLOBALS['TECH_DATA'] as $i => $techRaw) {
                $tech = new Tech(
                    $techRaw[0],
                    $techRaw[1],
                    $techRaw[2],
                );
                $tech->setPlayerId($this->playerId);
                $tech->setRequirements($techRaw[3]);
                $buildingId = $techRaw[5];
                $flag = 0;
                if (in_array($tech->unitId, [U_LONG_SWORDSMAN, U_CROSSBOWMAN, U_KNIGHT])) {
                    $flag = 1;
                } else if (in_array($tech->unitId, [U_CHAMPION, U_KNIGHT, U_PALADIN])) {
                    $flag = 2;
                }
                $origin = $this->getBaseOffsetForObject($buildingId);
                $nameUniq = substr(uniqid(), 0, 5);
                Trig($nameUniq, 0, 1);
                    $this->create($buildingId, $origin->offset(-$flag*$this->DISTANCE));
                    foreach ((array)$techRaw[4] as $t)
                        Efft_Research($this->playerId, $t);
                $tech->placeAtLocation($base->offset(0, -8 + 2*$i), $nameUniq);
            }   
        }
    }

    class StoreZone extends PlayerRegion {
        function render() {
            parent::render();
        }
    }

    class HouseZone extends PlayerRegion {
        function render() {
            $this->trig("House Place");
                $this->createInArea(U_HOUSE, $this->getArea());
            parent::render();
        }
    }

    class EcoZone extends PlayerRegion {
        private $VIL_TRIGGER_NAME = "Create 5 Vils";

        private $goldOffset = 10;

        function render() {
            parent::render();
            $goldOffset = $this->getCenter()->offset(0, -$this->goldOffset);
            $lumberCamp = $this->getZoneEnd()->offset(4);
            $treeArea = $this->createAreaRow(1, $this->getZoneEnd());
            $goldArea = AreaSet($goldOffset->asArr());
            $this->trig("Initial Eco Placement");
                $this->create(U_TOWN_CENTER, $this->getCenter());
                $this->createInArea(U_SHEEP, $this->getCenter()->offset(3)->areaFromOffset(0, 6));
                $this->create(U_MINING_CAMP, $goldOffset->offset(-3));
                $this->create(U_LUMBER_CAMP, $lumberCamp);
                $this->createInArea(U_OLD_STONE_HEAD, $this->createAreaRow(0), 0);
            $this->trig("Gold Placement", 1, 1);
                $this->createInArea(U_GOLD_MINE, $goldArea, 0);
            $this->trig("Tree Placement", 1, 1);
                $this->createInArea(U_TREE_A, $treeArea, 0);
        }

        public function placeStoreTriggersAt($storeOrigin) {
            $vilSpawnArea = $this->createAreaRow(1, $this->origin->offset(-3), 5);
            $createVilTech = new Tech(
                $this->VIL_TRIGGER_NAME,
                U_SHEEP,
                [100, 200, 400, 800]
            );
            $createVilTech->setPlayerId($this->playerId);
            $createVilTech->setTriggerName($this->VIL_TRIGGER_NAME);
            Trig($this->VIL_TRIGGER_NAME);
                foreach (AreaPts($vilSpawnArea) as $i => $p)
                    $this->create(($i % 2 == 0) ? U_VILLAGER_F : U_VILLAGER_M, Point::fromArr($p));
            $createVilTech->placeAtLocation($storeOrigin->offset(-9), $this->VIL_TRIGGER_NAME);
        }
    }

    class Tech extends PlayerRegion {
        public $WALL_MATERIAL = U_OLD_STONE_HEAD;

        public $relicName; 
        public $costs; 
        public $requirements = []; 
        public $unitId; 
        public $buildingId;
        // nullable, can make a trigger optionally
        public $triggerName;

        public $row = 0;
        public $positionIndex;


        function __construct($relicName, $unitId, $costs) {
            $this->relicName = $relicName;
            $this->unitId = $unitId;
            $this->costs = $costs;
        }

        public function setRequirements($requirements) {
            $this->requirements = $requirements;
        }

        public function setTriggerName($triggerName) {
            $this->triggerName = $triggerName;
        }

        function getNameIndexString($relicName, $index) {
            return "$relicName $index";
        }

        function placeAtLocation($location, $triggerName) {
            // give meaningful relicNames to data array
            // x is offset by 2 on map
            // lan    $Length_Xs are 2
            $blockLocation = $location;
            $relicLocation = $location->offset(-2);
            $unitLocation = $location->offset(-1);
            $killLocation = $location->offset(1);

            $size = count($this->costs);

            // one time event
            Trig(uniqid());
                $this->act($this->getNameIndexString($this->relicName, 0));
                $this->createGaia(U_RELIC, $relicLocation);
                $this->createGaia(U_HAY_STACK, $blockLocation);
            foreach($this->costs as $i => $cost) {
                $this->create($this->unitId, $unitLocation);
                Efft_NameO(0, "{$this->relicName} ($cost stone)", $relicLocation->asArr());

                $this->trig($this->getNameIndexString($this->relicName, $i), 0);
                    Cond_Timer(2); // debounce the last purchase
                    Cond_Accumulate($this->playerId, $cost, R_STONE_STORAGE);
                    Cond_InAreaU($this->playerId, 1, $this->unitId, $killLocation->asArr());
                    Efft_KillU($this->playerId, $this->unitId, $killLocation->asArr());
                    Efft_Tribute($this->playerId, $cost, R_STONE_STORAGE, 0);
                    $this->chat("<YELLOW> Bought {$this->relicName} for {$cost} stone");
                    Efft_Act($triggerName);

                // place down for another round if it exists
                if ($i != $size - 1) 
                    $this->act($this->getNameIndexString($this->relicName, $i + 1));
            }
            Trig(uniqId());
                /// will place wall over blocked location
                $this->createInArea($this->WALL_MATERIAL, AreaSet($unitLocation->asArr()), 0);
                $this->createInArea($this->WALL_MATERIAL, AreaSet($killLocation->asArr()), 0);
                Efft_RemoveO(0, $killLocation->asArr());
            
            Trig(uniqid());
                if (is_array($this->requirements))
                    foreach ($this->requirements as $req) 
                        Cond_Researched($this->playerId, $req);
                Efft_RemoveO(0, $blockLocation->asArr());
                
        }
    }

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