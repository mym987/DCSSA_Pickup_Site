<?PHP
require_once("./include/volunteer.php");
$vars = array();
$vars['target'] = "mym987@gmail.com";
$vars['name'] = "Mike";
$vars['subject'] = "Hello";
$vars['body'] = "Hello "."mym987@gmail.com"."<br/>".
        "Welcome! Your registration  with us is completed.<br/>".
        "<br/>".
        "Regards,<br/>".
        "Webmaster<br/>";
$volunteer->Email($vars['target'],$vars['name'],$vars['subject'],$vars['body']);
?>