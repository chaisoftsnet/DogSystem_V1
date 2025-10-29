<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
	<title>Untitled</title>
</head>

<body>
<!--#include file="DbConnect.asp"-->
<% 
	Sql="select * from M_MAN "
	Set RC1 =Server.CreateObject("ADODB.Recordset")
	RC1.open Sql,Conn,1,3 
	Do While Not Rc.EOF
		M_AGE=DateDiff("yyyy",Rc1("M_MAN_BORN"),date())%>
		<%= M_AGE %><br>
		<%		
Rc.MoveNext
Loop	
 %>


</body>
</html>
