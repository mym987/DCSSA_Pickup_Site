<?PHP
require_once("./include/membersite_config.php");

$fgmembersite->RedirectToURL($fgmembersite->GetParentURL());
exit;    

?>
<!--
<!DOCTYPE HTML>
<html lang="en-US">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="refresh" content="1;url=http://www.dukechina.org/pickup/">
        <script type="text/javascript">
            window.location.href = "http://www.dukechina.org/pickup/"
        </script>
        <title>Page Redirection</title>
    </head>
    <body>
         Note: don't tell people to `click` the link, just tell them that it is a link. 
        If you are not redirected automatically, follow the <a href='http://www.dukechina.org/pickup/'>link to example</a>
    </body>
</html>
-->