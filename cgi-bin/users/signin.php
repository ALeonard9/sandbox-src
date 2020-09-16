<?php
require_once '../composer/vendor/autoload.php';

session_start();
ob_start();

if (isset($_SESSION['userid'])){
  die('You are already signed in.');
}

include '../connectToDB.php';
$CLIENT_ID = getenv('GOOGLE_CLIENT_ID');
$CLIENT_SECRET = getenv('GOOGLE_CLIENT_SECRET');
$REDIRECT_URI = getenv('GOOGLE_REDIRECT_URL');
$PROTOCOL = isset($_SERVER['HTTPS']) ? 'https':'http';

$client = new Google_Client();
$client->setClientId($CLIENT_ID);
$client->setClientSecret($CLIENT_SECRET);
$client->setRedirectUri($REDIRECT_URI);
$client->setScopes('email');
$plus = new Google_Service_Plus($client);

if (isset($_GET['code'])) {
  $client->authenticate($_GET['code']);
  $_SESSION['access_token'] = $client->getAccessToken();
  $redirect = $PROTOCOL . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
  header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
}
if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
  $client->setAccessToken($_SESSION['access_token']);
  $me = $plus->people->get('me');
  // Get User data
  $id = $me['id'];
  $name =  $me['displayName'];
  $email =  $me['emails'][0]['value'];
  $profile_image_url = $me['image']['url'];
  $cover_image_url = $me['cover']['coverPhoto']['url'];
  $profile_url = $me['url'];

      $sql = "SELECT * FROM `orion`.`users` WHERE email = '".$email."'";
      $query = $db->query($sql);
      $row_count = $query->rowCount();
        $results = $query->fetch(PDO::FETCH_ASSOC);
      if ($row_count>0){
        $_SESSION['username']=$results['display_name'];
        $_SESSION['email']= $results['email'];
         if ($_SESSION['username']==''){
           $_SESSION['username']=$_SESSION['email'];
         }
        $_SESSION['usergroup']=$results['user_group'];
        $_SESSION['userid']=$results['id'];
          } else {
        $sql1 = "INSERT INTO `orion`.`users` (`display_name`, `user_group`, `email`) VALUES ('".$name."', 'User', '".$email."')";
        $query = $db->query($sql1);
        $sql2 = "SELECT * FROM `orion`.`users` WHERE email = '".$email."'";
        $query = $db->query($sql2);
          $results = $query->fetch(PDO::FETCH_ASSOC);
          $_SESSION['username']=$results['display_name'];
          $_SESSION['email'] = $results['email'];
          if ($_SESSION['username']==''){
            $_SESSION['username']=$_SESSION['email'];
          }
          $_SESSION['usergroup']=$results['user_group'];
          $_SESSION['userid']=$results['id'];
      }

  if(isset($_SESSION['url']))
    $url = $_SESSION['url']; // holds url for last page visited.
  else
    $url = "/dashboard.php";
  header("Location: " . $PROTOCOL . "://".$_SERVER['HTTP_HOST'].$url);
  exit;

} else {
  // get the login url
  $authUrl = $client->createAuthUrl();
}

echo "<!DOCTYPE html>
<html lang='en'>
<head>
        <title id='pageTitle'>LeoNine Studios</title>";
include('../header.php');
echo "</head><body><div class='container'>";
include('../navigation.php');

echo "<div class='col-md-3'></div><div class='col-md-6'>
<div class='text-center'>
<a class='login' href='" . $authUrl . "''><img src='../images/signin_button.png' height='100px'/></a>
<h2> - OR - </h2></div>
<form class='form-signin' action='login.php' method='POST'>
        <h2 class='form-signin-heading'>Please sign in</h2>
        <label for='inputEmail' class='sr-only'>Email address</label>
        <input name='username' type='email' id='inputEmail' class='form-control' placeholder='Email address' required autofocus>
        <label for='inputPassword' class='sr-only'>Password</label>
        <input name='password' type='password' id='inputPassword' class='form-control' placeholder='Password' required>
        <br>
        <button class='btn btn-lg btn-inverse btn-block' type='submit'><span class='glyphicon glyphicon-user'></span> Sign in</button>
</form>
</br><button class='btn btn-lg btn-inverse btn-block' onclick=location.href='createprofile.php'><span class='glyphicon glyphicon-pencil'></span> Sign up</button>
  </div>";


include('../footer.php');
echo "</div></body></html>";

?>