<?php
date_default_timezone_set('Asia/Kolkata');
$c_date = date('d M Y');
$c_time = date('H:i');

/*database connection*/
$xbl_sn = "localhost";
$xbl_nm = "root";
$xbl_ps = "";
$xbl_db = "chatbot";
/*database connection*/

$session = "56AdzVGd";

$root_url = "http://www/chatbot/2019 - demo/";

$jquery = "http://www/cdn/js/jquery.min.js";



//encode 2
function ec($data) {
  $data = strrev(str_rot13(strrev(str_rot13(base_ec($data)))));
  return $data;
}
//decode 2
function de($data) {
  $data= base_de(str_rot13(strrev(str_rot13(strrev($data)))));
  return $data;
}
//encode sub function
function base_ec($data){
  $data = strrev(base64_encode($data));
  $data = str_replace("==", "56", $data);
  $data = str_replace("=", "28", $data);
  return $data;
}
function base_de($data){
  $data = str_replace("28", "=", $data);
  $data = str_replace("56", "==", $data);
  $data = base64_decode(strrev($data));
  return $data;
}


//database connection
function db_conn($type){
    $XBLPUBcon = new mysqli($GLOBALS['xbl_sn'], $GLOBALS['xbl_nm'], $GLOBALS['xbl_ps'], $GLOBALS['xbl_db']);
    return $XBLPUBcon;
}
?>