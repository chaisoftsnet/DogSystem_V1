<!--

/*
Configure menu styles below
NOTE: To edit the link colors, go to the STYLE tags and edit the ssm2Items colors
*/
YOffset=150; // no quotes!!
XOffset=0;
staticYOffset=30; // no quotes!!
slideSpeed=20 // no quotes!!
waitTime=100; // no quotes!! this sets the time the menu stays out for after the mouse goes off it.
menuBGColor="black";
menuIsStatic="yes"; //this sets whether menu should stay static on the screen
menuWidth=150; // Must be a multiple of 10! no quotes!!
menuCols=2;
hdrFontFamily="verdana";
hdrFontSize="2";
hdrFontColor="white";
hdrBGColor="#170088";
hdrAlign="left";
hdrVAlign="center";
hdrHeight="15";
linkFontFamily="Verdana";
linkFontSize="2";
linkBGColor="white";
linkOverBGColor="#FFFF99";
linkTarget="_top";
linkAlign="Left";
barBGColor="#444444";
barFontFamily="Verdana";
barFontSize="2";
barFontColor="white";
barVAlign="center";
barWidth=20; // no quotes!!
barText="SIDE NEWS"; // <IMG> tag supported. Put exact html for an image to show.

///////////////////////////

// ssmItems[...]=[name, link, target, colspan, endrow?] - leave 'link' and 'target' blank to make a header
ssmItems[0]=["หนังสือพิมพ์ข่าวรายวัน"] //create header
ssmItems[1]=["ผู้จัดการรายวัน", "http://www.manager.co.th", "_blank"]
ssmItems[2]=["ไทยรัฐ", "http://www.thairath.co.th",""]
ssmItems[3]=["เดลินิวส์", "http://www.dailynews.co.th/", ""]
ssmItems[4]=["แนวหน้า", "http://www.naewna.com", "_new"]
ssmItems[5]=["บ้านเมือง", "http://www.banmuang.co.th", ""]
ssmItems[6]=["สยามรัฐ", "http://www.siamrath.co.th", ""]
ssmItems[7]=["ผจก.รายสัปดาห์", "http://www.manager.co.th/mgrweekly/", "", 1, "no"] //create two column row
ssmItems[8]=["ผจก.รายเดือน", "http://www.gotomanager.com", "",1]
ssmItems[9]=["คมชัดลึก : เกาะติดข่าวเด่น", "http://www.komchadluek.net/", ""] //create header
ssmItems[10]=["The Nation", "http://www.nationmultimedia.com/home/", ""]
ssmItems[11]=["Bangkokpost", "http://http://www.bangkokpost.com/", ""]
ssmItems[12]=["ฟังธรรมะ-หลวงพ่อชา", "http://www.fungdham.com/sound/cha.html", ""]

ssmItems[13]=["พยากรณ์อากาศ"] //create header
ssmItems[14]=["พยากรณ์อากาศประจำ", "http://www.tmd.go.th/index.php", ""]
ssmItems[15]=["พยากรณ์อากาศประจำ 7 วันข้างหน้า", "http://www.tmd.go.th/7-day_forecast.php", ""]
ssmItems[16]=["ดัชนีตลาดหุ้น-เงิน"]
ssmItems[17]=["ดัชนีตลาดหุ้น", "http://www.settrade.com/login.jsp?txtBrokerId=IPO", ""]
ssmItems[18]=["อัตราแลกเปลี่ยนเงินตรา", "http://www.scb.co.th/scb_api/index.jsp", ""]

buildMenu();

//-->