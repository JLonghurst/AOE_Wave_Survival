<?php 
spl_autoload_register(function ($class_name) {
    include 'src/scenario/' . $class_name . '.php';
});

SCX::$microtime = microtime(true);



# PHP SCX Editor v2.4.31 - By AOHH and AzZzRu (Help thread: http://aok.heavengames.com/cgi-bin/forums/display.cgi?action=ct&f=26,42243,,30)
# Run this file two times to build your scenario that you wrote in Scenario.php. If you run it once activations/deactivations effects won't work.
# Script is made for 1.4 RC or HD maps, it can work for 1.0c if you don't use 1.4 RC / HD features (new triggers effects).
# HD Designer: PSE can't read HD scenarios, so use 1.0c / 1.4 RC maps as input file. Set $editor_version to 'HD' to use new triggers effects.


# --- Compiler configuration --- #

SCX::$scenarios_path = 'C:\Users\Jacob\Games\Age of Empires 2 DE\76561198067817983\resources\_common\scenario';		
SCX::$input_scenario = 'large.scx';
SCX::$output_name = 'WAVE_SURVIVAL_BUILD_YEET.scx';
SCX::$hide_triggers = false; # Hide triggers names
SCX::$resized_format = false; # Resized format will compress the scenario as much as possible for smaller file size
SCX::$triggers_version = 1.6; # 1.6 is version used by aoc, but you can down it to 1.3 for smaller file size, without removing important features
SCX::$editor_version = 'HD'; # 1.0c / 1.4RC / HD - Enable new triggers effects depending of aoc version.



# --- Compiler code --- #

# Code below is used to read/write scx files, modify it can break the script.
# You don't need to understand it to make scenarios.

require 'Data/data_aok.php'; # Import aoc constants
require 'Library.php'; # Import standard library functions

require 'Data/wave_data.php';
require 'src/scenario/Scenario.php'; # Import scenario code
# Add your extra project files / libraries here
# IMPORTANT: You should name your custom functions with camelCase. PascalCase should be reserved for standard libraries, it will prevent conflicts.


if(file_exists('Triggers.inc')) include 'Triggers.inc'; # Get triggers IDs from last compilation

SCX::$TgAr = isset($TriggerId) ? $TriggerId:array();
SCX::init(); # Init SCX converter
SCX::format(); # Convert SCX scenario to PHP format

Scenario(SCX::$data_serial); # Modify scenario with PHP

$Conts = '<?php $TriggerId = array(';
foreach(SCX::$G as $_I => $I) $Conts .= '"' . $_I . '"=>' . $I . ','; # Reference triggers IDs by triggers name
$Conts .= '); ?>';
file_put_contents('Triggers.inc',$Conts);

SCX::format(false); # Convert PHP scenario to SCX format

SCX::Write(); # Add triggers in SCX format

file_put_contents('new_head.hex',SCX::$data_head);
file_put_contents('new_body.hex',SCX::$data_body);

file_put_contents(SCX::$scenarios_path.'\\'.SCX::$output_name,SCX::$data_head.gzdeflate(SCX::$data_body.SCX::$data_foot,9)); # Export scenario

SCX::$microtime = microtime(true)- SCX::$microtime;

# Write Results:
echo "\nTIME: ".SCX::$microtime.'';
echo "\nFILE: ".SCX::$output_name;
echo "\nTRIGGERS ".(SCX::$ID_T + 1);
echo "\nEFFECTS ".SCX::$E_CT."\n";

$TriggerId = array_flip(SCX::$TgAr);
# Echo triggers not found by activate / deactivate effects
foreach(SCX::$FK as $Trig => $Fake) echo '<p><b>' . @$TriggerId[$Trig]. '</b> ===>>> ' . $Fake . '</p>';
# Echo unused triggers (off at start and never activated)
foreach(SCX::$off_triggers as $TrigName => $Data)if(!isset($Data[1]))echo '<p><b>' . $TrigName . '</b> --->>> Unused !</p>';
echo '</body>';
echo "\n\n\n\n\n";
# Decompression / Compression of the SCX format
class SCX {
    # Configuration variables
    static $scenarios_path;
	static $input_scenario;
    static $output_name;
    static $hide_triggers;
    static $triggers_version;
    static $resized_format;
    static $editor_version;
    # System variables
    static $microtime;
    static $off_triggers = array();
    static $player_triggers = array();
	static $data_head;
	static $data_body;
	static $data_foot;
	static $data_serial;
	static $obj_inc = -999999;
    static $triggers_effects;
    static $triggers_conditions;
    # Variable used for caching triggers into arrays:
    static $O = array();
    # Variable used for caching triggers into coding:
    static $X = '';
    # Variables
    static $G = array();
    static $TgAr = array();
    static $C_CT = 0;
    static $E_CT = 0;
    static $FK = array();
    # Incremented identity for variables of trigger writer:
    static $ID_T = -1;
    static $ID_E = 0;
    static $ID_C = 0;

