
<!DOCTYPE html> 
<html lang="en"> 
<head> 
    <meta charset="utf-8"/> 
    <title>Url Editor</title>
    <link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
    <script>
    $(function() {
        $("#select_all").click(function() {
            $("[name=url_checkbox]:checkbox").each(function() {
                $(this).attr("checked", true);
            })
        });
        
        $("#deselect_all").click(function() {
            $("[name=url_checkbox]:checkbox").each(function() {
                $(this).attr("checked", false);
            })
        });
        
        $("#switch")
	    .button()
	    .click(function() {
	        var url="url_editor.php?group_id=" + $("#url_group").val();
	        window.location.href=url;
        });
        
        $("#dialog-form").dialog({
		autoOpen: false,
		height: 180,
		width: 280,
		modal: true,
		buttons: {
			"OK": function() {
			    if($("#new_group_name").val() == "")
			        return;
			    $.post("url_editor.php",{new_group_name:$("#new_group_name").val()},function( data ) {
                                location.reload(true);
                            });
			},
			Cancel: function() {
				$( this ).dialog( "close" );
			}
		},
		close: function() {
		}
	});
        
        $("#new_group")
	    .button()
	    .click(function() {
		$( "#dialog-form" ).dialog( "open" );
	    });
	    
	function trim(str) {
            return str.replace(/(^\s*)|(\s*$)/g, "");
        }
	    
	$("#add")
	    .button()
	    .click(function() {
		var urls = $("#new_urls").val();
		urls = trim(urls);
		if (urls != "") {
    	            $.post("url_editor.php",{add_urls:urls},function( data ) {
                        location.reload(true);
                    });
                }
	    });
	    
        $("#remove")
	    .button()
	    .click(function() {
		var id_list = "";
                $("[name=url_checkbox]:checkbox").each(function() {
                    if ($(this).attr("checked")){
                        id_list += $(this).val();
                        id_list += "\n";
                    }
                });
                $.post("url_editor.php",{remove_url_ids:id_list},function( data ) {
                    location.reload(true);
                });
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

<div>
<select id="url_group" name="url_group">

<?php
include_once( 'saemysql.class.php' );

$group_id = NULL;
if(!empty($_GET) && isset($_GET['group_id']))
{
    $group_id = $_GET['group_id'];
}

echo "$group_id = " . $group_id;

$mysql = new SaeMysql();
$sql = "SELECT * FROM `url_group`";
$groups= $mysql->getData( $sql );

for ($i = 0; $i < sizeof($groups); $i++)
{
    if ($group_id === $groups[$i]['group_id'])
    {
        echo '<option value="' . $groups[$i]['group_id'] . '" selected="selected">' . $groups[$i]['group_name'] . '</option>';
    }
    else
    {
        echo '<option value="' . $groups[$i]['group_id'] . '">' . $groups[$i]['group_name'] . '</option>';
    }
}

$mysql->closeDb();
?>

</select> 
<button id="switch" name="switch">Switch</button >
<button id="new_group" name="new_group">New Group</button >
</div>

<div>
<?php
function AddUrl(&$db, $url, $group_id)
{
    $sql_query = "SELECT url FROM `url` WHERE `url`='" . $url . "' AND `group_id`=" . $group_id;
    if (!$db->getVar( $sql_query ))
    {
        $sql_add = "INSERT INTO `url` (`url`, `group_id`) VALUES ('" . $url . "'," . $group_id . ")";
        return (false != $db->runSql($sql_add ));
    }
    
    return false;
}

function RemoveUrl(&$db, $url, $group_id)
{
    $sql = "DELETE FROM `url` WHERE `url`='" . $url . "' AND `group_id`=" . $group_id;
    return (false != $db->runSql( $sql ));
}

function RemoveUrlById(&$db, $id, $group_id)
{
    $sql = "DELETE FROM `url` WHERE `id`='" . $id . "' AND `group_id`=" . $group_id;
    return (false != $db->runSql( $sql ));
}

function CreateNewGroup(&$db, $group_name)
{
    $sql = "INSERT INTO `url_group` (`group_name`) VALUES('" . $group_name. "')";
    return (false != $db->runSql( $sql ));
}

$mysql = new SaeMysql();

if(!empty($_POST))
{
    if ($group_id && isset($_POST['add_urls']))
    {
        $url_array = explode("\n", $_POST['add_urls']);
        for ($i = 0; $i < sizeof($url_array); $i++)
        {
            AddUrl($mysql, $url_array[$i], $group_id);
        }
    }
    elseif ($group_id && isset($_POST['remove_urls']))
    {
        $url_array = explode("\n", $_POST['remove_urls']);
        for ($i = 0; $i < sizeof($url_array); $i++)
        {
            RemoveUrl($mysql, $url_array[$i], $group_id);
        }
    }
    elseif ($group_id && isset($_POST['remove_url_ids']))
    {
        $url_id_array = explode("\n", $_POST['remove_url_ids']);
        for ($i = 0; $i < sizeof($url_id_array); $i++)
        {
            RemoveUrlById($mysql, $url_id_array[$i], $group_id);
        }
    }
    
    if (isset($_POST['new_group_name']))
    {
        CreateNewGroup($mysql, $_POST['new_group_name']);
    }

    
}

if ($group_id)
{
    $sql = "SELECT * FROM `url` WHERE `group_id`=" . $group_id;
    $data = $mysql->getData( $sql );
}
$mysql->closeDb();

?>  

<label for="new_url">New Urls(One url for each line.): </label> </br>
<textarea  cols ="80" rows = "10" id="new_urls" name="new_urls"></textarea> </br>
<button id="add" name="add">ADD</button> </br></br></br></br>
</div>

<div>
<form action="url_editor.php">
<?php
for($i = 0; $i < sizeof($data); ++$i)
{
    echo '<input type=checkbox name="url_checkbox" value="' . $data[$i]["id"] . '">'. ($i+1) . '. '  . $data[$i]["url"] . '</br>';
}
?>
</form>
</br>
<a href="javascript:;" id="select_all">Select All</a> | <a href="javascript:;" id="deselect_all">Deselect All</a>
<button id="remove" name="remove">REMOVE</button>
</div>


<div id="dialog-form" title="Create new group">
    <p class="Tips">Please input a new group name.</p>

    <form>
    <fieldset>
	<label for="new_group_name">Name</label>
	<input type="text" name="new_group_name" id="new_group_name" class="text ui-widget-content ui-corner-all" />
    </fieldset>
    </form>
</div>

</body> 
</html> 