<?PHP
require_once("./include/volunteer.php");

if(!$volunteer->CheckLogin())
{
    $volunteer->RedirectToURL("login.php");
    exit;
}
if(isset($_POST['submit'])){
	if (!empty($_POST['check_list'])){
		if ($volunteer->PairStudent()){
			$volunteer->RedirectToURL("thank-you-volunteer.html");
		}
	} else {
		echo "<b>Please Select at Least One Option.</b>";
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
      <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
      <title>Pair up with Students</title>
      <link rel="STYLESHEET" type="text/css" href="style/main.css">
      <link rel="STYLESHEET" type="text/css" href="../../tablesorter/themes/blue/style.css">
      <script type="text/javascript" src="../../tablesorter/jquery-latest.js"></script> 
	  <script type="text/javascript" src="../../tablesorter/jquery.tablesorter.js"></script> 
      
</head>

<body>
<div id='fg_membersite_content'>
<h2>DCSSA Airport Pickup Manager</h2>


<?php
if(!$volunteer->DBLogin()){
   $volunteer->HandleError("Database login failed!");
   exit;
}
echo "<form id='main' action='' method='post' accept-charset='UTF-8'>";
echo "<table id='main-table' border='0' cellpadding='0' cellspacing='0' class='tablesorter'> 
<thead><tr>
<th width='20px' position='fixed' />
<th width='200px' position='fixed'>接机日期</th> 
<th width='200px' >接机时间</th>
<th width='150px'>航班号</th>
<th width='50px'>人数</th>
<th width='300px'>行李</th>
<th>我要接这名学生</th>
</tr></thead><tbody>";

$qry = "Select * from $volunteer->table_stu where confirmcode='y' and volunteer is null";
$result = mysql_query($qry,$volunteer->connection);
$count = 0;
while($row = mysql_fetch_array($result) ) {
	$count = $count + 1;
	$id = $row['id_user'];
	echo "<tr>";
	echo "<td>" . $count . "</td>";
	echo "<td>" . $row['date'] . "</td>";
	echo "<td>" . $row['time'] . "</td>";
	echo "<td>" . $row['flight'] . "</td>";
	echo "<td>" . $row['nump'] . "</td>";
	echo "<td>" . $row['numc'] . "</td>";
	//echo "<td style='width: 150px; border: 1px solid black;'>" . $row['email'] . "</td>";
	echo "<td>"."<input type='checkbox' name='check_list[]' value=$id >"."</td>";
	echo "</tr>";

}
echo "</tbody></table>";
$msg = "是否继续？";
echo "<input type='submit' name='submit' value='提交(Submit)'/>";
echo "<p>*点击提交后，您和待接机的学生都会收到邮件</p>";
echo "</form>";
?> 

<div><span class='error'><?php echo $volunteer->GetErrorMessage(); ?></span></div>

<script type="text/javascript">
	$(document).ready(function() 
    { 
        $("#main-table").tablesorter(); 
    } 
	); 

	$("#main").submit(function() {
		var cnt = document.querySelectorAll('input[type="checkbox"]:checked').length;
		return confirm("请确认要接这"+cnt+"名学生","确认提交");
	});

</script>
<p>
Logged in as: <?= $volunteer->UserFullName() ?>
</p>
<p>
<a href='login-home.php'>Home</a>
</p>
</div>
</body>
</html>
