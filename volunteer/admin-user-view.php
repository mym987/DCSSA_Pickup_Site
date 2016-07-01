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
<h2>查看所有志愿者</h2>


<?php
if(!$volunteer->DBLogin()){
   $volunteer->HandleError("Database login failed!");
   exit;
}
echo date('Y年m月d日 h:m A P');
?>
<form id='main' action='' method='post' accept-charset='UTF-8'>
<table id='main-table' border='0' cellpadding='0' cellspacing='0' class='tablesorter'> 
<thead><tr>
<th width='20px' position='fixed' />
<th>姓名</th>
<th>已确认</th>
<th>邮箱</th>
<th>登录名</th> 
<th>驾照号</th>
<th>公司／院校</th>
<th>性别</th>
<th>电话</th>
<th>微信</th>
<th>QQ</th> 
<th>计划接机总数</th>
<th>已接人数</th>  
</tr></thead><tbody>
<?php
$user_email = $user_rec['email'];
$level = $user_rec['level'];
$qry = "Select * from $volunteer->tablename where level is null or level<=$level";
$result = mysql_query($qry,$volunteer->connection);
$count = 0;
$today = date('m/d');
while($row = mysql_fetch_array($result) ) {
	$count = $count + 1;
	$email = $row['email'];
	echo "<tr>";
	echo "<td>" . $count . "</td>";
	echo "<td>" . $row['name'] . "</td>";
	echo "<td>" . (($row['confirmcode']== 'y')?"是":"否") . "</td>";
	echo "<td>" . $row['email'] . "</td>";
	echo "<td>" . $row['username'] . "</td>";
	echo "<td>" . $row['driver'] . "</td>";
	echo "<td>" . $row['office'] . "</td>";
	echo "<td>" . $row['gender'] . "</td>";
	echo "<td>" . $row['cell'] . "</td>";
	echo "<td>" . $row['wechat'] . "</td>";
	echo "<td>" . $row['qq'] . "</td>";
	$num_picked = mysql_result(mysql_query("select count(*) from $volunteer->table_stu
		where volunteer='$email'"
		,$volunteer->connection),0,0);
	echo "<td>" . $num_picked . "</td>";
	$num_picked = mysql_result(mysql_query("select count(*) from $volunteer->table_stu 
		where volunteer='$email' and date<'$today'"
		,$volunteer->connection),0,0);
	echo "<td>" . $num_picked . "</td>";
	//echo "<td style='width: 150px; border: 1px solid black;'>" . $row['email'] . "</td>";
	//echo "<td>"."<input type='checkbox' name='check_list[]' value=$email >"."</td>";
	echo "</tr>";

}
echo "</tbody></table>";
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
