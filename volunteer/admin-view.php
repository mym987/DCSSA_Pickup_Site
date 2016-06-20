<?PHP
require_once("./include/volunteer.php");

if(!$volunteer->CheckLogin())
{
    $volunteer->RedirectToURL("login.php");
    exit;
}

$user_rec = array();

$volunteer->GetUserFromEmail($volunteer->UserEmail(), $user_rec);

if(!$user_rec['level'])
{
    $volunteer->RedirectToURL("login-home.php");
    exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
      <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
      <title>Admin</title>
      <link rel="STYLESHEET" type="text/css" href="style/main.css">
      <link rel="STYLESHEET" type="text/css" href="../../tablesorter/themes/blue/style.css">
      <script type="text/javascript" src="../../tablesorter/jquery-latest.js"></script> 
	  <script type="text/javascript" src="../../tablesorter/jquery.tablesorter.js"></script> 
</head>

<body>
<div id='fg_membersite_content'>
<h2>查看所有学生</h2>


<?php
if(!$volunteer->DBLogin()){
   $volunteer->HandleError("Database login failed!");
   exit;
}
?>
<form id='main' action='' method='post' accept-charset='UTF-8'>
<table id='main-table' border='0' cellpadding='0' cellspacing='0' class='tablesorter'> 
<thead><tr>
<th width='20px' position='fixed' />
<th>姓名</th>
<th>已确认</th>
<th>接机日期</th> 
<th>接机时间</th>
<th>航班号</th>
<th>人数</th>
<th>行李</th>
<th>邮箱</th>
<th>手机</th> 
<th>微信／QQ</th>
<th>性别</th> 
<th>专业</th> 
<th>毕业院校</th>
<th>接机志愿者</th>
</tr></thead><tbody>
<?php
$user_email = $user_rec['email'];
$qry = "Select * from $volunteer->table_stu";
$result = mysql_query($qry,$volunteer->connection);
$count = 0;
while($row = mysql_fetch_array($result) ) {
	$count = $count + 1;
	$email = $row['email'];
	echo "<tr>";
	echo "<td>" . $count . "</td>";
	echo "<td>" . $row['name'] . "</td>";
	echo "<td>" . (($row['confirmcode']== 'y')?"是":"否") . "</td>";
	echo "<td>" . $row['date'] . "</td>";
	echo "<td>" . $row['time'] . "</td>";
	echo "<td>" . $row['flight'] . "</td>";
	echo "<td>" . $row['nump'] . "</td>";
	echo "<td>" . $row['numc'] . "</td>";
	echo "<td>" . $row['email'] . "</td>";
	echo "<td>" . $row['contact'] . "</td>";
	echo "<td>" . $row['wechat'] . "</td>";
	echo "<td>" . $row['gender'] . "</td>";
	echo "<td>" . $row['major'] . "</td>";
	echo "<td>" . $row['comp'] . "</td>";
	echo "<td>" . $row['volunteer'] . "</td>";
	//echo "<td style='width: 150px; border: 1px solid black;'>" . $row['email'] . "</td>";
	//echo "<td>"."<input type='checkbox' name='check_list[]' value=$email >"."</td>";
	echo "</tr>";

}
echo "</tbody></table>";
//$msg = "是否继续？";
//echo "<input type='submit' name='submit' value='提交(Submit)' onclick='return confirm($msg)' />";
//echo "<p>*点击提交后，您和待接机的学生都会收到邮件</p>";
echo "</form>";
?> 

<div><span class='error'><?php echo $volunteer->GetErrorMessage(); ?></span></div>

<script type="text/javascript">
	$(document).ready(function() 
    { 
        $("#main-table").tablesorter(); 
    } 
	); 

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
