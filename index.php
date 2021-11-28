<?php include_once 'db.php'; ?>

<?php

$nos = rand(1,2500);
$url = "https://xkcd.com/".$nos."/info.0.json";

$data_contents = file_get_contents($url);

if($data_contents)
{
$cc = json_decode($data_contents,true);
$cc = $cc["img"];
}
else
{
    echo "NOT READY";
}

?>

<?php 

if(isset($_POST['submit']))
{
    $email = $_POST['email'];
    $sanitizedEmail = filter_var($email, FILTER_SANITIZE_EMAIL);

   $query = "SELECT * FROM user WHERE email = '$email'";
   $result = mysqli_query($connection,$query);
   
   
   
   if(mysqli_num_rows($result)>0)
    {
        echo "<script type='text/javascript'>alert('user with this email already exists');window.location.href='index.php';</script>";  
    }
    
    else
    {
        
        $query="INSERT INTO user (email) VALUES('$sanitizedEmail')";
        $result = mysqli_query($connection,$query);
        $id = mysqli_insert_id($connection);
        $random_token = password_hash($id.'iamarandomstringwithnomeaning',PASSWORD_BCRYPT);


        require_once 'config.php';
        require 'vendor/autoload.php';
        $email = new \SendGrid\Mail\Mail(); 
        $email->setFrom("viswanathank1499@gmail.com", "ViswanathanK");
        $email->setSubject("Email Verification");
        $email->addTo($sanitizedEmail, "viswanathank");
        $email->addContent("text/html","Please confirm your subscription to xkcd comics mailer using the link given: 
        <a href='http://localhost:85/xkcdf/check.php?id=$id&token=$random_token dom_token'>http://localhost:85/xkcdf/check.php?id=$id&token=$random_token</a><br/><img src='cid:logo-cid'/>");
        $file_encoded = base64_encode(file_get_contents($cc));
       

        $email->addAttachment($file_encoded, 'image/jpeg', '' , 'inline' , 'logo-cid'); 
        $email->addAttachment(
           $file_encoded,
           "image/jpeg",
           "comic.jpg",
           "attachment"
        );
       
        
        $sendgrid = new \SendGrid(SENDGRID_API_KEY);
        try {
            $response = $sendgrid->send($email);
           
                echo "<script type='text/javascript'>alert('A verification link is successfully sent');window.location.href='index.php';</script>";
            
            // print $response->statusCode() . "\n";
            // print_r($response->headers());
            // print $response->body() . "\n";
        } catch (Exception $e) {
            // echo 'Caught exception: '. $e->getMessage() ."\n";
        }
        $_POST = array();
}
   
    // $connection->close();
  
}

?> 


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>XKCD COMIC MAILER</title>
</head>
<body>
    
<h3 class="head">XKCD COMIC MAILER</h3>

<div></div>
        <form method="post" class="container">
    <input type="email" name="email" placeholder="email" required/>
    <input type="submit" name="submit" value="VERIFY"  class="btn"/>
        </form> 
</body>
</html>