    static function init(){
        self::$triggers_effects = array(
			'None' => 0,
			'ChangeDiplomacy' => 1,
			'ResearchTechnology' => 2,
			'SendChat' => 3,
			'PlaySound' => 4,
			'SendTribute' => 5,
			'UnlockGate' => 6,
			'LockGate' => 7,
			'ActivateTrigger' => 8,
			'DeactivateTrigger' => 9,
			'AIScriptGoal' => 10,
			'CreateObject' => 11,
			'TaskObject' => 12,
			'DeclareVictory' => 13,
			'KillObject' => 14,
			'RemoveObject' => 15,
			'ChangeView' => 16,
			'Unload' => 17,
			'ChangeOwnership' => 18,
			'Patrol' => 19,
			'DisplayInstructions' => 20,
			'ClearInstructions' => 21,
			'FreezeUnit' => 22,
			'UseAdvancedButtons' => 23,
			'DamageObject' => 24,
			'PlaceFoundation' => 25,
			'ChangeObjectName' => 26,
			'ChangeObjectHP' => 27,
			'ChangeObjectAttack' => 28,
			'StopUnit' => 29
        );
        
        self::$triggers_conditions = array(
			'None' => 0,
			'BringObjectToArea' => 1,
			'BringObjectToObject' => 2,
			'OwnObjects' => 3,
			'OwnFewerObjects' => 4,
			'ObjectsInArea' => 5,
			'DestroyObject' => 6,
			'CaptureObject' => 7,
			'AccumulateAttribute' => 8,
			'ResearchTechnology' => 9,
			'Timer' => 10,
			'ObjectSelected' => 11,
			'AISignal' => 12,
			'PlayerDefeated' => 13,
			'ObjectHasTarget' => 14,
			'ObjectVisible' => 15,
			'ObjectNotVisible' => 16,
			'ResearchingTechnology' => 17,
			'UnitsGarrisoned' => 18,
			'DifficultyLevel' => 19
        );
        
        if(self::$editor_version === '1.4RC'){
            self::$triggers_effects['ChangeSpeed'] = 30;
            self::$triggers_effects['ChangeRange'] = 31;
            self::$triggers_effects['ChangeArmor1'] = 32;
            self::$triggers_effects['ChangeArmor2'] = 33;
        }
        else if(self::$editor_version === 'HD'){
            self::$triggers_effects['AttackMove'] = 30;
            self::$triggers_effects['ChangeArmor'] = 31;
            self::$triggers_effects['ChangeRange'] = 32;
            self::$triggers_effects['ChangeSpeed'] = 33;
            self::$triggers_effects['HealObject'] = 34;
            self::$triggers_effects['TeleportObject'] = 35;
            self::$triggers_effects['ChangeUnitStance'] = 36;
        }
        else if(self::$editor_version !== '1.0c'){
            self::Error('Unknown $editor_version: '.$editor_version.' (It should be 1.0c, 1.4RC or HD)');
        }
    }

