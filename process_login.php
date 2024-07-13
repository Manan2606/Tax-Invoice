<?php
require_once 'db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor\phpmailer\phpmailer\src\Exception.php';
require 'vendor\phpmailer\phpmailer\src\PHPMailer.php';
require 'vendor\phpmailer\phpmailer\src\SMTP.php';

$flag = $_POST['flag'];
$mobile_number = $_POST['mobile_number'];

if ($flag == "1") {
    $select_query = "SELECT mobile_number FROM user_details";
    $result = $con->query($select_query);

    $mobileNumbers = array();

    if ($result->num_rows > 0) {
        // Fetch all mobile numbers from the database
        while ($row = $result->fetch_assoc()) {
            $mobileNumbers[] = $row['mobile_number'];
        }

        if (in_array($mobile_number, $mobileNumbers)) {
            // Mobile number exists in the database
            print(json_encode(array('status' => true, 'response' => 'exist')));
        } else {
            // Mobile number doesn't exist in the database
            print(json_encode(array('status' => false, 'response' => 'not exist')));
        }
    } else {
        print(json_encode(array('status' => false, 'response' => 'not exist')));
    }
}

if ($flag == "2") {

    $select_email = "SELECT email FROM user_details WHERE mobile_number = $mobile_number";
    $result1 = $con->query($select_email);

    if ($result1) {
        if ($result1->num_rows > 0) {
            $row1 = mysqli_fetch_assoc($result1);
            $email = $row1['email'];

            // Generate a random OTP
            $otp = rand(1000, 9999);

            echo "OTP " . $otp;

            $insert_otp = "UPDATE user_details SET `otp` = '$otp' WHERE email = '$email'";
            $result2 = $con->query($insert_otp);

            // Recipient email address
            $recipient = $email;

            // Create a PHPMailer instance
            $mail = new PHPMailer(true);

            try {
                // Server settings
                $mail->SMTPDebug = SMTP::DEBUG_SERVER;
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // Your SMTP server
                $mail->SMTPAuth = true;
                $mail->Username = 'shahmanan300@gmail.com';
                $mail->Password = 'pvaxtgwzbnrzdlae';
                $mail->SMTPSecure = 'tls'; // Enable TLS encryption, 'ssl' also accepted
                $mail->Port = 587; // TCP port to connect to    

                // Sender information
                $mail->setFrom('shahmanan300@gmail.com', 'Manan');
                $mail->addAddress($recipient);

                // Email content
                $mail->isHTML(true);
                $mail->Subject = 'Your OTP';
                $mail->Body = "Your OTP is: $otp";

                // Send the email
                $mail->send();
                echo "Success";
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        }
    } else {
        echo "Email is not registerd on this mobile number";
    }
}

if ($flag == "3") {

    $select_otp = "SELECT otp FROM user_details WHERE mobile_number = '$mobile_number'";
    $result3 = $con->query($select_otp);

    if ($result3) {
        if ($result3->num_rows > 0) {
            $row2 = mysqli_fetch_assoc($result3);
            $send_otp = $row2['otp'];
        }
    }
    print(json_encode(array('status' => true, 'OTP' => $send_otp)));
}

$con->close();
?>