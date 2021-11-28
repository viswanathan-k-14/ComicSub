<?php

$nos = rand(1,2500);


$data_contents = file_get_contents($url);

if($data_contents)
{
$cc = json_decode($data_contents,true);
}
else
{
    echo "NOT READY";
}

?>

<?php include_once 'db.php';

$status = false;

if(isset(($_GET['id'])) &&  isset(($_GET['token'])))
{

$token = $_GET['token'];
$random_token = explode(" ",$token)[0];
$status = password_verify($_GET['id'].'iamarandomstringwithnomeaning',$random_token);

if($status)
{
$id = mysqli_real_escape_string($connection,$_GET['id']);
$id = (int)$id;
mysqli_query($connection,"UPDATE user SET is_verified=1,is_subscribed=1 WHERE id=$id");
}


}

if(isset($_POST['resend']))
{
    $resend_id = $_POST['id'];
    $resend_res = mysqli_query($connection,"SELECT * FROM user WHERE id=$resend_id");
    $email_address = mysqli_fetch_array($resend_res)['email'];
    $random_token = password_hash($resend_id.'iamarandomstringwithnomeaning',PASSWORD_BCRYPT);


    require_once 'config.php';
    require 'vendor/autoload.php';
    $email = new \SendGrid\Mail\Mail(); 
    $email->setFrom("viswanathank1499@gmail.com", "ViswanathanK");
    $email->setSubject("Email Verification");
    $email->addTo($email_address, "viswanathank");
    $email->addContent("text/html","Please confirm your subscription to xkcd comics mailer using the link given: 
    <a href='http://localhost:85/xkcdf/check.php?id=$resend_id&token=$random_token dom_token'>http://localhost:85/xkcdf/check.php?id=$resend_id&token=$random_token</a>");
    // $email->addContent(
    //     "text/html", "<strong>and easy to do anywhere, even with PHP</strong>"
    // );
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
    
}



?>

<?php  if(isset($_POST['unsubscribe'])){
    $subscription = true;
    mysqli_query($connection , "UPDATE user SET is_verified=1 , is_subscribed=0 WHERE id=$id");
    $subscription = false;
}  ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Verification</title>
</head>
<body>
    <?php if($status == true)
    {?>
    <div class="container">
    <h2>Account verification successful ✅ . You will start receiving random comics every 5 minutes.</h2>
    </div>
    <?php }
    else
    {
        ?>
         <h2 style="text-align: center;">We are Unable to verify your account.</h2>
    <h4><a href="index.php">Try using another email ?</a></h4>
<?php if(isset($_GET['id'])){?>
    <form class="form-container" method="post" action="check.php">
<input type="hidden" name="id" value=<?php echo $_GET['id']; ?> /> <?php } ?>
<input type="submit" name="resend" value="RESEND" class="btn" /> 
    </form>
   

    <?php } ?>
   
    
</body>
</html>