    static function format($module = true){
        $scfile = self::$scenarios_path.'\\'.self::$input_scenario;
    	
        $padd = pack('c',0);
    
    	if(!$module)$serial = self::$data_serial;
    
    	# define scenario file
    	if($module)if(!is_file($scfile))self::Error("\"$scfile\" not found !");
    
    	# define uncompressed stream
    	if($module)if(!$stream = file_get_contents($scfile))self::Error('Cannot open the scenario file !');
    
    	# general / version / 1
    	if($module){
    		$serial['version'][0x01] = substr($stream,0,4);
            $cursor = 4;}
    	else{
    		$header = $serial['version'][0x01];}
    
    	# general / header
    	if($module)
    		$cursor += 4;
    	else{
    		$strlen = strlen($string = trim((string)$serial['message']['transcript'],"\0"));
    		$strlen = strlen($serial['message']['transcript'] = (string)$string.($strlen ? $padd:null));
    		$header .= pack('l',(20 + $strlen));}
    
    	# general / unknown / 1
    	if($module)
    		$cursor += 4;
    	else
    		$header .= pack('l1',2); # usually 1 or 2,but rarely 0
    
    	# general / timestamp
    	if($module)
    		$cursor += 4;
    	else
    		$header .= pack('l1',time()); # current timestamp
    
    	# message / briefing
    	if($module){
    		$unpack = unpack('l',substr($stream,$cursor,4)); $cursor += 4;
    		$serial['message']['transcript'] = $string = @(string)substr($stream,$cursor,$unpack[1]);
    		$cursor += strlen($string);}
    	else{
    		$header .= pack('l',strlen($string = (string)$serial['message']['transcript'])).$string;}
    
    	# general / unknown / 2
    	if($module)
    		$cursor += 4;
    	else
    		$header .= pack('l1',1); # usually 1,sometimes 0
    
    	# players / count / 1
    	if($module){
    		$unpack = unpack('l',substr($stream,$cursor,4)); $cursor += 4;
    		$serial['players']['count'][0x01] = $unpack[1];
    		file_put_contents('old_head.hex',substr($stream,0,$cursor));
    		if(!file_put_contents('old_body.hex',gzinflate(substr($stream,$cursor))))self::Error('Cannot inflate scenario data into stream !');
    		if(!$stream = fopen('old_body.hex','r'))self::Error('Cannot open deflation stream !');}
    	else{
    		$header .= pack('l',$serial['players']['count'][0x01]);
    		self::$data_head = $header;}
    
    	# objects / increment
    	if($module){
    		$unpack = unpack('l1',fread($stream,4));
    		$serial['objects'] = $unpack[1];}
    	else{
            $packed = null;
    		$packed .= pack('l1',(int)$serial['objects']);}
    
    	# version / 2
    	if($module){
    		$unpack = unpack('f',fread($stream,4));
    		$serial['version'][0x02] = $unpack[1];}
    	else
    		$packed .= pack('f',$serial['version'][0x02]);
    
    	# define players
    	$players = range(1,16);
    
    	# players / name
    	$length = 256;
    	if($module)
    		foreach($players as $player)
    			$serial['player'][$player]['name'] = (string)trim(fread($stream,$length),"\0"); # remove null-padding
    	else
    		foreach($players as $player)
    			$packed .= pack('a'.$length,$serial['player'][$player]['name']);
    
    	# players / string
    	if($module)
    		fread($stream,(4 * 16));
    	else
    		$packed .= str_repeat(pack('l',-1),16);
    
    	# players / config
    	foreach($players as $player){
    		if($module){
    			$unpack = unpack('l4',fread($stream,16));
    			$serial['player'][$player]['boolean'] = $unpack[1];
    			$serial['player'][$player]['machine'] = $unpack[2];
    			$serial['player'][$player]['profile'] = $unpack[3];
    			$serial['player'][$player]['unknown'] = $unpack[4];}
    		else{
    			$packed .= pack('l',(int)$serial['player'][$player]['boolean']);
    			$packed .= pack('l',(int)$serial['player'][$player]['machine']);
    			$packed .= pack('l',(int)$serial['player'][$player]['profile']);
    			$packed .= pack('l',(int)$serial['player'][$player]['unknown']);}} # usually = 4(this might be "baseline";as seen in the language.dll)
    
    	# message / unknowns
    	if($module){
    		$unpack = unpack('l',fread($stream,4));
    		$serial['message']['option'][0x01] = $unpack[1];
    		$unpack = unpack('c',fread($stream,1));
    		$serial['message']['option'][0x02] = $unpack[1];
    		$unpack = unpack('f',fread($stream,4));
    		$serial['message']['option'][0x03] = $unpack[1];}
    	else{
    		$packed .= pack('l',$serial['message']['option'][0x01]);  # usually 0(if above 500,it crashes)
    		$packed .= pack('c',$serial['message']['option'][0x02]);  # usually 1,sometimes 0
    		$packed .= pack('f',$serial['message']['option'][0x03]);} # usually -1(but why a float? maybe this is a timer...)
    	
    
    	# message / filename(shown in cinematics without the last 4 characters ".scn/.scx"... but what about AoF HD ".scx2"?)
    	if($module){
    		$unpack = unpack('s',fread($stream,2));
    		$serial['filename'] = fread($stream,$unpack[1]);}
    	else{
    		$packed .= pack('s',strlen($string = $serial['filename'])).$string;}
    
    	# message / strings
    	# 0x01 = objective
    	# 0x02 = hints
    	# 0x03 = victory
    	# 0x04 = failure
    	# 0x05 = history
    	# 0x06 = scouts
    	foreach(range(0x01,0x06)as $struct){
    		if($module){
    			$unpack = unpack('l',fread($stream,4));
    			$serial['message']['string'][$struct] = $unpack[1];}
    		else{
    			$packed .= pack('l',$serial['message']['string'][$struct]);}}
    
    	# message / scripts
    	# 0x01 = objective
    	# 0x02 = hints
    	# 0x03 = victory
    	# 0x04 = failure
    	# 0x05 = history
    	# 0x06 = scouts
    	foreach(range(0x01,0x06)as $struct){
    		if($module){
    			$unpack = unpack('s',fread($stream,2));
    			$serial['message']['directive'][$struct] = $string = @(string)fread($stream,$unpack[1]);}
    		else{
    			$strlen = strlen($string = trim((string)$serial['message']['directive'][$struct],"\0"));
    			$strlen = strlen($string = $string.($strlen ? $padd:null));
    			$packed .= pack('s',$strlen).$string;}}
    
    	# message / cinematics
    	# 0x01 = pregame
    	# 0x02 = victory
    	# 0x03 = failure
    	# 0x04 = background
    	foreach(range(0x01,0x04)as $struct){
    		if($module){
    			$unpack = unpack('s',fread($stream,2));
    			$serial['message']['cinematic'][$struct] = @(string)fread($stream,$unpack[1]);}
    		else{
    			$packed .= pack('s',strlen($string = $serial['message']['cinematic'][$struct])).$string;}}
    
    	# message / bitmap
    	if($module){
    		# bitmap / external data
    		$unpack = unpack('l3',fread($stream,12));
    		$serial['bitmap']['boolean'] = $length = $unpack[1];
    		$serial['bitmap']['size_w'] = $w = $unpack[2];
    		$serial['bitmap']['size_h'] = $h = $unpack[3];
    		$unpack = unpack('s',fread($stream,2));
    		$serial['bitmap']['default'] = $unpack[1];
    
    		# bitmap / internal data
    		if($length > 0)
    			$serial['bitmap']['image'] = fread($stream,(40 + 1024 + ($w * $h)));
    		else
    			$serial['bitmap']['image'] = null;}
    	else{
    		$packed .= pack('l',$length = $serial['bitmap']['boolean']);
    		$packed .= pack('l',$serial['bitmap']['size_w']);
    		$packed .= pack('l',$serial['bitmap']['size_h']);
    		$packed .= pack('s',$serial['bitmap']['default']);
    		if($length)$packed .= $serial['bitmap']['image'];}
    
    	# behaviors(i believe that the strategy and city-plan files actually work in AoK/AoC)
    	# 0x01 = strategy(AoE only)".CD files"
    	# 0x02 = city plan(AoE only)".CTY files"
    	# 0x03 = personality AI ".PER files"
    	# behavior / names
    	foreach(range(0x01,0x03)as $struct)
    		foreach($players as $player)
    			if($module){
    				$unpack = unpack('s',fread($stream,2));
    				$serial['behavior'][$player][0x01][$struct] = @(string)fread($stream,$unpack[1]);}
    			else{
    				$packed .= pack('s',strlen($string = $serial['behavior'][$player][0x01][$struct])).$string;}
    
    	unset($length);
    
    	# behavior / size & data(here is a strange way of storing data... why not use traditional method of storing strings?)
    	foreach($players as $player){
    		# behavior / size
    		foreach(range(0x01,0x03)as $struct){
    			if($module){
    				$unpack = unpack('l1',fread($stream,4));
    				$length[$struct] = $unpack[1];}
    			else
    				$packed .= pack('l1',strlen($serial['behavior'][$player][0x02][$struct]));}
    		# behavior / data
    		foreach(range(0x01,0x03)as $struct){
    			if($module)
    				$serial['behavior'][$player][0x02][$struct] = @(string)fread($stream,$length[$struct]);
    			else
    				$packed .= $serial['behavior'][$player][0x02][$struct];}} # null-padding not needed for such a big structure? wtf?
    
    	# behavior / type
    	if($module){
    		$unpack = unpack('c16',fread($stream,16));
    		foreach($players as $player)
    			$serial['behavior'][$player][0x03] = $unpack[$player];}
    	else
    		foreach($players as $player)
    			$packed .= pack('c',$serial['behavior'][$player][0x03]);
    
    
    	# general / separator / 1
    	# there are 4 seperators used in scenarios;and they all = -99,however they still work with a different value
    	if($module)
    		fread($stream,4);
    	else
    		$packed .= pack('l',-99);
    
    	# player / config(2)(the sources stored in this section are integers and they were used in older versions;yet unactive by AoK/AoC but still follows the changes.
    	# basically,the developers later needed floating-point numbers;hence they used another section to store them)
    	foreach($players as $player){
    		if($module)
    			fread($stream,24);
    		else{
                if(isset($serial['player'][$player]['source'])){
        			$packed .= pack('l',$serial['player'][$player]['source']['gold']);
        			$packed .= pack('l',$serial['player'][$player]['source']['wood']);
        			$packed .= pack('l',$serial['player'][$player]['source']['food']);
        			$packed .= pack('l',$serial['player'][$player]['source']['rock']);
        			$packed .= pack('l',$serial['player'][$player]['source']['iron']);
        			$packed .= pack('l',$serial['player'][$player]['source']['padd']);}
                else{
        			$packed .= pack('l',null);
        			$packed .= pack('l',null);
        			$packed .= pack('l',null);
        			$packed .= pack('l',null);
        			$packed .= pack('l',null);
        			$packed .= pack('l',null);}}}
    
    	# general / separator / 2
    	if($module)
    		fread($stream,4);
    	else
    		$packed .= pack('l',-99);
    
    	# victory / globals(some of these do not work in AoK/AoC)
    	# 0x01 = conquest
    	# 0x02 = ruins
    	# 0x03 = artifacts
    	# 0x04 = discoveries
    	# 0x05 = explored
    	# 0x06 = gold count
    	# 0x07 = required
    	# 0x08 = condition
    	# 0x09 = score
    	# 0x0A = time limit
    	foreach(range(0x01,0x0A)as $struct){
    		if($module){
    			$unpack = unpack('l',fread($stream,4));
    			$serial['victory']['global'][$struct] = $unpack[1];}
    		else
    			$packed .= pack('l',$serial['victory']['global'][$struct]);}
    
    	# victory / diplomacy / player / stance
    	if($module){
    		foreach($players as $player)
    			$serial['victory']['diplomacy']['stance'][$player] = unpack('l16',fread($stream,64));}
    	else{
    		foreach($players as $player)foreach($players as $target)
    			$packed .= pack('l',$serial['victory']['diplomacy']['stance'][$player][$target]);}
    
    	# victory / individual-victory(12 triggers per players)(they are unused in AoK/AoC once the new trigger system was introduced)
    	$length = 16 * 12 * 15 * 4;
    	if($module)
    		fread($stream,$length);
    	else
    		$packed .= pack('a'.$length,null);
    
    	# general / separator / 3
    	if($module)
    		fread($stream,4);
    	else
    		$packed .= pack('l',-99);
    
    	# victory / diplomacy / player / allied
    	if($module)
    		$serial['victory']['diplomacy']['allied'] = unpack('l16',fread($stream,64));
    	else{
    		foreach($players as $player)
    			$packed .= pack('l',$serial['victory']['diplomacy']['allied'][$player]);}
    
    	# disability / techtree
    	$params = array(
    		1 => 30,   # Techs
    		2 => 30,   # Units
    		3 => 20 ); # Buildings
    	
    	if($module){
    		foreach($params as $struct => $counts){
    			fread($stream,64);
    			foreach($players as $player)
    				$serial['disabled']['techtree'][$player][$struct] = unpack('l'.$counts,fread($stream,(4 * $counts)));}}
    	else{
    		foreach($params as $struct => $counts){
    			foreach($players as $player){
    				$length = 0;
    				foreach($serial['disabled']['techtree'][$player][$struct] as $number)
    					if($number != -1) $length++;
    				$packed .= pack('l',$length);}
    			foreach($players as $player){
    				foreach(range(1,$counts) as $number)
    					$packed .= pack('l',(int)$serial['disabled']['techtree'][$player][$struct][$number]);}}}
    
    	# disability / options
    	if($module)
    		$serial['disabled']['options'] = unpack('l3',fread($stream,12));
    	else{
    		$packed .= pack('l',$serial['disabled']['options'][0x01]); # unknown 1
    		$packed .= pack('l',$serial['disabled']['options'][0x02]); # unknown 2
    		$packed .= pack('l',$serial['disabled']['options'][0x03]);} # fulltechs
    
    	# disability / starting age
    	if($module)
    		$serial['disabled']['initial'] = unpack('l16',fread($stream,64));
    	else
    		foreach($players as $player)
    			$packed .= pack('l',$serial['disabled']['initial'][$player]);
    
    	# general / separator / 4
    	if($module)
    		$unpack = unpack('l',fread($stream,4));
    	else
    		$packed .= pack('l',-99);
        
        # terrain / view
        if($module)
            $unpack = unpack('l2',fread($stream,8));
        else
            $packed .= pack('l2',$serial['player'][1]['view'][1][1],$serial['player'][1]['view'][1][0]);
    
    	# terrain / type
    	if($module)
    		fread($stream,4);
    	else
    		$packed .= pack('l1',0);
    
    	# terrain / size
    	if($module)
    		$serial['terrain']['size'] = $length = unpack('l2',fread($stream,8));
    	else{
    		$length = $serial['terrain']['size'];
    		$packed .= pack('l2',$length[1],$length[2]);}
    
    	# terrain / data
        if ($module)
        {
            for($y = 0; $y < $length[2]; $y++)for($x = 0; $x < $length[1]; $x++)
            {
                $unpack = unpack('c2',fread($stream,3));
                $serial['terrain']['data'][$y.','.$x] = $unpack;
            }
        }
        else
        {
            for($y = 0; $y < $length[2]; $y++)for($x = 0; $x < $length[1]; $x++)
            {
    			$packed .= pack('c3',$serial['terrain']['data'][$y.','.$x][1],$serial['terrain']['data'][$y.','.$x][2],0);
            }
        }
    
    	# players / count / 2(i have tested this on below and above 9,it works even on 2500! although beware when placing objects on extra players as it will sometimes crash depending on the player;and some players ID are duplicates of other players;mostly Gaia)
    	if($module){
    		$unpack = unpack('l',fread($stream,4));
    		$serial['players']['count'][0x02] = $players = $unpack[1];}
    	else
    		$packed .= pack('l',$players = $serial['players']['count'][0x02]);
    
    	# loop each player
    	for($player = 1; $player < $players; $player++){
    		# player / sources & config
    		if($module){
    			$unpack = unpack('f7',fread($stream,(4 * 7))); # these will work for AoK/AoC,but why duplicates of resources? because the later versions needed floating-points;so you can set sources and population to decimals
    			$serial['player'][$player]['source']['food'] = $unpack[1];
    			$serial['player'][$player]['source']['wood'] = $unpack[2];
    			$serial['player'][$player]['source']['gold'] = $unpack[3];
    			$serial['player'][$player]['source']['rock'] = $unpack[4];
    			$serial['player'][$player]['source']['iron'] = $unpack[5];
    			$serial['player'][$player]['source']['padd'] = $unpack[6];
    			$serial['player'][$player]['source']['popl'] = $unpack[7];}
    		else{
    			$packed .= pack('f',$serial['player'][$player]['source']['food']);
    			$packed .= pack('f',$serial['player'][$player]['source']['wood']);
    			$packed .= pack('f',$serial['player'][$player]['source']['gold']);
    			$packed .= pack('f',$serial['player'][$player]['source']['rock']);
    			$packed .= pack('f',$serial['player'][$player]['source']['iron']);
    			$packed .= pack('f',$serial['player'][$player]['source']['padd']);
    			$packed .= pack('f',$serial['player'][$player]['source']['popl']);}}
    
    	# objects / players
    	for($player = 0; $player < $players; $player ++){
    		# objects / player / count
    		if($module){
    			$unpack = unpack('l1',fread($stream,4));
    			$objects = $unpack[1];}
    		else
    			$packed .= pack('l1',$objects = count($serial['object'][$player]));
            
    		# objects / player / object
    		if($module){
                $serial['object'][$player] = array();
    			for($object = 0; $object < $objects; $object ++){
    				# positions XYZ(note that when objects are out-of-bounds the game will place them correctly;but this is unstable and sometimes even crashes;therefore beware when placing objects accross the map with scripted method)
    				$struct = unpack('f3',fread($stream,12)); #(Z-axis is for projectiles only;does not affect buildings/units,and also i have not tested if above or below a certain height)
    				# identity(not sure what would happen if there were multiple objects with same ID)(also,make sure to not use -1 as the ID so you may use it in triggers;since -1 is the default for triggers)
    				$unpack = unpack('l',fread($stream,4));
    				$struct[0x04] = $unpack[1];
    				# constant
    				$unpack = unpack('s',fread($stream,2));
    				$struct[0x05] = $unpack[1];
    				# progress(-1 = hidden,0 = founded,1 = construct,2 = alive,3+ = destruct)(if not = 2;it crashes in testing/saving;so test via standard game instead)
    				$unpack = unpack('c',fread($stream,1));
    				$struct[0x06] = $unpack[1];
    				# rotation(from 0-7 as radians(8 angles;but this is a float;therefore it can also handle 16 angles or more)
    				$unpack = unpack('f',fread($stream,4));
    				$struct[0x07] = $unpack[1];
    				# animated(number of frames depends on the unit's graphic;most have 10 frames)
    				$unpack = unpack('s',fread($stream,2));
                     $struct[0x08] = $unpack[1];
    				# garrison(default = -1)
    				$unpack = unpack('l',fread($stream,4));
    				if($unpack[1] != -1)$struct[0x09] = $unpack[1];
                    $serial['object'][$player][] = $struct;}}
    		else
    			foreach($serial['object'][$player] as $object){
    				$packed .= pack('f3',$object[0x01],$object[0x02],$object[0x03]); # positions XYZ
    				$packed .= pack('l',$object[0x04]); # identity
    				$packed .= pack('s',$object[0x05]); # constant
    				$packed .= pack('c',$object[0x06]); # progress
    				$packed .= pack('f',$object[0x07]); # rotation
    				$packed .= pack('s',$object[0x08]); # animated
    				$packed .= pack('l',(isset($object[0x09])? $object[0x09]:-1));}} # garrison
    	
    	# players / count / 3
    	if($module){
    		$unpack = unpack('l1',fread($stream,4)); # should always = 9,but not yet tested if != 9
    		$serial['players']['count'][0x03] = $players = $unpack[1];}
    	else
    		$packed .= pack('l1',$players = $serial['players']['count'][0x03]);
    
    	for($player = 1; $player < $players; $player ++){
    		# player / script(this is the ancient "tribe name" which is now unused;it is a constant string of "Player #" with null-padding)
    		if($module){
    			$unpack = unpack('s',fread($stream,2));
    			$serial['player'][$player]['subtitle'] = trim(fread($stream,$unpack[1]),"\0");}
    		else{
    			$packed .= pack('s',strlen($string = $serial['player'][$player]['subtitle']. pack('c',0))).$string;}
    
    		# player / views
    		if($module){
    			# view / initial
    			$unpack = unpack('f2',fread($stream,8));
    			$serial['player'][$player]['view'][0x01] = array($unpack[1],$unpack[2]); # this is a float
    			# view / unknown
    			$unpack = unpack('s2',fread($stream,4));
    			$serial['player'][$player]['view'][0x02] = array($unpack[1],$unpack[2]);} # this is an integer(not sure why there is a second view;maybe because they wanted floating-point)
    		else{
    			# view / initial
    			$packed .= pack('f2',$serial['player'][$player]['view'][0x01][0],$serial['player'][$player]['view'][0x01][1]);
    			# view / unknown
    			$packed .= pack('s2',$serial['player'][$player]['view'][0x02][0],$serial['player'][$player]['view'][0x02][1]);}
    
    		# player / diplomacy(this diplomacy is different from the one found in the victory section;it affects the gameplay between each player)
    		if($module){
    			# diplomacy / allied
    			$unpack = unpack('c',fread($stream,1));
    			$serial['player'][$player]['diplomacy']['allied'] = $unpack[1];
    			# diplomacy / count(always = 9;however i have never tested if not 9;and why do we need this anyway since player-count-3 is always = 9)
    			$unpack = unpack('s',fread($stream,2));
    			$length = $unpack[1];
    			# diplomacy / stance / 1
    			$unpack = unpack('c' . $length,fread($stream,$length));
    			$serial['player'][$player]['diplomacy']['stance_1'] = array_merge($unpack);
    			# diplomacy / stance / 2
    			$unpack = unpack('l' . $length,fread($stream,(4 * $length)));
    			$serial['player'][$player]['diplomacy']['stance_2'] = array_merge($unpack);
                }
    		else{
    			# diplomacy / allied
    			$packed .= pack('c',$serial['player'][$player]['diplomacy']['allied']);
    			# diplomacy / count
    			$packed .= pack('s',$players);
    			# diplomacy / stance / 1
    			for($i = 0; $i < $players; $i++)$packed .= pack('c',$serial['player'][$player]['diplomacy']['stance_1'][$i]);
    			# diplomacy / stance / 2
    			for($i = 0; $i < $players; $i++)$packed .= pack('l',$serial['player'][$player]['diplomacy']['stance_2'][$i]); 
            }
    
    		# player / color(starts from 0;so therefore 0 = blue,1 = red,ect...)(not known if above 9)
    		if($module){
    			$unpack = unpack('l',fread($stream,4));
    			$serial['player'][$player]['color'] = $unpack[1];}
    		else
    			$packed .= pack('l',$serial['player'][$player]['color']);
    
    		# player / victory / version(this defines the version of the ancient victory conditions;unused now)
    		if($module){
    			$unpack = unpack('f',fread($stream,4));
    			$number = $unpack[1];}
    		else
    			$packed .= pack('f',2.0);
    
    		# player / victory / triggers / count(there should probably be only 12 maximum like the victory triggers;however has not been tested yet)
    		if($module){
    			$unpack = unpack('s',fread($stream,2));
    			$triggers = $unpack[1];}
    		else
    			$packed .= pack('s',0); # let's forget about any victory triggers;set to 0
    
    		# player / victory / values(this seems to be the much older global victories consisting of the 8 of 10 earlier values;however,not sure since these seem to be used only in pre-alpha versions)
    		$length = 8;
    		if($module){
    			if((int)$number == 2)
    				$serial['player'][$player]['special'][1] = unpack('c'.$length,fread($stream,$length));}
    		else foreach(range(1,$length)as $struct)
    			$packed .= pack('c',$serial['player'][$player]['special'][1][$struct]);
    
    		# player / triggers / trigger(the ancient triggers,also known as the oldest individual victory;and i believe them to be unused yet they still change according to modifications)
    		if($module)
    			@ fread($stream,($triggers * 11 * 4));
    
    		# player / unknown(this was probably the ancient storage for configs and sources;but i'm not entirely sure,i still need to experiment)
    		$length = 7;
    		if($module)
    			$serial['player'][$player]['special'][2] = unpack('c' . $length,fread($stream,$length));
    		else foreach(range(1,$length)as $struct)
    			$packed .= pack('c',$serial['player'][$player]['special'][2][$struct]);
    
    		# player / victory / unknown(maybe this was the ancient starting age. default = 0 if vict. ver. <= 1,but -1 if vict. ver. > 1.0;and why? because later versions used -1 instead of 0 as defaults)
    		if($module)
    			fread($stream,4);
    		else
    			$packed .= pack('l',-1);}
    
    	if($module)
    		self::$data_serial = $serial;
    	else
    		self::$data_body = $packed;
    }

