	pagerange = 4;
$(document).ready(function(){
	$.get('ei.php', function (data) {
		$("#episode-index").html(utf8to16(decode64(data)));
	});
	$('#search-submit').click(Search);
	
	_initSearchData();
	_initUserData();
	$('#Login').submit(function () {return Login();})
});

function _initSearchData()
{
	if(location.hash.length > 1)
		initdata = location.hash.slice(1);
	else
		initdata = '1';
	initdata = initdata.split('|');
	if(initdata.length == 1)
	{
		searchword = '';
		initpage = initdata[0];
	}
	else
	{
		searchword = decodeURIComponent(initdata[0]);
		$('#search-input').val(searchword);
		initpage = initdata[1];
	}
	cpage = 0;
	Pagi(searchword, Number(initpage));
}

function Pagi(word, page)
{
	var url;
	var eword = encodeURIComponent(word);
	var eeword = encode64(eword);
	if(word.length == 0)
		url = 'ajax.php?page=' + page;
	else
		url = 'ajax.php?page=' + page + '&word=' + eeword;
	$('.mi_progress').show();
	$.get(url, null, function(data) {
		$("#main-index").html(utf8to16(decode64(data)));
		var pcount = $('tbody[count]').attr('count');
		$('.main-pagination li[id]').remove();
		if(pcount > 0)
		{
			var arr = [];
			var i = 1;
			if(eword.length > 0)
				eword += '|';
			if(page != i)
				arr.push('<li id="' + i + '"><a href="#' + eword + i + '" onclick="return PrePage(' + i + ')">' + i + '</a></li>');
			var st = Math.max(page - pagerange, 2);
			if(st > i + 1)
				arr.push('<li id="sp">...</li>');
			for(i = st; i < page; i++)
				arr.push('<li id="' + i + '"><a href="#' + eword + i + '" onclick="return PrePage(' + i + ')">' + i + '</a></li>');
			i = page;
			arr.push('<li id="' + i + '"><a href="#' + eword + i + '" onclick="return PrePage(' + i + ')"><strong>' + i + '</strong></a></li>');
			i++;
			var ed = Math.min(page + pagerange, pcount);
			for(; i < ed; i++)
				arr.push('<li id="' + i + '"><a href="#' + eword + i + '" onclick="return PrePage(' + i + ')">' + i + '</a></li>');
			if(pcount > ed)
				arr.push('<li id="sp">...</li>');
			i = pcount;
			if(page != i)
				arr.push('<li id="' + i + '"><a href="#' + eword + i + '" onclick="return PrePage(' + i + ')">' + i + '</a></li>');
			$('.main-pagination').append(arr.join(''));
		}
		cpage = page;
		if(word.length > 0)
		{
			pageTracker._trackPageview("/Search:" + eeword);
		}
		else
		{
			pageTracker._trackPageview("/Pagi");
		}
		$('.magnet-link').click(function (){pageTracker._trackPageview('/Magnet');});
		$('.torrent-link').click(function (){pageTracker._trackPageview('/Torrent');});
		
		$('#feedurl').attr('href', 'feed.php' + (word ? ('?word=' + eeword) : ''));
		
		$('.mi_progress').hide();
	});
	return false;
}

function PrePage(i)
{
	if(typeof i == 'undefined')
		i = 1;
	if(searchword.length > 0)
		location.hash = '#' + encodeURIComponent(searchword) + '|' + i;
	else
		location.hash = '#' + i;
	return Pagi(searchword, i);
}

function Search()
{
	searchword = $('#search-input').val();
	PrePage();
}

function PreSearch(word)
{
	searchword = word;
	$('#search-input').val(word);
	return PrePage();
}

function _initUserData()
{
	var cusername = $.cookie('username');
	if(cusername)
		$('#UserGuestPanel').hide();
	else
		$('#UserMemberPanel').hide();
	var username = cusername || '游客';
	$('#Username').html(username);
	$('#UserAction').click(function (){$('#UserPanel').toggle();return false;});
}

function Login()
{
	var formdata = $('form#Login').serializeArray();
	console.debug(formdata);
	var postdata = encode64(JSON.stringify(formdata));
	$('.box-error').hide();
	$('#LoginLoading').show();
	$.post('user.php', {post: postdata}, function(data) {
		$('#LoginLoading').hide();
		if(data == 'SUCC')
			window.refresh();
		else if(data == 'MAIL')
			$('#MailValidate').fadeIn(500);
		else if(data == 'FAIL')
			$('#FailMessage').fadeIn(500).delay(3000).fadeOut(2000);
		else if(data == 'REGSUCC')
			$('#RegSucc').fadeIn(500);
		else
			alert(data);
	});
	return false;
}

