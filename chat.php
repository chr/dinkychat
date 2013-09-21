<?php

// DinkyChat is free and unencumbered public domain software. For more
// information see http://unlicense.org/ or the accompanying UNLICENSE file.


// http://www.php.net/manual/it/function.array-search.php#94598
function getKeyPositionInArray($haystack, $keyNeedle)
{
    $i = 0;
    foreach($haystack as $key)
    {
        if($key['time'] == $keyNeedle)
        {
            return $i;
        }
        $i++;
    }
}

$now = time();

$chat_file = 'json.txt';

$contents = file_get_contents($chat_file);
$results = json_decode($contents, true);

$x = end($results);

if ( $_COOKIE['time'] == '' ) {
	setcookie('time', $x['time'], time()+2592000);
} else {
	$id_last = $_COOKIE['time'];
}

if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
	$str = $_POST['msg'];
	$name = $_POST['name'];

	$txt = array('time' => $now, 'name' => $name, 'msg' => $str);
	$results[] = $txt;
	file_put_contents($chat_file, json_encode($results), LOCK_EX);
} elseif ( $_GET['chat']==1 && $id_last < $x['time'] ) {
	// print last messages
	$i = getKeyPositionInArray($results, $id_last) + 1;
	echo json_encode(array_slice($results,$i));

	setcookie('time', $x['time'], time()+2592000);
} elseif ( isset($_GET['sticker']) ) {
	$str = $_GET['sticker'];
	$name = $_GET['name'];

	$txt = array('time' => $now, 'name' => $name, 'msg' => $str);
	$results[] = $txt;
	file_put_contents($chat_file, json_encode($results), LOCK_EX);
} else {

$html = "
<html>
<head>
	<link rel='stylesheet' href='http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css' />
	<script src='http://code.jquery.com/jquery-1.10.2.min.js'></script>
	<script src='http://code.jquery.com/ui/1.10.3/jquery-ui.min.js'></script>

<script>

// http://stackoverflow.com/questions/37684/how-to-replace-plain-urls-with-links
function replaceURLWithHTMLLinks(text) {
	if ( text.search(/^<img/) == 0 ) {
		return text;
	} else {
		var exp = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
		return text.replace(exp,\"<a href='$1'>$1</a>\");
	}
}

// http://www.quirksmode.org/js/cookies.html
function readCookie(name) {
	var nameEQ = name + '=';
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

var user=readCookie('user');

if ( user == null ) {
	var user=prompt('Nickname:');
	document.cookie='user=' + user;
} else {
	alert('Ciao ' + user);
}

var interv = 0;

function intervalTrigger() {
	    var title = document.title;
	    document.title = (title == user ? 'Hey' : user);
};

setInterval(function(){
	$.ajax({
		url: 'chat.php',
		dataType: 'json',
		data: { chat: 1 },
		cache: false,
		success: function(data){
			if ( data[0]['time'] ) {
				$.each(data, function(i, item) {
					var d = new Date(item.time * 1000);
					var date = d.toLocaleTimeString(); // or toLocaleString()
					$('#chatbox').append( '<p title=\'' + d + '\'><strong>' + item.name + '</strong>: <span style=\'font-size: 70%;\'>' + date + '</span><br />' + replaceURLWithHTMLLinks(item.msg) );
				});
// http://stackoverflow.com/questions/9830886/jquery-blinking-title
if ( interv == 0 ) {
	interv = setInterval(intervalTrigger, 1000);
}
			}
		}
	});
	// http://stackoverflow.com/questions/1966784/auto-scroll-to-bottom-of-page-with-jquery
	$(document).scrollTop($(document).height());
}, 1000);

$(function() {
	$('#chatref').submit(function(event) {

		var form = $(this);

		$('<input />').attr({
			type: 'hidden',
			id: 'foo',
			name: 'name',
			value: user
		}).appendTo(form);

		$.ajax({
			type: 'POST',
			url: 'chat.php',
			data: form.serialize()
		}).fail(function() {
			alert('A problem occurred.');
		});

		event.preventDefault(); // Prevent submission via browser.
		form.trigger('reset');
	});
	
	$('#chatlabel').text(user);
});

//$(document).ready(function() {
	$('*').mouseover(function() {
		window.clearInterval(interv);
		interv=0;
		document.title='chat';
	});
//});

	$('#msg').change(function() {
		window.clearInterval(interv);
		document.title='chat';
	});

$(function() {
	$('#stickers div a').click(function(event) {

		var sticker = $(this);

		$.ajax({
			url: 'chat.php',
			data: { name: user, sticker: sticker.html() }
		}).fail(function() {
			alert('FAIL');
		});
	      $('#stickers').toggle('drop');
		event.preventDefault(); // Prevent submission via browser.
	});
	
});

$(function() {
	$('#buttonEmoticon').click(function() {
		// $('#stickers').css({ 'position': 'relative', 'bottom': this.height() });
	      $('#stickers').toggle('drop');
		event.preventDefault();
	});

});

$(function() {
	$( '#stickers' ).tabs({
		event: 'mouseover'
	});
});

</script>

</head>

<body style='z-index: 100; width: 100%; height: 90%;'>
<div id='chatbox'></div>
<form id='chatref'>
<label id='chatlabel'></label>
<input type='text' name='msg' id='msg' style='width: 80%;'></input>
<button>!</button>
<a href='#' id='buttonEmoticon'>Emoticon</a>
</form>

<div id='stickers' style='display: none'>
  <ul>
    <li><a href='#stickers-1'>emoticons 1</a></li>
    <li><a href='#stickers-2'>emoticons 2</a></li>
  </ul>
<div id='stickers0'>
	<a href='#'><img src='http://www.iconsdb.com/icons/preview/soylent-red/emoticon-xl.png'></a>
	<a href='#'><img src='http://www.iconsdb.com/icons/preview/soylent-red/emoticon-57-xl.png'></a>
	<a href='#'><img src='http://www.iconsdb.com/icons/preview/soylent-red/emoticon-14-xl.png'></a>
	<a href='#'><img src='http://www.iconsdb.com/icons/preview/soylent-red/emoticon-54-xl.png'></a>
	<a href='#'><img src='http://www.iconsdb.com/icons/preview/soylent-red/cool-xl.png'></a>
	<a href='#'><img src='http://www.iconsdb.com/icons/preview/soylent-red/crying-xl.png'></a>
	<a href='#'><img src='http://www.iconsdb.com/icons/preview/soylent-red/emoticon-59-xl.png'></a>
	<a href='#'><img src='http://www.iconsdb.com/icons/preview/soylent-red/in-love-xl.png'></a>
	<a href='#'><img src='http://www.iconsdb.com/icons/preview/soylent-red/emoticon-12-xl.png'></a>
	<a href='#'><img src='http://www.iconsdb.com/icons/preview/soylent-red/guestion-xl.png'></a>
	<a href='#'><img src='http://www.iconsdb.com/icons/preview/soylent-red/emoticon-31-xl.png'></a>
	<a href='#'><img src='http://www.iconsdb.com/icons/preview/soylent-red/emoticon-16-xl.png'></a>
	<a href='#'><img src='http://www.iconsdb.com/icons/preview/soylent-red/emoticon-3-xl.png'></a>
	<a href='#'><img src='http://www.iconsdb.com/icons/preview/soylent-red/emoticon-18-xl.png'></a>
	<a href='#'><img src='http://www.iconsdb.com/icons/preview/soylent-red/emoticon-27-xl.png'></a>
	<a href='#'><img src='http://www.iconsdb.com/icons/preview/soylent-red/emoticon-48-xl.png'></a>
	<a href='#'><img src='http://www.iconsdb.com/icons/preview/soylent-red/emoticon-4-xl.png'></a>
	<a href='#'><img src='http://www.iconsdb.com/icons/preview/soylent-red/lol-xl.png'></a>
	<a href='#'><img src='http://www.iconsdb.com/icons/preview/soylent-red/private-3-xl.png'></a>
<!--
	<a href='#'><img src=''></a>
-->
</div>
<div id='stickers-2'>
	<a href='#'><img src='http://www.iconsdb.com/icons/preview/soylent-red/sombrero-xl.png'></a>
	<a href='#'><img src='http://www.iconsdb.com/icons/preview/soylent-red/facebook-like-xl.png'></a>
	<a href='#'><img src='http://www.iconsdb.com/icons/preview/soylent-red/heart-69-xl.png'></a>
	<a href='#'><img src='http://www.iconsdb.com/icons/preview/soylent-red/pig-xl.png'></a>
	<a href='#'><img src='http://www.iconsdb.com/icons/preview/soylent-red/dog-3-xl.png'></a>
	<a href='#'><img src='http://www.iconsdb.com/icons/preview/soylent-red/chili-pepper-13-xl.png'></a>
<!--
	<a href='#'><img src=''></a>
-->
</div>
</div>
</body>
</html>
";
	print($html);
}
?>
