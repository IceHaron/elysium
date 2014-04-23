<?
/* Получение статуса сервера */
function status($ip, $port){
	if ($fp = @stream_socket_client("tcp://".$ip.":".$port,$e, $e1, 10)) {
		@stream_set_timeout($fp, 10);
		fwrite($fp,chr(0xFE));
		$shiza = fread($fp, 2048);
		$status = explode('§', substr($shiza,1));
		return $status;
		fclose ($fp);
	} else return null;
}

// Получаем статусы всех серверов
$ip='78.46.52.181';
$kernel = status($ip,25565);
$backtrack = status($ip,25566);
$gentoo = status($ip,25567);

REQUIRE_ONCE('php/classes/db.php');
$db = new db();

// Определяем нужный модуль
$module = preg_replace('/\/|\?.+$/', '', $_SERVER['REQUEST_URI']);
if ($module == '') $module = 'news';
if (glob("php/controllers/$module.php")) INCLUDE_ONCE("php/controllers/$module.php");

// Получаем список забаненных игроков
// $result = mysql_query("SELECT * FROM banlist ORDER BY id DESC");
// echo '<table width=70% border=1 cellpadding=2 align=center>';

// echo '<thead><tr>
// <!--<td>Тип</td>-->
// <td>Ник</td>
// <td>Причина</td>
// <td>Администратор</td>
// <td>Время бана</td>
// <td>Время разбана</td>
// </tr></thead>
// ';

// while($row = mysql_fetch_assoc($result)){

// switch ($row['type'])
// {
// case 0:
// $type = "Temp. Banned";
// break;
// case 1:
// $type = "IP Banned";
// break;
// case 2:
// $type = 0; //"Warn";
// break;
// case 3:
// $type = 0; //"Kick";
// break;
// case 4:
// $type = 0; //"Fine";
// break;
// case 5:
// $type = 0; //"Unbanned";
// break;
// case 6:
// $type = 0; //"Jailed";
// break;
// case 9:
// $type = "Permanent Ban";
// break;
// default:
// $type = 0; //"Unknown";
// }
// if($type && (((time()-$row['temptime'])<0) || ($row['temptime']==0)))
// {
// echo "<tr>\n";
// echo iconv('Windows-1251','UTF-8',"<td>".$row['name']."</td>\n");
// echo iconv('Windows-1251','UTF-8',"<td>".$row['reason']."</td>\n");
// echo "<td>".$row['admin']."</td>\n";
// //Convert Epoch Time to Standard format
// $datetime = date("H:s:i d.m.Y", $row['time']);  
// echo "<td>$datetime</td>\n";
// $dateconvert = date("H:s:i d.m.Y", $row['temptime']);  
// if($row['temptime'] == "0"){
// echo "<td>Никогда</td>";
// }else{
// echo "<td>$dateconvert</td>\n";
// }
// //echo "<td>".$row['id']."</td>";

// echo "</tr>\n\n\n";
// }}
// echo"</table>\n";

// Подключаем основной макет
REQUIRE_ONCE('/template/main.html');