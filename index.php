<?php
/**
 * @author Steinsplitter
 * @copyright 2015 tool authors
 * @license http://unlicense.org/ Unlicense
 */
?>
<!DOCTYPE html PUBLIC "//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
        <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
        <title>Unpatrolled IP edits (last ~30 days)</title>
        <script src="//tools-static.wmflabs.org/tooltranslate/tt.js"></script>
        <script src="//tools-static.wmflabs.org/cdnjs/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
        <link href="bootstrap.css" rel="stylesheet">
    <style>
      body {
        padding-top: 63px;
      }
    </style>
</head>
<body>
<!-- Source code is located here: https://github.com/Steinsplitter/up -->
    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">

          <a class="brand" href="#"><span tt="upe">Unpatrolled IP edits</span></a>
          <div class="nav-collapse collapse">
                <ul id="toolbar-right" class="nav pull-right">
            <div class="navbar-form pull-right">
               <span class = "i18ntools"><span id='tooltranslate_wrapper'></span></span>
            </div>            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>

  <div class="container">
<?php
// i18n
require_once ( "/data/project/tooltranslate/public_html/tt.php") ;
$tt = new ToolTranslation ( array ( 'tool' => 'upie' , 'language' => 'en' , 'fallback' => 'en' , 'highlight_missing' => false ) ) ;
print $tt->getJS('#tooltranslate_wrapper') ;
print $tt->getJS() ;

// Logging access
$hi = ( "acount.txt" );
$hii = file( $hi );
$hii[0] ++;
$fp = fopen( $hi , "w" );
fputs( $fp , "$hii[0]" );
fclose( $fp );

// Init
function dput ()
{
    echo "<div class=\"well form-submit\">\n";
    echo "<form action=\"up.php\">\n";
    echo "<input value=\"\" name=\"wiki\" id=\"wiki\" class=\"input-medium search-query\" placeholder=\"wiki\" type=\"text\"><br>\n";
    echo "<input type = \"checkbox\" name=\"users\" id=\"users\" class=\"fs\"> <span tt=\"su\">Show users</span><br>\n";
    echo "<input type = \"checkbox\" name=\"limit\" id=\"limit\" class=\"fr\"> <span tt=\"sum\">Show moore than 400 results</span><br>\n";
    echo "<button type=\"submit\" class=\"btn\"> <span tt=\"go\">GO!</button>\n";
    echo "</form></div>";
}
function error ()
{
if(isset($_GET['wiki'])) {

    echo "<div class=\"alert alert-error\"><span tt=\"noxwiki\">Selected wiki does not exist.</span></div><br>\n";
} else {
    echo "<div class=\"alert alert-notice\"><big><span tt=\"nowiki\">Please enter a wiki! For example: commonswiki</span></big></div>";
}
    echo "\n</body>\n</div>\n</div>\n</html>\n";
die();
}

if(isset($_GET['limit'])) {
$lh = "";
} else {
$lh = "limit 400";
}

// About
if ( isset( $_GET['about'] ) ) {
echo "<pre>";
echo "<b>Klicks:</b> ";
include( "acount.txt" );
echo " since August 2015.<br>";
echo "<b>Version:</b> <span class=\"badge badge-success\">2.0</span><br>";
echo "</pre>";
dput();
error();
}

// Continue
if(isset($_GET['users'])) {
$h = "";
} else {
$h = "and rc_user = 0 and rc_type < 5";
}
$gv = $_GET['wiki'];
if(preg_match("/^[a-z]{1,100}$/", $gv) == 1) {
  $dbp = preg_replace("/[^a-z0-9.]+/i", "", $_GET['wiki']);
} else {
  echo '<!-- SafeEsc: False -->';
  dput();
  error();
}

$rdb = (''.$dbp.'_p');

$tools_pw = posix_getpwuid (posix_getuid ());
$tools_mycnf = parse_ini_file($tools_pw['dir'] . "/replica.my.cnf");
$db = new mysqli('s1.labsdb', $tools_mycnf['user'], $tools_mycnf['password'], $rdb);
$cno = '1049';
$cf = '0';
$cf2 = '1049';
if ($db->connect_errno !=  $cf && $db->connect_errno != $cf2){
        echo('Connect Error: ' . $db->connect_errno);
}

if ($db->connect_errno == $cno) {
  dput();
  error();
}

$meta = $db->query('select * from meta_p.wiki WHERE dbname = \''.$dbp.'\' limit 1;');

$r = $db->query('select rc_user_text, COUNT(rc_user_text) from recentchanges where rc_patrolled = 0 '.$h.' group by rc_user_text having COUNT(rc_user_text) order by COUNT(rc_user_text) desc '.$lh.';');

$row_cnt = $r->num_rows;

if ($row_cnt > 20000) {
  dput();
  echo "<span tt=\"bwt\"><b>Script stopped:</b> Huch! Moore than 20000 results.  Your browser will thank :-)!</span>";
  die();
}

unset($tools_mycnf, $tools_pw);
$wlink = $meta->fetch_assoc();
?>
<p><span tt="intro">Unpatrolled IP edits from the last ~31 days on</span> <b><?=$wlink['name']?></b> (<a href="<?=$wlink['url']?>"><?=$wlink['lang']?>.<?=$wlink['family']?></a>).</p>
</p>
<p><b><span tt="results">Results:</span></b> <?=$row_cnt?> <br><b><span tt="url">Wikiurl:</span></b> <?=$wlink['url']?>
<?=dput();?>
<div id="table1">
<table border="1" class="table table-hover">
<tr><td><b><span tt="ip">IP</span></b></td><td><b><span tt="count">Count</span></b></td></tr>
<?php while ($row = $r->fetch_row()): ?>
    <tr><td><a href="<?=$wlink['url']?>/wiki/Special:Contributions/<?= $row[0] ?>"><?= $row[0] ?></a></td><td><?= $row[1] ?></td></tr>
<?php endwhile; ?>

</table>

</div>
</div>
</body>
</html>
