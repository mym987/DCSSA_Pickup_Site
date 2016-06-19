<?PHP

require_once("PHPMailerAutoload.php");
require_once("formvalidator.php");
$student = new Student("conf_student.ini");

class Student
{
    
    var $connection;
    
    var $error_message;
    
    //-----Initialization -------
    function Student($conf_ini)
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

    function CheckUser(&$info){
        if(!isset($_POST['submit']))
        {
           return false;
        }
        
        if(!$this->ValidateCheckSubmission())
        {
            return false;
        }

        $formvars = array();
        
        $this->CollectCheckSubmission($formvars);

        return $this->GetStudentByEmailAndFlight($info, $formvars);
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
    
    function UserFullName()
    {
        return isset($_SESSION['name_of_user'])?$_SESSION['name_of_user']:'';
    }
    
    function UserEmail()
    {
        return isset($_SESSION['email_of_user'])?$_SESSION['email_of_user']:'';
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

    function GetParentURL(){
        $url  = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
        $url .= $_SERVER['HTTP_HOST'];
        $url .= $_SERVER['REQUEST_URI'];

        return dirname($url);
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
    
    function HandleError($err)
    {
        $this->error_message .= $err;
    }
    
    function HandleDBError($err)
    {
        $this->HandleError($err."<br/> mysqlerror:".mysql_error());
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

    function GetStudentByEmailAndFlight(&$info, &$formvars) {
        if(!$this->DBLogin())
        {
            $this->HandleError("Database login failed!");
            return false;
        }   
        $email = $this->SanitizeForSQL($formvars['email']);
        $flight = $this->SanitizeForSQL($formvars['flight']);

        $qry = "Select * from $this->tablename where email='$email' and flight like '%$flight%'";
        $result = mysql_query($qry,$this->connection);  

        if ($row = mysql_fetch_array($result)){
            $info = $row;
            return true;
        } else {
            return false;
        }
    }
    
    function SendUserWelcomeEmail(&$user_rec)
    {
        $body ="Hello ".$user_rec['name']."<br/><br/>".
        "Thank you! Your registration  with ".$this->sitename." is completed.<br/>".
        "Please wait for our volunteers to contact you.<br/>".
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

        if ($this->Email($this->admin_email,"Admin","Registration Completed: ".$user_rec['name'],$body)){
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
        $validator->addValidation("name","req","请填写姓名");
        $validator->addValidation("email","req","请填写Email");
        $validator->addValidation("date","req","请填写抵达日期");
        $validator->addValidation("time","req","请填写抵达时间");
        $validator->addValidation("flight","req","请填写航班号");
        $validator->addValidation("nump","req","请填写需接机人数");
        $validator->addValidation("numc","req","请填写行李信息");
        $validator->addValidation("contact","req","请填写电话号码");
        $validator->addValidation("wechat","req","请填写微信/QQ（至少一个）");
        $validator->addValidation("email","duke_email","请提供一个以 @duke.edu 结尾的邮箱");

        
        if(!$validator->ValidateForm())
        {
            $error='';
            $error_hash = $validator->GetErrors();
            foreach($error_hash as $inpname => $inp_err)
            {
                //$error .= $inpname.':'.$inp_err."\n";
                $error .= $inp_err."\n";
            }
            $this->HandleError($error);
            return false;
        }        
        return true;
    }
    
    function CollectRegistrationSubmission(&$formvars)
    {
        //$formvars['ts'] = $_SERVER['REQUEST_TIME'];
        $formvars['name'] = $this->Sanitize($_POST['name']);
        $formvars['email'] = $this->Sanitize($_POST['email']);
        $formvars['gender'] = $this->Sanitize($_POST['gender']);
        $formvars['major'] = $this->Sanitize($_POST['major']);
        $formvars['comp'] = $this->Sanitize($_POST['comp']);
        $formvars['date'] = $this->Sanitize($_POST['date']);
        $formvars['time'] = $this->Sanitize($_POST['time']);
        $formvars['flight'] = $this->Sanitize($_POST['flight']);
        $formvars['nump'] = $this->Sanitize($_POST['nump']);
        $formvars['numc'] = $this->Sanitize($_POST['numc']);
        $formvars['contact'] = $this->Sanitize($_POST['contact']);
        $formvars['wechat'] = $this->Sanitize($_POST['wechat']);
    }

    function ValidateCheckSubmission()
    {
        //This is a hidden input field. Humans won't fill this field.
        if(!empty($_POST[$this->GetSpamTrapInputName()]) )
        {
            //The proper error is not given intentionally
            $this->HandleError("Automated submission prevention: case 2 failed");
            return false;
        }
        
        $validator = new FormValidator();
        $validator->addValidation("email","req","请填写Email");
        $validator->addValidation("flight","req","请填写航班号");
        $validator->addValidation("flight","num","航班号仅需数字部分");
        $validator->addValidation("email","duke_email","请提供一个以 @duke.edu 结尾的邮箱");

        
        if(!$validator->ValidateForm())
        {
            $error='';
            $error_hash = $validator->GetErrors();
            foreach($error_hash as $inpname => $inp_err)
            {
                //$error .= $inpname.':'.$inp_err."\n";
                $error .= $inp_err."\n";
            }
            $this->HandleError($error);
            return false;
        }        
        return true;
    }
    
    function CollectCheckSubmission(&$formvars)
    {
        $formvars['email'] = $this->Sanitize($_POST['email']);
        $formvars['flight'] = $this->Sanitize($_POST['flight']);
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
    
    function SendAdminIntimationEmail(&$formvars)
    {       
        $body ="A new user registered at ".$this->sitename."<br/>".
        "Name: ".$formvars['name']."<br/>".
        "Email address: ".$formvars['email']."<br/>".
        "UserName: ".$formvars['username'];
        
        return $this->Email($this->admin_email,"Admin","New registration: ".$formvars['name'],$body);
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
        $qry = "select $fieldname from $this->tablename where $fieldname='".$field_val."'";
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
                "ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP ,".
                "name VARCHAR( 128 ) NOT NULL ,".
                "email VARCHAR( 64 ) NOT NULL ,".
                "gender VARCHAR( 16 ),".
                "major VARCHAR( 128 ) ,".
                "comp VARCHAR( 128 ) ,".
                "date VARCHAR( 64 ) ,".
                "time VARCHAR( 64 ) ,".
                "flight VARCHAR( 64 ) ,".
                "nump VARCHAR( 64 ) ,".
                "numc VARCHAR( 64 ) ,".
                "contact VARCHAR( 64 ) NOT NULL ,".
                "wechat VARCHAR( 32 ),".
                "volunteer VARCHAR( 128 ),".
                "confirmcode VARCHAR(32) ,".
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
        
        //date('Y-m-d H:i:s',$formvars['ts'])
        $insert_query = 'insert into '.$this->tablename.'(
                name,
                email,
                gender,
                major,
                comp,
                date,
                time,
                flight,
                nump,
                numc,
                contact,
                wechat,
                confirmcode
                )
                values
                (
                "' . $this->SanitizeForSQL($formvars['name']) . '",
                "' . $this->SanitizeForSQL($formvars['email']) . '",
                "' . $this->SanitizeForSQL($formvars['gender']) . '",
                "' . $this->SanitizeForSQL($formvars['major']) . '",
                "' . $this->SanitizeForSQL($formvars['comp']) . '",
                "' . $this->SanitizeForSQL($formvars['date']) . '",
                "' . $this->SanitizeForSQL($formvars['time']) . '",
                "' . $this->SanitizeForSQL($formvars['flight']) . '",
                "' . $this->SanitizeForSQL($formvars['nump']) . '",
                "' . $this->SanitizeForSQL($formvars['numc']) . '",
                "' . $this->SanitizeForSQL($formvars['contact']) . '",
                "' . $this->SanitizeForSQL($formvars['wechat']) . '",
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