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

$str = $_POST['msg'];
$name = $_POST['name'];

if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
	$txt = array('time' => $now, 'name' => $name, 'msg' => $str);
	$results[] = $txt;
	file_put_contents($chat_file, json_encode($results), LOCK_EX);
} elseif ( $_GET['chat']==1 && $id_last < $x['time'] ) {
	// print last messages
	$i = getKeyPositionInArray($results, $id_last) + 1;
	echo json_encode(array_slice($results,$i));

	setcookie('time', $x['time'], time()+2592000);
} else {
$html = "
<html>
<head>
	<script
		src='http://code.jquery.com/jquery-1.10.2.min.js'>
	</script>
<script>

// http://stackoverflow.com/questions/37684/how-to-replace-plain-urls-with-links
function replaceURLWithHTMLLinks(text) {
	var exp = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
	return text.replace(exp,\"<a href='$1'>$1</a>\"); 
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
					$('#chatbox').append( '<strong>' + item.name + '</strong>: ' );
					$('#chatbox').append( replaceURLWithHTMLLinks(item.msg) + '<br />' );
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

$

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

</script>

</head>
<body style='z-index: 100; width: 100%; height: 90%;'>
<div id='chatbox'></div>
<form id='chatref'>
<label id='chatlabel'></label>
<input type='text' name='msg' id='msg' style='width: 80%;'></input>
<button>!</button>
</form>
</body>
</html>
";
	print($html);
}

?>
