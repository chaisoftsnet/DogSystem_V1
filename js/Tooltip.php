<?php
$dbconn=mysql_connect('OSD102_LDD','sa','1111');//เชื่อมต่อกับฐานข้อมูล
mysql_select_db('db_exshop1',$dbconn);//เลือกฐานข้อมูล
mysql_query('SET NAMES UTF8');//เพื่อรองรับภาษาไทย
$rsShowMb=mysql_query('SELECT mb_id,mb_name FROM tb_member ORDER BY mb_name');//แสดงข้อมูลสมาชิกเฉพาะidและชื่อสมาชิกเท่านั้น
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script type="text/javascript" src="../jquery.js"></script>
<script type="text/javascript">
$(document).ready(function(){
     $TtoolTipAjax();
});
$TtoolTipAjax=function(){//ทีทูลทิป ฟังก์ชั่น
$('.lnk').hover(function(e){ //Mouse Hover แอทริบิวต์ คลาส ชื่อ lnk
$('body').append('<div class="showTooltip"> </div>');
var showTooltip=$('.showTooltip');
    $.ajax({//เรียกใช้ ajax ของ jQuery
        url:$(this).attr('turl')+'&'+new Date().getTime(),
        beforeSend :function(){//ก่อนส่งค่า 
             showTooltip.html('<img src="wait.gif"/>'); //แสดงตัว loading 
          },
        success:function(data){//ส่งค่าเสร็จสมบูรณ์ พร้อมกับผลลัพธุ์ถูกส่งกลับมา(data)
            showTooltip.html(data);
       }
    });
var mousex = e.pageX+10 ; 
var mousey = e.pageY;  
var tooltipWidth = showTooltip.width(); 
var tooltipHeight = showTooltip.height(); 
var toolVisX = $(window).width() - (mousex + tooltipWidth); 
var toolVisY = ($(window).height()+$(window).scrollTop())-(mousey+tooltipHeight); 
if ( toolVisX < 10 ) {  mousex = e.pageX - tooltipWidth - 40;  }
if ( toolVisY < 10 ) {   mousey = e.pageY - tooltipHeight - 10;  }
showTooltip.css({ top: mousey, left: mousex,display:'none'});
showTooltip.slideDown('slow');
},function(){ //Mouse Out
       $('.showTooltip').remove();//Remove Tooltip
})
}
</script>
<title>Tooltip Ajax PHP+MySQL</title>
<style type="text/css">
/*ปรับสีสันของ Tooltip ได้จากคำสั่ง CSS ตรงนี้*/
body{
font-size:12px;
font-family:Tahoma, Geneva, sans-serif;
}
.showTooltip{
float:left;
padding:10px;
background:#F3F3F3;
border:2px solid #CFCFCF;
-moz-border-radius: 4px;  
-webkit-border-radius: 4px;  
border-radius: 4px;
color:#333;
position:absolute;
}
a{
margin:5px;
color:#06C;
text-decoration:none;
}
</style>
</style>
</head>
<body>
<div align="left">
  <table border="0" cellspacing="0" cellpadding="3">
    <tr align="center" bgcolor="#D1D1D1">
      <td><strong>รหัสลูกค้า</strong></td>
      <td><strong>ชื่อ-สกุลลูกค้า</strong></td>
    </tr>
    <?php while($showMb=mysql_fetch_array($rsShowMb)){?>
    <tr>
      <td align="center" bgcolor="#E8E8E8"><?=$showMb['mb_id']?></td>
      <td bgcolor="#E8E8E8"><a href="#" turl="showmbdetail.php?mbid=<?=$showMb['mb_id']?>" class="lnk"><?=$showMb['mb_name']?></a></td>
    </tr>
    <?php } ?>
  </table>
</div>
</body>
</html>
<?php mysql_close($dbconn);?>
