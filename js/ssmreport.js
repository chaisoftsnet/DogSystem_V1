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
menuWidth=180; // Must be a multiple of 10! no quotes!!
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
barText="REPORT"; // <IMG> tag supported. Put exact html for an image to show.

///////////////////////////

// ssmItems[...]=[name, link, target, colspan, endrow?] - leave 'link' and 'target' blank to make a header
ssmItems[0]=["พิมพ์รายงานแยกตาม.."] 
ssmItems[1]=["1. แยกตาม..สังกัดหน่วยงาน", "SortR_JP41_D_by_Dep_id.asp?xFields=0&ys=2011", "_blank"]
ssmItems[2]=["2. แยกตาม..จังหวัดที่ตั้งเครื่อง", "SortR_Jp41_D_by_W01_CUSTOMER_ADDR_CC.asp?xFields=1&ys=2011", "_blank"]
ssmItems[3]=["3. แยกตาม..ประเภทเครื่อง", "SortR_Jp41_D_by_TM.asp?xFields=2&ys=2011", "_blank"]
ssmItems[4]=["4. แยกตาม..ชนิดเครื่อง", "SortR_Jp41_D_by_dbody.asp?xFields=3&ys=2011", "_blank"]
ssmItems[5]=["5. แยกตาม..ประโยชน์", "SortR_JP41_D_by_M_HTOUSE.asp?xFields=4&ys=2011", "_blank"]
ssmItems[6]=["6. แยกตาม..ผู้ผลิต", "SortR_JP41_D_by_D_SUP.asp?xFields=5&ys=2011", "_blank"]
ssmItems[7]=["7. แยกตาม..การใช้ทางการแพทย์ฯลฯ", "SortR_JP41_D_by_useto.asp?xFields=6&ys=2011", "_blank"]
buildMenu();

//-->


