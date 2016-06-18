<?PHP
require_once("./include/membersite_config.php");

if(isset($_POST['submitted']))
{
   if($fgmembersite->RegisterUser())
   {
        $fgmembersite->RedirectToURL("thank-you.html");
   }
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
    <title>Contact us</title>
    <link rel="STYLESHEET" type="text/css" href="style/fg_membersite.css" />
    <script type='text/javascript' src='scripts/gen_validatorv31.js'></script>
    <link rel="STYLESHEET" type="text/css" href="style/pwdwidget.css" />
    <script src="scripts/pwdwidget.js" type="text/javascript"></script>      
</head>
<body>

<!-- Form Code Start -->
<div id='fg_membersite'>
<form id='register' action='<?php echo $fgmembersite->GetSelfScript(); ?>' method='post' accept-charset='UTF-8'>
<fieldset >
<legend>DCSSA志愿者注册（Volunteer Registration）</legend>

<input type='hidden' name='submitted' id='submitted' value='1'/>

<div class='short_explanation'>* required fields</div>
<input type='text'  class='spmhidip' name='<?php echo $fgmembersite->GetSpamTrapInputName(); ?>' />

<div><span class='error'><?php echo $fgmembersite->GetErrorMessage(); ?></span></div>
<div class='container'>
    <label for='username' >用户名(UserName)*:</label><br/>
    <input type='text' name='username' id='username' value='<?php echo $fgmembersite->SafeDisplay('username') ?>' maxlength="50" /><br/>
    <span id='register_username_errorloc' class='error'></span>
</div>
<div class='container' style='height:80px;'>
    <label for='password' >密码(Password)*:</label><br/>
    <div class='pwdwidgetdiv' id='thepwddiv' ></div>
    <noscript>
    <input type='password' name='password' id='password' maxlength="50" />
    </noscript>    
    <div id='register_password_errorloc' class='error' style='clear:both'></div>
</div>
<div class='container'>
    <label for='name' >姓名(Full Name in English)*: </label><br/>
    <input type='text' name='name' id='name' value='<?php echo $fgmembersite->SafeDisplay('name') ?>' maxlength="50" /><br/>
    <span id='register_name_errorloc' class='error'></span>
</div>
<div class='container'>
    <label for='gender' >性别(M/F)*: </label><br/>
    <input type='text' name='gender' id='gender' value='<?php echo $fgmembersite->SafeDisplay('gender') ?>' maxlength="50" /><br/>
    <span id='register_gender_errorloc' class='error'></span>
</div>
<div class='container'>
    <label for='email' >电子邮箱(Email Address)*:</label><br/>
    <input type='text' name='email' id='email' value='<?php echo $fgmembersite->SafeDisplay('email') ?>' maxlength="50" /><br/>
    <span id='register_email_errorloc' class='error'></span>
</div>
<div class='container'>
    <label for='driver' >驾照号(Driver License No.)*:</label><br/>
    <input type='text' name='driver' id='driver' value='<?php echo $fgmembersite->SafeDisplay('driver') ?>' maxlength="50" /><br/>
    <span id='register_driver_errorloc' class='error'></span>
</div>
<div class='container'>
    <label for='office' >单位／院校(Company/School):</label><br/>
    <input type='text' name='office' id='office' value='<?php echo $fgmembersite->SafeDisplay('office') ?>' maxlength="50" /><br/>
    <span id='register_office_errorloc' class='error'></span>
</div>
<div class='container'>
    <label for='cell' >手机号(Cellphone No.)*: </label><br/>
    <input type='text' name='cell' id='cell' value='<?php echo $fgmembersite->SafeDisplay('cell') ?>' maxlength="50" /><br/>
    <span id='register_cell_errorloc' class='error'></span>
</div>
<div class='container'>
    <label for='wechat' >微信号(WeChat ID): </label><br/>
    <input type='text' name='wechat' id='wechat' value='<?php echo $fgmembersite->SafeDisplay('wechat') ?>' maxlength="50" /><br/>
    <span id='register_wechat_errorloc' class='error'></span>
</div>
<div class='container'>
    <label for='qq' >QQ号: </label><br/>
    <input type='text' name='qq' id='qq' value='<?php echo $fgmembersite->SafeDisplay('qq') ?>' maxlength="50" /><br/>
    <span id='register_qq_errorloc' class='error'></span>
</div>

<div class='container'>
    <input type='submit' name='Submit' value='Submit' />
</div>

</fieldset>
</form>
<!-- client-side Form Validations:
Uses the excellent form validation script from JavaScript-coder.com-->

<script type='text/javascript'>
// <![CDATA[
    var pwdwidget = new PasswordWidget('thepwddiv','password');
    pwdwidget.MakePWDWidget();
    
    var frmvalidator  = new Validator("register");
    frmvalidator.EnableOnPageErrorDisplay();
    frmvalidator.EnableMsgsTogether();
    frmvalidator.addValidation("name","req","Please provide your name");
    frmvalidator.addValidation("email","req","Please provide your email address");
    frmvalidator.addValidation("email","email","Please provide a valid email address");
    frmvalidator.addValidation("username","req","Please provide a username");
    frmvalidator.addValidation("password","req","Please provide a password");

// ]]>
</script>

<!--
Form Code End (see html-form-guide.com for more info.)
-->

</body>
</html>