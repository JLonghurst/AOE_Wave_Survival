<?php /* 

    PHP SCX Editor Standard Library - By AzZzRu
    
    This library has not been fully tested, please report any bugs.
    It contains essential functions and constants to make triggers and edit scenario properties.
    You need that your IDE uses intelligent code completion to find functions and know parameters easily.
    
    
    Notes:
    
    - Most of time you can specify a location to $A param, it will be converted as single point area.
    
    
    Notes about triggers functions:
    
    - You can specify a single object ID for $S, it will be converted as array
    - $LO means you can specify a location (L) or a target unit ID (O)
    - With 1.4RC, $Q of Efft_Armor can be array to set melee / piercing armor independently
    - Efft_Tribute auto fix -1 tributes
    - Cond_NotOwn is alias of OwnFewerObjects and it auto fix the -1 offset bug
    - These conditions / effects work only with 1.4 RC or HD patch:
        - Cond_Not* (Except Cond_NotOwn, it's alias of OwnFewer)
        - Efft_MS*
        - Efft_Range*
        - Efft_Armor*
        - Efft_NameO
        - Efft_NameU
        - Efft_NameY
    
    
    Notes about references (civ names, ages names, diplomacy states, etc...):
    
    - You can know all references at bottom of this page in the class LIB
    - References are case sensitive
    
    
    Notes about library configuration:
    
    - You can configure / customize things in the LIB class at bottom of this page
    
    
    Conditions / Effects Keys List:
    
    P = Source Player ID [Integer]
    E = Target Player ID [Integer]
    U = Object Constant ID [Integer]
    G = Object Group ID [Integer]
    Y = Object Type ID [Integer]
    A = Area [Array(Array(X1,Y1),Array(X2,Y2))] - You can write areas with Area(X1,Y1,X2,Y2)
    L = Location [Array(X,Y)]
    Q = Quantity [Integer]
    R = Resource [Integer]
    H = Technology ID [Integer]
    I = Trigger Name [String]
    X = Text [String]
    T = Time [Integer]
    N = Panel [Integer 0 | 1 | 2]
    S = Objects IDs [Array(ID_1,ID_2,ID_3,...,ID_n)]
    F = Object Source ID [Integer]
    O = Object Location ID [Integer]
    D = Sound [String]
    M = Diplomacy [Integer 0 | 1 | 2 | 3]
    Z = AI Signal / AI Goal [Integer]
    K = Inverted condition (Only for 1.4 RC) [Boolean] */




/********************************************************
    Custom Constants
*/

# Magic numbers
define('MAGIC_RESET',2147483647);

# Ressources
define('FOOD',R_FOOD_STORAGE);
define('WOOD',R_WOOD_STORAGE);
define('STONE',R_STONE_STORAGE);
define('GOLD',R_GOLD_STORAGE);
define('KILLS',R_UNITS_KILLED);
define('RAZES',R_BUILDINGS_RAZED);




/********************************************************
    General mains functions

/*  Trig
Make a new trigger.
N = Trigger Name
S = Starting State
L = Looping State
P = Player assignation - Used for Efft_DeactPlayerTriggers()
E = Description Enable
D = Description Text - Trick: If you specify it, trigger will bypass $hide_triggers
R = Description Order */
function Trig($N = '', $S = 1, $L = 0, $P = 0, $E = 0, $D = '', $R = ''){
    SCX::Trig($N,$S,$L,$P,$E,$D,$R);
}

/*  NewObject
Create a new object on the map.
P = Player
U = Unit ID
L = Location
R = Rotation (0 to 360�)
G = Garrison
F = Frame
ID = Object ID - If null next available will be used */
function NewObject($P,$U,$L,$R = 0, $G = -1, $F = 0, $ID = null){
    $data[0x01] = (float)$L[1]; # Y
    $data[0x02] = (float)$L[0]; # X
    $data[0x03] = (float)(isset($L[2])? $L[2]:0); # Z
    $data[0x04] = $ID === null ? (int)SCX::$data_serial['objects'] : (int)$ID; # Object ID
    $data[0x05] = (int)$U; # Unit ID
    $data[0x06] = (int)2; # Progress (other values crash)
    $data[0x07] = (float)deg2rad($R); # Rotation
    $data[0x08] = (int)$F; # Frame
    $data[0x09] = (int)$G; # Garrison
    SCX::$data_serial['object'][$P][] = $data;
    SCX::$data_serial['objects']++;
}

/*  Area
Returns an area to the good format. It's a lot easier to use this function instead writing area by hand. */
function Area($X1,$Y1,$X2,$Y2){
    return array(
        array($X1,$Y1),
        array($X2,$Y2));
}
        
/*  Color
Returns the player's color tag. Used in "Send Chat" and "Display Instructions".
P = Player(0-8) */
function Color($P){
    static $colors = array('<WHITE>','<BLUE>','<RED>','<GREEN>','<YELLOW>','<AQUA>','<PURPLE>','<GREY>','<ORANGE>');
    return $colors[$P];
}

/*  out
Log the value of a variable or an array. Use it to debug your code. */
function out($data){
	echo '<pre>'.print_r($data, true).'</pre>';
}




/********************************************************
    Triggers functions
*/

function Cond_BringOToA($F, $A){
    if(is_numeric($A[0])) $A = array($A,$A);
	SCX::Cond('BringObjectToArea',array(
        'F' => $F,
        'A' => $A
    ));
}
 
function Cond_NotBringOToA($F, $A){
    if(is_numeric($A[0])) $A = array($A,$A);
    SCX::Cond('BringObjectToArea',array(
        'F' => $F,
        'A' => $A,
        'K' => true
    ));
}

function Cond_BringOToO($F, $O){
    SCX::Cond('BringObjectToObject',array(
        'F' => $F,
        'O' => $O
    ));
}

function Cond_NotBringOToO($F, $O){
    SCX::Cond('BringObjectToObject',array(
        'F' => $F,
        'O' => $O,
        'K' => false
    ));
}

function Cond_OwnO($P, $Q){
    SCX::Cond('OwnObjects',array(
        'P' => $P,
        'Q' => $Q
    ));
}

function Cond_OwnU($P, $Q, $U){
    SCX::Cond('OwnObjects',array(
        'P' => $P,
        'Q' => $Q,
        'U' => $U
    ));
}

function Cond_OwnY($P, $Q, $Y){
    SCX::Cond('OwnObjects',array(
        'P' => $P,
        'Q' => $Q,
        'Y' => $Y
    ));
}

function Cond_OwnG($P, $Q, $G){
    SCX::Cond('OwnObjects',array(
        'P' => $P,
        'Q' => $Q,
        'G' => $G
    ));
}

function Cond_NotOwnO($P, $Q){
    SCX::Cond('OwnFewerObjects',array(
        'P' => $P,
        'Q' => $Q - 1
    ));
}

function Cond_NotOwnU($P, $Q, $U){
    SCX::Cond('OwnFewerObjects',array(
        'P' => $P,
        'Q' => $Q - 1,
        'U' => $U
    ));
}

function Cond_NotOwnY($P, $Q, $Y){
    SCX::Cond('OwnFewerObjects',array(
        'P' => $P,
        'Q' => $Q - 1,
        'Y' => $Y
    ));
}

function Cond_NotOwnG($P, $Q, $G){
    SCX::Cond('OwnFewerObjects',array(
        'P' => $P,
        'Q' => $Q - 1,
        'G' => $G
    ));
}

function Cond_InAreaO($P, $Q, $A){
    if(is_numeric($A[0])) $A = array($A,$A);
	SCX::Cond('ObjectsInArea',array(
        'P' => $P,
        'Q' => $Q,
        'A' => $A
    ));
}

function Cond_InAreaU($P, $Q, $U, $A){
    if(is_numeric($A[0])) $A = array($A,$A);
	SCX::Cond('ObjectsInArea',array(
        'P' => $P,
        'Q' => $Q,
        'U' => $U,
        'A' => $A
    ));
}

function Cond_InAreaY($P, $Q, $Y, $A){
    if(is_numeric($A[0])) $A = array($A,$A);
	SCX::Cond('ObjectsInArea',array(
        'P' => $P,
        'Q' => $Q,
        'Y' => $Y,
        'A' => $A
    ));
}

function Cond_InAreaG($P, $Q, $G, $A){
    if(is_numeric($A[0])) $A = array($A,$A);
	SCX::Cond('ObjectsInArea',array(
        'P' => $P,
        'Q' => $Q,
        'G' => $G,
        'A' => $A
    ));
}

function Cond_NotInAreaO($P, $Q, $A){
    if(is_numeric($A[0])) $A = array($A,$A);
	SCX::Cond('ObjectsInArea',array(
        'P' => $P,
        'Q' => $Q,
        'A' => $A,
        'K' => true
    ));
}

function Cond_NotInAreaU($P, $Q, $U, $A){
    if(is_numeric($A[0])) $A = array($A,$A);
	SCX::Cond('ObjectsInArea',array(
        'P' => $P,
        'Q' => $Q,
        'U' => $U,
        'A' => $A,
        'K' => true
    ));
}

function Cond_NotInAreaY($P, $Q, $Y, $A){
    if(is_numeric($A[0])) $A = array($A,$A);
	SCX::Cond('ObjectsInArea',array(
        'P' => $P,
        'Q' => $Q,
        'Y' => $Y,
        'A' => $A,
        'K' => true
    ));
}

