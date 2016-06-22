<?PHP
require_once("PHPMailerAutoload.php");
require_once("formvalidator.php");

$volunteer = new Volunteer('conf_volunteer.ini');

class Volunteer
{
    var $connection;
    
    var $error_message;
    
    //-----Initialization -------
    function Volunteer($conf_ini)
    {
        $conf = parse_ini_file($conf_ini, true);

        $this->sitename = $conf['web']['name'];
        $this->admin_email = $conf['web']['email'];
        $this->rand_key = $conf['web']['key'];

        $this->db_host  = $conf['db']['hostname'];
        $this->username = $conf['db']['username'];
        $this->pwd      = $conf['db']['password'];
        $this->database  = $conf['db']['dbname'];
        $this->tablename = $conf['db']['tablename'];
        $this->table_stu = 'students';


        $this->email_host  = $conf['email']['host'];
        $this->email_port  = $conf['email']['port'];
        $this->email_secure  = $conf['email']['secure'];
        $this->email_username  = $conf['email']['username'];
        $this->email_password  = $conf['email']['password'];
        $this->email_reply_name  = $conf['email']['reply_name'];
        $this->email_reply_addr  = $conf['email']['reply_addr'];
        $this->email_from_name  = $conf['email']['from_name'];
        $this->email_from_addr  = $conf['email']['from_addr'];

    }

    function Email($target,$name,$subject,$body){
         
        $mail = new PHPMailer(true);
        $mail->CharSet = 'utf-8';
        ini_set('default_charset', 'UTF-8');
        try {
            $to = $target;
        if(!PHPMailer::validateAddress($to)) {
            throw new phpmailerException("Email address " . $to . " is invalid -- aborting!");
        }
        $mail->isSMTP();
        $mail->SMTPDebug  = 0;
        $mail->Host       = $this->email_host;
        $mail->Port       = $this->email_port;
        $mail->SMTPSecure = $this->email_secure;
        $mail->SMTPAuth   = true;
        $mail->Username   = $this->email_username;
        $mail->Password   = $this->email_password;
        $mail->addReplyTo($this->email_reply_addr, $this->email_reply_name);
        $mail->setFrom($this->email_from_addr, $this->email_from_name);
        $mail->addAddress($target, $name);
        $mail->Subject  = $subject;
        $mail->WordWrap = 78;
        $mail->msgHTML($body, dirname(__FILE__), true); //Create message bodies and embed images
         
        try {
          $mail->send();
          return true;
          //$results_messages[] = "Message has been sent using SMTP";
        }
        catch (phpmailerException $e) {
          throw new phpmailerException('Unable to send to: ' . $to. ': '.$e->getMessage());
        }
        }
        catch (phpmailerException $e) {
            echo "\n";
            echo $e->getMessage();
          return false;
          //$results_messages[] = $e->errorMessage();
        }
        return true;
    }
    
    //-------Main Operations ----------------------
    function RegisterUser()
    {
        if(!isset($_POST['submitted']))
        {
           return false;
        }
        
        $formvars = array();
        
        if(!$this->ValidateRegistrationSubmission())
        {
            return false;
        }
        
        $this->CollectRegistrationSubmission($formvars);
        
        if(!$this->SaveToDatabase($formvars))
        {
            return false;
        }
        if(!$this->SendUserConfirmationEmail($formvars))
        {
            return false;
        }

        $this->SendAdminIntimationEmail($formvars);
        
        return true;
    }

    function ConfirmUser()
    {
        if(empty($_GET['code'])||strlen($_GET['code'])<=10)
        {
            $this->HandleError("Please provide the confirm code");
            return false;
        }
        $user_rec = array();
        if(!$this->UpdateDBRecForConfirmation($user_rec))
        {
            return false;
        }
        
        $this->SendUserWelcomeEmail($user_rec);
        
        $this->SendAdminIntimationOnRegComplete($user_rec);
        
        return true;
    }    
    
