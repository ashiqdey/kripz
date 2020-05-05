<?php
session_start();
include('../inc/def.php');

$XBLPUBcon = db_conn("obj");
$mgs='';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	if(isset($_POST['setname'])){
		$mgs=mysqli_real_escape_String($XBLPUBcon,$_POST['setname']);
	}
}

echo $mgs;
?>