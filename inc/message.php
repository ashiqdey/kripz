<?php
session_start();
include('def.php');
include('func.php');

$XBLPUBcon = db_conn("obj");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$messages = array();
	//requested for robots reply
	if(isset($_SESSION['liag']) && isset($_SESSION['name'])){
		$userid=$_SESSION['liag'];
		$name=$_SESSION['name'];

		if(isset($_POST['m'])){

			$mgs = mysqli_real_escape_string($XBLPUBcon,$_POST['m']);
			
			//insert message in db
			$data = insert("mgs","INSERT INTO chats 
					(m_userid,m_user,m_mgs,m_time,m_date,m_type,m_status) 
					VALUES ('$userid','1','$mgs','$c_time','$c_date','1','1')");

			//find revelent reply
			$data = get_reply($mgs);
		
			$messages = '{"row":'.json_encode($messages).'}';		
		}

		//requested initial messages
		else if(isset($_POST['initial'])){
			$limit = mysqli_real_escape_string($XBLPUBcon,$_POST['initial']);
			$data = init_fetch($limit,"SELECT * FROM chats WHERE m_userid=$userid AND m_status=1");

			$messages = '{"row":'.json_encode($messages).'}';
		}
	}

	//need to login
	else{
		push_new(0,0,0,"login");
		//array_push($messages,array("id"=>0, "t"=>0,"u"=>0,"m"=>"login"));
	}
	
}else{
	$messages = "Invalid response";
}

echo $messages;


function push_new($id,$t,$u,$mgs){
	if($id==0){
		$id = rand(00000,99999);
	}
	$mgs=str_replace("", "", $mgs);
	array_push($GLOBALS['messages'],array("id"=>$id, "t"=>$t,"u"=>$u,"m"=>"$mgs"));
}



//search for reply
function get_reply($mgs){
	//fetch reply fromdb
	$XBLPUBcon = $GLOBALS['XBLPUBcon'];
	$reply="";
	$query="no_query";
	$type=1;

	//operation on whole string
	$mgs = strtolower($mgs);
	$mgs = remove_special($mgs);
	$mgs = remove_double($mgs);
	

	$mgs_explod = explode (" ", $mgs);
	$mgs_size = count($mgs_explod);
	$x = "";
	$construct = "";
	//for call 7008237
	/*
	if($mgs_explod[0]=="call"){
			$reply = call($mgs);
			$type=2;
	}
	else if($mgs_explod[0]=="calc"){
		$reply = calculate($mgs);
		
	}
	*/
	if($mgs_size>2){
		if($mgs_explod[0]=="cal"){
			$reply = call($mgs);
			$type=2;
		}
		else if(strpos("$mgs","calc") !== false){
			$reply = calculate($mgs);
		}
	}

	//reply is empty
	if($reply==""){
		foreach($mgs_explod as $mgs_each){
			//various speeel check and word removal
			//operation on single word
		
			if(strlen($mgs_each)>0){
				$x++;
				

				if($mgs_size==1){
					$mgs_each = replace_single($mgs_each);
				}else{
					$mgs_each = remove_stop($mgs_each);
					$mgs_each = replace_word($mgs_each);
				}

				

				if($x==1){
					$construct ="f_mgs LIKE '%$mgs_each%'";
				}
				
				else{
					$construct .=" AND f_mgs LIKE '%$mgs_each%'"; 
				}
			}
		}


		$query="SELECT * FROM first WHERE $construct ORDER BY f_id LIMIT 1";
		$result = $XBLPUBcon->query($query);
	    if ($result->num_rows > 0){
	        $row = $result->fetch_assoc();
	        $reply = $row['f_reply'];
	        $type = $row['f_type'];
	        $n = $row['f_n'];

	        $_SESSION['fail']=0;

	        if($n>1){
	        	//fetch all result with reference to $reply
	        	$query_b="SELECT * FROM first WHERE f_mgs='$reply' ORDER BY f_id LIMIT $n";
	        	$result_b = $XBLPUBcon->query($query_b);
	        	if ($result_b->num_rows > 0){
	        		while($row_b = $result_b->fetch_assoc()){
	        			$reply_b = $row_b['f_reply'];
				        $type_b = $row_b['f_type'];

				        //insert and push in array
				        $reply = reply_in_push($type_b,$reply_b);
	        		}
	        		//$reply = reply_in_push($type,$query_b);

	        	}
	        	$reply ='';
	        }

	        
	    }
	    else{
	    	$type=1;
	    	$_SESSION['fail']=$_SESSION['fail']+1;
	    	if($_SESSION['fail']>0 && $_SESSION['fail']%3==0){
	    		$reply = fail();
	    	}
	    	else if($_SESSION['fail']%4==0){
	    		$reply = fail();
	    	}
			
			
		}
	}
	


	if($reply ==''){
        $reply='done';
	}else{
		$reply = reply_in_push($type,$reply);
	}

}