    # This function must be absolutely perfect. The slightest error is not acceptable.
    # It writes all triggers array into raw scenario data. I prefer the Pack function rather than Sprintf.
    static function Write(){
		$I = self::$O;
		$padd = pack('c',0);
		$write = pack('d',self::$triggers_version); # <TriggersVersion>
		if(self::$triggers_version >= 1.4) $write .= pack('c',0); # <TriggersUnknown>
        $write .= pack('l',count($I)); # <TriggersCount>
        
        foreach($I as $T){
            $write .= pack('l',($T[0] ? 1:0)); # <Enabled>
            $write .= pack('l',($T[1] ? 1:0)); # <Looping>
            $write .= pack('c',0); # <Unknown_1>
            $write .= pack('c',($T[2] ? 1:0)); # <Objective>
            $write .= pack('l',(isset($T[3]) ? $T[3]:0)); # <ObjOrder>
			if(self::$triggers_version >= 1.6) $write .= pack('l',0); # <Unknown_2>
            $write .= pack('l',strlen($T[4]) + 1); # <DescLength>
            $write .= pack('a*',$T[4]). pack('c1',0); # <DescValue>
            $write .= pack('l',strlen($T[5]) + 1); # <NameLength>
            $write .= pack('a*',$T[5]). pack('c1',0); # <NameValue>
            $write .= pack('l',isset($T['E']) ? count($T['E']):0); # <EffectsCounts>
            
            if(isset($T['E']) && count($T['E'])){
                 foreach($T['E'] as $E){
					if($sel = (isset($E['S']) ? count($E['S']) : 0) ) $E[5] = $sel;
					if(isset($E[23]) && $E[23] == 0) unset($E[23]);
					$write .= pack('l',$E[0]);
					$len = 23;
                    if(self::$resized_format)foreach(range(23,$len = 2)as $key){
						if(isset($E[$key])){
							$len = $key;
							break;}}
                    $write .= pack('l',$len);
					foreach(range(1,$len)as $key){
						$write .= pack('l',(isset($E[$key])? $E[$key]:-1));}

					$Y1 = ($E[0] == 3 || $E[0] == 20 || $E[0] == 26 ? 1:0);
					$Y2 = ($E[0] == 4 || $E[0] == 20 ? 1:0);

					$write .= pack('l',(isset($E['X']) ? strlen($E['X']):0) + $Y1); # <TextLength>
					$write .= isset($E['X']) ? $E['X']:null; # <TextValue>
					if($Y1)$write .= $padd;

					$write .= pack('l',(isset($E['D']) ? strlen($E['D']):0) + $Y2); # <SoundLength>
					$write .= isset($E['D']) ? $E['D']:null; # <SoundValue>
					if($Y2)$write .= $padd;

					if($sel) foreach($E['S'] as $S) $write .= pack('l',$S);}} # <UnitObj> <>
          
            if(isset($T['E']) && count($T['E'])){
                $Z = 0;
                foreach($T['E'] as $E){
                    $write .= pack('l', $Z); # <EffectsOrder> <>
                    $Z++;}}
                    
            $ConditionsCount = isset($T['C'])? count($T['C']):0;
            $write .= pack('l', $ConditionsCount); # <ConditionsCount>
            
            if($ConditionsCount){
                foreach($T['C'] as $C){
                    $write .= pack('l',($C[0] ? $C[0]:0));
					$len = 16;
                    if(self::$resized_format)foreach(range(16, $len = 2) as $key){
						if(isset($C[$key])){
							$len = $key;
							break;}}
                    $write .= pack('l',$len);
					foreach(range(1,$len) as $key){
						$write .= pack('l',(isset($C[$key]) ? $C[$key]:-1));}}
                $Z = 0;
                foreach($T['C'] as $C){
                    $write .= pack('l', $Z); # <EffectsOrder> <>
                    $Z ++;}}}
        $Z = 0;
        foreach($I as $T){
            $write .= pack('l', $Z); # <TriggersOrder> <>
            $Z ++;}

        # Write the end:
        $write .= pack('l',0); # <FilesIncluded>
        $write .= pack('l',0); # <ExtraDataFlag>
		self::$data_foot = $write;}
    