    function Login()
    {
        if(empty($_POST['username']))
        {
            $this->HandleError("UserName is empty!");
            return false;
        }
        
        if(empty($_POST['password']))
        {
            $this->HandleError("Password is empty!");
            return false;
        }
        
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        
        if(!isset($_SESSION)){ session_start(); }
        if(!$this->CheckLoginInDB($username,$password))
        {
            return false;
        }
        
        $_SESSION[$this->GetLoginSessionVar()] = $username;
        
        return true;
    }
    
    function CheckLogin()
    {
         if(!isset($_SESSION)){ session_start(); }

         $sessionvar = $this->GetLoginSessionVar();
         
         if(empty($_SESSION[$sessionvar]))
         {
            return false;
         }
         return true;
    }
    
    function UserFullName()
    {
        return isset($_SESSION['name_of_user'])?$_SESSION['name_of_user']:'';
    }
    
    function UserEmail()
    {
        return isset($_SESSION['email_of_user'])?$_SESSION['email_of_user']:'';
    }
    
    function LogOut()
    {
        session_start();
        
        $sessionvar = $this->GetLoginSessionVar();
        
        $_SESSION[$sessionvar]=NULL;
        
        unset($_SESSION[$sessionvar]);
    }
    
    function EmailResetPasswordLink()
    {
        if(empty($_POST['email']))
        {
            $this->HandleError("Email is empty!");
            return false;
        }
        $user_rec = array();
        if(false === $this->GetUserFromEmail($_POST['email'], $user_rec))
        {
            return false;
        }
        if(false === $this->SendResetPasswordLink($user_rec))
        {
            return false;
        }
        return true;
    }
    
    function ResetPassword()
    {
        if(empty($_GET['email']))
        {
            $this->HandleError("Email is empty!");
            return false;
        }
        if(empty($_GET['code']))
        {
            $this->HandleError("reset code is empty!");
            return false;
        }
        $email = trim($_GET['email']);
        $code = trim($_GET['code']);
        
        if($this->GetResetPasswordCode($email) != $code)
        {
            $this->HandleError("Bad reset code!");
            return false;
        }
        
        $user_rec = array();
        if(!$this->GetUserFromEmail($email,$user_rec))
        {
            return false;
        }
        
        $new_password = $this->ResetUserPasswordInDB($user_rec);
        if(false === $new_password || empty($new_password))
        {
            $this->HandleError("Error updating new password");
            return false;
        }
        
        if(false == $this->SendNewPassword($user_rec,$new_password))
        {
            $this->HandleError("Error sending new password");
            return false;
        }
        return true;
    }
    
    function ChangePassword()
    {
        if(!$this->CheckLogin())
        {
            $this->HandleError("Not logged in!");
            return false;
        }
        
        if(empty($_POST['oldpwd']))
        {
            $this->HandleError("Old password is empty!");
            return false;
        }
        if(empty($_POST['newpwd']))
        {
            $this->HandleError("New password is empty!");
            return false;
        }
        
        $user_rec = array();
        if(!$this->GetUserFromEmail($this->UserEmail(),$user_rec))
        {
            return false;
        }
        
        $pwd = trim($_POST['oldpwd']);
        
        if($user_rec['password'] != md5($pwd))
        {
            $this->HandleError("The old password does not match!");
            return false;
        }
        $newpwd = trim($_POST['newpwd']);
        
        if(!$this->ChangePasswordInDB($user_rec, $newpwd))
        {
            return false;
        }
        return true;
    }