function Cond_NotInAreaG($P, $Q, $G, $A){
    if(is_numeric($A[0])) $A = array($A,$A);
	SCX::Cond('ObjectsInArea',array(
        'P' => $P,
        'Q' => $Q,
        'G' => $G,
        'A' => $A,
        'K' => true
    ));
}

function Cond_Destroy($F){
    SCX::Cond('DestroyObject',array(
        'F' => $F
    ));
}

function Cond_NotDestroy($F){
    SCX::Cond('DestroyObject',array(
        'F' => $F,
        'K' => true
    ));
}

function Cond_Capture($P, $F){
    SCX::Cond('CaptureObject',array(
		'P' => $P,
        'F' => $F
    ));
}

function Cond_NotCapture($P, $F){
    SCX::Cond('CaptureObject',array(
		'P' => $P,
        'F' => $F,
        'K' => true
    ));
}

function Cond_True($P, $R){
    SCX::Cond('AccumulateAttribute',array(
        'P' => $P,
        'Q' => 1,
        'R' => $R
    ));
}

function Cond_False($P, $R){
    SCX::Cond('AccumulateAttribute',array(
        'P' => $P,
        'Q' => 1,
        'R' => $R,
        'K' => true
    ));
}

function Cond_Accumulate($P, $Q, $R){
    SCX::Cond('AccumulateAttribute',array(
        'P' => $P,
        'Q' => $Q,
        'R' => $R
    ));
}

function Cond_NotAccumulate($P, $Q, $R){
    SCX::Cond('AccumulateAttribute',array(
        'P' => $P,
        'Q' => $Q,
        'R' => $R,
        'K' => true
    ));
}

function Cond_AccumulateExactly($P, $Q, $R){
    SCX::Cond('AccumulateAttribute',array(
        'P' => $P,
        'Q' => $Q,
        'R' => $R
    ));
    SCX::Cond('AccumulateAttribute',array(
        'P' => $P,
        'Q' => $Q + 1,
        'R' => $R,
        'K' => true
    ));
}

function Cond_Researched($P, $H){
    SCX::Cond('ResearchTechnology',array(
        'P' => $P,
        'H' => $H
    ));
}

function Cond_NotResearched($P, $H){
    SCX::Cond('ResearchTechnology',array(
        'P' => $P,
        'H' => $H,
        'K' => true
    ));
}

function Cond_Timer($T){
    SCX::Cond('Timer',array(
        'T' => $T
    ));
}

function Cond_NotTimer($T){
    SCX::Cond('Timer',array(
        'T' => $T,
        'K' => true
    ));
}

function Cond_Selected($F){
    SCX::Cond('ObjectSelected',array(
        'F' => $F
    ));
}

function Cond_NotSelected($F){
    SCX::Cond('ObjectSelected',array(
        'F' => $F,
        'K' => true
    ));
}

function Cond_AISignal($Z){
    SCX::Cond('AISignal',array(
        'Z' => $Z
    ));
}

function Cond_NotAISignal($Z){
    SCX::Cond('AISignal',array(
        'Z' => $Z,
        'K' => true
    ));
}

function Cond_Died($P){
    SCX::Cond('PlayerDefeated',array(
        'P' => $P
    ));
}
    
function Cond_Alive($P){
    SCX::Cond('PlayerDefeated',array(
        'P' => $P,
        'K' => true
    ));
}

function Cond_HasTarget($F, $O){
    SCX::Cond('ObjectHasTarget',array(
        'F' => $F,
        'O' => $O
    ));
}

function Cond_NotHasTarget($F, $O){
    SCX::Cond('ObjectHasTarget',array(
        'F' => $F,
        'O' => $O,
        'K' => true
    ));
}

function Cond_Visible($F){
    SCX::Cond('ObjectVisible',array(
        'F' => $F
    ));
}

function Cond_NotVisible($F){
    SCX::Cond('ObjectNotVisible',array(
        'F' => $F
    ));
}

function Cond_Researching($P, $H){
    SCX::Cond('ResearchingTechnology',array(
        'P' => $P,
        'H' => $H
    ));
}

function Cond_NotResearching($P, $H){
    SCX::Cond('ResearchingTechnology',array(
        'P' => $P,
        'H' => $H,
        'K' => false
    ));
}

function Cond_Garrisoned($F){
    SCX::Cond('UnitsGarrisoned',array(
        'F' => $F
    ));
}

function Cond_NotGarrisoned($F){
    SCX::Cond('UnitsGarrisoned',array(
        'F' => $F,
        'K' => true
    ));
}

function Cond_Difficulty($difficultyName){
    if(!key_exists($difficultyName,LIB::$Difficulties)) SCX::ErrorTrig("Unknown difficulty in Cond_Difficulty(): $difficultyName");
    $id = LIB::$Difficulties[$difficultyName];
    SCX::Cond('DifficultyLevel',array(
        'Q' => $id
    ));
}

function Cond_NotDifficulty($difficultyName){
    if(!key_exists($difficultyName,LIB::$Difficulties)) SCX::ErrorTrig("Unknown difficulty in Cond_NotDifficulty(): $difficultyName");
    $id = LIB::$Difficulties[$difficultyName];
    SCX::Cond('DifficultyLevel',array(
        'Q' => $id,
        'K' => true
    ));
}

function Efft_ChangeDiplomacy($P, $E, $stateName){
    if(!key_exists($stateName,LIB::$Diplomacies)) SCX::ErrorTrig("Unknown diplomacy state in Efft_ChangeDiplomacy(): $stateName");
    $id = LIB::$Diplomacies[$stateName];
    SCX::Efft('ChangeDiplomacy',array(
        'P' => $P,
        'E' => $E,
        'M' => $id
    ));
}

function Efft_Research($P, $H){
    SCX::Efft("ResearchTechnology",array(
        'P' => $P,
        'H' => $H
    ));
}

function Efft_Chat($P, $X){
    SCX::Efft('SendChat',array(
        'P' => $P,
        'X' => $X
    ));
}

function Efft_PlaySound($P, $D){
    SCX::Efft('PlaySound',array(
        'P' => $P,
        'D' => $D
    ));
}

function Efft_Give($P, $Q, $R){
    if($Q == 1){
        $Q = -1;
        SCX::Efft('SendTribute',array(
            'P' => $P,
            'Q' => -2,
            'R' => $R,
            'E' => 0
        ));
    }
    SCX::Efft('SendTribute',array(
        'P' => $P,
        'Q' => -$Q,
        'R' => $R,
        'E' => 0
    ));
}

function Efft_Tribute($P, $Q, $R, $E = 0){
    if($Q == -1 ){
        $Q = 1;
        SCX::Efft('SendTribute',array(
            'P' => $P,
            'Q' => -2,
            'R' => $R,
            'E' => $E
        ));
    }
    SCX::Efft('SendTribute',array(
        'P' => $P,
        'Q' => $Q,
        'R' => $R,
        'E' => $E
    ));
}

function Efft_Reset($P, $R){
    SCX::Efft('SendTribute',array(
        'P' => $P,
        'Q' => 2147483647,
        'R' => $R,
        'E' => 0
    ));
}

function Efft_Set($P, $Q, $R){
    SCX::Efft('SendTribute',array(
        'P' => $P,
        'Q' => 2147483647,
        'R' => $R,
        'E' => 0
    ));
    if($Q == -1 ){
        $Q = 1;
        SCX::Efft('SendTribute',array(
            'P' => $P,
            'Q' => -2,
            'R' => $R,
            'E' => 0
        ));
    }
    SCX::Efft('SendTribute',array(
        'P' => $P,
        'Q' => $Q,
        'R' => $R,
        'E' => 0
    ));
}

function Efft_True($P, $R){
    SCX::Efft('SendTribute',array(
        'P' => $P,
        'Q' => -2,
        'R' => $R,
        'E' => 0
    ));
}

function Efft_False($P, $R){
    SCX::Efft('SendTribute',array(
        'P' => $P,
        'Q' => 2147483647,
        'R' => $R,
        'E' => 0
    ));
}

function Efft_UnlockGate($S){
    SCX::Efft('UnlockGate',array(
        'S' => ( is_array($S) ? $S : array($S) )
    ));
}

function Efft_LockGate($S){
    SCX::Efft('LockGate',array(
        'S' => ( is_array($S) ? $S : array($S) )
    ));
}

function Efft_Act($I){
    SCX::Efft('ActivateTrigger',array(
        'I' => $I
    ));
}

function Efft_Deact($I){
    SCX::Efft('DeactivateTrigger',array(
        'I' => $I
    ));
}

function Efft_AIGoal($P, $Z){
    SCX::Efft('AIScriptGoal',array(
        'P' => $P,
        'Z' => $Z
    ));
}

function Efft_Create($P, $U, $L){
    SCX::Efft('CreateObject',array(
        'P' => $P,
        'U' => $U,
        'L' => $L
    ));
}

