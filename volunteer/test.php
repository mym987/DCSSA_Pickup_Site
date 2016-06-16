<?PHP
require_once("./include/membersite_config.php");
$vars = array();
$vars['target'] = "mym987@gmail.com";
$vars['name'] = "Mike";
$vars['subject'] = "Hello";
$vars['body'] = "Hello "."mym987@gmail.com"."<br/>".
        "Welcome! Your registration  with us is completed.<br/>".
        "<br/>".
        "Regards,<br/>".
        "Webmaster<br/>";
$fgmembersite->Email($vars['target'],$vars['name'],$vars['subject'],$vars['body']);
?>