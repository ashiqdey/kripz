<?php
session_start();
if(!isset($_SESSION['liag']) && !isset($_SESSION['name'])){
  $_SESSION['liag'] = "101";
  $_SESSION['name'] ="Jhon";
	header("location:index.php");
}
?>

<!DOCTYPE html>
  <html lang='en'>
  <head>
    <meta charset='UTF-8'>
    <meta name='theme-color' content=''>
    <meta name='aplication-name' content='Kripz'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta name='keywords' content='ashiqdey xbytelab, kripz, chatbot'/>
    <meta name='description' content='Kripz is a lil virtual chatbot'/>
    <meta name='subject' content='Chatbot'>
    <meta name='copyright' content='X Byte Lab'>
    <meta name='revised' content='Thursday, October 8th, 2019, 5:15 pm' />
    <meta name='Classification' content='Authentication'>
    <meta name='author' content='Ashiq Dey, ashiqdey@xbytelab.com'>
    <meta name='designer' content='Ashiq Dey'>
    <meta name='copyright' content='X Byte Lab'>
    <meta name='owner' content='X Byte Lab'>
    <meta name='url' content='https://xbytelab.com/kripz'>
    <meta name='directory' content='submission'>
    <meta name='rating' content='General'>
    <meta name='revisit-after' content='30 days'>
    <meta http-equiv='Expires' content='0'>
    <meta name='og:title' content='Kripz - A lil virtual chatbot'/>
    <meta name='og:type' content='Chatbot'/>
    <meta name='og:url' content='https://xbytelab.com/kripz'/>
    <meta name='og:image' content='https://xbytelab.com/logo.png'/>
    <meta name='og:site_name' content='Account - X Byte lab'/>
    <meta name='og:description' content='...'/>
    <meta name='og:country-name' content='India'/>
    <meta http-equiv='X-UA-Compatible' content='chrome=1'>
    <link rel='shortcut icon' type='image/ico' href='https://xbytelab.com/logo.png' />
    <link rel='shortcut icon' href='https://xbytelab.com/logo.png'>
    <link rel="stylesheet" type="text/css" href="data/css_js/chatbot.css">
    <link href='https://fonts.googleapis.com/css?family=Roboto:100,400' rel='stylesheet'>
    <title>Kripz - A lil virtual chatbot</title>
</head>
<body>

<style type="text/css">
.bg0{background:#003}
.bg1{
	background-image:url('data/img/bg_2.jpg');
	background-size:cover;
	opacity:0.6;
}
.bg2{opacity:0.8}
.bg3{background-image:url('data/img/text_2.png');
background-size:500px;
background-position:top;
background-repeat:no-repeat;
width:500px;
height:300px;
top:20%;left:10%;
}

.iframe_200{overflow:hidden;}


#pmenu{position:absolute;top:0;left:0;z-index:7100;overflow:hidden;}
#pmenu .mask{height:100%;width:100%;position:absolute;}
.in_pmenu .mask{display:none;}
.ac_pmenu .mask{display:block;}

@media all and (max-width:600px){
#pmenu{position:fixed;}
#pmenu .mask{position:fixed;}
}


.ibox{padding:10px 40px 10px 20px;}
#form_chat{position:relative;}
.voice{
	height:40px;width:35px;
	padding:5px;
	transform:translateY(2px);
	background-size:20px;position:absolute;
	background-repeat:no-repeat;
	background-position:center;
	right:80px;bottom:20px;

	background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100' %3E	%3Cpath fill='%23828181' d='M64.3,50.7c0-9.9,0-19.8,0-29.7c0-17.8-27.6-17.8-27.6,0c0,9.9,0,19.8,0,29.7	C36.7,68.5,64.3,68.5,64.3,50.7z'/%3E	%3Cpath fill='%23828181' d='M84.3,51H76c-1.7,13-12.6,22.7-25.8,22.7S26,64,24.3,51H16c1.6,16,14.1,28.7,29.9,30.7l-0.1,14.8l8.5,0.1	l0.1-14.9C70.2,79.7,82.8,67,84.3,51z'/%3E%3C/svg%3E");
}

.ribbon{width:100%;}
.ribbon .dp{width:35px;margin:8px;}
.ribbon .name{width:calc(100% - 85px);padding:8px 0;}
.ribbon .dot{width:50px}

</style>



<div class="bg0 w100 pa h100"></div>
<div class="bg1 w100 pa h100"></div>
<div class="bg2 themeg w100 pa h100"></div>
<div class="bg3 pa"></div>



<div class='chat pa br8 bslg white ofh'>
  <div class='ribbon themeg'>
    <div class='flex w100'>
       <div class='dp br50'></div>
      <div class='d name f1 cwhite pl10'>
        Kripz
        <div class='f08 status pr pl13'><span class='o6'>Online</span></div>
      </div>
    </div>
  </div>
  <!--ribbon ends-->

  <div class='message' ><div id='show_mgs'></div></div>


  <div class='input pa w100'>
    <form id='form_chat'>
      <div class='dtab w100 p10'>
        <div class='dcel vam ibox_hold p5'>
          <input type='text' name='m' id='m' class='ibox w100 no_b br30 f12 bslg clblack' placeholder="Hey let's chat" autocomplete='off'>
        </div>
        <div class='dcel vam ibut_hold p5'>
          <div class='themeg br50 ofh'>
            <input type='submit' value ='.' class='ibut no_b cp br50' id='send'>
          </div>
        </div>
      </div>
      
      
    </form>
  </div>
  <!--input field ends-->



  </div>


<script type='text/javascript' src='../../cdn/js/jquery.min.js'></script>
<script type='text/javascript' src='data/css_js/xbl_001.js'></script>
<script type='text/javascript' src='data/css_js/xbl_svg_001.js'></script>";

<script type="text/javascript">
var form = $("#form_chat");
var m = $("#m");
var show = $("#show_mgs");

var query ='';
//list of all messeges sent by user whose reply is yet to be fetched
var queries = [];
//to tracking if any process is going on
var ongoing=false;

//to display messages
var id='';
var type='';
var user='';
var mgs='';

//unique id for user's id
var unique = 100;

//class ui
var old_user='';
var curclass='';

var test=true;
var test=false;
</script>

</body>
</html>