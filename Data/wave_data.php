<?php
    // Initalize spawns
    function Spawn_Box($bottom_left, $top_right) {
        $result = array();
        for ($i = $bottom_left[0]; $i <= $top_right[0]; $i++) {
            for ($j = $bottom_left[1]; $j <= $top_right[1]; $j++) {
                $result = array_merge($result, array(array($i, $j)));
            }
        }
        return $result;
    }
    
    $spawns1_1 = Spawn_Box(array(9, 62), array(9, 62));
    $spawns1_3 = Spawn_Box(array(8, 62), array(10, 62));
    $spawns1_5 = Spawn_Box(array(7, 62), array(11, 62));
    $spawns1_10 = Spawn_Box(array(4, 62), array(13, 62));

    // Layer 2
    $spawns2_1 = Spawn_Box(array(9, 63), array(9, 63));
    $spawns2_3 = Spawn_Box(array(8, 63), array(10, 63));
    $spawns2_5 = Spawn_Box(array(7, 63), array(11, 63));
    $spawns2_10 = Spawn_Box(array(4, 63), array(13, 63));

    $spawns20 = Spawn_Box(array(4, 64), array(13, 65));
    $spawns2_20 = Spawn_Box(array(4, 66), array(13, 67));

    $spawns3_30 = Spawn_Box(array(4, 68), array(13, 69));
    // big spawn
    $spawnsB_5 = Spawn_Box(array(7, 70), array(11, 70));

    $round_data = [
        array("Dark Age", "", ""),
        array("1 Milita", U_MILITIA, $spawns1_1), 
        array("3 Milita", U_MILITIA, $spawns1_3), 
        array("1 Ram and 5 Militia", array(U_MILITIA, U_BATTERING_RAM), array($spawns2_5, $spawns1_1)), 
        array("Feudal Age", "", ""), 
        array("5 Archers", U_ARCHER, $spawns1_5), 
        array("5 Scouts", U_SCOUT_CAVALRY, $spawns1_5), 
        array("5 Archers and 5 Scouts", array(U_ARCHER, U_SCOUT_CAVALRY), array($spawns1_5, $spawns2_5)), 
        array("10 Man At Arms", U_MAN_AT_ARMS, $spawns1_10), 
        array("20 Spearman", U_SPEARMAN, $spawns20), 
        array("10 Skrimisher and 10 Archers", array(U_SKIRMISHER, U_ARCHER), array($spawns1_10, $spawns2_10)),
        array("10 Man At Arms 10 Scouts", array(U_MAN_AT_ARMS, U_SCOUT_CAVALRY), array($spawns1_10, $spawns2_10)), 
        array("5 Knights", U_KNIGHT, $spawns1_5), 
        array("Castle Age", "", ""), 
        array("20 Crossbows", U_CROSSBOWMAN, $spawns20), 
        array("15 Knights", array(U_KNIGHT, U_KNIGHT), array($spawns1_10, $spawns2_5)), 
        array("10 Eagles and 10 Pikemen", array(U_PIKEMAN, U_EAGLE_SCOUT), array($spawns1_10, $spawns2_10)),
        array("20 monks 10 Mangonels", array(U_MONK, U_MANGONEL), array($spawns20, $spawns1_10)), 
        array("5 Rams and 20 Long Swords", array(U_LONG_SWORDSMAN, U_BATTERING_RAM), array($spawns20, $spawns1_5)), 
        array("10 Camels 20 Elite Skirms", array(U_CAMEL, U_ELITE_SKIRMISHER), array($spawns1_10, $spawns2_20)), 
        array("15 Knights 15 Light Cav", array(U_KNIGHT, U_LIGHT_CAVALRY), array($spawns1_10, $spawns20)),
        array("30 Scorpions 5 Rams", array(U_SCORPION, U_BATTERING_RAM, U_SCORPION), array($spawns1_10, $spawns2_5, $spawns20)), 
        array("25 Pikemen", array(U_PIKEMAN, U_PIKEMAN), array($spawns2_5, $spawns20)), 
        array("20 Eagles", U_EAGLE_SCOUT, $spawns20), 
        array("20 Cavalry Archers 10 Crossbows", array(U_CROSSBOWMAN, U_CAVALRY_ARCHER), array($spawns2_10, $spawns20)),
        array("20 War Elephants 20 Longbows", array(U_WAR_ELEPHANT, U_LONGBOWMAN), array($spawns20, $spawns2_20)), 
        array("Imperial Age", "", ""), 
        array("30 Hussar 5 Trebs", array(U_HUSSAR, U_TREBUCHET_P), array($spawns3_30, $spawnsB_5)), 
        array("30 Cavalier", U_PALADIN, $spawns3_30), 
        array("30 Arbs", U_ARBALEST, $spawns3_30), 
        array("30 Two Handed Sword", U_TWO_HANDED_SWORDSMAN, $spawns3_30), 
        array("20 hand cannons 5 bombard cannons", array(U_BOMBARD_CANNON, U_HAND_CANNONEER), array($spawns1_5, $spawns2_20)), 
        array("20 Heavy Camel 20 Cavalier", array(U_HEAVY_CAMEL, U_CAVALIER), array($spawns20, $spawns2_20)), 
        array("40 Halberdier", array(U_HALBERDIER, U_HALBERDIER), array($spawns20, $spawns2_20)), 
        array("15 Siege Onager 10 Heavy Scorpion", array(U_HEAVY_SCORPION, U_SIEGE_ONAGER), array($spawns1_10, $spawns2_10)), 
        array("30 Heavy Cal Arch and 10 Siege Rams", array(U_SIEGE_RAM, U_HEAVY_CAVALRY_ARCHER), array($spawns1_10, $spawns3_30)), 
        array("20 Champion 20 Elite Eagles", array(U_CHAMPION, U_ELITE_EAGLE_WARRIOR), array($spawns2_20, $spawns20)),
        array("30 Paladin", U_PALADIN, $spawns3_30), 
        array("30 Petards", U_PETARD, $spawns3_30), 
        array("Full imperial trash line (skrims, halbs, hussar)", array(U_HALBERDIER, U_HUSSAR, U_SKIRMISHER), array($spawns20, $spawns2_20, $spawns3_30)), 
        array("Full imperial gold line (arbs, champion, palidin)", array(U_CHAMPION, U_PALADIN, U_ARBALEST), array($spawns20, $spawns2_20, $spawns3_30))
    ];
    // [relic label, cost, array of technologies to research, unit (if respawning), trigger event, relic location]
    $tech_data = [
        // Archer Line
        array("Fletching and Padded Archer Armor", 300, array(T_FLETCHING, T_PADDED_ARCHER_ARMOR), 0, "", array(34, 30)),
        array("Man At Arms, Forging, Scale Mail Armor", 300, array(T_MAN_AT_ARMS, T_FORGING, T_SCALE_MAIL_ARMOR), 0, "", array(34, 32)),
        array("Bloodlines, Scale Barding Armor", 300, array(T_BLOODLINES, T_SCALE_BARDING_ARMOR), 0, "", array(34, 34)),
        
        array("Crossbow, Elite Skirms, Balistics, Bodkin, and Leather Archer Armor", 600, array(
            T_CROSSBOWMAN, T_ELITE_SKIRMISHER, T_BODKIN_ARROW, T_BALLISTICS, T_LEATHER_ARCHER_ARMOR), 0, "", array(34, 38)),
        array("Long Swordsman, Eagle Scout, Pikemen, Chain Mail Armor", 600, array(
            T_LONG_SWORDSMAN, T_EAGLE_SCOUT_A, T_PIKEMAN, T_CHAIN_MAIL_ARMOR), 0, "", array(34, 40)),
         array("Light Cavalry, Chain Barding Armor, Iron Casting", 600, array(
            T_LIGHT_CAVALRY, T_CHAIN_BARDING_ARMOR, T_IRON_CASTING), 0, "", array(34, 42)),       
        
        array("Arbalest, Heavy Cav Archer, Bracer, Ring Archer Armor", 500, array(
            T_ARBALEST, T_HEAVY_CAVALRY_ARCHER, T_BRACER, T_RING_ARCHER_ARMOR), 0, "", array(34, 46)),
        array("Champian, Halbs, Plate Mail Armor, Blast Furnace", 1000, array(
            T_TWO_HANDED_SWORDSMAN, T_CHAMPION, T_HALBERDIER, T_PLATE_MAIL_ARMOR, T_BLAST_FURNACE), 0, "", array(34, 48)),
        array("Palidin, Hussar, Heavy Camel, Plate Barding Armor", 1500, array(
            T_CAVALIER, T_PALADIN, T_HUSSAR, T_HEAVY_CAMEL, T_PLATE_BARDING_ARMOR), 0, "", array(34, 50)),
        array("Imperial Seige", 500, array(T_HEAVY_SCORPION, T_ONAGER, T_CAPPED_RAM), 0, "", array(34, 52)),
        array("Post-Imperial Seige", 500, array(T_SIEGE_RAM, T_SIEGE_ONAGER), 0, "", array(34, 54)),
        array("Chemistry", 750, array(T_CHEMISTRY), 0, "", array(34, 56)),
        
        // 2nd row
        array("Hail Mary (kill all units on field)", 500, array(), 0, "Hail Mary", array(41, 42)),
        
        // 3rd row trigger activators
        array("Spawn 5 Villagers", array(200, 400, 600, 800, 1000), array(), U_VILLAGER_M, "Vil Spawn 5", array(48, 30)),
        array("Clear Tower of Enemies", array(100, 200, 300, 400, 500, 600, 700, 800, 900, 1000), array(), U_PETARD, "Bomb Area", array(48, 32)),
        array("Add 1000 Health to your Tower", array(100, 200, 300, 400, 500), array(), U_MONK, "Add 1000 Health", array(48, 34)),
        array("Create Castle", 1000, array(), U_FURIOUS_THE_MONKEY_BOY, "Castle Creation", array(100, 100)),
        array("Guard Tower, Tower Attack x2, Tower Regain x4", 300, array(), U_MONK, "Guard Tower Upgrade", array(40, 32)),
        array("Tower Health Regeneration x2", 150, array(), U_MONK, "Health Regain Upgrade", array(100, 100)),
        array("Keep, Tower Health 6000", 500, array(T_KEEP), U_MONK, "Keep Upgrade", array(100, 100))
    ];
?>