    /*  Trig
        This function store a new trigger.
        N = Trigger Name
        S = Starting State
        L = Looping State
        P = Player assignation - Used for Efft_DeactPlayerTriggers()
        E = Description Enable
        D = Description Text - Trick: If you specify it, trigger will bypass $hide_triggers
        R = Description Order */
        
	static function Trig($N, $S, $L, $P, $E, $D, $R){
	    if($P != 0) self::$player_triggers[$P][] = $N;
        if($S == 0) self::$off_triggers[$N][0] = true;
		self::$ID_E = 0; # Reset relative Effect-ID
		self::$ID_C = 0; # Reset relative Condition-ID
		self::$ID_T ++; # Increment absolute Trigger-ID
		$array[0] = $S; # Starting-State
		$array[1] = $L; # Looping-State
		$array[2] = $E; # Enable Description
		$array[3] = $R; # Description Order
		$array[4] = $D; # Description Text
        if(self::$hide_triggers && $D === '') $array[5] = ''; # Nameless
        else $array[5] = $N; # Name
		self::$O[self::$ID_T] = $array;
		self::$G[$N] = self::$ID_T; # Add the Name and ID of trigger into the list
    }
    
	/*  Efft
		This function store an effect in current edited trigger $O[$ID_T]['E'][$ID_E]
		E = Effect Type (Without spaces)
		I = Array containing the effect's attributes */
		
