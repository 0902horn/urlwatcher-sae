<!DOCTYPE html> 
<html lang="en"> 
<head> 
	<meta charset="utf-8"> 
	<title>Show Diff</title>

    <link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
    <script> 
	$(function(){
	    var from_dates = $( "#from" ).datepicker({
		defaultDate: "-2m",
		changeMonth: false,
		numberOfMonths: 3,
                minDate: "-90d",
                maxDate: "-0d",
                dateFormat: "yymmdd"
            });
        
            var to_date = $( "#to" ).datepicker({
		defaultDate: "-2m",
		changeMonth: false,
		numberOfMonths: 3,
                maxDate: "-0d",
                dateFormat: "yymmdd"
	    });
	    
	    $("#go")
	        .button()
	        .click(function() {
		    if ($("#from").val() < $("#to").val())
                    {
                        $( "#errortext" ).html("success.")
                        var url="index.php?from_date=" + $("#from").val() + "&to_date=" + $("#to").val() + "&group_id=" + $("#url_group").val();
                        window.location.href=url;
                    }
	            else
	            {
	                $( "#errortext" ).html("error: the \"from date\" is beyond the \"to date\".")
	                return;
	            }
	    });
	});
    </script>
</head> 

<body style="font-size:62.5%;">

<div class="navigation"> 
<p>
NAVIGATION: 
<a href="index.php">Show Diff</a> | <a href="url_editor.php">Edit URLs</a>
</p>
</div>

<div class="description"> 
<p>Select the date range to diff. <br>History data is kept for 90 days, in which period you can check changes on pre-set urls.</p> 
</div>

<div class="daterange"> 
 
<label for="from">From</label> 
<input type="text" id="from" name="from"/> 
<label for="to">to</label> 
<input type="text" id="to" name="to"/>
<select id="url_group" name="url_group">

<?php
include_once( 'saemysql.class.php' );

$mysql = new SaeMysql();
$sql = "SELECT * FROM `url_group`";
$groups= $mysql->getData( $sql );

for ($i = 0; $i < sizeof($groups); $i++)
{
    echo '<option value="' . $groups[$i]['group_id'] . '">' . $groups[$i]['group_name'] . '</option>';
}

$mysql->closeDb();
?>

</select> 
<button id="go" name="go">GO</button >

</div>

<div class="error" id="errortext">

</div>

<div class="diff" id="difftext">

<?php
include( 'diff.php' );

if(!empty($_GET)
    && isset($_GET['from_date'])
    && isset($_GET['to_date'])
    && isset($_GET['group_id'])
    )
{
    $from_date = htmlspecialchars($_GET['from_date']);
    $to_date = htmlspecialchars($_GET['to_date']);
    $group_id = htmlspecialchars($_GET['group_id']);
    
    echo '</br></br>Date Range: ' . $from_date . ' - ' . $to_date . '</br></br>';
    
    if (NULL != $from_date && NULL != $to_date && (intval($from_date) < intval($to_date)))
    {
        //$summary = array();
        $results = ShowDiff($group_id, $from_date, $to_date, $summary);
        $results = str_replace(' ', '&nbsp;', $results);
        
        echo "***************************************************************************</br>";
        for ($i = 0; $i < sizeof($summary ); $i++)
        {
            echo $summary [$i] . '</br>';
        }
        echo "***************************************************************************</br></br>";
            
        for ($i = 0; $i < sizeof($results); $i++)
        {
            if ('-' == $results[$i][0])
            {
                $results[$i] = '<font color=#FF0000>' . $results[$i] . '</font>';
            }
            elseif ('+' == $results[$i][0])
            {
                $results[$i] = '<font color=#009900>' . $results[$i] . '</font>';
            }
            elseif ('@' == $results[$i][0])
            {
                $results[$i] = '<font color=#FFCC33>' . $results[$i] . '</font>';
            }
            
            echo $results[$i] . '</br>';
        }        
    }
    else
    {
        echo "error: invalid request.";
    }
}

?>

</div>
 
</body> 
</html> 