    function PairStudent()
    {
        if(!$this->CheckLogin())
        {
            $this->HandleError("Not logged in!");
            return false;
        }

        $checked_count = count($_POST['check_list']);
        //echo "You have selected following ".$checked_count." option(s): <br/>";
        // Loop to store and display values of individual checked checkbox.
        foreach($_POST['check_list'] as $id) {
            $stu_rec = array();
            if(!$this->GetStudentFromID($id,$stu_rec))
            {
                return false;
            }
            $user_rec = array();
            if(!$this->GetUserFromEmail($this->UserEmail(),$user_rec))
            {
                return false;
            }
            if(!$this->UpdateStudentAsPicked($this->UserEmail(),$stu_rec))
            {
                return false;
            }
            if(!$this->SendStudentPickEmail($stu_rec,$user_rec))
            {
                return false;
            }
            if(!$this->SendUserPickEmail($stu_rec,$user_rec))
            {
                return false;
            }
            //echo "<p>".$selected ."</p>";
        }
        //echo "<br/><b>Note :</b> <span>Similarily, You Can Also Perform CRUD Operations using These Selected Values.</span>";
        
        return true;
    }
    
    //-------Public Helper functions -------------
    function GetSelfScript()
    {
        return htmlentities($_SERVER['PHP_SELF']);
    }    
    
    function SafeDisplay($value_name)
    {
        if(empty($_POST[$value_name]))
        {
            return'';
        }
        return htmlentities($_POST[$value_name]);
    }
    
    function RedirectToURL($url)
    {
        header("Location: $url");
        exit;
    }
    
    function GetSpamTrapInputName()
    {
        return 'sp'.md5('KHGdnbvsgst'.$this->rand_key);
    }
    
    function GetErrorMessage()
    {
        if(empty($this->error_message))
        {
            return '';
        }
        $errormsg = nl2br(htmlentities($this->error_message));
        return $errormsg;
    }    
    //-------Private Helper functions-----------

    function UpdateStudentAsPicked($email,&$user_rec){
        $email = $this->SanitizeForSQL($email);
        
        $qry = "Update $this->table_stu Set volunteer='$email' Where  id_user=".$user_rec['id_user']."";
        
        if(!mysql_query( $qry ,$this->connection))
        {
            $this->HandleDBError("Error updating the password \nquery:$qry");
            return false;
        }     
        return true;
    }
    
    function HandleError($err)
    {
        $this->error_message .= $err;
    }
    
    function HandleDBError($err)
    {
        $this->HandleError($err."<br/> mysqlerror:".mysql_error());
    }
    
    function GetFromAddress()
    {
        if(!empty($this->from_address))
        {
            return $this->from_address;
        }

        $host = $_SERVER['SERVER_NAME'];

        $from ="nobody@$host";
        return $from;
    } 
    
    function GetLoginSessionVar()
    {
        $retvar = md5($this->rand_key);
        $retvar = 'usr_'.substr($retvar,0,10);
        return $retvar;
    }
    
    function CheckLoginInDB($username,$password)
    {
        if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        }          
        $username = $this->SanitizeForSQL($username);
        $pwdmd5 = md5($password);
        $qry = "Select name, email from $this->tablename where username='$username' and password='$pwdmd5' and confirmcode='y'";
        
        $result = mysql_query($qry,$this->connection);
        
        if(!$result || mysql_num_rows($result) <= 0)
        {
            $this->HandleError("Error logging in. The username or password does not match");
            return false;
        }
        
        $row = mysql_fetch_assoc($result);
        
        
        $_SESSION['name_of_user']  = $row['name'];
        $_SESSION['email_of_user'] = $row['email'];
        
