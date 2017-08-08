<?php
include('Data/wave_data.php');

function Scenario() {
    
    SetPlayerStartFood(1, 0);
    SetPlayerStartWood(1, 0);
    SetPlayerStartGold(1, 0);
    SetPlayerStartStone(1, 0);
    SetPlayerMaxPop(1, 100);
    SetAllTech(true);
    
    global $tower_location;
    $tower_location = array(8, 9);
    $banned_units = [U_VILLAGER_F, U_VILLAGER_M, U_BLACKSMITH, U_MARKET, U_UNIVERSITY, 
        U_MONASTERY, U_WATCH_TOWER];
    SetPlayerDisabilityUnitList(1, $banned_units);
    
    $banned_techs = [

    ];
    SetPlayerDisabilityTechList(1, $banned_techs);
    
    $times_noob = [
        20, 30, 60, 60, // Dark Age
        120, 60, 30, 60, 30, 60, 60, 60, 60, // Feudal Age
        180, 90, 90, 90, 90, 90, 90, 90, 90, 90, 90, 90, 90, // Castle Age
        60, 90, 90, 90, 90, 90, 90, 90, 90, 90, 90, 90, 90, 90, 90 // Imperial Age
       ]; 
    
    $times_easy = [
        30, 30, 60, 60, // Dark Age
        120, 90, 90, 90, 90, 90, 90, 90, 90, // Feudal Age
        240, 120, 120, 120, 120, 120, 120, 120, 120, 120, 120, 120, 120, // Castle Age
        240, 120, 120, 120, 120, 120, 120, 120, 120, 120, 120, 120, 120, 160, 160 // Imperial Age
    ]; 
        
    $times_hero = [
        20, 30, 60, 60, // Dark Age
        120, 60, 30, 60, 30, 60, 60, 60, 60, // Feudal Age
        180, 90, 90, 90, 90, 90, 90, 90, 90, 90, 90, 90, 90, // Castle Age
        60, 90, 90, 90, 90, 90, 90, 90, 90, 90, 90, 90, 90, 90, 90 // Imperial Age
    ]; 
    
    $tech_data = $GLOBALS["tech_data"];
    
    $round_data = $GLOBALS["round_data"];
    
    $mode_times  = [$times_noob, $times_easy, $times_hero];
    
    $DEBUG = false;
    if ($DEBUG) {
        Trig("Kill Zone 2 ", 1, 1);
            Cond_Timer(1);
            Cond_InAreaY(2, 1, Y_MILITARY, [array(0, 60), array(17, 70)]);
            Efft_KillY(2, Y_MILITARY, [array(0, 60), array(17, 70)]);
        // Times and delete for round debugging purposes
        $debug_times = [
            5, 5, 5, 5,
            5, 5, 5, 5, 5, 5, 5, 5, 5,
            5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5,
            5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 5
        ];
        $mode_times = [$debug_times, $debug_times, $debug_times];
        SetPlayerStartFood(1, 10000);
        SetPlayerStartWood(1, 10000);
        SetPlayerStartGold(1, 10000);
        SetPlayerStartStone(1, 200);
    }
    
    function Age_Triggers() {
        Trig("Dark Age", 0, 0);
            Efft_Display(20, 0, 
                    "<BLUE> Welcome to Wave Survival. You begin humbly in the dark age");
            
        Trig("Feudal Age", 0, 0);
            Efft_Display(30, 0, 
                    "<BLUE> Youve Made it to feudal. 5 more vils for you");
            Efft_Research(1, T_FEUDAL_AGE);
            Efft_Research(2, T_FEUDAL_AGE);
            Efft_Research(2, T_FLETCHING);
            Efft_Research(2, T_PADDED_ARCHER_ARMOR);
            Efft_Research(2, T_FORGING);
            Efft_Research(2, T_SCALE_BARDING_ARMOR);
            Efft_Research(2, T_SCALE_MAIL_ARMOR);
            Efft_Research(2, T_BLOODLINES);
            Efft_Research(2, T_EAGLE_WARRIOR);
            // Archer upgrade Line
            Efft_RemoveO(0, array(31, 30));
            // Infandry upgrade Line
            Efft_RemoveO(0, array(31, 32));
            // Cavalry upgrade Line
            Efft_RemoveO(0, array(31, 34));
            
            // Second row techs
            Efft_RemoveO(0, array(37, 30));
            Efft_RemoveO(0, array(37, 32));
            Efft_RemoveO(0, array(37, 34));            
            Efft_Act("Vil Spawn 5");
            Efft_Chat(1, "<YELLOW> Bonus 200 stone for feudal advancement! #youwillneedit");
            Efft_Give(1, 200, STONE);
            Efft_Act("Building Spawn Feudal");

        Trig("Castle Age", 0, 0);
            Efft_Display(30, 0, "<BLUE> Well you arent a total failure! 10 more vils for you");
            Efft_Research(1, T_CASTLE_AGE);
            Efft_Research(2, T_CASTLE_AGE);
            Efft_Research(2, T_BODKIN_ARROW);
            Efft_Research(2, T_LEATHER_ARCHER_ARMOR);
            Efft_Research(2, T_IRON_CASTING);
            Efft_Research(2, T_CHAIN_BARDING_ARMOR);
            Efft_Research(2, T_CHAIN_MAIL_ARMOR);
            Efft_Research(2, T_BALLISTICS);
            Efft_Research(2, T_EAGLE_WARRIOR);
            Efft_Act("Vil Spawn");
            Efft_Act("Building Spawn Castle");
            Efft_Chat(1, "<YELLOW> Bonus 400 food for feudal advancement! Get a castle maybe? It's your life");
            Efft_Give(1, 400, STONE);  
            // second row techs
            Efft_RemoveO(0, array(37, 38));
            Efft_RemoveO(0, array(37, 40));
            Efft_RemoveO(0, array(37, 42));   
        
        Trig("Imperial Age", 0, 0);
            Efft_Research(1, T_IMPERIAL_AGE);
            Efft_Research(2, T_IMPERIAL_AGE);
            Efft_Research(2, T_RING_ARCHER_ARMOR);
            Efft_Research(2, T_BRACER);
            Efft_Research(2, T_BLAST_FURNACE);
            Efft_Research(2, T_PLATE_BARDING_ARMOR);
            Efft_Research(2, T_PLATE_MAIL_ARMOR);
            Efft_Act("Vil Spawn");
            Efft_Act("Building Spawn Imperial");
            Efft_Display(30, 0, 
                    "<BLUE> IMERIAL! waaaaa. 10 vils #imperial #swag #datmicrodoe.");
            Efft_Chat(1, "<YELLOW> Bonus 600 food for feudal advancement! How's the eco?");
            Efft_Give(1, 600, STONE);  
            // Seige upgrade Line
            Efft_RemoveO(0, array(31, 52));
            // Chemistry upgrade Line
            Efft_RemoveO(0, array(31, 56));
            
        Trig("Building Spawn Feudal", 0, 1);
            Efft_Create(1, U_ARCHERY_RANGE, array(20, 5));
            Efft_Create(1, U_STABLE, array(20, 10));
    
        Trig("Building Spawn Castle", 0, 1);
            Efft_Create(1, U_MONASTERY, array(20, 20));
            Efft_Create(1, U_SIEGE_WORKSHOP, array(20, 25));
            Efft_Create(1, U_MONASTERY, array(25, 20));
            Efft_Create(1, U_SIEGE_WORKSHOP, array(25, 25));
            Efft_Create(1, U_ARCHERY_RANGE, array(25, 5));
            Efft_Create(1, U_STABLE, array(25, 10));
            Efft_Create(1, U_BARRACKS, array(25, 15));
            
        Trig("Building Spawn Imperial", 0, 1);
            Efft_Create(1, U_MONASTERY, array(30, 20));
            Efft_Create(1, U_SIEGE_WORKSHOP, array(30, 25));
            Efft_Create(1, U_ARCHERY_RANGE, array(30, 5));
            Efft_Create(1, U_STABLE, array(30, 10));
            Efft_Create(1, U_BARRACKS, array(30, 15));            
    }
    Age_Triggers();
    
    function Block_Deleters() {
        // Archers 
        Trig("Crossbow Upgrade Delete");
            Cond_Researched(1, T_CASTLE_AGE);
            Cond_Researched(1, T_FLETCHING);
            Efft_RemoveO(0, array(31, 38));
        Trig("Arbalest Upgrade Delete");
            Cond_Researched(1, T_CASTLE_AGE);
            Cond_Researched(1, T_BODKIN_ARROW);
            Efft_RemoveO(0, array(31, 46));
        
        // Infandry
        Trig("Long Sword Upgrade Delete");
            Cond_Researched(1, T_CASTLE_AGE);
            Cond_Researched(1, T_SCALE_MAIL_ARMOR);
            Efft_RemoveO(0, array(31, 40));
        Trig("Champian Upgrade Delete");
            Cond_Researched(1, T_CASTLE_AGE);
            Cond_Researched(1, T_CHAIN_MAIL_ARMOR);
            Efft_RemoveO(0, array(31, 48));

        // Cavalry
        Trig("Cavalry Upgrade Delete");
            Cond_Researched(1, T_CASTLE_AGE);
            Cond_Researched(1, T_SCALE_BARDING_ARMOR);
            Efft_RemoveO(0, array(31, 42));    
        Trig("Paladin Upgrade Delete");
            Cond_Researched(1, T_CASTLE_AGE);
            Cond_Researched(1, T_CHAIN_BARDING_ARMOR);
            Efft_RemoveO(0, array(31, 50));  
            
        Trig("Seige Upgrade Delete");   
            Cond_Researched(1, T_IMPERIAL_AGE);
            Cond_Researched(1, T_ONAGER);
            Efft_RemoveO(0, array(31, 54));   
    }
    Block_Deleters();
       
    function Store_Triggers() {
        global $tower_location;
        Trig("Vil Spawn 5", 0, 0);
        Efft_ChangeView(1, array(37, 12));
        $vil_spawns = Spawn_Box(array(37, 12), array(37, 16));
        foreach($vil_spawns as $spawn) {
            Efft_Create(1, U_VILLAGER_M, $spawn);
        }
        Trig("Bomb Area", 0, 0);
            Efft_KillO(2, [array(6, 7), array(10, 11)]);
            Efft_ChangeView(1, $tower_location);
            foreach (Spawn_Box(array(6, 7), array(10, 11)) as $spawn) {
                Efft_Create(0,U_MACAW, $spawn);
                Efft_KillU(0,U_MACAW, $spawn);
            }
            
        Trig("Castle Creation", 0, 0);   
            Efft_Create(1, U_CASTLE, array(11, 3));
            
        Trig("Guard Tower Upgrade", 0, 0);
            Efft_Research(1, T_GUARD_TOWER);
            Efft_Deact("Tower Health Regain");
            Efft_Act("Tower Health Regain x4");
            Efft_RangeY(1, 10, Y_BUILDING, $tower_location);
            Efft_APY(1, 10, Y_BUILDING, $tower_location);
            
        Trig("Add 1000 Health", 0, 0);
            Efft_HPY(1, 1000, Y_BUILDING, $tower_location);
            
        Trig("Health Regain Upgrade", 0, 0);     
            
            
        Trig("Keep Upgrade", 0, 0);
            Efft_Research(1, T_KEEP);
            Efft_HPY(1, 2250, Y_BUILDING, $tower_location);
    }
    Store_Triggers();
    
    Trig("Upgrade Relic Naming");
    for ($i = 0; $i < count($tech_data); $i++) {
        $data = $tech_data[$i];
        $name = $data[0];
        $cost = $data[1];
        $location = $data[5];
        if (is_array($cost)) {
            Efft_NameU(0, $name . " ($cost[0] stone)", U_RELIC, $location);
        } else {
            Efft_NameU(0, $name . " ($cost stone)", U_RELIC, $location);
        }
    }
    
    // Hay Stack Effect Triggers
    for ($i = 0; $i < count($tech_data); $i++) {
        $data = $tech_data[$i];
        // give meaningful names to data array
        $name = $data[0];
        $cost = $data[1];  
        $techs = $data[2];
        $unit_type = $data[3];
        $relic_spot = $data[5];
        // x is offset by 2 on map
        $location = array($relic_spot[0] - 2, $relic_spot[1]);
        // lane lengths are 2
        $respawn =  array($location[0] - 2, $location[1]); 
        // If the effect can be used more than once
        if (is_array($cost)) {
            $j = 0;
            while (true) {
                // first one is on, all the rest are off
                Trig("$name $j", ($j == 0) ? 1 : 0, 0);
                Cond_InAreaO(1, 1, $location);
                Cond_Timer(4);
                Cond_Accumulate(1, $cost[$j], R_STONE_STORAGE);
                Efft_KillO(1, $location);
                Efft_Tribute(1, $cost[$j], R_STONE_STORAGE);
                Efft_Chat(1, "<YELLOW> Bought $name for $cost[$j] Stone");
                Efft_Act($data[4]);
                $j++; 
                // [j = j + 1]
                if ($j < count($cost)) {
                    Efft_NameU(0, $name . " " . "({$cost[$j]} stone)", U_RELIC, $relic_spot);
                    Efft_Create(1, $unit_type, $respawn);
                    Efft_Act("$name $j");
                } else {
                    Efft_NameU(0, "No More Spawns Availible for $name", U_RELIC, $relic_spot);
                    break;
                }
            }
        } 
        // single use technology effect
        else {
            Trig($name, 1, 0);
            Cond_InAreaO(1, 1, $location);
            Cond_Accumulate(1, $cost, R_STONE_STORAGE);
            Efft_KillY(1, Y_MILITARY, $location);
            foreach ($techs as $tech) {
                Efft_Research(1, $tech);
            }
            Efft_Chat(1, "<YELLOW> Bought $name for $cost Stone");
            Efft_Tribute(1, $cost, R_STONE_STORAGE);
        }
    }
    
    function static_needs() {
        global $tower_location;
        // Creates Kill Zone in the area where enemy units spawn
        Trig("Kill Zone", 1, 1);
            Cond_InAreaO(1, 1, [array(0, 62), array(17, 70)]);
            Efft_Chat(1, "<RED> No player units are not allowed in the enemy spawning area");
            Efft_KillY(1, Y_MILITARY, [array(0, 62), array(17, 70)]);
            
        // Town Center is indestrucable
        Trig("Enemy Town Center Invunerable", 1, 1);
            Efft_InvincibleU(2, U_TOWN_CENTER); 

        Trig("Tower Health Regain", 1, 1);
            Cond_Timer(1);
            Efft_DamageY(1, -1, Y_BUILDING, $tower_location);

        Trig("Tower Health Regain x4", 0, 1);
            Cond_Timer(1);
            Efft_DamageY(1, -4, Y_BUILDING, $tower_location); 
            
        Trig("Vil Spawn", 0, 0);
            Efft_ChangeView(1, array(37, 12));
            foreach(Spawn_Box(array(37, 9), array(37, 18)) as $spawn) {
                Efft_Create(1, U_VILLAGER_F, $spawn);
            }

        Trig("Game Over", 1, 0);
            Cond_NotOwnU(1, 1, U_WATCH_TOWER);
            Cond_NotOwnU(1, 1, U_GUARD_TOWER);
            Cond_NotOwnU(1, 1, U_KEEP);
            Efft_DeclareVictory(2);
    }
    static_needs();
    
    $game_modes = ["Noob", "Easy", "Hard"];
    $chats = [
        "<GREEN> Noob mode selected. Very cute. We all start somewhere :)",
        "<GREEN> Easy Mode selected. Maybe one day you will be worth somethings",
        "<GREEN> Hero Mode Selected. Prepare for a true challange of your skills!"
    ];
    $game_units = [U_MILITIA, U_LONG_SWORDSMAN, U_CHAMPION];
    $mode_choice_area = [array(37, 27), array(41, 27)];
    $mode_select_area = [array(36, 22), array(42, 27)];
    for ($i = 0; $i < count($game_modes); $i++) {
        $cur_mode = $game_modes[$i];
        Trig("Start $cur_mode Mode");
            Cond_Timer(30);
            Efft_Chat(1, "$cur_mode started");
            Efft_Act("$cur_mode Round 0");
            Efft_Act("$cur_mode Start Game");
            
        Trig("$cur_mode Mode Starter");
            Cond_InAreaU(1, 1, $game_units[$i], $mode_choice_area);
            Efft_Chat(1, $chats[$i]);
            Efft_KillO(1, $mode_select_area);
            Efft_KillO(0, $mode_select_area);
            Efft_Deact("Default Mode Starter");  
            foreach ($game_modes as $mode) {
                if ($mode != $game_modes[$i]) {
                    Efft_Deact("Start $mode Mode");
                    Efft_Deact("$mode Mode Starter");
                }
            }
    }
    
    Trig("Default Mode Starter");
        Cond_Timer(25);
        Efft_Display(10, 0, "<RED> You didn't pick in time, so Hero mode was selected");
        Efft_KillO(1, $mode_select_area);
        Efft_KillO(0, $mode_select_area); 
        Efft_Deact("Start Noob Mode");
        Efft_Deact("Start Easy Mode");
        
    Trig("Intro Prompt", 1, 0);
        Efft_Research(1, T_TOWN_WATCH);
        Efft_Research(1, T_LOOM);
        Efft_ChangeView(1, array(37, 27));
        Efft_Display(25, 0, "Welcome to AOE2 Wave Survival. The Game will begin at 00:30, prepare for carnage");        
        Efft_Display(25, 1, 
                "You have until 00:25 to choose your difficulty level before the default of Hero mode is selected");      
        Efft_Display(25, 2, "Task an infandry unit in between the flags to select your difficulty level");
    
    function Game_Starters() {
        Trig("Noob Start Game", 0, 0);
            Efft_Tribute(1, -100000, R_GOLD_STORAGE);
            Efft_Tribute(1, -100000, R_FOOD_STORAGE);
            Efft_Tribute(1, -100000, R_WOOD_STORAGE);

        Trig("Easy Start Game", 0, 0);
            Efft_Tribute(1, -200, R_GOLD_STORAGE);
            Efft_Tribute(1, -200, R_FOOD_STORAGE);
            Efft_Tribute(1, -200, R_WOOD_STORAGE);
            Efft_Tribute(1, -200, R_STONE_STORAGE);   
            foreach(Spawn_Box(array(40, 7), array(49, 7)) as $spawn) {
                Efft_Create(1, U_VILLAGER_M, $spawn);
            }
            foreach(Spawn_Box(array(40, 8), array(49, 8)) as $spawn) {
                Efft_Create(1, U_VILLAGER_F, $spawn);
            }

        Trig("Hard Start Game", 0, 0);
            Efft_Tribute(1, -200, R_GOLD_STORAGE);
            Efft_Tribute(1, -200, R_FOOD_STORAGE);
            Efft_Tribute(1, -200, R_WOOD_STORAGE);
            Efft_Tribute(1, -200, R_STONE_STORAGE);   
            foreach(Spawn_Box(array(40, 7), array(49, 7)) as $spawn) {
                Efft_Create(1, U_VILLAGER_M, $spawn);
            }
            foreach(Spawn_Box(array(45, 8), array(49, 8)) as $spawn) {
                Efft_Create(1, U_VILLAGER_F, $spawn);
            }           
    }
    Game_Starters();
      
    $mode_count = 0;    
    foreach($game_modes as $mode) {
        $times = $mode_times[$mode_count];    
        $mode_count++;
        $points = 50; // points awarded per round
        $game_time = 30; // current game time (30 for time to select game mode)
        for ($cur_round = 0; $cur_round < count($times) - 1; $cur_round++) {
            $round_info = $round_data[$cur_round];
            $units = $round_info[0];
            $next_round = $cur_round + 1;
            // Get times and time info
            $game_time += $times[$cur_round];
            $game_min = floor($game_time / 60);
            $game_sec = $game_time % 60;
            // Time correction for display in the instructions
            if ($game_sec < 10) {
                $game_sec = "0".$game_sec;
            }
            // hack to get the times to line up
            $next_time = $times[$cur_round] + 1;
            // Create This Round
            Trig("$mode Round $cur_round", 0, 0);
            if ($units == "Feudal Age") {
                $points = 100;
            } else if ($units == "Castle Age") {
                $points = 150;
            } else if ($units == "Imperial Age") {
                $points = 200;
            } else {
                Efft_Display($times[$cur_round], 0, "Round $cur_round: $units");
            }
            Efft_Give(1, $points, STONE);
            Efft_Chat(1, "<GREEN> $points stone for round advancement");
            Efft_Display($times[$cur_round], 1, "Round $next_round begins at "
                    . "$game_min:$game_sec in $times[$cur_round] seconds");
            Efft_Display($times[$cur_round], 2, "Up Next: {$round_data[$next_round][0]}");
            Efft_Act("Round $cur_round Spawn");
            Efft_Act("$mode Round $cur_round-$next_round Timer");
            // Create The Timer for next round
            Trig("$mode Round $cur_round-$next_round Timer", 0, 0);
            Cond_Timer($next_time);
            Efft_Act("$mode Round $next_round");
        }  
        // final round
        Trig("$mode Round 40", 0, 0);
        Efft_Act("Round 40 Spawn");
        Efft_Give(1, $points, STONE);
        Efft_Display($times[count($times) - 1], 3, "Round 40 final round");
    }

    for ($i = 0; $i < count($round_data); $i++) {
        $round_info = $round_data[$i];
        $name = $round_info[0];
        $units = $round_info[1];
        $spawns = $round_info[2];
        Trig("Round $i Spawn", 0, 0);
        // assumes lengths in the above arrays are the same at $i, aka count($units) = count($spawns)
        if (is_array($units)) {
            for ($j = 0; $j < count($spawns); $j++) {
                foreach($spawns[$j] as $spawn) {
                    Efft_Create(2, $units[$j], $spawn, 0);   
                }
            }
        } else if (strcmp($units, "") !== 0) {
            foreach($spawns as $spawn) {
                Efft_Create(2, $units, $spawn, 0);   
            }   
        } else {
            // if the units field is empty, activate age up trigger
            Efft_Act($name);
        }
    }
}
?>