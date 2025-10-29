$(document).ready(function(){
     $TtoolTipAjax();
});
$TtoolTipAjax=function(){//ทีทูลทิป ฟังก์ชั่น
$('.lnk').hover(function(e){ //Mouse Hover แอทริบิวต์ คลาส ชื่อ lnk
$('body').append('<div class="showTooltip">&nbsp;</div>');
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
