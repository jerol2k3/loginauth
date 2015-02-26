<?php	
	session_start();
	date_default_timezone_set("Asia/Hong_Kong");
	include "functions.php";
    include "User.php";

    $user = new User();
	
    if(isset($_POST['email']) && isset($_POST['password'])){
    	$logindatetime = date("Y-m-d H:i:s", time());
		$ipaddress = get_client_ip();		
		$logininfo = $user->getlogininfo($_POST['email']);		
		$loginattempt = $logininfo[0]['loginattempt'];
		$previouslogindatetime = $logininfo[0]['logindatetime'];
		$previousipaddress = $logininfo[0]['ipaddress'];
		$block = FALSE;
		$datetimediff = strtotime($logindatetime) - strtotime($previouslogindatetime);
		$datetimediff = $datetimediff / 3600;
		
		if($loginattempt > 2 && $datetimediff < 1){
			$block = TRUE;
			if($user->error != ""){
				$user->error .= "<br />";
			}
			$user->error .= "Your account is block for " . strval(round((1 - $datetimediff) * 60, 0)) . " minutes."; 
		}
		elseif($user->validate($_POST['email'], $_POST['password'])){
    		if($user->exists($_POST['email'])){    			
    			$user->update($_POST['email'], $_POST['password'], $logindatetime, $ipaddress, 0);	
    		}
			else{
				$user->register($_POST['email'], $_POST['password'], $logindatetime, $ipaddress, 0);
			}    		
    		$rows = $user->login($_POST['email'], $_POST['password']);
			if(count($rows) > 0){
				$_SESSION['email'] = $_POST['email'];
				$_SESSION['logindatetime'] = $logindatetime;
				$_SESSION['ipaddress'] = $ipaddress;
				$user->update($_POST['email'], $_POST['password'], $logindatetime, $ipaddress, 0);
				header("Location: index.php");
			}
        }  
		else{
			if($ipaddress == $previousipaddress){
				$loginattempt += 1;	
			}			
			if(count($logininfo) > 0){
				$user->update($_POST['email'], $_POST['password'], $logindatetime, $ipaddress, $loginattempt);	
			}
			else{
				$user->register($_POST['email'], $_POST['password'], $logindatetime, $ipaddress, 1);
			}			
		}  	
	}
    
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="favicon.ico">

    <title>Login Authentication</title>

    <!-- Bootstrap core CSS -->
    <link href="bootstrap-3.3.2/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/signin.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
    <script src="../../assets/js/ie-emulation-modes-warning.js"></script>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>

    <div class="container">
    	
    	<form id="form-signin" class="form-signin" method="post">
        <h2 class="form-signin-heading">Please sign in</h2>
        <label for="inputEmail" class="sr-only">Email address</label>
        <input type="text" name="email" id="inputEmail" class="form-control" placeholder="Email address" required autofocus />
        <label for="inputPassword" class="sr-only">Password</label>
        <input type="password" name="password" id="inputPassword" class="form-control" placeholder="Password" required />        
        <div class="checkbox">
          <label>
            <input type="checkbox" value="remember-me" /> Remember me
          </label>
        </div>
        <label class="form-signin-error"><?php echo $user->error; ?></label>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
      </form>

      

    </div> <!-- /container -->


    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="../../assets/js/ie10-viewport-bug-workaround.js"></script>
  </body>
</html>