function call($call){
	$call = remove_alphabet($call);
	$letter = array("/",",","\\","_","'","\"","<",">","&"," ","-","+","(",")");
	foreach($letter as $s){
		$call = str_replace($s,"",$call);
	}

	$call = "<a href='tel:".$call."'><button class='open call'>".$call."</button></a>";
	return $call;
}



function calculate($calculate){
	$calculate = remove_alphabet($calculate);
	$letter = array("\\","_","'","\"","<",">","&"," ");
	foreach($letter as $s){
		$calculate = str_replace($s,"",$calculate);
	}

	$calculate = eval("return ".$calculate.";");
	if(is_numeric($calculate)){
		$calculate = "Result : ".$calculate;
	}else{
		$calculate='';
	}
	return $calculate;
}

function remove_alphabet($str){
	$letter = array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z");
	foreach($letter as $s){
		$str = str_replace($s,"",$str);
	}
	return $str;
}





function reply_in_push($type,$reply){
	$XBLPUBcon = $GLOBALS['XBLPUBcon'];

	$userid = $GLOBALS['userid'];
	$name = $GLOBALS['name'];
	$c_time =  $GLOBALS['c_time'];

	//replace {{name}}
	$reply = replace_reply($reply);
	$reply = str_replace("\r\n", "<br>", $reply);



	$reply = mysqli_real_escape_string($XBLPUBcon,$reply);
	$data = insert("mgs","INSERT INTO chats 
			(m_userid,m_user,m_mgs,m_time,m_date,m_type,m_status) 
			VALUES ('$userid','2','$reply','$c_time','d','$type','1')");
	if($data =="fail"){

	}else{
		$data="done";
	}


/*
insert {{whatsapp}} {{realtimehtmleditor}} in db
but while showing chage the code


whatsapp
<br><a href=\"whatsapp://\"><button class=\"open whatsapp\">OPEN WHATSAPP</button></a><br>";
*/  
	$reply = replace_tool($reply);
	$reply = str_replace("\'","'", $reply);
	push_new(0,$type,2,$reply);
	return $reply;
}







//replace {{name}} in reply
function replace_reply($reply){
	$name=$_SESSION['name'];
	$replace_r = array(
		"{{name}}"=>$name,
		"{{daywish}}"=>daywish("any"),
		"{{doing}}"=>doing(),
		"{{joke}}"=>joke("type"),
		"{{fact}}"=>fact("type"),
		"{{date}}"=>date('F d Y'),
		"{{day}}"=>date('l'),
		"{{month}}"=>date('F'),
		"{{year}}"=>date('Y'),
		"{{time}}"=>date('g:i:s A'),
		"{{usd_rate}}"=>usd_rate(),
		"{{usdinr}}"=>"r",

	);
	foreach($replace_r as $key=>$value){
		$reply=str_replace($key, $value, $reply);
	}
	return $reply;
}
 
function replace_tool($reply){
	$replace_t = array(
		"{{whatsapp}}"=>"<a href='whatsapp://send?abid=username'><button class='open whatsapp'>OPEN WHATSAPP</button>",
		"{{realtimehtmleditor}}"=>"<a href='tools/html_editor' target='_blank'><button class='open htmleditor'>Open HTML editor</button></a>",
		"{{google}}"=>"<form action='https://www.google.com/search' method='GET' class='google_form' target='_blank'> <input type='text' name='q' placeholder='Search google' class='input'> <button></button> </form>",
		"{{bing}}"=>"<form action='https://www.bing.com/search' method='GET' class='bing_form' target='_blank'> <input type='text' name='q' placeholder='Search bing' class='input'> <button></button> </form>",
		"{{curency_convert}}"=>"<iframe src='tools/currency/index' class='iframe_200'></iframe>",
		
		"{{laugh}}"=>"<button class='emj laugh'></button>",
		"{{laugh1}}"=>"<button class='emj laugh1'></button>",
		"{{laugh2}}"=>"<button class='emj laugh2'></button>",
		"{{grin}}"=>"<button class='emj grin'></button>",
		"{{toung}}"=>"<button class='emj toung'></button>",
		"{{blush}}"=>"<button class='emj blush'></button>",
		"{{sad}}"=>"<button class='emj sad'></button>",
		"{{cry}}"=>"<button class='emj cry'></button>",
		"{{sleep}}"=>"<button class='emj sleep'></button>",
		"{{angry}}"=>"<button class='emj angry'></button>",
		"{{heart}}"=>"<button class='emj heart'></button>",
		"{{hearteye}}"=>"<button class='emj hearteye'></button>",
		"{{heartkiss}}"=>"<button class='emj heartkiss'></button>",

		"{{vv}}"=>"vv"
	);
	foreach($replace_t as $key=>$value){
		$reply=str_replace($key, $value, $reply);
	}
	return $reply;
}
 

 /*reply ends*/





function daywish($type){
	$time = date("H");

	$wish = " Good ";
	if($time>3 && $time<12){
		$wish.="morning";
	}
	else if($time>=12 && $time<16){
		$wish.="afternoon";
	}
	else if($time>=16 && $time<21){
		$wish.="evening";
	}else{
		$wish.="night";
	}

	if($type=="last"){
		if($time>19){
		$wish.="night";
		}
	}

	return $wish;
}



function joke($type){
	$XBLPUBcon = $GLOBALS['XBLPUBcon'];
	$joke="Opps";
	$but1 = "<button class='emj laugh1'></button>";
	$but2 = "<button class='emj laugh2'></button>";

	if(date('s')<10){
		$rand = $but1.$but1.$but1;
	}else if(date('s')<20){
		$rand = $but2.$but2.$but2;
	}else if(date('s')<30){
		$rand = $but1.$but2;
	}else if(date('s')<40){
		$rand = $but2.$but1.$but2;
	}else if(date('s')<50){
		$rand = $but1.$but1;
	}else{
		$rand = $but2.$but2.$but1.$but1;
	}

	$query = "SELECT content FROM chat_content WHERE type='joke' ORDER BY RAND()";
    $result = $XBLPUBcon->query($query);
    if ($result->num_rows > 0){
        $row = $result->fetch_assoc();
        $joke=$row['content'];
    }

    $joke .="<br><br><center class='tc14'>".$rand."</center>";
	return $joke;
}


function fact($type){
	$XBLPUBcon = $GLOBALS['XBLPUBcon'];
	$fact="Opps";
	$query = "SELECT content FROM chat_content WHERE type='fact' ORDER BY RAND()";
    $result = $XBLPUBcon->query($query);
    if ($result->num_rows > 0){
        $row = $result->fetch_assoc();
        $fact=$row['content'];
    }
	return $fact;
}




//failed to fetch revelant reply
function fail(){
	$XBLPUBcon = $GLOBALS['XBLPUBcon'];
	$fail="Opps";
	$query = "SELECT content FROM chat_content WHERE type='fail' ORDER BY RAND()";
    $result = $XBLPUBcon->query($query);
    if ($result->num_rows > 0){
        $row = $result->fetch_assoc();
        $fail=$row['content'];
    }
	return $fail;
}


function usd_rate(){
$converted_amount = "<b>1 USD = 71.15 INR</b>";
  return $converted_amount;
}




function doing(){
	$time = date('H');

	if($time<4){
		$doing="You know m feeling sleepy now, I think it time to sleep";
	}
	else if($time<5){
		$doing="Just woke up";
	}
	else if($time<7){
		$doing="Preparing breakfast";
	}
	else if($time<10){
		$doing="As usual<br><br><br><br><br><br><br>chating{{blush}}";
	}
	else if($time<13){
		$doing="Preparing lunch";
	}
	else if($time<17){
		$doing="Walking with my dog in park";
	}
	else if($time<20){
		$doing="Came from gym.....fitness first {{grin}}";
	}
	else if($time<22){
		$doing="Preparing dinner";
	}
	else{
		$doing="Watching my favourite show";
	}
	

	return $doing;
}

/*

search

*/


function remove_special($mgs){
	$rchar = array("'","\"","/","\\",".",",","?","<",">",";",":","[","]","{","}","|","_","~","`","!","#","$","%","^","&","*","(",")","","");
	foreach($rchar as $c){
		$mgs = str_replace($c,"",$mgs);
	}
	return $mgs;
}

function remove_double($mgs){
	$double = array("aa","bb","cc","dd","ee","ff","ff","hh","ii","jj","kk","ll","mm","nn","oo","pp","qq","rr","ss","tt","uu","vv","ww","xx","yy","zz");
	foreach($double as $d){
		$mgs = str_replace($d, substr($d, 1), $mgs);
		$mgs = str_replace($d, substr($d, 1), $mgs);
	}
	return $mgs;
}

function remove_stop($mgs){
	$stop = array("to","of","is","in","a","the","on","so","aur","dear","deer","ow","aw","oho","kripz","oh","ho","at","janu","hey","hi","oye");
	foreach($stop as $s){
		if($mgs==$s){
			$mgs = str_replace($s,"",$mgs);
		}
	}
	return $mgs;
}


function replace_single($mgs){
	$replace_w = array(
		"hi"=>"hiname",
		"hy"=>"hiname",
		"hey"=>"heyname"
	);
	foreach($replace_w as $key=>$value){
		$mgs=str_replace($key, $value, $mgs);
	}
	return $mgs;
}


function replace_word($mgs){
	$replace_w = array(
		"nt"=>"not",
		"wht"=>"what",
		"hows"=>"how",
		"howz"=>"how",

		"whch"=>"which",
		"wich"=>"which",

		"ur"=>"your",
		"whats"=>"what",
		"doesnt"=>"does not",
		"cant"=>"can not",
		"babe"=>"baby",
		"say"=>"tel",
		"have"=>"had", 

		"smthng"=>"something",
		"somthng"=>"something",
		"evrthng"=>"everything",
		"evrythng"=>"everything",
		"nthng"=>"nothing",
		"oky"=>"ok",
		"okay"=>"ok",
		"oka"=>"ok",
		"okh"=>"ok",
		"ys"=>"yes",
		"yep"=>"yes",
		"ya"=>"yes",
		"yeah"=>"yes",
		"yea"=>"yes"
	);
	foreach($replace_w as $key=>$value){
		if($mgs==$key){
			$mgs=str_replace($key, $value, $mgs);
		}
		
	}
	return $mgs;
}

/* message transmission format
		$messages = '{
				"no":3,
			    "row":[
			        {"id":4,"t":1,"u":1,"m":"My message '.date('s').'"},
			        {"id":5,"t":1,"u":2,"m":"My message '.date('s').'"},
			        {"id":6,"t":1,"u":1,"m":"My message '.date('s').'"}
			    ]}';

		array_push($messages,array("id"=>rand(10,99), "t"=>1,"u"=>2,"m"=>"My message ".$message.date('s')));
	*/
?>