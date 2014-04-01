<div id="basic-modal-content">
<center><h3>Список Банов</h3></center>
<link rel="stylesheet" media="screen" type="text/css" href="css/ban.css">
<script type="text/javascript">
    $(document).ready(function(){
     $('.shadowed').each(function(){
         $(this).textDropShadow('shadow')
       });
    });
    (function($) {
      $.fn.textDropShadow = function(ShdwClass){
         $(this).css('position','relative').html('<span class='+ShdwClass+'>'+$(this).html()+'</span><span style="position:relative;">'+$(this).html()+'</span>');
            return $(this);
       }
     })(jQuery);

</script>
<h2 class="shadowed header"></h2>
<center><p style="color:gray">(разбаненные игроки здесь не отображаются)</p></center>
<?php
$server = "localhost";
$dbuser = "root";
$dbpass = "olokari";
$dbname = "ultraban";
mysql_connect($server, $dbuser, $dbpass);
mysql_select_db($dbname);
$result = mysql_query("SELECT * FROM banlist ORDER BY id DESC");
echo '<table width=70% border=1 cellpadding=2 align=center>';

echo '<thead><tr>
<!--<td>Тип</td>-->
<td>Ник</td>
<td>Причина</td>
<td>Администратор</td>
<td>Время бана</td>
<td>Время разбана</td>
</tr></thead>
';

while($row = mysql_fetch_assoc($result)){

switch ($row['type'])
	{
	case 0:
		$type = "Temp. Banned";
		break;
	case 1:
		$type = "IP Banned";
		break;
	case 2:
		$type = 0; //"Warn";
		break;
	case 3:
		$type = 0; //"Kick";
		break;
	case 4:
		$type = 0; //"Fine";
		break;
	case 5:
		$type = 0; //"Unbanned";
		break;
	case 6:
		$type = 0; //"Jailed";
		break;
	case 9:
		$type = "Permanent Ban";
		break;
	default:
		$type = 0; //"Unknown";
	}
	if($type && (((time()-$row['temptime'])<0) || ($row['temptime']==0)))
	{
echo "<tr>\n";
echo iconv('Windows-1251','UTF-8',"<td>".$row['name']."</td>\n");
echo iconv('Windows-1251','UTF-8',"<td>".$row['reason']."</td>\n");
echo "<td>".$row['admin']."</td>\n";
//Convert Epoch Time to Standard format
$datetime = date("H:s:i d.m.Y", $row['time']);  
echo "<td>$datetime</td>\n";
$dateconvert = date("H:s:i d.m.Y", $row['temptime']);  
if($row['temptime'] == "0"){
echo "<td>Никогда</td>";
}else{
echo "<td>$dateconvert</td>\n";
}
//echo "<td>".$row['id']."</td>";

echo "</tr>\n\n\n";
}}
echo"</table>\n";
?>
</div>
<div style='display:none'>
	<img src='images/x.png' alt='' />

</div>	
</div>
<script type='text/javascript' src='js/ban.js'></script>
<script type='text/javascript' src='js/banb.js'></script>