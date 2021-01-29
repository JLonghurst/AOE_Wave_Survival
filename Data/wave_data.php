<?php
    function nameFromUnit($units) {
        $ENEMY_NAME_MAP = array(
            U_MILITIA => "Militia",
            U_BATTERING_RAM => "Rams",
            U_SCOUT_CAVALRY => "Scouts",
            U_ARCHER => "Archers",
            U_KNIGHT => "Knight",
            U_MAN_AT_ARMS => "Man At Arms",
            U_SKIRMISHER => "Skirmisher",
            U_ARCHERY_RANGE => "Range",
            U_BARRACKS => "Barracks",
            U_STABLE => "Stable",
            U_HEAVY_CAMEL => "Heavy Camel", 
            U_PALADIN => "Palidin", 
            U_TWO_HANDED_SWORDSMAN => "Two Handed Swordsmen", 
            U_ONAGER => "Onager", 
            U_HEAVY_SCORPION => "Heavy Scorpian", 
            U_CAVALIER => "Cavalier", 
            U_HUSSAR => "Hussar", 
            U_TREBUCHET_P => "Trebs", 
            U_WAR_ELEPHANT => "War Elepahent", 
            U_LONGBOWMAN => "Longbowmen", 
            U_CAVALRY_ARCHER => "Cav Archer", 
            U_CROSSBOWMAN => "Crossbowmen",
            U_EAGLE_SCOUT => "Eagle Scout", 
            U_PIKEMAN => "Pikemen", 
            U_LIGHT_CAVALRY => "Light Cavalry", 
            U_CAMEL => "Camel", 
            U_LONG_SWORDSMAN => "Long Swordsmen", 
            U_MANGONEL => "Mangonel", 
            U_ELITE_SKIRMISHER => "Elite Skirms", 
            U_CHAMPION => "Champion",
            U_SIEGE_RAM => "Siege Ram",
            U_PETARD => "Petards",
            U_HEAVY_CAVALRY_ARCHER => "Heavy Cav Arch",
            U_HALBERDIER => "Halbs",
            U_ELITE_EAGLE_WARRIOR => "Eagle Warriors",
            U_SCORPION => "Scorpians",
            T_HEAVY_CAMEL => "Heavy Camels",
            U_HAND_CANNONEER => "Hand Cannoneers",
            U_BOMBARD_CANNON => "Bombard Cannons"
        ); 
        $yeet = "";
        $i = 0;
        foreach ((array)$units as $unit) {
            if ($i != 0) $yeet .= ", ";
            $i++;
            $unitId = $unit[0];
            $unitCount = $unit[1];
            $human_name = $ENEMY_NAME_MAP[$unitId];
            $yeet .= "{$unitCount} {$human_name}";
        }
        return $yeet;
    }
    // must be size 16 for current use in random generator
    // $unique_spawn_data = [
    //     array("60 Longbowman", U_LONGBOWMAN, $spawn1_60),
    //     array("60 Cataphract", U_CATAPHRACT, $spawn1_60),
    //     array("60 Woad Raider", U_WOAD_RAIDER, $spawn1_60),
    //     array("60 Chu Ko Nu", U_LONGBOWMAN, $spawn1_60),
    //     array("60 Throwing Axeman", U_THROWING_AXEMAN, $spawn1_60),
    //     array("60 Huskarl", U_HUSKARL, $spawn1_60),
    //     //array("60 Samurai", U_SAMURAI, $spawn1_60),
    //     array("60 Mangudai", U_MANGUDAI, $spawn1_60),
    //     array("30 War Elephant", U_WAR_ELEPHANT, $spawns3_30),
    //     array("60 Mameluke", U_MAMELUKE, $spawn1_60),
    //     array("60 Teutonic Knight", U_TEUTONIC_KNIGHT, $spawn1_60),
    //     array("60 Janissary", U_JANISSARY, $spawn1_60),
    //     //array("60 Berserk", U_BERSERK, $spawn1_60),
    //     array("60 Jaguar Warrior", U_JAGUAR_WARRIOR, $spawn1_60),
    //     array("60 Tarkan", U_TARKAN, $spawn1_60),
    //     array("60 War Wagon", U_WAR_WAGON, $spawn1_60),
    //     array("60 Plumed Archer", U_PLUMED_ARCHER, $spawn1_60),
    //     array("60 Conquistador", U_CONQUISTADOR, $spawn1_60)
    // ];
    
    // $spawns100 = Spawn_Box(array(4, $SPAWN_CENTER_X), array(13, 71));
    // // big spawn
    // $spawnsB_5 = Spawn_Box(array(7, $SPAWN_CENTER_X+13), array(11, $SPAWN_CENTER_X+13));


    class UnitModel {
        public $unitId;
        public $unitCount;
    }

    class Wave {
        public $time;

        public $units;

    }

    $UNITS = array(
        [30, array([U_MILITIA, 1])],
        [60, array([U_MILITIA, 3])],
        [60, array([U_MILITIA, 5])],
        [90, array([U_MILITIA, 7], [U_BATTERING_RAM, 3])],

        "Feudal Upgrade",

        [60, array([U_ARCHER, 5])],
        [60, array([U_SCOUT_CAVALRY, 5])],
        [60, array([U_SCOUT_CAVALRY, 5], [U_ARCHER, 5])],
        [60, array([U_MAN_AT_ARMS, 9])],
        [60, array([U_SKIRMISHER, 9], [U_ARCHER, 9])],
        [60, array([U_MAN_AT_ARMS, 9], [U_SCOUT_CAVALRY, 9])],
        [60, array([U_KNIGHT, 5])],
 
        //CASTLE
        "Castle Upgrade",

        [60, array([U_CROSSBOWMAN, 20])],
        [60, array([U_KNIGHT, 15])],
        [60, array([U_EAGLE_SCOUT, 10], [U_EAGLE_SCOUT, 10])],
        [60, array([U_MANGONEL, 10])],
        [60, array([U_BATTERING_RAM, 5], [U_LONG_SWORDSMAN, 20])],
        [60, array([U_CAMEL, 10], [U_ELITE_SKIRMISHER, 20])],
        [60, array([U_KNIGHT, 15])],
        [60, array([U_EAGLE_SCOUT, 10], [U_PIKEMAN, 10])],
        [60, array([U_MANGONEL, 10])],
        [60, array([U_KNIGHT, 15], [U_LIGHT_CAVALRY, 15])],
        [60, array([U_SCORPION, 30], [U_BATTERING_RAM, 5])],
        [60, array([U_PIKEMAN, 25])],
        [60, array([U_EAGLE_SCOUT, 20])],
        [60, array([U_CAVALRY_ARCHER, 20], [U_CROSSBOWMAN, 10])],
        [60, array([U_WAR_ELEPHANT, 20], [U_LONGBOWMAN, 20])],
        
        // IMPERIAL

        "Imperial Upgrade",

        [60, array([U_HUSSAR, 30], [U_TREBUCHET_P, 5])],
        [60, array([U_CAVALIER, 60])],
        [60, array([U_TWO_HANDED_SWORDSMAN, 60])],
        [60, array([U_HAND_CANNONEER, 60], [U_BOMBARD_CANNON, 20])],
        [60, array([U_HEAVY_CAMEL, 20], [U_PALADIN, 60])],
        [60, array([U_HALBERDIER, 60])],
        [60, array([U_ONAGER, 20], [U_HEAVY_SCORPION, 20])],
        [60, array([U_HEAVY_CAVALRY_ARCHER, 60], [U_SIEGE_RAM, 20])],
        [60, array([U_CHAMPION, 30], [U_ELITE_EAGLE_WARRIOR, 60])],
        [60, array([U_PALADIN, 60])],
        [60, array([U_PETARD, 100])],
        [60, array([U_HALBERDIER, 30], [U_HUSSAR, 30], [U_ELITE_SKIRMISHER, 30])],
        [60, array([U_PALADIN, 30], [U_CHAMPION, 30], [U_PALADIN, 30])],
    );


    //print(count($UNITS));
    $BANNED_BUILDINGS = [U_VILLAGER_F, U_VILLAGER_M, U_BLACKSMITH, U_MARKET, U_UNIVERSITY, U_MONASTERY, U_WATCH_TOWER, U_HOUSE];
    
    // array of entitys and spawn numbers, and a time of round
    $game_modes = array(
        array(
            "name" => "Easy",
            "chat" =>  "<GREEN> Noob mode selected. Very cute. We all start somewhere :)",
            "unit" => U_MILITIA,
        ),
        array(
            "chat" =>  "<GREEN> Easy Mode selected. Maybe one day you will be worth somethings",
            "name" => "Hard",
        )
        // array(
        //     "chat" => "<GREEN> Hero Mode Selected. Prepare for a true challange of your skills!",
        //     "name" => "Brutal",
        // ),
    );

    $TECH_DATA = [
        array(
            "Infandry Upgrade 1 (Feudal Upgrades)", 
            U_MAN_AT_ARMS,
            [200],  
            [T_FEUDAL_AGE],
            [T_SQUIRES, T_MAN_AT_ARMS, T_SCALE_MAIL_ARMOR],
            U_BARRACKS
        ),
        array(
            "Infandry Upgrade 2 (Castle Upgrades + 1 Barracks)", 
            U_LONG_SWORDSMAN,
            [400],  
            [T_CASTLE_AGE, T_SCALE_MAIL_ARMOR],
            [T_LONG_SWORDSMAN, T_PIKEMAN, T_EAGLE_WARRIOR, T_CHAIN_MAIL_ARMOR],
            U_BARRACKS
        ),
        array(
            "Infandry Upgrade 3 (Imperial Upgrades + 1 Barracks)", 
            U_CHAMPION,
            [800],  
            [T_IMPERIAL_AGE, T_CHAIN_MAIL_ARMOR],
            [T_CHAMPION, T_TWO_HANDED_SWORDSMAN, T_PLATE_MAIL_ARMOR, T_HALBERDIER, T_ELITE_EAGLE_WARRIOR],
            U_BARRACKS
        ),
        array(
            "Archer Upgrade 1 (Feudal Upgrades)", 
            U_ARCHER,
            [200],  
            [T_FEUDAL_AGE],
            [T_FLETCHING, T_LEATHER_ARCHER_ARMOR],
            U_ARCHERY_RANGE
        ),
        array(
            "Archer Upgrade 2 (Castle Upgrades + 1 Range)", 
            U_CROSSBOWMAN,
            [400],  
            [T_CASTLE_AGE, T_LEATHER_ARCHER_ARMOR],
            [T_THUMB_RING, T_BODKIN_ARROW, T_PADDED_ARCHER_ARMOR, T_CROSSBOWMAN, T_ELITE_SKIRMISHER],
            U_ARCHERY_RANGE
        ),
        array(
            "Archer Upgrade 3 (Imperial Upgrades + 1 Range)", 
            U_ARBALEST,
            [800],  
            [T_IMPERIAL_AGE, T_PADDED_ARCHER_ARMOR],
            [T_HEAVY_CAVALRY_ARCHER, T_ARBALEST, T_RING_ARCHER_ARMOR, T_PARTHIAN_TACTICS],
            U_ARCHERY_RANGE
        ),
        array(
            "Cavalry Upgrade 1 (Feudal Upgrades)", 
            U_SCOUT_CAVALRY,
            [200],  
            [T_FEUDAL_AGE],
            [T_FORGING, T_BLOODLINES, T_SCALE_BARDING_ARMOR],
            U_STABLE
        ),
        array(
            "Cavalry Upgrade 2 (Castle Upgrades + 1 Stable)", 
            U_KNIGHT,
            [400],  
            [T_CASTLE_AGE, T_SCALE_BARDING_ARMOR],
            [T_FORGING,  T_CHAIN_BARDING_ARMOR, T_LIGHT_CAVALRY],
            U_STABLE
        ),
        array(
            "Cavalry Upgrade 3 (Imperial Upgrades + 1 Stable)", 
            U_PALADIN,
            [1000],  
            [T_IMPERIAL_AGE, T_CHAIN_BARDING_ARMOR],
            [T_CAVALIER, T_PALADIN, T_HUSSAR, T_HEAVY_CAMEL, T_PLATE_BARDING_ARMOR],
            U_STABLE
        ),
    ];

    
    //    array("Imperial Seige, Siege Engineers", 500, array(T_IMPERIAL_AGE),
    //            array(T_HEAVY_SCORPION, T_ONAGER, T_CAPPED_RAM, T_SIEGE_ENGINEERS), "", array(34, 52)),
    //    array("Post-Imperial Seige", 500, array(T_IMPERIAL_AGE, T_SIEGE_ENGINEERS),
    //            array(T_SIEGE_RAM, T_SIEGE_ONAGER),  "", array(34, 54)),
    //    array("Chemistry", 130, array(T_IMPERIAL_AGE), array(T_CHEMISTRY), "", array(34, 56)),
        
    //     // 2nd row
    //     //array("Hail Mary (kill all units on field)", 500, array(), "Hail Mary", array(41, 42)),
        
    //     // 3rd row trigger activators
    //     array("Clear Tower of Enemies", array(U_PETARD, 100, 200, 300, 400, 500, 600, 700, 800, 900, 1000), array(T_LOOM), array(), "Bomb Area", array(48, 32)),
        
    //     array("Murder Holes, Tower Attack/Range +5, Health Regain 4/sec", 300, array(T_FEUDAL_AGE),
    //             array(T_MURDER_HOLES), "Tower Upgrade 1", array(40, 32)),
        
    //     array("Create Castle", 1000, array(T_CASTLE_AGE),
    //             array(), "Castle Creation", array(40, 38)),
    //     array("Masonry, Tower Attack/Range +5, Health Regain 10/sec", 400, array(T_CASTLE_AGE, T_MURDER_HOLES),
    //             array(T_MASONRY), "Tower Upgrade 2", array(40, 40)),
    //     array("Sanctity, Redemption, Atonement, and Herbal Medicine", 500, array(T_CASTLE_AGE),
    //             array(T_SANCTITY, T_ATONEMENT, T_REDEMPTION, T_HERBAL_MEDICINE), "", array(40, 42)),
        
    //     array("Architecture, Tower Attack/Range +5, Health Regain 20/sec",  500, array(T_IMPERIAL_AGE, T_MASONRY),
    //             array(T_ARCHITECTURE), "Tower Upgrade 3", array(40, 51))
?>