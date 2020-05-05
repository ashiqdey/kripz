function tog_class(id,in_class,ac_class){
  document.getElementById(id).classList.toggle(in_class);
  document.getElementById(id).classList.toggle(ac_class);
}

function change_class(id,new_class){
  document.getElementById(id).className=new_class;
}


var notif_timer = setTimeout(function (){change_class('notification','n_hide');},3000);
function notif(mgs){
	//console.log(mgs);
  change_class('notification','n_show');
  document.getElementById('notif_mgs').innerHTML=mgs;
  clearInterval(notif_timer);
  notif_timer = setTimeout(function (){change_class('notification','n_hide');},5000);
}


//copy start
function getSelectionText() {
	$('#targetArea').empty();
    var text = "";
    if (window.getSelection) {
        text = window.getSelection().toString();
    } else if (document.selection && document.selection.type != "Control") {
        text = document.selection.createRange().text;
    }
    return text;
}

//copy second
function copy() {
	var selected = getSelectionText();
	if(selected != ''){
		$('#copyArea').text(selected).select();
		document.execCommand('Copy');
		notif('Copied');
	}
}

function log(mgs){
  console.log(mgs);
}




$(document).ready(function(){

  $("#send").click(function(e){
    e.preventDefault();
    query = m.val();
    if(query.length>0){
      m.val("");
      id=unique;
      type=1;
      user="me";
      mgs=query;
      output();
      queries.push(query);
      get_reply();

      
    }
    m.focus();
    return false;
  });


  //submit name
  $("#sendname").click(function(e){
    e.preventDefault();
    query = m.val();
    if(query.length>1){
      $.ajax({
        type: "POST",
        url: "https://xbytelab.com/account/inc/name",
        url: "http://www/my_website/account/inc/name",
        data: {setname: query},
        cache: true,
        success: function(html){
          
          if(html!="ok"){
            $("#name_mgs").html(html);
          }
          else{
            if(html=="ok"){
              $("#name_mgs").html("Name updated");
            }
            
            location.reload();
          }
        }
    }); 
    }else{
      $("#name_mgs").html("Please let me know your correct name");
      setTimeout(function(){$("#name_mgs").css({"opacity":"0"})},2000);
      
    }
    return false;
  });


  $(".pmenu").click(function(){
    tog_class("pmenu","in_pmenu","ac_pmenu");
  });



  /*smooth scroll*/
  $("a").on('click', function(e) {
    if (this.hash !== "") {
        e.preventDefault();
        var hash = this.hash;
        $('html, body').animate({
          scrollTop: $(hash).offset().top
        }, 800, function(){
          window.location.hash = hash;
        });
      }
  });

  //load initial messages
  $.ajax({
      type: "POST",
      url: "inc/message",
      data: {initial: '50'},
      cache: true,
      success: function(html){
        if(test==true){
            $("#show_mgs").html(html);
          }else{
            display(html);
          }
      }
  }); 


});





function get_reply(){
  if(ongoing==false){
    ongoing=true;
    query = queries[0];
    $.ajax({
        type: "POST",
        url: "inc/message",
        data: {m: query},
        cache: true,
        success: function(html){

          if(test==true){
            $("#show_mgs").html(html);
          }else{
            display(html);
          }
          query = queries.shift();
          ongoing=false;
          if(queries.length>0){
            get_reply();
          }
        }
    }); 
  }
}



function display(data){
  var result = JSON.parse(data);


  $.each(result.row, function(index, value) {
    id=value.id;
    type=value.t;
    user=value.u;
    mgs=value.m;

    if(type==1){
      if(user==1){user="me";}
      else{user="opp";}
      output();
    }
    else{
      user="opp";
      output();
    }
    
    
  });

  if($(window).width()<600){
    $("html, body").animate({ scrollTop: $(document).height() }, 1000);
  }else{
    $(".message").animate({scrollTop: $('#show_mgs').height()}, 600);
  }

  
}






function output(){
  //show.append("<div class='"+user+"' id='mgs"+id+"'>"+mgs+"</div>");
  var lastid = $( "#show_mgs .hold").last().attr("id");
  var lastclass = $( "#"+lastid+" div").attr("class");


  //same user
  if(old_user==user){
    curclass = "last";

    if(lastclass=="single"){
      $( "#"+lastid+" div").removeClass(lastclass);
      $( "#"+lastid+" div").addClass("first");
    }
    else if(lastclass=="last"){
      $( "#"+lastid+" div").removeClass(lastclass);
      $( "#"+lastid+" div").addClass("mid");
    }

  }else{
    show.append("<div class='seperate'></div>");
    curclass="single";
  }

  if(user=="me"){
    show.append("<div class='hold "+user+"' id='mgs_"+unique+"'><div class='"+curclass+"'><div class='text'>"+mgs+"</div></div></div>");
  }
  else if(user=="opp"){
    if(type==2){
      show.append("<div class='hold "+user+" bg_trans' id='mgs_"+unique+"'><div class='"+curclass+"'><div class='text'>"+mgs+"</div></div></div>");
    }
    //date month etc
    else if(type==3){
      show.append("<div class='hold "+user+" full' id='mgs_"+unique+"'><div class='"+curclass+"'><div class='themeg'>"+mgs+"</div></div></div>");
    }
    //joke
    else if(type==4){
      show.append("<div class='hold "+user+" full' id='mgs_"+unique+"'><div class='"+curclass+"'><div class='violetg'>"+mgs+"</div></div></div>");
    }
    //google
    else if(type==5){
      show.append("<div class='hold "+user+" bg_trans full' id='mgs_"+unique+"'><div class='"+curclass+"'><div class='text'>"+mgs+"</div></div></div>");
    }else{
      show.append("<div class='hold "+user+"' id='mgs_"+unique+"'><div class='"+curclass+"'><div class='text'>"+mgs+"</div></div></div>");
    }
    
  }


  old_user = user;
  unique++;
}
