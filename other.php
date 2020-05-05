<?php
session_start();
include('inc/def.php');
include('inc/func.php');
$XBLPUBcon = db_conn("obj");

if(isset($_SESSION['liag']) && isset($_SESSION['name'])){
	if(isset($_GET['download'])){
		download_chat();
	}
	else if(isset($_GET['clear'])){
		$userid=$_SESSION['liag'];
		$data = insert("clear_chat","UPDATE chats SET m_status=0 WHERE m_userid=$userid");
		if($data=="ok"){
			$data = "Chat cleared";
		}else{
			$data = "Failed to clear chat";
		}
		header("location:../index");
	}
	else{
		header("location:../");
	}
}
else{
	header("location:../");
}




?>
