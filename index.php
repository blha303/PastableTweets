<html>
<head>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script type="text/javascript">
function getTweet(url) {
 $('#outputbox').val("Loading...");
 var id = url.split("/").pop();
 $.get('gettweet.php?id=' + id, function(data) { $('#outputbox').val(data); });
}
</script>
<title>Pastable Tweets</title>
</head>
<body>
<h3>Pastable Tweets!</h3>
<p>Paste a direct link to a tweet in the box, hit Enter, then copy the text from the text area below it.<br>
We accept links in the form https://twitter.com/username/status/longlineofnumbers or even just the number at the end.<br>
If you have any questions about this free service, please tweet at <a href="https://twitter.com/blha303">@blha303</a> or email <a href="mailto:pastabletweets@blha303.com.au">pastabletweets@blha303.com.au</a></p>
<form method="GET" onSubmit="getTweet(document.getElementById('urlfield').value); return false;">
<input type="text" value="https://twitter.com/blha303/status/371281788770844673" name="url" id="urlfield" size=67><br>
<input type="submit">
</form>
<textarea id="outputbox" style="font-style: normal;" wrap="soft" cols="80" rows="6"></textarea>
</body>
</html>