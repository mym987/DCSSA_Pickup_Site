<?PHP
require_once("./include/fg_membersite.php");

$fgmembersite = new FGMembersite();

//Provide your site name here
$fgmembersite->SetWebsiteName('DCSSA Airport Pickup Manager');

//Provide the email address where you want to get notifications
$fgmembersite->SetAdminEmail('dcssa2016@gmail.com');

//Provide your database login details here:
//hostname, user name, password, database name and table name
//note that the script will create the table (for example, fgusers in this case)
//by itself on submitting register.php for the first time
$fgmembersite->InitDB(/*hostname*/'localhost',
                      /*username*/'user',
                      /*password*/'qwertyuiop[]',
                      /*database name*/'pickup',
                      /*table name*/'students');

//For better security. Get a random string from this link: http://tinyurl.com/randstr
// and put it here
$fgmembersite->SetRandomKey('H7XmaJP24JyVNCd');

?>