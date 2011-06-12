<?php
include_once( 'saemysql.class.php' );

function array_attach(&$array1, &$array2)
{
    for ($i = 0; $i < sizeof($array2); $i++)
    {
        $array1[] = $array2[$i];
    }
}

/*Format output messages
    
Returns a snippet of a specific message type (i.e. 'changed') for
a specific URL and an optional (possibly multi-line) content.

The parameter "summary" (if specified) should be a list variable
that gets one item appended for the summary of the changes.

The return value is a list of strings (one item per line).
*/
function foutput($type, $url, $content, &$summary)
{
    $result = array();
    $summary_txt = strtoupper($type) . ': ' . $url;

    if (NULL == $content)
    {
        $summary[] = $summary_txt;
    }
    else
    {
        $summary[] = $summary_txt . ' (' . strlen($content) . ' bytes)';
    }

    $result[] = "***************************************************************************";
    $result[] = $summary_txt;
    if ($content)
    {
        $result[] = "***************************************************************************";
        $content_array = explode("\n", $content);
        array_attach($result, $content_array);
    }
    $result[] = "***************************************************************************";
    $result[] = '';
    $result[] = '';

    return $result;
}

require_once 'Text/Diff.php';
require_once 'Text/Diff/Renderer.php';
require_once 'Text/Diff/Renderer/inline.php';
require_once 'Text/Diff/Renderer/context.php';
require_once 'Text/Diff/Renderer/unified.php';

function GetFormattedDiff($text1, $text2)
{
    $lines1 = explode("\n", $text1);
    $lines2 = explode("\n", $text2);

    /* Create the Diff object. */
    $diff = new Text_Diff('native', array($lines1, $lines2));
    
    /* Output the diff in unified format. */
    $renderer = new Text_Diff_Renderer_unified();
    return $renderer->render($diff);
}

function ShowDiff($group_id, $from, $to, &$summary)
{
    $mysql = new SaeMysql();
    $sql = "SELECT url FROM `url` WHERE `group_id`=" . $group_id;
    $urls = $mysql->getData( $sql );
    $results = array();
    
    for($i = 0; $i < sizeof($urls); ++$i)
    {
        $title = "";
        $content_diff = "";
        
        $sql_from = "SELECT content FROM `web_content` WHERE `url`='" . $urls[$i]['url'] . "' AND `date`='" . $from . "'";
        $sql_to = "SELECT content FROM `web_content` WHERE `url`='" . $urls[$i]['url'] . "' AND `date`='" . $to . "'";

        $content_from = $mysql->getVar( $sql_from );
        $content_to = $mysql->getVar( $sql_to );
       
        if (!$content_from || !$content_to)
        {
            array_attach($results, foutput('failed', $urls[$i]['url'], NULL, $summary));
        }
        else
        {
            if ($content_from !== $content_to)
            {
                $content_diff = GetFormattedDiff($content_from, $content_to);
                
                if (is_string($content_diff))
                {
                    array_attach($results, foutput('changed', $urls[$i]['url'], $content_diff, $summary));
                }
                else
                {
                    array_attach($results, foutput('error', $urls[$i]['url'], NULL, $summary));
                }
            }
            else
            {
                array_attach($results, foutput('identical', $urls[$i]['url'], NULL, $summary));
            }
        }
    }
    
    $mysql->closeDb();
    
    return $results;
}
?>
