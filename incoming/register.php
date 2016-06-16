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
<form id='register' action='' method='post' accept-charset='UTF-8'>
<fieldset >
<legend>DCSSA新生接机登记</legend>

<div class='intro'>
<p>各位2016秋季入学的杜克新生:</p>
<p>大家好!我们是杜克大学学生学者联谊会(Duke Chinese Students & Scholars Association, DCSSA)。首先代表杜克大学全体华人祝贺大家以优异的成绩和出色的个人能力申请杜克大学成功! DCSSA是杜克最大的华人学生团体,每年积极地为将要来校的新生和在校学生提供高质量的服务。请大家登录我们的主页察看详情: </p>
<p>http://www.dukechina.org/</p>
<p>并请加入China Mailing List,上面有各种实用的生活类信息如二手物品买卖等,DCSSA也会通过这个邮件列表发布活动的信息: </p>
<p>https://lists.duke.edu/sympa/subscribe/china</p>
<p>鉴于志愿者人数有限,希望新入学的同学先自己联系在杜克的师兄师姐解决接机问题。如果联系不到的话,我们会尽力帮忙联系接机志愿者去机场接机。由于联系志愿者需要时间, 我们希望需要接机的同学至少在抵达一周之前和我们取得联系。同时，由于志愿者人数的限制，我们并不能保证每一位报名接机的新生都能够联系到志愿者，还请大家谅解。</p>

<p>注意事项:</p>
<ol type="1">
<li>由于我们是非盈利学生组织,志愿者也是不要薪酬的,新生请不要主动给志愿者接机费用。与此同时, 志愿者们为了接新生,付出了宝贵的时间和精力,请新生们尊重志愿者的奉献。另外,新生往往需要办理银行卡,而银行有时会有给双方各$25的referral bonus。所以办卡时可以找接机的志愿者推荐一下,这样也算是对油费的补偿。</li>
<li>由于绝大部分志愿者开学后就会非常忙碌,本次秋季入学我们只有能力安排7月1日到8月30日之间到达RDU的新生 (春季新生会另行通知).如果您的到达时间不在此时间段之内,请自己与在杜克的师兄师姐或者与本系的招生秘书联系.</li>
<li>由于志愿者都是用自己的车来接新生,时间和空间有限,建议大家选择到达RDU机场的时间尽量在午夜12:00之前;行李不要过多(2个158cm, 1个115cm 加一个书包是极限了),更多行李如不急用的书籍、冬季的衣物及床上用品等可以考虑海运。另外,RDU离Duke只有约20分钟车程,所以新生完全可以在飞机落地后再与志愿者电话/微信联系
(等行李至少需15分钟;行李处有公用电话;RDU有免费的WIFI)。如因航班取消等意外导致无法与志愿者碰面,请提前联系志愿者说明情况,可以协商更改接机时间;实在不行可考虑乘坐出租,从RDU到Duke附近大概费用为$30。</li>
<li>请不要重复填写此问卷，如果有些信息（例如航班到港时间）尚未确定，请大家确定后再填写。如需要更改填写过的问卷信息，请发邮件给ym67@duke.edu</li>
<li>带星号的为必答项目，其他题目为选答项目。</li>
<li>本问卷截止日期为7月30日。如果有任何问题，请联系DCSSA1516@gmail.com</li>
</ol>
<p>希望大家度过一个愉快的暑假,我们在杜克大学等待大家的入学。</p>
<p>DCSSA 2016-2017 执委会</p>
</div>

<input type='hidden' name='submitted' id='submitted' value='1'/>

<div class='short_explanation'>* required fields</div>
<input type='text'  class='spmhidip' name='<?php echo $fgmembersite->GetSpamTrapInputName(); ?>' />

<div><span class='error'><?php echo $fgmembersite->GetErrorMessage(); ?></span></div>
<div class='container'>
    <label for='name' >姓名(Full Name)*: </label><br/>
    <input type='text' name='name' id='name' value='<?php echo $fgmembersite->SafeDisplay('name') ?>' maxlength="20" /><br/>
    <span id='register_name_errorloc' class='error'></span>
</div>
<div class='container'>
    <label for='email' >电子邮件(请填写@duke.edu的邮箱，用于接收确认邮件)*:</label><br/>
    <input type='text' name='email' id='email' value='<?php echo $fgmembersite->SafeDisplay('email') ?>' maxlength="50" /><br/>
    <span id='register_email_errorloc' class='error'></span>
