<?PHP
require_once("./include/volunteer.php");

if(!$volunteer->CheckLogin())
{
    $volunteer->RedirectToURL("login.php");
    exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
      <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
      <title>Home page</title>
      <link rel="STYLESHEET" type="text/css" href="style/fg_membersite.css">
</head>
<body>
<div id='fg_membersite_content'>
<h2>DCSSA Airport Pickup Manager</h2>
Welcome back <?= $volunteer->UserFullName(); ?>!

<p><a href='change-pwd.php'>修改密码(Change password)</a></p>
<p><a href='main.php'>接机安排(Pickup Arrangement Page)</a></p>
<p><a href='viewer.php'>查看我要接的学生(View my Assigned Students)</a></p>
<?php
$user_rec = array();
$volunteer->GetUserFromEmail($volunteer->UserEmail(), $user_rec);
if($user_rec['level'])
{
    echo "<p><a href='admin-view.php'>查看所有学生</a></p>";
    echo "<p><a href='admin-user-view.php'>查看所有志愿者</a></p>";
}
if(!$volunteer->DBLogin()){
   $volunteer->HandleError("Database login failed!");
   exit;
}
$today = date('m/d');
$total = mysql_result(mysql_query("select count(*) from $volunteer->table_stu where confirmcode='y'"
		,$volunteer->connection),0,0);
$unpicked = mysql_result(mysql_query("select count(*) from $volunteer->table_stu where confirmcode='y' and volunteer is null and date >= '$today'"
		,$volunteer->connection),0,0);
echo "<br/>";
echo "<p>总共有${total}名已确认的学生，还有${unpicked}名学生等待接机。感谢您为DCSSA做出的贡献！</p>";
?>
<br><br><br>
<p><a href='logout.php'>退出(Logout)</a></p>
</div>
</body>
</html>