	static function Efft($E,$I){
		$E = self::$triggers_effects[$E]; # Get effect id from string

		if($E == 20 && ! isset($I['T']))$I['T'] = 20;
		
		if($E == 8 || $E == 9){
			if($E == 8){
				self::$off_triggers[$I['I']][1] = true;}
			if(key_exists($I['I'],self::$TgAr)){
				$I['I'] = self::$TgAr[$I['I']];}
			else{
				self::$FK[self::$ID_T] = $I['I']; # Add trigger index to the fake-list
				$I['I'] = -1;}}

		$array = array($E);
		if(key_exists('Z',$I))$array[1] = $I['Z']; # AI Goal
		if(key_exists('Q',$I))$array[2] = $I['Q']; # Amount
		if(key_exists('R',$I))$array[3] = $I['R']; # Resource
		if(key_exists('M',$I))$array[4] = $I['M']; # Diplomacy
		if(key_exists('O',$I))$array[6] = $I['O']; # Object Location ID
		if(key_exists('U',$I))$array[7] = $I['U']; # Unit constant
		if(key_exists('P',$I))$array[8] = $I['P']; # Player Source
		if(key_exists('E',$I))$array[9] = $I['E']; # Player Target
		if(key_exists('H',$I))$array[10] = $I['H']; # Technology
		if(key_exists('B',$I))$array[11] = $I['B']; # String Table
		if(key_exists('K',$I))$array[12] = $I['K']; # Unknown
		if(key_exists('T',$I))$array[13] = $I['T']; # Time
		if(key_exists('I',$I))$array[14] = $I['I']; # Trigger Index
		if(key_exists('L',$I))$array[15] = $I['L'][1]; # Location Y
		if(key_exists('L',$I))$array[16] = $I['L'][0]; # Location X
		if(key_exists('A',$I)){ # Area
			$I['A'] = self::Mapper($I['A']);
			$array[17] = $I['A'][0][1]; # Point 1 Y
			$array[18] = $I['A'][0][0]; # Point 1 X
			$array[19] = $I['A'][1][1]; # Point 2 Y
			$array[20] = $I['A'][1][0]; # Point 2 X
		} 
		if(key_exists('G',$I))$array[21] = $I['G']; # Unit Group
		if(key_exists('Y',$I))$array[22] = $I['Y']; # Unit Type
		if(key_exists('N',$I))$array[23] = $I['N']; # Panel
		if(key_exists('X',$I))$array['X'] = $I['X']; # Text
		if(key_exists('D',$I))$array['D'] = $I['D']; # Sound
		if(key_exists('S',$I))$array['S'] = $I['S']; # Objects Ids
		self::$O[self::$ID_T]['E'][self::$ID_E] = $array;
		self::$E_CT ++; # Increment absolute Effect counter
		self::$ID_E ++;} # Increment relative Effect counter

