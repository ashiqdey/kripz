<?php

//insert in user login table
function insert($type,$insert){
	$XBLPUBcon = $GLOBALS['XBLPUBcon'];
	if ($XBLPUBcon->query($insert) === TRUE){
		$data = "ok";
	}else{
		$data = "fail".$XBLPUBcon->error;
	}
	return $data;
}




//initial fetch
function init_fetch($limit,$query){
    $XBLPUBcon = $GLOBALS['XBLPUBcon'];
	$time_now = $GLOBALS['c_time'];

    $result = $XBLPUBcon->query($query);
        
        //calculate offset
        $totalrows  = mysqli_num_rows($result);
        $offset  = 0;
        if($totalrows>$limit){$offset  = $totalrows - $limit;}

    $query .= " ORDER BY m_id LIMIT $limit OFFSET $offset";
    $result = $XBLPUBcon->query($query);
    if ($result->num_rows > 0){

        $result = $XBLPUBcon->query($query);
        while($row = $result->fetch_assoc()) {
            $id=$row['m_id'];
            $user=$row['m_user'];
            $mgs=$row['m_mgs'];
            $type=$row['m_type'];
            $time=$row['m_time'];

            $mgs = replace_tool($mgs);
            array_push($GLOBALS['messages'],array("id"=>$id, "t"=>$type,"u"=>$user,"m"=>"$mgs"));
        }

        
        if(strpos("$mgs","hi") !== false){
            $mgs="contains";
        }else if($time==$time_now){
            $mgs="chated just now";
        }
        else{
            $reply="Hi {{name}}";
            if(date("H")>4 && date("H")<21){
                $reply.=" {{daywish}}";
            }
            
            $reply = reply_in_push(1,$reply);
        }
        
    }else{
        $reply="Hi ".$_SESSION['name'];
        if(date("H")>4 && date("H")<21){
            $reply.=" {{daywish}}";
        }
        $reply = reply_in_push(1,$reply);
    }

    //return $messages;
}




function download_chat(){
    $XBLPUBcon = $GLOBALS['XBLPUBcon'];
    $user_name=$_SESSION['name'];
    $userid=$_SESSION['liag'];
    $data='';

    $fetch = "SELECT * FROM chats WHERE m_userid='$userid' AND m_status=1";
    $result = $XBLPUBcon->query($fetch);
    if ($result->num_rows > 0){

      while($row = $result->fetch_assoc()) {

        $user = $row["m_user"];
        $date = $row["m_date"];
        $time = $row["m_time"];
        $mgs = $row["m_mgs"];
        
        if($user=="2"){
            $name="Kripz";
        }else if($user=="1"){
            $name=$user_name;
        }else{
            $name=' --- ';
        }
        $data .= "\n".$name." : ".$time." : ".$mgs;

      }

        
    }else{
        $data = "NO DATA";
    }

    $filename = "Kripz ".date('mdhi').".txt";
        header("Content-Type: application/csv");
        header("Content-Disposition: attachment; filename=".$filename);
        header("Pragma: no-cache");
        header("Expires: 0");
  echo $data;
}

?>