</div>
<!--
<div class='container'>
    <label for='username' >UserName*:</label><br/>
    <input type='text' name='username' id='username' value='<?php echo $fgmembersite->SafeDisplay('username') ?>' maxlength="50" /><br/>
    <span id='register_username_errorloc' class='error'></span>
</div>
<div class='container' style='height:80px;'>
    <label for='password' >Password*:</label><br/>
    <div class='pwdwidgetdiv' id='thepwddiv' ></div>
    <noscript>
    <input type='password' name='password' id='password' maxlength="50" />
    </noscript>    
    <div id='register_password_errorloc' class='error' style='clear:both'></div>
</div>
-->
<div class='container'>
    <label for='gender' >性别(M/F):</label><br/>
    <input type='text' name='gender' id='gender' value='<?php echo $fgmembersite->SafeDisplay('gender') ?>' maxlength="10" /><br/>
    <span id='register_gender_errorloc' class='error'></span>
</div>

<div class='container'>
    <label for='major' >就读专业(Department/Program at Duke):</label><br/>
    <input type='text' name='major' id='major' value='<?php echo $fgmembersite->SafeDisplay('major') ?>' maxlength="50" /><br/>
    <span id='register_major_errorloc' class='error'></span>
</div>

<div class='container'>
    <label for='comp' >毕业院校/单位(Alma mater/company):</label><br/>
    <input type='text' name='comp' id='comp' value='<?php echo $fgmembersite->SafeDisplay('comp') ?>' maxlength="50" /><br/>
    <span id='register_comp_errorloc' class='error'></span>
</div>

<div class='container'>
    <label for='date' >抵达RDU机场的日期(MM/DD)*:</label><br/>
    <input type='text' name='date' id='date' value='<?php echo $fgmembersite->SafeDisplay('date') ?>' maxlength="10" /><br/>
    <span id='register_date_errorloc' class='error'></span>
</div>

<div class='container'>
    <label for='time' >抵达RDU机场的时间(e.g. 11:35pm)*:</label><br/>
    <input type='text' name='time' id='time' value='<?php echo $fgmembersite->SafeDisplay('time') ?>' maxlength="10" /><br/>
    <span id='register_time_errorloc' class='error'></span>
</div>

<div class='container'>
    <label for='flight' >航班号(Flight no.)*:</label><br/>
    <input type='text' name='flight' id='flight' value='<?php echo $fgmembersite->SafeDisplay('flight') ?>' maxlength="20" /><br/>
    <span id='register_flight_errorloc' class='error'></span>
</div>

<div class='container'>
    <label for='nump' >需接机的人数(Number of Passengers)*:</label><br/>
    <input type='text' name='nump' id='nump' value='<?php echo $fgmembersite->SafeDisplay('nump') ?>' maxlength="5" /><br/>
    <span id='register_nump_errorloc' class='error'></span>
</div>

<div class='container'>
    <label for='numc' >行李信息(e.g.2个158cm，1个115cm，1个书包)*:</label><br/>
    <input type='text' name='numc' id='numc' value='<?php echo $fgmembersite->SafeDisplay('numc') ?>' maxlength="50" /><br/>
    <span id='register_numc_errorloc' class='error'></span>
</div>

<div class='container'>
    <label for='contact' >联系方式(手机号)*:</label><br/>
    <input type='text' name='contact' id='contact' value='<?php echo $fgmembersite->SafeDisplay('contact') ?>' maxlength="50" /><br/>
    <span id='register_contact_errorloc' class='error'></span>
</div>

<div class='container'>
    <label for='wechat' >微信/QQ号(请注明):</label><br/>
    <input type='text' name='wechat' id='wechat' value='<?php echo $fgmembersite->SafeDisplay('wechat') ?>' maxlength="50" /><br/>
    <span id='register_wechat_errorloc' class='error'></span>
</div>

<div class='container'>
    <input type='submit' name='Submit' value='提交(Submit)' />
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

    //frmvalidator.addValidation("username","req","Please provide a username");
    
    //frmvalidator.addValidation("password","req","Please provide a password");

// ]]>
</script>

<!--
Form Code End (see html-form-guide.com for more info.)
-->

</body>
</html>