	/*  Cond
		This function store a condition in current edited trigger $O[$ID_T]['C'][$ID_C]
		E = Condition Type (Without spaces)
		I = Array containing the condition's attributes */
		
	static function Cond($C,$I){
		$array = array(self::$triggers_conditions[$C]);# Get condition id from string
		if(key_exists('Q',$I))$array[1] = $I['Q']; # Amount
		if(key_exists('R',$I))$array[2] = $I['R']; # Resource
		if(key_exists('F',$I))$array[3] = $I['F']; # Object Source ID (Not same as effect)
		if(key_exists('O',$I))$array[4] = $I['O']; # Object Location ID
		if(key_exists('U',$I))$array[5] = $I['U']; # Unit Constant
		if(key_exists('P',$I))$array[6] = $I['P']; # Player Source
		if(key_exists('H',$I))$array[7] = $I['H']; # Technology
		if(key_exists('T',$I))$array[8] = $I['T']; # Time
		if(key_exists('K',$I))$array[9] = ($I['K'] == true ? -256:-1); # Inverted Condition for 1.4 RC
		if(key_exists('A',$I)){ # Area
			$I['A'] = self::Mapper($I['A']);
			$array[10] = $I['A'][0][1]; # Point 1 Y
			$array[11] = $I['A'][0][0]; # Point 1 X
			$array[12] = $I['A'][1][1]; # Point 2 Y
			$array[13] = $I['A'][1][0]; # Point 2 X
		} 
		if(key_exists('G',$I))$array[14] = $I['G']; # Unit Group
		if(key_exists('Y',$I))$array[15] = $I['Y']; # Unit Type
		if(key_exists('Z',$I))$array[16] = $I['Z']; # AI Signal
		self::$O[self::$ID_T]['C'][self::$ID_C] = $array;
		self::$C_CT ++; # Increment absolute Condition count
		self::$ID_C ++; # Increment relative Condition count
	} 

	# Area mapper to put points in good order
	static function Mapper($A){
		if($A[0][0] >= $A[1][0])# If Area-Start-X is greater than Area-End-X:
			$A = array($A[1],$A[0]); # Exchange Locations End to Start:
		if($A[1][1] < $A[0][1])# If Area-Start-Y is greater than Area-End-Y:
			$A = array(array($A[0][0],$A[1][1]),array($A[1][0],$A[0][1])); # Exchange Locations Y End to Start:
		return $A;}
		
	# Die and return an error
	static function Error($X){ die("<b>Error:</b> $X"); }
		
	# Die and return an error with current edited trigger
	static function ErrorTrig($X){
		die("<b>Error on trigger \"".self::GetCurrentEditedTrigger()."\":</b> $X");}

	# Get the name of the current edited trigger
	static function GetCurrentEditedTrigger(){
		return self::$O[$ID_T][5];}
} 

?>
