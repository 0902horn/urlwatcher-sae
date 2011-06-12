<!DOCTYPE html> 
<html lang="en"> 
<head><meta charset="utf-8"> </head>
<body>

<?php

function get_web_page( $url )
{
	$options = array(
		CURLOPT_RETURNTRANSFER => true,     // return web page
		CURLOPT_HEADER         => false,    // don't return headers
		CURLOPT_FOLLOWLOCATION => true,     // follow redirects
		CURLOPT_ENCODING       => "",       // handle compressed
		CURLOPT_USERAGENT      => "spider", // who am i
		CURLOPT_AUTOREFERER    => true,     // set referer on redirect
		CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
		CURLOPT_TIMEOUT        => 120,      // timeout on response
		CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
	);

	$ch      = curl_init( $url );
	curl_setopt_array( $ch, $options );
	$content = curl_exec( $ch );
	$err     = curl_errno( $ch );
	$errmsg  = curl_error( $ch );
	$header  = curl_getinfo( $ch );
	curl_close( $ch );

	$header['errno']   = $err;
	$header['errmsg']  = $errmsg;
	$header['content'] = $content;
    
	return $header;
}

function strip_html_tags( $text )
{
	// PHP's strip_tags() function will remove tags, but it
	// doesn't remove scripts, styles, and other unwanted
	// invisible text between tags.  Also, as a prelude to
	// tokenizing the text, we need to insure that when
	// block-level tags (such as <p> or <div>) are removed,
	// neighboring words aren't joined.
	$text = preg_replace(
		array(
			// Remove invisible content
			'@<head[^>]*?>.*?</head>@siu',
			'@<style[^>]*?>.*?</style>@siu',
			'@<script[^>]*?.*?</script>@siu',
			'@<object[^>]*?.*?</object>@siu',
			'@<embed[^>]*?.*?</embed>@siu',
			'@<applet[^>]*?.*?</applet>@siu',
			'@<noframes[^>]*?.*?</noframes>@siu',
			'@<noscript[^>]*?.*?</noscript>@siu',
			'@<noembed[^>]*?.*?</noembed>@siu',

			// Add line breaks before & after blocks
			'@<((br)|(hr))@iu',
			'@</?((address)|(blockquote)|(center)|(del))@iu',
			'@</?((div)|(h[1-9])|(ins)|(isindex)|(p))@iu',
			'@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
			'@</?((table)|(th)|(td)|(tr)|(caption))@iu',
			'@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
			'@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
			'@</?((frameset)|(frame)|(iframe))@iu',
			'@</?i[ >]@iu',
            
		),
		array(
			' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',
			"\n$0", "\n$0", "\n$0", "\n$0", "\n$0", "\n$0", "\n$0", "\n$0", ' ',
		),
		$text );
        
    $text = strip_tags( $text );
    
    // Replace continurous spaces with one space.
    // Remove the space at begining of a line.
    $text = preg_replace(
        array(
            '@\s*\n\s*@iu',
            '@[\s{^\n}][\s{^\n}]+@iu',
            '@^ +@iu',
            '@&quot;@iu',
            '@&qpos;@iu',
            '@&gt;@iu',
            '@&lt;@iu',
            '@&amp;@iu',
            '@&nbsp;@iu',
        ),
        array(
            '\n',
            ' ',
            '',
            '\"', '\'', '>', '<', '&', ' ',
        ),
        $text);

	// Remove all remaining tags and comments and return.
	return $text;
}
   
function RetrieveContent($url)
{
    $url = mb_convert_encoding($url, "gb2312", mb_detect_encoding($url));
    $header = get_web_page($url);
    if (NULL === $header['content'])
        return NULL;
        
    $raw_text = $header['content'];
    
    /* Get the file's character encoding from a <meta> tag */
    preg_match( '@<meta\s+http-equiv="?Content-Type"?\s+content="([\w/]+)(;\s*charset=([^\s"]+))?@i',
        $raw_text, $matches );
    $encoding = $matches[3];
    
    $utf8_text = $raw_text;
    if ($encoding !== "utf-8")
    {
        /* Convert to UTF-8 before doing anything else */
        $utf8_text = mb_convert_encoding( $raw_text, "utf-8", $encoding );
        //$utf8_text = iconv( $encoding, "utf-8", $raw_text );
    }
    
    return strip_html_tags($utf8_text);
}

function Watch($url)
{
    $datetime = new DateTime();
    $date = $datetime->format('Ymd');
    
    // fetch urls
    $web_content = RetrieveContent($url);
    if (NULL === $web_content)
        return NULL;
    
    $mysql = new SaeMysql();
    // store web content(plain text)
    $sql = "INSERT INTO `web_content` ( `url` , `date` , `content` ) VALUES ( '"  . $mysql->escape($url) . "' , '" . $date . "' , '" . $web_content . "')";
    if (!$mysql->runSql( $sql ))
        echo "Error : " . $sql;
    else
        echo "Sql is executed successfully.";
        
    $mysql->closeDb();
    
    return $web_content;
}

if(!empty($_GET))
{
    if (isset($_GET["target"]))
    {
        $target = $_GET["target"];
        $content = Watch($target);

        echo $target;
        echo $content;
    }
}
?>

</body>
</html>