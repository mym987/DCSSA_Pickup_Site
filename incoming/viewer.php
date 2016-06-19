<?php
require_once("./include/student.php");
//session_start(); 
//echo "Code is ".$_SESSION['code'];

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
      <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
      <title>Check my registration status</title>
      <link rel="STYLESHEET" type="text/css" href="style/fg_membersite.css" />
      <script type='text/javascript' src='scripts/gen_validatorv31.js'></script>
</head>
<body>

<h2>Check my registration status</h2>
<p>
Please enter your email address and the numerical part of your flight number exactly as you provided in the registration form.
</p>

<!-- Form Code Start -->
<div id='fg_membersite'>
<form id='check' action='' method='post' accept-charset='UTF-8'>
<div class='short_explanation'>* required fields</div>
<div><span class='error'><?php echo $student->GetErrorMessage(); ?></span></div>
<div class='container'>
    <label for='email' >Email:* </label><br/>
    <input type='text' name='email' id='email' maxlength="50" /><br/>
    <span id='register_email_errorloc' class='error'></span>
</div>
<div class='container'>
    <label for='flight' >Flight number(if your flight is UA999, then enter 999):* </label><br/>
    <input type='text' name='flight' id='flight' maxlength="50" /><br/>
    <span id='register_flight_errorloc' class='error'></span>
</div>
<!--
<div class='container'>
<input type="text" name="code" />
<img id="code" src="code.php" alt="看不清楚，换一张" style="cursor: pointer; vertical-align:middle;" onClick="create_code()"/>
</div>-->
<div class='container'>
    <input type='submit' name='submit' value='Submit' />
</div>

</form>

<script>
function create_code(){
    document.getElementById('code').src = 'code.php?'+Math.random()*10000;
}
</script>

<script type='text/javascript'>
// <![CDATA[

    var frmvalidator  = new Validator("check");
    frmvalidator.EnableOnPageErrorDisplay();
    frmvalidator.EnableMsgsTogether();
    frmvalidator.addValidation("email","req","Please enter your email");
    frmvalidator.addValidation("flight","req","Please enter your flight no.");
    frmvalidator.addValidation("code","req","Please enter verification code");
    frmvalidator.addValidation("flight","num","Numbers only!");

// ]]>
</script>
</div>

<?php
$submitted = isset($_POST['email']);
if($submitted)
{
   $info = array();
   if($student->CheckUser($info)){
      if ($info['confirmcode']!='y'){
        echo "We have received your registration, but you have not clicked on the confirmation link yet. <br/>";
        echo "Please remember that we cannot display your information to volunteers unless you confirm your registration. <br/>";
        echo "Confirmation email got lost? Please contact ym67 AT duke.edu";
      } else {
        $email = $info['volunteer'];
        if ($email==null){
          echo "No volunteer has responded to your request yet. Please be patient!";
        } else {
          echo "Awesome! A volunteer is willing to pick you up. Please contact $email to arrange your airport pickup. ";
        }
      }
   } else {
      if ($student->GetErrorMessage()!=null){
        echo $student->GetErrorMessage().'<br/>';
      }
      else {
        echo "Sorry, you are not registered or you entered incorrect information";
      }
   } 
  /*$student->RedirectToURL('http://www.google.com/');
   if($_POST['code'] == $_SESSION['code']){
    $student->RedirectToURL('http://www.google.com/');//GetUserByEmailAndFlight();
   } else {
    echo 'Wrong verification code';
   } */
}


?>

</body>
</html>