function Efft_TaskO($P, $A = null, $LO){
    $I = array(
        'P' => $P,
        (is_array($LO) ? 'L':'O') => $LO
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('TaskObject',$I);
}

function Efft_TaskS($S, $LO){
    SCX::Efft('TaskObject',array(
        'S' => ( is_array($S) ? $S : array($S) ),
        (is_array($LO) ? 'L':'O') => $LO
    ));
}

function Efft_TaskU($P, $U, $A = null, $LO){
    $I = array(
        'P' => $P,
        'U' => $U,
        (is_array($LO) ? 'L':'O') => $LO
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('TaskObject',$I);
}

function Efft_TaskY($P, $Y, $A = null, $LO){
    $I = array(
        'P' => $P,
        'Y' => $Y,
        (is_array($LO) ? 'L':'O') => $LO
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('TaskObject',$I);
}

function Efft_TaskG($P, $G, $A = null, $LO){
    $I = array(
        'P' => $P,
        'G' => $G,
        (is_array($LO) ? 'L':'O') => $LO
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('TaskObject',$I);
}

function Efft_DeclareVictory($P){
    SCX::Efft('DeclareVictory',array(
        'P' => $P
    ));
}

function Efft_KillO($P, $A = null){
    $I = array(
        'P' => $P
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('KillObject',$I);
}

function Efft_KillS($S){
    SCX::Efft('KillObject',array(
        'S' => ( is_array($S) ? $S : array($S) )
    ));
}

function Efft_KillU($P, $U, $A = null){
    $I = array(
        'P' => $P,
        'U' => $U
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('KillObject',$I);
}

function Efft_KillY($P, $Y, $A = null){
    $I = array(
        'P' => $P,
        'Y' => $Y
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('KillObject',$I);
}

function Efft_KillG($P, $G, $A = null){
    $I = array(
        'P' => $P,
        'G' => $G
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('KillObject',$I);
}

function Efft_RemoveO($P, $A = null){
    $I = array(
        'P' => $P
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('RemoveObject',$I);
}

function Efft_RemoveS($S){
    SCX::Efft('RemoveObject',array(
        'S' => ( is_array($S) ? $S : array($S) )
    ));
}

function Efft_RemoveU($P, $U, $A = null){
    $I = array(
        'P' => $P,
        'U' => $U
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('RemoveObject',$I);
}

function Efft_RemoveY($P, $Y, $A = null){
    $I = array(
        'P' => $P,
        'Y' => $Y
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('RemoveObject',$I);
}

function Efft_RemoveG($P, $G, $A = null){
    $I = array(
        'P' => $P,
        'G' => $G
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('RemoveObject',$I);
}

function Efft_ChangeView($P, $L){
    SCX::Efft('ChangeView',array(
        'P' => $P,
        'L' => $L
    ));
}

function Efft_UnloadO($P, $A = null, $L){
    $I = array(
        'P' => $P,
        'L' => $L
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('Unload',$I);
}

function Efft_UnloadS($S, $L){
    SCX::Efft('Unload',array(
        'S' => ( is_array($S) ? $S : array($S) ),
        'L' => $L
    ));
}

function Efft_UnloadU($P, $U, $A = null, $L){
    $I = array(
        'P' => $P,
        'U' => $U,
        'L' => $L
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('Unload',$I);
}

function Efft_UnloadY($P, $Y, $A = null, $L){
    $I = array(
        'P' => $P,
        'Y' => $Y,
        'L' => $L
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('Unload',$I);
}

function Efft_UnloadG($P, $G, $A = null, $L){
    $I = array(
        'P' => $P,
        'G' => $G,
        'L' => $L
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('Unload',$I);
}

function Efft_ChangeOwnerO($P, $A = null, $E = 0){
    $I = array(
        'P' => $P,
        'E' => $E
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('ChangeOwnership',$I);
}

function Efft_ChangeOwnerS($S, $E = 0){
    SCX::Efft('ChangeOwnership',array(
        'S' => ( is_array($S) ? $S : array($S) ),
        'E' => $E
    ));
}

function Efft_ChangeOwnerU($P, $U, $A = null, $E = 0){
    $I = array(
        'P' => $P,
        'U' => $U,
        'E' => $E
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('ChangeOwnership',$I);
}

function Efft_ChangeOwnerY($P, $Y, $A = null, $E = 0){
    $I = array(
        'P' => $P,
        'Y' => $Y,
        'E' => $E
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('ChangeOwnership',$I);
}

function Efft_ChangeOwnerG($P, $G, $A = null, $E = 0){
    $I = array(
        'P' => $P,
        'G' => $G,
        'E' => $E
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('ChangeOwnership',$I);
}

function Efft_PatrolO($P, $A = null, $L){
    $I = array(
        'P' => $P,
        'L' => $L
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('Patrol',$I);
}

function Efft_PatrolS($S, $L){
    SCX::Efft('Patrol',array(
        'S' => ( is_array($S) ? $S : array($S) ),
        'L' => $L
    ));
}

function Efft_PatrolU($P, $U, $A = null, $L){
    $I = array(
        'P' => $P,
        'U' => $U,
        'L' => $L
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('Patrol',$I);
}

function Efft_PatrolY($P, $Y, $A = null, $L){
    $I = array(
        'P' => $P,
        'Y' => $Y,
        'L' => $L
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('Patrol',$I);
}

function Efft_PatrolG($P, $G, $A = null, $L){
    $I = array(
        'P' => $P,
        'G' => $G,
        'L' => $L
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('Patrol',$I);
}

function Efft_Display($T, $N, $X){
	SCX::Efft('DisplayInstructions',array(
        'T' => $T,
        'N' => $N,
        'X' => $X
    ));
}

function Efft_Clear($N){
	SCX::Efft('ClearInstructions',array(
        'N' => $N
    ));
}

function Efft_FreezeO($P, $A = null){
    $I = array(
        'P' => $P
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('FreezeUnit',$I);
}

function Efft_FreezeS($S){
    SCX::Efft('FreezeUnit',array(
        'S' => ( is_array($S) ? $S : array($S) )
    ));
}

function Efft_FreezeU($P, $U, $A = null){
    $I = array(
        'P' => $P,
        'U' => $U
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('FreezeUnit',$I);
}

function Efft_FreezeY($P, $Y, $A = null){
    $I = array(
        'P' => $P,
        'Y' => $Y
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('FreezeUnit',$I);
}

function Efft_FreezeG($P, $G, $A = null){
    $I = array(
        'P' => $P,
        'G' => $G
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('FreezeUnit',$I);
}

function Efft_DamageO($P, $Q, $A = null){
    $I = array(
        'P' => $P,
        'Q' => $Q
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('DamageObject',$I);
}

function Efft_DamageS($Q, $S){
    SCX::Efft('DamageObject',array(
        'Q' => $Q,
        'S' => ( is_array($S) ? $S : array($S) )
    ));
}

function Efft_DamageU($P, $Q, $U, $A = null){
    $I = array(
        'P' => $P,
        'Q' => $Q,
        'U' => $U
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('DamageObject',$I);
}

function Efft_DamageY($P, $Q, $Y, $A = null){
    $I = array(
        'P' => $P,
        'Q' => $Q,
        'Y' => $Y
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('DamageObject',$I);
}

function Efft_DamageG($P, $Q, $G, $A = null){
    $I = array(
        'P' => $P,
        'Q' => $Q,
        'G' => $G
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('DamageObject',$I);
}

function Efft_PlaceFoundation($P, $U, $L){
    SCX::Efft('PlaceFoundation',array(
        'P' => $P,
        'U' => $U,
        'L' => $L
    ));
}

function Efft_NameO($P, $X, $A = null){
    $I = array(
        'P' => $P,
        'X' => $X,
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('ChangeObjectName',$I);
}

function Efft_NameS($S, $X){
    SCX::Efft('ChangeObjectName',array(
        'S' => ( is_array($S) ? $S : array($S) ),
        'X' => $X
    ));
}

function Efft_NameU($P, $X, $U, $A = null){
    $I = array(
        'P' => $P,
        'X' => $X,
        'U' => $U
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('ChangeObjectName',$I);
}

function Efft_NameY($P, $X, $Y, $A = null){
    $I = array(
        'P' => $P,
        'X' => $X,
        'Y' => $Y
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('ChangeObjectName',$I);
}

function Efft_HPO($P, $Q, $A = null){
    $I = array(
        'P' => $P,
        'Q' => $Q
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('ChangeObjectHP',$I);
}

function Efft_HPS($Q, $S){
    SCX::Efft('ChangeObjectHP',array(
        'Q' => $Q,
        'S' => ( is_array($S) ? $S : array($S) )
    ));
}

function Efft_HPU($P, $Q, $U, $A = null){
    $I = array(
        'P' => $P,
        'Q' => $Q,
        'U' => $U
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('ChangeObjectHP',$I);
}

function Efft_HPY($P, $Q, $Y, $A = null){
    $I = array(
        'P' => $P,
        'Q' => $Q,
        'Y' => $Y
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('ChangeObjectHP',$I);
}

function Efft_HPG($P, $Q, $G, $A = null){
    $I = array(
        'P' => $P,
        'Q' => $Q,
        'G' => $G
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('ChangeObjectHP',$I);
}

function Efft_APO($P, $Q, $A = null){
    $I = array(
        'P' => $P,
        'Q' => $Q
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('ChangeObjectAttack',$I);
}

function Efft_APS($Q, $S){
    SCX::Efft('ChangeObjectAttack',array(
        'Q' => $Q,
        'S' => ( is_array($S) ? $S : array($S) )
    ));
}

function Efft_APU($P, $Q, $U, $A = null){
    $I = array(
        'P' => $P,
        'Q' => $Q,
        'U' => $U
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('ChangeObjectAttack',$I);
}

function Efft_APY($P, $Q, $Y, $A = null){
    $I = array(
        'P' => $P,
        'Q' => $Q,
        'Y' => $Y
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('ChangeObjectAttack',$I);
}

function Efft_APG($P, $Q, $G, $A = null){
    $I = array(
        'P' => $P,
        'Q' => $Q,
        'G' => $G
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('ChangeObjectAttack',$I);
}

function Efft_StopO($P, $A = null){
    $I = array(
        'P' => $P
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('StopUnit',$I);
}

function Efft_StopS($S){
    SCX::Efft('StopUnit',array(
        'S' => ( is_array($S) ? $S : array($S) )
    ));
}

function Efft_StopU($P, $U, $A = null){
    $I = array(
        'P' => $P,
        'U' => $U
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('StopUnit',$I);
}

function Efft_StopY($P, $Y, $A = null){
    $I = array(
        'P' => $P,
        'Y' => $Y
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('StopUnit',$I);
}

function Efft_StopG($P, $G, $A = null){
    $I = array(
        'P' => $P,
        'G' => $G
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('StopUnit',$I);
}

function Efft_MSO($P, $Q, $A = null){
    $I = array(
        'P' => $P,
        'Q' => $Q
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('ChangeSpeed',$I);
}

function Efft_MSS($Q, $S){
    SCX::Efft('ChangeSpeed',array(
        'Q' => $Q,
        'S' => ( is_array($S) ? $S : array($S) )
    ));
}

function Efft_MSU($P, $Q, $U, $A = null){
    $I = array(
        'P' => $P,
        'Q' => $Q,
        'U' => $U
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('ChangeSpeed',$I);
}

function Efft_MSY($P, $Q, $Y, $A = null){
    $I = array(
        'P' => $P,
        'Q' => $Q,
        'Y' => $Y
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('ChangeSpeed',$I);
}

function Efft_MSG($P, $Q, $G, $A = null){
    $I = array(
        'P' => $P,
        'Q' => $Q,
        'G' => $G
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('ChangeSpeed',$I);
}

function Efft_RangeO($P, $Q, $A = null){
    $I = array(
        'P' => $P,
        'Q' => $Q
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('ChangeRange',$I);
}

function Efft_RangeS($Q, $S){
    SCX::Efft('ChangeRange',array(
        'Q' => $Q,
        'S' => ( is_array($S) ? $S : array($S) )
    ));
}

function Efft_RangeU($P, $Q, $U, $A = null){
    $I = array(
        'P' => $P,
        'Q' => $Q,
        'U' => $U
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('ChangeRange',$I);
}

function Efft_RangeY($P, $Q, $Y, $A = null){
    $I = array(
        'P' => $P,
        'Q' => $Q,
        'Y' => $Y
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('ChangeRange',$I);
}

function Efft_RangeG($P, $Q, $G, $A = null){
    $I = array(
        'P' => $P,
        'Q' => $Q,
        'G' => $G
    );
    if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
    SCX::Efft('ChangeRange',$I);
}

if(SCX::$editor_version === 'HD'){
    
    /**
     * Change armor for HD version
     **/
   	function Efft_ArmorO($P, $Q, $A = null){
		$I = array(
			'P' => $P,
			'Q' => $Q
		);
		if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
		SCX::Efft('ChangeArmor',$I);
	}

    /**
     * Change armor for HD version
     **/
	function Efft_ArmorS($Q, $S){
		SCX::Efft('ChangeArmor',array(
			'Q' => $Q,
			'S' => ( is_array($S) ? $S : array($S) )
		));
	}

    /**
     * Change armor for HD version
     **/
	function Efft_ArmorU($P, $Q, $U, $A = null){
		$I = array(
			'P' => $P,
			'Q' => $Q,
			'U' => $U
		);
		if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
		SCX::Efft('ChangeArmor',$I);
	}

    /**
     * Change armor for HD version
     **/
	function Efft_ArmorY($P, $Q, $Y, $A = null){
		$I = array(
			'P' => $P,
			'Q' => $Q,
			'Y' => $Y
		);
		if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
		SCX::Efft('ChangeArmor',$I);
	}

    /**
     * Change armor for HD version
     **/
	function Efft_ArmorG($P, $Q, $G, $A = null){
		$I = array(
			'P' => $P,
			'Q' => $Q,
			'G' => $G
		);
		if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
		SCX::Efft('ChangeArmor',$I);
	}
}
else{
    
    /**
     * Change armor for 1.4 RC version
     **/
    function Efft_ArmorO($P, $Q_IntegerOrArray, $A = null){
        $Q = $Q_IntegerOrArray;
        $I = array(
            'P' => $P
        );
        if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
        if(is_array($Q)){
            $I1 = $I2 = $I;
            $I1['Q'] = $Q[0];
            $I2['Q'] = $Q[1];
            if($I1['Q'] != 0)SCX::Efft('ChangeArmor1',$I1);
            if($I2['Q'] != 0)SCX::Efft('ChangeArmor2',$I2);
        }
        else{
            $I['Q'] = $Q;
            SCX::Efft('ChangeArmor1',$I);
            SCX::Efft('ChangeArmor2',$I);
        }
    }

    /**
     * Change armor for 1.4 RC version
     **/
    function Efft_ArmorS($Q_IntegerOrArray, $S){
        $Q = $Q_IntegerOrArray;
        $I = array(
            'S' => ( is_array($S) ? $S : array($S) )
        );
        if(is_array($Q)){
            $I1 = $I2 = $I;
            $I1['Q'] = $Q[0];
            $I2['Q'] = $Q[1];
            if($I1['Q'] != 0)SCX::Efft('ChangeArmor1',$I1);
            if($I2['Q'] != 0)SCX::Efft('ChangeArmor2',$I2);
        }
        else{
            $I['Q'] = $Q;
            SCX::Efft('ChangeArmor1',$I);
            SCX::Efft('ChangeArmor2',$I);
        }
    }
    
    /**
     * Change armor for 1.4 RC version
     **/
    function Efft_ArmorU($P, $Q_IntegerOrArray, $U, $A = null){
        $Q = $Q_IntegerOrArray;
        $I = array(
            'P' => $P,
            'U' => $U
        );
        if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
        if(is_array($Q)){
            $I1 = $I2 = $I;
            $I1['Q'] = $Q[0];
            $I2['Q'] = $Q[1];
            if($I1['Q'] != 0)SCX::Efft('ChangeArmor1',$I1);
            if($I2['Q'] != 0)SCX::Efft('ChangeArmor2',$I2);
        }
        else{
            $I['Q'] = $Q;
            SCX::Efft('ChangeArmor1',$I);
            SCX::Efft('ChangeArmor2',$I);
        }
    }
    
    /**
     * Change armor for 1.4 RC version
     **/
    function Efft_ArmorY($P, $Q_IntegerOrArray, $Y, $A = null){
        $Q = $Q_IntegerOrArray;
        $I = array(
            'P' => $P,
            'Y' => $Y
        );
        if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
        if(is_array($Q)){
            $I1 = $I2 = $I;
            $I1['Q'] = $Q[0];
            $I2['Q'] = $Q[1];
            if($I1['Q'] != 0)SCX::Efft('ChangeArmor1',$I1);
            if($I2['Q'] != 0)SCX::Efft('ChangeArmor2',$I2);
        }
        else{
            $I['Q'] = $Q;
            SCX::Efft('ChangeArmor1',$I);
            SCX::Efft('ChangeArmor2',$I);
        }
    }
    
    /**
     * Change armor for 1.4 RC version
     **/
    function Efft_ArmorG($P, $Q_IntegerOrArray, $G, $A = null){
        $Q = $Q_IntegerOrArray;
        $I = array(
            'P' => $P,
            'G' => $G
        );
        if($A !== null) $I['A'] = ( is_numeric($A[0]) ? array($A,$A) : $A );
        if(is_array($Q)){
            $I1 = $I2 = $I;
            $I1['Q'] = $Q[0];
            $I2['Q'] = $Q[1];
            if($I1['Q'] != 0)SCX::Efft('ChangeArmor1',$I1);
            if($I2['Q'] != 0)SCX::Efft('ChangeArmor2',$I2);
        }
        else{
            $I['Q'] = $Q;
            SCX::Efft('ChangeArmor1',$I);
            SCX::Efft('ChangeArmor2',$I);
        }
    }
}




/********************************************************
    Properties functions
*/

function GetMapSize(){
    return SCX::$data_serial['terrain']['size'][1];
}

function SetMapSize($size){
    SCX::$data_serial['terrain']['size'][1] = $size;
    SCX::$data_serial['terrain']['size'][2] = $size;
}

function GetPlayersCount(){
    return SCX::$data_serial['players']['count'][0x01];
}

function SetPlayersCount($playersCount){
    SCX::$data_serial['players']['count'][0x01] = $playersCount;
    foreach(range(1,$playersCount) as $player){
        SCX::$data_serial['player'][$player]['boolean'] = true;
    }
}

function GetPlayerName($player){
    return SCX::$data_serial['player'][$player]['name'];
}

function SetPlayerName($player, $name){
    SCX::$data_serial['player'][$player]['name'] = $name;
}

function GetPlayerCiv($player){
    return ucfirst(array_search(SCX::$data_serial['player'][$player]['profile'],LIB::$CivIds));
}

function SetPlayerCiv($player, $civName){
    if(!key_exists($civName,LIB::$CivIds)) SCX::Error("Unknown civ name in SetPlayerCiv(): \"$civName\"");
    SCX::$data_serial['player'][$player]['profile'] = LIB::$CivIds[$civName];
}

function GetMessageObjective(){
    return SCX::$data_serial['message']['directive'][0x01];
}

function GetMessageHints(){
    return SCX::$data_serial['message']['directive'][0x02];
}

function GetMessageVictory(){
    return SCX::$data_serial['message']['directive'][0x03];
}

function GetMessageFailure(){
    return SCX::$data_serial['message']['directive'][0x04];
}

function GetMessageHistory(){
    return SCX::$data_serial['message']['directive'][0x05];
}

function GetMessageScouts(){
    return SCX::$data_serial['message']['directive'][0x06];
}

function SetMessageObjective($text){
    SCX::$data_serial['message']['directive'][0x01] = $text;
}

function SetMessageHints($text){
    SCX::$data_serial['message']['directive'][0x02] = $text;
}

function SetMessageVictory($text){
    SCX::$data_serial['message']['directive'][0x03] = $text;
}

function SetMessageFailure($text){
    SCX::$data_serial['message']['directive'][0x04] = $text;
}

function SetMessageHistory($text){
    SCX::$data_serial['message']['directive'][0x05] = $text;
}

function SetMessageScouts($text){
    SCX::$data_serial['message']['directive'][0x06] = $text;
}

function GetPlayerStartFood($player){
    return SCX::$data_serial['player'][$player]['source']['food'];
}

function SetPlayerStartFood($player, $quantity){
    SCX::$data_serial['player'][$player]['source']['food'] = $quantity;
}

function GetPlayerStartWood($player){
    return SCX::$data_serial['player'][$player]['source']['wood'];
}

function SetPlayerStartWood($player, $quantity){
    SCX::$data_serial['player'][$player]['source']['wood'] = $quantity;
}

function GetPlayerStartStone($player){
    return SCX::$data_serial['player'][$player]['source']['rock'];
}

function SetPlayerStartStone($player, $quantity){
    SCX::$data_serial['player'][$player]['source']['rock'] = $quantity;
}

function GetPlayerStartGold($player){
    return SCX::$data_serial['player'][$player]['source']['gold'];
}

function SetPlayerStartGold($player, $quantity){
    SCX::$data_serial['player'][$player]['source']['gold'] = $quantity;
}

function GetPlayerMaxPop($player){
    return SCX::$data_serial['player'][$player]['source']['popl'];
}

function SetPlayerMaxPop($player, $maxPop){
    SCX::$data_serial['player'][$player]['source']['popl'] = $maxPop;
}

function GetPlayerDisabilityTechList($player){
    $list = SCX::$data_serial['disabled']['techtree'][$player][0x01];
    while(($key = array_search(-1, $list)) !== false) {
        unset($list[$key]);
    }
    return is_array($list) ? $list:array();
}

/**
 * Maximum 30 techs
 **/
function SetPlayerDisabilityTechList($player, $idsArray){
    if(count($idsArray) > 30) SCX::Error("Maximum disabilities reached (30) in SetPlayerDisabilityTechList()");
    for($i = 1; $i <= 30; $i++){
        SCX::$data_serial['disabled']['techtree'][$player][0x01][$i] = isset($idsArray[$i-1]) ? $idsArray[$i-1] : -1;
    }
}

function GetPlayerDisabilityUnitList($player){
    $list = SCX::$data_serial['disabled']['techtree'][$player][0x02];
    while(($key = array_search(-1, $list)) !== false) {
        unset($list[$key]);
    }
    return is_array($list) ? $list:array();
}

/**
 * Maximum 30 units
 **/
function SetPlayerDisabilityUnitList($player, $idsArray){
    if(count($idsArray) > 30) SCX::Error("Maximum disabilities reached (30) in SetPlayerDisabilityUnitList()");
    for($i = 1; $i <= 30; $i++){
        SCX::$data_serial['disabled']['techtree'][$player][0x02][$i] = isset($idsArray[$i-1]) ? $idsArray[$i-1] : -1;
    }
}

function GetPlayerDisabilityBuildingList($player){
    $list = SCX::$data_serial['disabled']['techtree'][$player][0x03];
    while(($key = array_search(-1, $list)) !== false) {
        unset($list[$key]);
    }
    return is_array($list) ? $list:array();
}

/**
 * Maximum 20 buildings
 **/
function SetPlayerDisabilityBuildingList($player, $idsArray){
    if(count($idsArray) > 20) SCX::Error("Maximum disabilities reached (20) in SetPlayerDisabilityBuildingList()");
    for($i = 1; $i <= 20; $i++){
        SCX::$data_serial['disabled']['techtree'][$player][0x03][$i] = isset($idsArray[$i-1]) ? $idsArray[$i-1] : -1;
    }
}

function GetAllTech(){
    return SCX::$data_serial['disabled']['options'][0x03] ? true : false;
}

function SetAllTech($boolean){
    SCX::$data_serial['disabled']['options'][0x03] = $boolean ? 1 : 0;
}

function GetPlayerStartAge($player){
    return array_search(SCX::$data_serial['disabled']['initial'][$player],LIB::$StartAges);
}

function SetPlayerStartAge($player, $ageName){
    if(!key_exists($ageName,LIB::$StartAges)) SCX::Error("Unknown age name in SetPlayerStartAge(): $ageName");
    SCX::$data_serial['disabled']['initial'][$player] = LIB::$StartAges[$ageName];
}

function GetPlayerStartView($player){
    return SCX::$data_serial['player'][$player]['view'][0x01];
}

/**
 * Return: Array of Objects
 * Object Format:
 * 1 => Y
 * 2 => X
 * 3 => Z
 * 4 => Object ID
 * 5 => Unit ID
 * 6 => Progress
 * 7 => Rotation
 * 8 => Frame
 * 9 => Garrison
 **/
function GetPlayerObjects($player){
    return SCX::$data_serial['object'][$player];
}

/**
 * Param: $objects = Array of Objects
 * Object Format:
 * 1 => Y
 * 2 => X
 * 3 => Z
 * 4 => Object ID
 * 5 => Unit ID
 * 6 => Progress
 * 7 => Rotation
 * 8 => Frame
 * 9 => Garrison
 * You can also use NewObject() to add a single object
 **/
function SetPlayerObjects($player, $objects){
    return SCX::$data_serial['object'][$player] = $objects;
}

/**
 * Param: $objectIdAvailable need to be an ID that is available to create a new object (use different one for each player)
 * Param: $objectConstantId is what will be this object, it will garrisoned into itself and located at player start view
 * It is needed to insert an object to set the start view, else it won't work (aoc bug)
 **/
function SetPlayerStartView($player, $location, $objectIdAvailable, $objectConstantId){
    SCX::$data_serial['player'][$player]['view'][0x01] = $location;
    SCX::$data_serial['player'][$player]['view'][0x02] = $location;
    # Insert object as first
    $object[0x01] = (float)$location[1]; # Y
	$object[0x02] = (float)$location[0]; # X
	$object[0x03] = (float)0; # Z
	$object[0x04] = (int)$objectIdAvailable; # Object ID
	$object[0x05] = (int)$objectConstantId; # Unit ID
	$object[0x06] = (int)2; # Progress (other values crash)
	$object[0x07] = (float)0; # Rotation
	$object[0x08] = (int)0; # Frame
	$object[0x09] = (int)$objectIdAvailable; # Garrison
    SCX::$data_serial['object'][$player][] = @SCX::$data_serial['object'][$player][0];
    SCX::$data_serial['object'][$player][0] = $object;
}

/**
 * Return: Cell
 * Cell Format:
 * terrain => terrainId
 * elevation => elevationId
 **/
function GetTerrainCell($x,$y){
    $cell['terrain'] = SCX::$data_serial['terrain']['data'][$x.','.$y][1];
    $cell['elevation'] = SCX::$data_serial['terrain']['data'][$x.','.$y][2];
    return $cell;
}

/**
 * Param: $cell = Cell
 * Cell Format:
 * terrain => terrainId (do not specify it if you want modify only elevation)
 * elevation => elevationId (do not specify it if you want modify only terrain)
 **/
function SetTerrainCell($x,$y,$cell){
    if(isset($cell['terrain']))  SCX::$data_serial['terrain']['data'][$x.','.$y][1] = $cell['terrain'];
    if(isset($cell['elevation']))SCX::$data_serial['terrain']['data'][$x.','.$y][2] = $cell['elevation'];
}

function setCell($point, $terrainId) {
    SetTerrainCell(round($point[0]), round($point[1]), array("terrain" => $terrainId));
}

function setElevation($point, $elevation) {
    SetTerrainCell(round($point[0]), round($point[1]), array("elevation" => $elevation));
}

/**
 * Return: Array of diplomacy states (one key for each player, exclude $player key)
 **/
function GetPlayerDiplomacy($player){
    $diplomacies = array_slice(SCX::$data_serial['victory']['diplomacy']['stance'][$player],0,9);
    unset($diplomacies[0]);
    unset($diplomacies[$player]);
    $diplomacyRef = array_flip(LIB::$Diplomacies);
    foreach($diplomacies as &$diplomacy){
        $diplomacy = $diplomacyRef[$diplomacy];
    } unset($diplomacy);
    return $diplomacies;
}

/**
 * Param: Array of diplomacy states (one key for each player, exclude $player key)
 **/
function SetPlayerDiplomacy($player, $diplomacyStates){
    foreach($diplomacyStates as $p => $diplomacy)if($p != $player){
        if(!key_exists($diplomacy,LIB::$Diplomacies)) SCX::Error("Unknown diplomacy state in SetPlayerDiplomacy(): $diplomacy");
        SCX::$data_serial['victory']['diplomacy']['stance'][$player][$p] = LIB::$Diplomacies[$diplomacy];
    }
}

/**
 * Param: $filename = Absolute path to your image (png or gif)
 * Param: $terrainIds = An array that contains terrain ids bound to color ids
 * Param: $forestDensity = Forest density percentage 0 to 100
 * It works only with images that have indexed colors, you can convert any
 * image to indexed colors with GIMP. Image should have same size as your map.
 * $terrainIds example, for an image that contains 3 colors:
 * 0 => 4 (Bind color 0 to Shallows)
 * 1 => 0 (Bind color 1 to Grass 1)
 * 2 => 5 (Bind color 2 to Leaves)
 **/
function SetTerrainFromImage($filename, $terrainIds, $forestDensity = 100){
    static $types = array(
        'png' => 'imagecreatefrompng',
        'gif' => 'imagecreatefromgif'
    );
    if(!extension_loaded('gd')) SCX::Error("You need to enable gd2.dll in your php.ini to use SetTerrainFromImage()");
    if(!file_exists($filename)) SCX::Error("Image not found in SetTerrainFromImage(): \"$filename\"");
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    if(!key_exists($ext,$types)) SCX::Error("Image type not supported in SetTerrainFromImage(): \"$ext\"");
    $image = $types[$ext]($filename);
    $mapSize = GetMapSize();
    for($y = 0; $y < $mapSize; $y++)
        for($x = 0; $x < $mapSize; $x++){
            $id = @imageColorAt($image,$x,$y);
            if($id !== false && isset($terrainIds[$id])){
                # Set terrain
                SCX::$data_serial['terrain']['data'][$x.','.$y][1] = $terrainIds[$id];
                # Add tree for forest terrain
                if(key_exists($terrainIds[$id], LIB::$TerrainTrees) && ($forestDensity == 100 || rand(0, 100) < $forestDensity)){
                    $tree = LIB::$TerrainTrees[$terrainIds[$id]];
                    NewObject(0,$tree[0], array($x + 0.5,$y + 0.5), 0, -1, rand(0, $tree[1]));
                }
            }
        }
}




/********************************************************
    Areas

/**
 * Calcul an area from a middle point and a wanted size
 * Return: Area
 **/
function AreaSet($L, $Size = 1){
    return Area(
        $L[0] - $Size,
        $L[1] - $Size,
        $L[0] + $Size,
        $L[1] + $Size
    );
}

/**
 * Calcul an area by applying an offset to another area
 * Return: Area
 **/
function AreaOffset($A, $OffsetX = 0, $OffsetY = 0){
    if(!is_array($A[0])){
        $A[0] += $OffsetX;
        $A[1] += $OffsetY;
    }
    else{
        $L1 = AreaOffset($A[0], $OffsetX, $OffsetY);
        $L2 = AreaOffset($A[1], $OffsetX, $OffsetY);
        $A = array($L1,$L2);
    }
    return $A;
}

/**
 * Cuts an area into multiple areas
 * Return: Array of areas
 **/
function AreaCut($A, $Cuts = 1){
	$Areas[0][0][0] = $A[0][0];
	$Areas[0][0][1] = $A[0][1];
	$Areas[0][1][0] = $A[0][0] + ($A[1][0] - $A[0][0]) / 2;
	$Areas[0][1][1] = $A[1][1];
	$Areas[1][0][0] = $A[0][0] + ($A[1][0] - $A[0][0]) / 2;
	$Areas[1][0][1] = $A[0][1];
	$Areas[1][1][0] = $A[1][0];
	$Areas[1][1][1] = $A[1][1];
    if($Cuts > 1){
        $Cuts--;
        $ToCut = $Areas;
        $Areas = array();
        foreach($ToCut as $A){
            $CutsResults = AreaCut($A, $Cuts);
            foreach($CutsResults as $ACut)
                array_push($Areas, $ACut);
        }
    }
	return $Areas;
}

/**
 * Get each point X,Y of an area
 * Return: Array of location points
 **/
function AreaPts($A){
    $Pts = array();
    if($A[0][0] > $A[1][0]) {
        $StartX = $A[1][0];
        $EndX = $A[0][0];
    }
    else {
        $StartX = $A[0][0];
        $EndX = $A[1][0];
    }
    if($A[0][1] > $A[1][1]) {
        $StartY = $A[1][1];
        $EndY = $A[0][1];
    }
    else {
        $StartY = $A[0][1];
        $EndY = $A[1][1];
    }
    foreach(range($StartX, $EndX) as $X)
        foreach(range($StartY, $EndY) as $Y)
            $Pts[] = array($X,$Y);
    return $Pts;
}	

/**
 * Merge multiple areas from an array into a single area
 * Return: Area
 **/
function AreaMerge($Areas){
    $Xmin=999;
    $Ymin=999;
    $Xmax=-1;
    $Ymax=-1;
    # Find lower / higher value
    foreach($Areas as $A)
        foreach($A as $L){
        if($Xmin > $L[0]) $Xmin = $L[0];  
        if($Ymin > $L[1]) $Ymin = $L[1]; 
        if($Xmax < $L[0]) $Xmax = $L[0];  
        if($Ymax < $L[1]) $Ymax = $L[1];
    }
    Return Area($Xmax,$Ymax,$Xmin,$Ymin);
}

/**
 * Calcul a rectangular area with advanced parametters, mainly used to make store purchase areas
 * Param $L: Origin point, at $Width / 2 (round to top) and $Depth = 0
 * Param $Axe: Orientation of the area ('E' = Top right, 'N' = Top left, 'W' = Bot left, 'S' = Bot right)
 * Param $Width: Total width of the area
 * Param $Depth: Total height of the area
 * Return: Area
 **/
function AreaAdvanced($L, $Axe, $Width = 3, $Depth = 2){
    $Depth = $Depth - 1;
    $Width = $Width / 2 - 0.5;
    $Z = 0;
    if(round($Width) != $Width){
        $Width = floor($Width);
        $Z = 1;
    }
    if($Axe == 'E'){ # Face to east (top right side)
        $L1[0] = $L[0] - $Width;
        $L1[1] = $L[1];
        $L2[0] = $L[0] + $Width + $Z;
        $L2[1] = $L[1] + $Depth;
    }
    else if($Axe == 'N'){ # Face to north (top left side)
    	$L1[0] = $L[0] - $Depth;
    	$L1[1] = $L[1] - $Width - $Z;
    	$L2[0] = $L[0];
    	$L2[1] = $L[1] + $Width;
    }
    else if($Axe == 'W'){ # Face to west (bottom left side)
    	$L1[0] = $L[0] - $Width;
    	$L1[1] = $L[1] - $Depth;
    	$L2[0] = $L[0] + $Width + $Z;
    	$L2[1] = $L[1];
    }
    else if($Axe == 'S'){ # Face to south (bottom right side)
    	$L1[0] = $L[0] + $Depth;
    	$L1[1] = $L[1] - $Width - $Z;
    	$L2[0] = $L[0];
    	$L2[1] = $L[1] + $Width;
    }
    return array($L1,$L2);
}

/**
 * Calcul player area from host area, used for symmetric MP maps
 * The function uses two defines to compile faster, it uses middle point coordinates to calcul players areas
 * You can change this middle point if your game isn't exactly at middle of the map
 * You should customize this function depending of players orientation
 * Return: Area
 **/
define('MIDDLE_X', 0);
define('MIDDLE_Y', 0);
function AreaPlayer($P, $A){ # 
 	if(!is_array($A[0])){ #Is not an area ?
		switch ($P) { #It's a location, calcul the point
			 case 1:
			 return $A;
			 break;
			 case 2:
             $L[0] = MIDDLE_X - (MIDDLE_X - $A[0]);
		  	 $L[1] = MIDDLE_Y + (MIDDLE_Y - $A[1]) - 1;
			 break;
			 case 3: 
             $L[0] = MIDDLE_X - (MIDDLE_Y - $A[1]);
		  	 $L[1] = MIDDLE_Y - (MIDDLE_X - $A[0]);
			 break;
			 case 4:
			 $L[0] = MIDDLE_X - (MIDDLE_Y - $A[1]);
		  	 $L[1] = MIDDLE_Y + (MIDDLE_X - $A[0]) - 1;
			 break;
			 case 5:
             $L[0] = MIDDLE_X + (MIDDLE_Y - $A[1]) - 1;
		  	 $L[1] = MIDDLE_Y - (MIDDLE_X - $A[0]);
			 break;
			 case 6:
             $L[0] = MIDDLE_X + (MIDDLE_Y - $A[1]) - 1;
		  	 $L[1] = MIDDLE_Y + (MIDDLE_X - $A[0]) - 1;
			 break;
			 case 7: 
             $L[0] = MIDDLE_X + (MIDDLE_X - $A[0]) - 1;
		  	 $L[1] = MIDDLE_Y - (MIDDLE_Y - $A[1]);
			 break;
			 case 8:
             $L[0] = MIDDLE_X + (MIDDLE_X - $A[0]) - 1;
		  	 $L[1] = MIDDLE_Y + (MIDDLE_Y - $A[1]) - 1;
			 break;
        }
    }
	else{ #It's an area, calcul each points
		$L[0] = Axis($P, $A[0]);
		$L[1] = Axis($P, $A[1]);
    }
	return $L;
}

/**
 * MY CUSTOM FUNCTIONS
 **/  
function offAreaXRight($a, $x) {
    return Area($a[1][0] + 1, $a[0][1], $a[1][0] + $x, $a[1][1]);
}

function offAreaXLeft($a, $x) {
    return Area($a[0][0] - $x + 1, $a[0][1], $a[0][0], $a[1][1]);
}

function offAreaYDown($a, $y) {
    return Area($a[0][0], $a[0][1] - $y, $a[1][0], $a[0][1] - 1);
}

function offAreaYUp($a, $y) {
    return Area($a[0][0], $a[1][1] + 1, $a[1][0], $a[1][1] + $y);
}

/**
 * Calcul areas for all players from host area, used for MP maps
 * By default it will return the result of AreaPlayer for each player, except if $StepX and/or $StepY != 0
 * If you specify $StepX and/or $StepY, it will calculs areas for each player by applying an offset X and Y
 * Param $Players: Get areas only for a specific range of players. By default all players.
 * Return: Array of areas with players id reference
 **/
function AreaPlayers($Area, $StepX = 0, $StepY = 0, $Players = array()){
    if(count($Players) == 0) $Players = LIB::$Players;
    if(!is_array($Area[0])){
        if($StepX != 0 || $StepY != 0)
            foreach($Players as $P){
                $Areas[$P][0] = $Area[0] + ($StepX * ($P - $Players[0]));
                $Areas[$P][1] = $Area[1] + ($StepY * ($P - $Players[0]));
            }
        else
            foreach($Players as $P)
                $Areas[$P] = AreaPlayer($P, $Area);
    }
    else{
        $L1 = AreaPlayers($Area[0], $StepX, $StepY, $Players);
        $L2 = AreaPlayers($Area[1], $StepX, $StepY, $Players);
        foreach($Players as $P){
            $Areas[$P] = array($L1[$P],$L2[$P]);
        }
    }
    return $Areas;
}




/********************************************************
    Triggers - Advanced

/**
 * Boost AP/HP/AP/MS/Range to player units from a single array
 * Examples:
 * $Params = array('Armor' => 2) // +2/+2 armor to all units
 * $Params = array('Y' => 4, 'AP' => 10, 'HP' => 100) // +10 AP and +100 HP to military units
 * $Params = array('Y' => 4, 'Range' => 5, 'A' => Area(10,10,20,20)) // +5 Range to military units at 10,10,20,20 area
 **/
function Efft_Boost($P, $Params){
    if(!key_exists('A',$Params))$Params['A'] = null;
    if(key_exists('U',$Params)){
        if(key_exists('Armor',$Params))Efft_ArmorU($P,$Params['Armor'],$Params['U'],$Params['A']);
        if(key_exists('HP',$Params))Efft_HPU($P,$Params['HP'],$Params['U'],$Params['A']);
        if(key_exists('AP',$Params))Efft_APU($P,$Params['AP'],$Params['U'],$Params['A']);
        if(key_exists('MS',$Params))Efft_MSU($P,$Params['MS'],$Params['U'],$Params['A']);
        if(key_exists('Range',$Params))Efft_RangeU($P,$Params['Range'],$Params['U'],$Params['A']);
    }
    else if(key_exists('Y',$Params)){
        if(key_exists('Armor',$Params))Efft_ArmorY($P,$Params['Armor'],$Params['Y'],$Params['A']);
        if(key_exists('HP',$Params))Efft_HPY($P,$Params['HP'],$Params['Y'],$Params['A']);
        if(key_exists('AP',$Params))Efft_APY($P,$Params['AP'],$Params['Y'],$Params['A']);
        if(key_exists('MS',$Params))Efft_MSY($P,$Params['MS'],$Params['Y'],$Params['A']);
        if(key_exists('Range',$Params))Efft_RangeY($P,$Params['Range'],$Params['Y'],$Params['A']);
    }
    else if(key_exists('G',$Params)){
        if(key_exists('Armor',$Params))Efft_ArmorG($P,$Params['Armor'],$Params['G'],$Params['A']);
        if(key_exists('HP',$Params))Efft_HPG($P,$Params['HP'],$Params['G'],$Params['A']);
        if(key_exists('AP',$Params))Efft_APG($P,$Params['AP'],$Params['G'],$Params['A']);
        if(key_exists('MS',$Params))Efft_MSG($P,$Params['MS'],$Params['G'],$Params['A']);
        if(key_exists('Range',$Params))Efft_RangeG($P,$Params['Range'],$Params['G'],$Params['A']);
    }
    else{
        if(key_exists('Armor',$Params))Efft_ArmorO($P,$Params['Armor'],$Params['A']);
        if(key_exists('HP',$Params))Efft_HPO($P,$Params['HP'],$Params['A']);
        if(key_exists('AP',$Params))Efft_APO($P,$Params['AP'],$Params['A']);
        if(key_exists('MS',$Params))Efft_MSO($P,$Params['MS'],$Params['A']);
        if(key_exists('Range',$Params))Efft_RangeO($P,$Params['Range'],$Params['A']);
    }
}

/**
 * Make an explosion at location $L
 * Param $Rayon: Explosion rayon around $L
 * Param $Filled: Fill the whole area with explosions
 **/
function Efft_Explosion($L, $Rayon = 0, $Filled = false){
    $RayonSource = $Rayon;
    $X = $L[0];
    $Y = $L[1];
    if($Rayon < 1 || $Filled)
        Efft_Create(0, 816, $L);
    if($Rayon >= 1){
      	do{ #Make a square of $Rayon size, $Filled or not
    	 	Efft_Create(0, 816, array($X - $Rayon, $Y));
    	 	Efft_Create(0, 816, array($X + $Rayon, $Y));
    	 	Efft_Create(0, 816, array($X, $Y - $Rayon));
    	 	Efft_Create(0, 816, array($X, $Y + $Rayon));
    	 	for($i = 1; $i <= $Rayon; $i++){
    			Efft_Create(0, 816, array($X - $Rayon, $Y - $i));
    			Efft_Create(0, 816, array($X - $Rayon, $Y + $i));
    			Efft_Create(0, 816, array($X + $Rayon, $Y- $i));
    			Efft_Create(0, 816, array($X + $Rayon, $Y + $i));
    			if($i > 1){
    				Efft_Create(0, 816, array($X + ($i - 1), $Y - $Rayon));
    				Efft_Create(0, 816, array($X - ($i - 1), $Y - $Rayon));
    				Efft_Create(0, 816, array($X + ($i - 1), $Rayon + $Y));
    				Efft_Create(0, 816, array($X - ($i - 1), $Rayon + $Y));}}
    		$Rayon--;
        }while($Filled && $Rayon != 0);
    }
    Efft_KillU(0,816,AreaSet($L,$RayonSource));
}

/**
 * Make an unit invincible (0 HP), you need to specify current HP for buildings
 **/
function Efft_InvincibleO($P, $HP_For_Buildings = null, $A = null){
	if($HP_For_Buildings === null){
	   Efft_DamageO($P, -2147483647, $A);
       Efft_DamageO($P, -2147483647, $A);
    }
	else{
	   Efft_HPO($P, -$HP_For_Buildings, $A);
       Efft_DamageO($P, -1, $A);
       Efft_HPO($P, $HP_For_Buildings, $A);
    }
}

/**
 * Make an unit invincible (0 HP), you need to specify current HP for buildings
 **/
function Efft_InvincibleS($S, $HP_For_Buildings = null){
	if($HP_For_Buildings === null){
	   Efft_DamageS(-2147483647, $S);
       Efft_DamageS(-2147483647, $S);
    }
	else{
	   Efft_HPS(-$HP_For_Buildings, $S);
       Efft_DamageS(-1, $S);
       Efft_HPS($HP_For_Buildings, $S);
    }
}

/**
 * Make an unit invincible (0 HP), you need to specify current HP for buildings
 **/
function Efft_InvincibleU($P, $U, $HP_For_Buildings = null, $A = null){
	if($HP_For_Buildings === null){
	   Efft_DamageU($P, -2147483647, $U, $A);
       Efft_DamageU($P, -2147483647, $U, $A);
    }
	else{
	   Efft_HPU($P, -$HP_For_Buildings, $U, $A);
       Efft_DamageU($P, -1, $U, $A);
       Efft_HPU($P, $HP_For_Buildings, $U, $A);
    }
}

/**
 * Make an unit invincible (0 HP), you need to specify current HP for buildings
 **/
function Efft_InvincibleY($P, $Y, $HP_For_Buildings = null, $A = null){
	if($HP_For_Buildings === null){
	   Efft_DamageY($P, -2147483647, $Y, $A);
       Efft_DamageY($P, -2147483647, $Y, $A);
    }
	else{
	   Efft_HPU($P, -$HP_For_Buildings, $Y, $A);
       Efft_DamageU($P, -1, $Y, $A);
       Efft_HPU($P, $HP_For_Buildings, $Y, $A);
    }
}

/**
 * Make an unit invincible (0 HP), you need to specify current HP for buildings
 **/
function Efft_InvincibleG($P, $G, $HP_For_Buildings = null, $A = null){
	if($HP_For_Buildings === null){
	   Efft_DamageG($P, -2147483647, $G, $A);
       Efft_DamageG($P, -2147483647, $G, $A);
    }
	else{
	   Efft_HPG($P, -$HP_For_Buildings, $G, $A);
       Efft_DamageG($P, -1, $G, $A);
       Efft_HPG($P, $HP_For_Buildings, $G, $A);
    }
}

/**
 * Deactive triggers assigned to a player
 * You can assign a player to a trigger by specifying $P param in Trig()
 **/
function Efft_DeactPlayerTriggers($P){
    foreach(SCX::$player_triggers[$P] as $I)
        Efft_Deact($I);
}




/********************************************************
    Constants used by the library, you can configure it as you want
*/

class LIB{
    
    /**
     * Players involved in library functions (AreaPlayers() for example)
     * Configure it as you want to run function on different range of players
     * This is used only for MP maps
     **/
    static $Players = array(1,2,3,4,5,6,7,8);
    
    static $StartAges = array(
        'None' => -1,
        'Dark' => 0,
        'Feudal' => 1,
        'Castle' => 2,
        'Imperial' => 3,
        'Post-Imperial' => 4
    );
    
    static $Difficulties = array(
        'Hardest' => 0,
        'Hard' => 1,
        'Moderate' => 2,
        'Standard' => 3,
        'Easiest' => 4
    );
    
    static $Diplomacies = array(
        'Ally' => 0,
        'Neutral' => 1,
        'Enemy' => 3
    );

    static $CivIds = array(
		'Britons' => 1,
        'Franks' => 2,
        'Goths' => 3,
        'Teutons' => 4,
        'Japanese' => 5,
        'Chinese' => 6,
        'Byzantines' => 7,
        'Persians' => 8,
        'Sarracens' => 9,
        'Turks' => 10,
        'Vikings' => 11,
        'Mongols' => 12,
        'Celts' => 13,
        'Spanish' => 14,
        'Aztecs' => 15,
        'Mayans' => 16,
        'Huns' => 17,
        'Koreans' => 18,
    );

    static $CivTechs = array( # Hidden civ technology Id (used for civ detection)
		'Aztecs' => 431,
		'Britons' => 263,
		'Byzantines' => 267,
		'Celts' => 277,
		'Chinese' => 268,
		'Franks' => 275,
		'Goths' => 446,
		'Huns' => 1,
		'Japanese' => 262,
		'Koreans' => 449,
		'Mayans' => 26,
		'Mongols' => 273,
		'Persians' => 274,
		'Saracens' => 269,
		'Spanish' => 58,
		'Teutons' => 276,
		'Turks' => 271,
		'Vikings' => 399
	);
	
	static $CivEliteTechs = array( # Elite unit technology Id
		'Aztecs' => 432,
		'Britons' => 360,
		'Byzantines' => 361,
		'Celts' => 370,
		'Chinese' => 362,
		'Franks' => 363,
		'Goths' => 365,
		'Huns' => 2,
		'Japanese' => 366,
		'Koreans' => 450,
		'Mayans' => 27,
		'Mongols' => 371,
		'Persians' => 367,
		'Saracens' => 368,
		'Spanish' => 60,
		'Teutons' => 364,
		'Turks' => 369,
		'Vikings' => 398
	);
    
    static $CivEliteUnit = array( # Elite unit object Id
		'Aztecs' => 726,
		'Britons' => 530,
		'Byzantines' => 553,
		'Celts' => 534,
		'Chinese' => 559,
		'Franks' => 531,
		'Goths' => 761,
		'Huns' => 757,
		'Japanese' => 560,
		'Koreans' => 829,
		'Mayans' => 765,
		'Mongols' => 561,
		'Persians' => 558,
		'Saracens' => 556,
		'Spanish' => 773,
		'Teutons' => 554,
		'Turks' => 557,
		'Vikings' => 694
	);
    
    # Custom groups, customize it as you want
    static $Group_Archers = array(4,5,24,39,474,492);
    static $Group_Arrows = array(54,97,245,246,315,316,317,318,319,320,321,322,328,360,363,364,367,372,375,378,381,385,466,470,471,475,476,477,478,485,503,504,505,507,509,510,511,512,514,516,517,518,519,521,522,523,524,525,537,538,540,541,627,628,746,747,786,787);
    static $Group_Betas = array(76,158,299,444,479,493,544,571,573,575,577,748,749);
    static $Group_Buildings = array(117,115,10,12,14,18,19,20,30,31,32,33,45,47,49,50,51,68,70,71,72,79,82,84,86,87,101,103,104,105,109,110,116,129,130,131,132,133,137,141,142,150,153,179,199,208,209,210,234,235,236,276,278,345,445,446,463,464,465,481,482,483,484,498,562,563,564,565,584,585,586,587,597,598,599,605,606,607,608,609,610,611,612,613,614,615,616,617,618,619,620,621,624,625,626,655,684,685,689,690,696,712,713,714,715,716,717,718,719,738,739,740,741,742,743,785,805,806,807,808,826,63,64,67,78,80,81,85,88,90,91,92,95,487,488,490,491,659,660,661,662,663,664,665,666,667,668,669,670,671,672,673,674,789,790,791,792,793,794,795,796,797,798,799,800,801,802,803,804);
    static $Group_Castle = array(6,7,8,11,25,40,41,42,46,73,94,232,239,281,282,291,331,434,440,441,530,531,534,553,554,555,556,557,558,559,560,561,583,596,692,694,725,726,755,757,759,761,763,765,771,773,827,829);
    static $Group_Cavalry = array(37,38,283,329,330,448,546,569);
    static $Group_Cliffs = array(265,266,270,271,272,273);
    static $Group_Dead = array(3,16,22,23,26,27,28,34,43,44,58,60,62,98,99,100,107,111,113,115,121,134,135,136,138,139,140,149,151,152,154,157,178,180,181,194,205,211,213,215,217,219,221,223,224,225,226,227,228,229,230,233,237,238,353,355,356,423,425,431,435,449,480,494,495,496,497,501,502,543,547,549,568,570,572,574,576,578,580,582,589,591,593,595,622,630,631,633,675,687,693,695,705,708,735,750,754,756,762,764,772,776,778,780,784,811,813,815,823,825,828,839,841,843,853);
    static $Group_Heroes = array(160,161,163,164,165,166,167,168,169,170,171,172,173,174,175,176,177,195,196,197,198,200,202,424,426,428,430,432,629,632,634,636,638,640,642,644,646,648,650,652,678,680,682,683,686,698,700,702,704,706,707,729,730,731,733,777,779,781,783,824,838,840,842,844,845,847,849,852,860,861);
    static $Group_Infantry = array(74,75,77,93,358,359,473,567,751,752);
    static $Group_Market = array(84,110,116,128,137,204);
    static $Group_Misc = array(159,242,244,247,248,249,252,253,262,274,310,311,313,314,324,325,326,327,352,365,366,368,369,371,374,376,377,380,417,447,452,453,454,459,462,468,469,506,508,513,515,520,526,551,552,656,657,658,676,677,728,736,737,767);
    static $Group_Monastery = array(125,285,286,287,288,289,290,292,294,295,296,297,298,300,301,302,303,304,305,306,307,308,775);
    static $Group_Others = array(48,53,59,65,66,69,89,96,102,126,143,144,145,146,147,148,241,333,334,335,336,337,338,339,340,341,389,396,450,451,455,456,457,458,499,594,600,601,602,603,604,623,688,709,710,711,720,721,722,723,744,745,810,812,814,816,817,818,819,820,821,822,833,835,837,851,854,855,856,857,858,859,862,863,864,865);
    static $Group_Ships = array(13,15,17,21,61,250,420,436,438,443,527,528,529,532,533,535,536,539,545,691,831,832);
    static $Group_Siege = array(5,35,36,279,280,422,542,548,550,588);
    static $Group_Trees = array(284,348,349,350,351,399,400,401,402,403,404,405,406,407,408,409,410,411,413,414,415,809);
    static $Group_Units = array(4,5,5,6,7,8,11,24,25,35,36,39,40,41,42,46,73,74,75,77,93,94,125,204,232,239,279,280,281,282,291,331,358,359,422,434,440,441,473,474,492,530,531,534,542,548,550,553,554,555,556,557,558,559,560,561,567,583,588,596,692,694,725,726,751,752,755,757,759,761,763,765,771,773,775,827,829);
    static $Group_Villagers = array(56,57,83,118,120,122,123,124,156,206,207,212,214,216,218,220,222,259,293,354,579,581,590,592);
    static $Group_Walls = array(63,64,67,78,80,81,85,88,90,91,92,95,117,155,487,488,490,491,659,660,661,662,663,664,665,666,667,668,669,670,671,672,673,674,789,790,791,792,793,794,795,796,797,798,799,800,801,802,803,804);

    # Tree list with maximum frame referenced by terrains
    static $TerrainTrees = array(
      TERRAIN_BAMBOO => array(U_FOREST_BAMBOO, 3),
      TERRAIN_FOREST => array(U_FOREST_TREE, 13),
      TERRAIN_FOREST_OAK => array(U_FOREST_OAK, 13),
      TERRAIN_JUNGLE => array(U_JUNGLE_TREE, 13),
      TERRAIN_PALM_DESERT => array(U_FOREST_PALM, 12),
      TERRAIN_PINE_FOREST => array(U_FOREST_PINE, 8),
      TERRAIN_SNOW_PINE_FOREST => array(U_SNOW_PINE_TREE, 8)
    );
}

?>