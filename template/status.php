        <div id="nav"> 
		<ul>
		<li>
<?
function status($ip, $port){
   if ($fp = @stream_socket_client("tcp://".$ip.":".$port,$e, $e1, 10)) {
      @stream_set_timeout($fp, 10);
      fwrite($fp,chr(0xFE));
    $shiza = fread($fp, 2048);
      $status = explode('�', substr($shiza,1));
    return $status;
      fclose ($fp);
   } else return null;
   }

$ip='78.46.52.181';
$kernel = status($ip,25565);
$backtrack = status($ip,25566);
$gentoo = status($ip,25567);
echo '<b>Kernel[Classic]</b>';    
     if($kernel[2]) {
     echo '<br><text style="color:darkgreen">Игроков: '.$kernel[1].'/'.$kernel[2].'</text>';
  }else echo '<br><text style="color:darkred">Тех. работы</text></br>';?></li>
  <li><?
  echo '<b>BackTrack[IC2+RP2]</b>'; 
    if($backtrack[2]){    
    echo '<br><text style="color:darkgreen">Игроков: '.$backtrack[1].'/'.$backtrack[2].'</text></br>';
  }else echo '<br><text style="color:darkred">Тех. работы</text></br>';
   ?>	</li>
  <li><?
  echo '<b>Gentoo[FullPVP]</b>'; 
    if($gentoo[2]){    
    echo '<br><text style="color:darkgreen">Игроков: '.$gentoo[1].'/'.$gentoo[2].'</text></br>';
  }else echo '<br><text style="color:darkred">Тех. работы</text></br>';
   ?> </li>
  
  
 
 <li class="banner">	<!--LiveInternet counter--><script type="text/javascript"><!--
document.write("<a href='http://www.liveinternet.ru/click' "+
"target=_blank><img src='//counter.yadro.ru/hit?t14.1;r"+
escape(document.referrer)+((typeof(screen)=="undefined")?"":
";s"+screen.width+"*"+screen.height+"*"+(screen.colorDepth?
screen.colorDepth:screen.pixelDepth))+";u"+escape(document.URL)+
";"+Math.random()+
"' alt='' title='"+
" ' "+
"border='0' width='88' height='31'><\/a>")
//--></script><!--/LiveInternet--> 


<li class="banner"><a href="http://mctop.su/rating/vote/airbreaker" style="border: 0;"><img src="http://mctop.su/counter/p4587" /></a></li>
 <li class="banner"><a href="http://www.want2vote.com/info.php?id=1104" style="border: 0;"><img src="http://w2v.biz/_status/pictures/status_votebanner/1104.jpg"></a>
</li>

				   </li>
  </ul>  
  
</div> 
 