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
barText="SIDE DATA"; // <IMG> tag supported. Put exact html for an image to show.

///////////////////////////

// ssmItems[...]=[name, link, target, colspan, endrow?] - leave 'link' and 'target' blank to make a header
ssmItems[0]=["ข้อมูลอ้างอิงระบบ"] //create header
ssmItems[1]=["1. คำนำหน้าชื่อ", "Grid_Prefix.asp", "_blank"]
ssmItems[2]=["2. ชื่อจังหวัดทั้งหมด", "Grid_Code_cc.asp","_blank"]
ssmItems[3]=["3. ชนิดเครื่องกำเนิดรังสี", "Grid_M_Dbody.asp", "_blank"]
ssmItems[4]=["4. ประเภทเครื่องกำเนิดรังสี", "Grid_M_TM.asp", "_blank"]
ssmItems[5]=["5. ประโยชน์เครื่องกำเนิดรังสี", "Grid_M_HTOUSE.asp", "_blank"]
ssmItems[6]=["6. ผู้ผลิตเครื่องกำเนิดรังสี", "Grid_M_PRODU.asp", "_blank"]
ssmItems[7]=["7. แบบ พ.ป.ส.", "Grid_M_FORM.asp", "_blank"]
ssmItems[8]=["8. แบบเครื่องกำเนิดรังสี", "Grid_D_PACK.asp", "_blank"]
ssmItems[9]=["9. ข้อมูลชื่อกระทรวงฯลฯ", "Grid_DEPARTMENT.asp", "_blank"]
ssmItems[10]=["10. ข้อมูลชื่อกรม/กองในกระทรวงฯลฯ", "Grid_DEPARTMENT_SUB.asp", "_blank"]
ssmItems[11]=["11. ข้อมูล font", "Grid_font.asp", "_blank"]
ssmItems[12]=["พิมพ์รายงาน"] 
ssmItems[13]=["1. แยกตาม..สังกัดหน่วยงาน", "SortR_JP41_D_by_Dep_id.asp?xFields=0&ys=2011", "_blank"]
ssmItems[14]=["2. แยกตาม..จังหวัดที่ตั้งเครื่อง", "SortR_Jp41_D_by_W01_CUSTOMER_ADDR_CC.asp?xFields=1&ys=2011", "_blank"]
ssmItems[15]=["3. แยกตาม..ประเภทเครื่อง", "SortR_Jp41_D_by_TM.asp?xFields=2&ys=2011", "_blank"]
ssmItems[16]=["4. แยกตาม..ชนิดเครื่อง", "SortR_Jp41_D_by_dbody.asp?xFields=3&ys=2011", "_blank"]
ssmItems[17]=["5. แยกตาม..ประโยชน์", "SortR_JP41_D_by_M_HTOUSE.asp?xFields=4&ys=2011", "_blank"]
ssmItems[18]=["6. แยกตาม..ผู้ผลิต", "SortR_JP41_D_by_D_SUP.asp?xFields=5&ys=2011", "_blank"]
ssmItems[19]=["7. แยกตาม..การใช้ทางการแพทย์ฯลฯ", "SortR_JP41_D_by_useto.asp?xFields=6&ys=2011", "_blank"]
ssmItems[20]=["พิมพ์รายงานอื่นๆ"] 
ssmItems[21]=["1. รายงานใบขออนุญาต (ตั้งแต่-จนถึง)", "PrintStartEnd.asp", "_blank"]
buildMenu();

//-->