        return true;
    }
    
    function UpdateDBRecForConfirmation(&$user_rec)
    {
        if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        }   
        $email = $this->SanitizeForSQL($_GET['email']);
        $confirmcode = $this->SanitizeForSQL($_GET['code']);
        
        $result = mysql_query("Select name, confirmcode from $this->tablename where email='$email'",$this->connection);   
        if(!$result || mysql_num_rows($result) <= 0)
        {
            $this->HandleError("Wrong credentials.");
            return false;
        }
        $row = mysql_fetch_assoc($result);
        if($row['confirmcode'] == 'y'){
            $this->HandleError("You have already confirmed.");
            return false;
        } else if ($row['confirmcode'] != $confirmcode){
            $this->HandleError("Wrong credentials.");
            return false;
        }
        $user_rec['name'] = $row['name'];
        $user_rec['email']= $email;
        
        $qry = "Update $this->tablename Set confirmcode='y' Where  confirmcode='$confirmcode'";
        
        if(!mysql_query( $qry ,$this->connection))
        {
            $this->HandleDBError("Error inserting data to the table\nquery:$qry");
            return false;
        }      
        return true;
    }
    
    function ResetUserPasswordInDB($user_rec)
    {
        $new_password = substr(md5(uniqid()),0,10);
        
        if(false == $this->ChangePasswordInDB($user_rec,$new_password))
        {
            return false;
        }
        return $new_password;
    }
    
    function ChangePasswordInDB($user_rec, $newpwd)
    {
        $newpwd = $this->SanitizeForSQL($newpwd);
        
        $qry = "Update $this->tablename Set password='".md5($newpwd)."' Where  id_user=".$user_rec['id_user']."";
        
        if(!mysql_query( $qry ,$this->connection))
        {
            $this->HandleDBError("Error updating the password \nquery:$qry");
            return false;
        }     
        return true;
    }
    
    function GetUserFromEmail($email,&$user_rec)
    {
        if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        }   
        $email = $this->SanitizeForSQL($email);
        
        $result = mysql_query("Select * from $this->tablename where email='$email'",$this->connection);  

        if(!$result || mysql_num_rows($result) <= 0)
        {
            $this->HandleError("There is no user with email: $email");
            return false;
        }
        $user_rec = mysql_fetch_assoc($result);

        
        return true;
    }

    function GetStudentFromID($id,&$user_rec)
    {
        if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        }   
        $email = $this->SanitizeForSQL($id);
        
        $result = mysql_query("Select * from $this->table_stu where id_user='$id'",$this->connection);  

        if(!$result || mysql_num_rows($result) <= 0)
        {
            $this->HandleError("There is no user with id: $id");
            return false;
        }
        $user_rec = mysql_fetch_assoc($result);

        
        return true;
    }

    function GetStudentFromEmail($email,&$user_rec)
    {
        if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        }   
        $email = $this->SanitizeForSQL($email);
        
        $result = mysql_query("Select * from $this->table_stu where email='$email'",$this->connection);  

        if(!$result || mysql_num_rows($result) <= 0)
        {
            $this->HandleError("There is no user with email: $email");
            return false;
        }
        $user_rec = mysql_fetch_assoc($result);

        
        return true;
    }
    
    function SendUserWelcomeEmail(&$user_rec)
    {
        $body ="Hello ".$user_rec['name']."<br/><br/>".
        "Welcome! Your registration  with ".$this->sitename." is completed.<br/>".
        "<br/>".
        "Regards,<br/>".
        "Webmaster<br/>".
        $this->sitename;

        if ($this->Email($user_rec['email'],$user_rec['name'],"Welcome to ".$this->sitename,$body)){
            return true;
        } else {
            $this->HandleError("Failed sending user welcome email.");
            return false;
        }
    }
    
    function SendAdminIntimationOnRegComplete(&$user_rec)
    {
        if(empty($this->admin_email))
        {
            return false;
        }
        $body ="A new user registered at ".$this->sitename."<br/>".
        "Name: ".$user_rec['name']."<br/>".
        "Email address: ".$user_rec['email']."<br/>";

        if ($this->Email($this->admin_email,"Admin","Volunteer Registration Completed: ".$user_rec['name'],$body)){
            return true;
        } else {
            return false;
        }
    }

    function SendStudentPickEmail(&$stu_rec,&$user_rec) {
        $body ="Hello ".$stu_rec['name']."<br/><br/>".
        "Good news! Your RDU pickup will be served by ".$user_rec['name'].".<br/>".
        "Email Address: ".$user_rec['email']."<br/>".
        "Contact info: ".$user_rec['cell']."<br/>".
        "WeChat ID: ".$user_rec['wechat']."<br/>".
        "QQ: ".$user_rec['qq']."<br/>".
        "<br/>".
        "Regards,<br/>".
        "Webmaster<br/>".
        $this->sitename;

        if ($this->Email($stu_rec['email'],$stu_rec['name'],"Contact Info of Volunteer",$body)){
            return true;
        } else {
            $this->HandleError("Failed sending user welcome email.");
            return false;
        }
    }

    function SendUserPickEmail(&$stu_rec,&$user_rec) {
        $body ="Hello ".$user_rec['name']."<br/><br/>".
        "Please contact ".$stu_rec['name']." as shown below for further arrangements.<br/><br/>".
        "Email Address: ".$stu_rec['email']."<br/>".
        "Contact info: ".$stu_rec['contact']."<br/>".
        "Gender: ".$stu_rec['gender']."<br/>".
        "Date and Time of Arrival: ".$stu_rec['date']." ".$stu_rec['time']."<br/>".
        "Flight no.:".$stu_rec['flight']."<br/>".
        "Number of Passengers: ".$stu_rec['nump']."<br/>".
        "Number of Luggages: ".$stu_rec['numc']."<br/>".
        "Wechat/QQ: ".$stu_rec['wechat']."<br/>".
        "<br/>".
        "Regards,<br/>".
        "Webmaster<br/>".
        $this->sitename;

        if ($this->Email($user_rec['email'],$user_rec['name'],"Full Student Pickup Info",$body)){
            return true;
        } else {
            $this->HandleError("Failed sending user welcome email.");
            return false;
        }
    }
    
    function GetResetPasswordCode($email)
    {
       return substr(md5($email.$this->sitename.$this->rand_key),0,10);
    }
    
    function SendResetPasswordLink($user_rec)
    {
        $link = $this->GetAbsoluteURLFolder().
                '/resetpwd.php?email='.
                urlencode($user_rec['email']).'&code='.
                urlencode($this->GetResetPasswordCode($user_rec['email']));

        $body ="Hello ".$user_rec['name']."<br/><br/>".
        "There was a request to reset your password at ".$this->sitename."<br/>".
        "Please click the link below to complete the request: <br/>".$link."<br/>".
        "Regards,<br/>".
        "Webmaster<br/>".
        $this->sitename;

        if ($this->Email($user_rec['email'],$user_rec['name'],"Your reset password request at ".$this->sitename,$body)){
            return true;
        } else {
            return false;
        }
    }
    
    function SendNewPassword($user_rec, $new_password)
    {
        $body ="Hello ".$user_rec['name']."<br/><br/>".
        "Your password is reset successfully. ".
        "Here is your updated login:<br/>".
        "username:".$user_rec['username']."<br/>".
        "password:$new_password<br/>".
        "<br/>".
        "Login here: ".$this->GetAbsoluteURLFolder()."/login.php<br/>".
        "<br/>".
        "Regards,<br/>".
        "Webmaster<br/>".
        $this->sitename;

        if ($this->Email($user_rec['email'],$user_rec['name'],"Your new password for ".$this->sitename,$body)){
            return true;
        } else {
            return false;
        }

    }    
    
    function ValidateRegistrationSubmission()
    {
        //This is a hidden input field. Humans won't fill this field.
        if(!empty($_POST[$this->GetSpamTrapInputName()]) )
        {
            //The proper error is not given intentionally
            $this->HandleError("Automated submission prevention: case 2 failed");
            return false;
        }
        
        $validator = new FormValidator();
        $validator->addValidation("name","req","Please fill in Name");
        $validator->addValidation("email","email","The input for Email should be a valid email value");
        $validator->addValidation("email","req","Please fill in Email");
        $validator->addValidation("username","req","Please fill in UserName");
        $validator->addValidation("password","req","Please fill in Password");

        
        if(!$validator->ValidateForm())
        {
            $error='';
            $error_hash = $validator->GetErrors();
            foreach($error_hash as $inpname => $inp_err)
            {
                $error .= $inpname.':'.$inp_err."\n";
            }
            $this->HandleError($error);
            return false;
        }        
        return true;
    }
    
    function CollectRegistrationSubmission(&$formvars)
    {
        $formvars['name'] = $this->Sanitize($_POST['name']);
        $formvars['email'] = $this->Sanitize($_POST['email']);
        $formvars['username'] = $this->Sanitize($_POST['username']);
        $formvars['password'] = $this->Sanitize($_POST['password']);
        $formvars['driver'] = $this->Sanitize($_POST['driver']);
        $formvars['office'] = $this->Sanitize($_POST['office']);
        $formvars['gender'] = $this->Sanitize($_POST['gender']);
        $formvars['cell'] = $this->Sanitize($_POST['cell']);
        $formvars['wechat'] = $this->Sanitize($_POST['wechat']);
        $formvars['qq'] = $this->Sanitize($_POST['qq']);
    }
    
    function SendUserConfirmationEmail(&$formvars)
    {
        $confirmcode = $formvars['confirmcode'];
        $email = $formvars['email'];
        
        $confirm_url = $this->GetAbsoluteURLFolder().'/confirmreg.php?email='.$email.'&code='.$confirmcode;
        
        $body ="Hello ".$formvars['name']."<br/><br/>".
        "Thanks for your registration with ".$this->sitename."<br/>".
        "Please click the link below or enter the url in a web browser to confirm your registration.<br/>".
        "$confirm_url<br/>".
        "<br/>".
        "Regards,<br/>".
        "Webmaster<br/>".
        $this->sitename;

        if ($this->Email($formvars['email'],$formvars['name'],"Your registration with ".$this->sitename,$body)){
            return true;
        } else {
            $this->HandleError("Failed sending registration confirmation email.");
            return false;
        }
    }

    function GetAbsoluteURLFolder()
    {
        $scriptFolder = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on')) ? 'https://' : 'http://';
        $scriptFolder .= $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']);
        return $scriptFolder;
    }

    function GetParentURL(){
        $url  = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
        $url .= $_SERVER['HTTP_HOST'];
        $url .= $_SERVER['REQUEST_URI'];

        return dirname($url);
    }
    
    function SendAdminIntimationEmail(&$formvars)
    {       
        $body ="A new user registered at ".$this->sitename."<br/>".
        "Name: ".$formvars['name']."<br/>".
        "Email address: ".$formvars['email']."<br/>".
        "UserName: ".$formvars['username'];
        
        return $this->Email($this->admin_email,"Admin","New Volunteer Registration: ".$formvars['name'],$body);
    }
    
    function SaveToDatabase(&$formvars)
    {
        if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        }
        if(!$this->Ensuretable())
        {
            return false;
        }
        if(!$this->IsFieldUnique($formvars,'email'))
        {
            $this->HandleError("This email is already registered");
            return false;
        }
        
        if(!$this->IsFieldUnique($formvars,'username'))
        {
            $this->HandleError("This UserName is already used. Please try another username");
            return false;
        }        
        if(!$this->InsertIntoDB($formvars))
        {
            $this->HandleError("Inserting to Database failed!");
            return false;
        }
        return true;
    }
    
    function IsFieldUnique($formvars,$fieldname)
    {
        $field_val = $this->SanitizeForSQL($formvars[$fieldname]);
        $qry = "select username from $this->tablename where $fieldname='".$field_val."'";
        $result = mysql_query($qry,$this->connection);   
        if($result && mysql_num_rows($result) > 0)
        {
            return false;
        }
        return true;
    }
    
    function DBLogin()
    {

        $this->connection = mysql_connect($this->db_host,$this->username,$this->pwd);

        if(!$this->connection)
        {   
            $this->HandleDBError("Database Login failed! Please make sure that the DB login credentials provided are correct");
            return false;
        }
        if(!mysql_select_db($this->database, $this->connection))
        {
            $this->HandleDBError('Failed to select database: '.$this->database.' Please make sure that the database name provided is correct');
            return false;
        }
        if(!mysql_query("SET NAMES 'UTF8'",$this->connection))
        {
            $this->HandleDBError('Error setting utf8 encoding');
            return false;
        }
        return true;
    }    
    
    function Ensuretable()
    {
        $result = mysql_query("SHOW COLUMNS FROM $this->tablename");   
        if(!$result || mysql_num_rows($result) <= 0)
        {
            return $this->CreateTable();
        }
        return true;
    }
    
    function CreateTable()
    {
        $qry = "Create Table $this->tablename (".
                "id_user INT NOT NULL AUTO_INCREMENT ,".
                "name VARCHAR( 128 ) NOT NULL ,".
                "email VARCHAR( 64 ) NOT NULL ,".
                "username VARCHAR( 16 ) NOT NULL ,".
                "password VARCHAR( 32 ) NOT NULL ,".
                "driver VARCHAR( 64 ),".
                "office VARCHAR( 64 ),".
                "gender VARCHAR( 16 ),".
                "cell VARCHAR( 16 ) NOT NULL ,".
                "wechat VARCHAR( 32 ),".
                "qq VARCHAR( 32 ),".
                "confirmcode VARCHAR(32) ,".
                "resetcode VARCHAR(32) ,".
                "PRIMARY KEY ( id_user )".
                ")DEFAULT CHARSET=utf8"; 
                
        if(!mysql_query($qry,$this->connection))
        {
            $this->HandleDBError("Error creating the table \nquery was\n $qry");
            return false;
        }
        return true;
    }
    
    function InsertIntoDB(&$formvars)
    {
    
        $confirmcode = $this->MakeConfirmationMd5($formvars['email']);
        
        $formvars['confirmcode'] = $confirmcode;
        
        $insert_query = 'insert into '.$this->tablename.'(
                name,
                email,
                username,
                password,
                driver,
                office,
                gender,
                cell,
                wechat,
                qq,
                confirmcode
                )
                values
                (
                "' . $this->SanitizeForSQL($formvars['name']) . '",
                "' . $this->SanitizeForSQL($formvars['email']) . '",
                "' . $this->SanitizeForSQL($formvars['username']) . '",
                "' . md5($formvars['password']) . '",
                "' . $this->SanitizeForSQL($formvars['driver']) . '",
                "' . $this->SanitizeForSQL($formvars['office']) . '",
                "' . $this->SanitizeForSQL($formvars['gender']) . '",
                "' . $this->SanitizeForSQL($formvars['cell']) . '",
                "' . $this->SanitizeForSQL($formvars['wechat']) . '",
                "' . $this->SanitizeForSQL($formvars['qq']) . '",
                "' . $confirmcode . '"
                )';      
        if(!mysql_query( $insert_query ,$this->connection))
        {
            $this->HandleDBError("Error inserting data to the table\nquery:$insert_query");
            return false;
        }        
        return true;
    }
    function MakeConfirmationMd5($email)
    {
        $randno1 = rand();
        $randno2 = rand();
        return md5($email.$this->rand_key.$randno1.''.$randno2);
    }
    function SanitizeForSQL($str)
    {
        if( function_exists( "mysql_real_escape_string" ) )
        {
              $ret_str = mysql_real_escape_string( $str );
        }
        else
        {
              $ret_str = addslashes( $str );
        }
        return $ret_str;
    }
    
 /*
    Sanitize() function removes any potential threat from the
    data submitted. Prevents email injections or any other hacker attempts.
    if $remove_nl is true, newline chracters are removed from the input.
    */
    function Sanitize($str,$remove_nl=true)
    {
        $str = $this->StripSlashes($str);

        if($remove_nl)
        {
            $injections = array('/(\n+)/i',
                '/(\r+)/i',
                '/(\t+)/i',
                '/(%0A+)/i',
                '/(%0D+)/i',
                '/(%08+)/i',
                '/(%09+)/i'
                );
            $str = preg_replace($injections,'',$str);
        }

        return $str;
    }    
    function StripSlashes($str)
    {
        if(get_magic_quotes_gpc())
        {
            $str = stripslashes($str);
        }
        return $str;
    }    
}
?>