<?php

use DevCoder\DotEnv;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require 'dotenv.php';
require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';


(new DotEnv(dirname(__FILE__, 3) . "/.env"))->load();

include("functions.php");

if (isset($_POST['checkDB'])) {

    $values = [
        //SETTINGS::VALUE => REQUEST-VALUE (coming from the html-form)
        "DB_HOST" => "databasehost",
        "DB_DATABASE" => "database",
        "DB_USERNAME" => "databaseuser",
        "DB_PASSWORD" => "databaseuserpass",
        "DB_PORT" => "databaseport",
        "DB_CONNECTION" => "databasedriver"
    ];


    $db = new mysqli($_POST["databasehost"], $_POST["databaseuser"], $_POST["databaseuserpass"], $_POST["database"], $_POST["databaseport"]);
    if ($db->connect_error) {
        header("LOCATION: index.php?step=2&message=Could not connect to the Database");
        die();
    }

    foreach ($values as $key => $value) {
        $param = $_POST[$value];
        if ($key=="DB_PASSWORD"){
           $param ='"' . $_POST[$value] . '"';
        }
        setEnvironmentValue($key, $param);
    }



    header("LOCATION: index.php?step=2.5");

}


if (isset($_POST['checkGeneral'])) {


    $appname = '"' . $_POST['name'] . '"';
    $appurl = $_POST['url'];

    if (substr($appurl, -1) === "/") {
        $appurl = substr_replace($appurl, "", -1);
    }


    setEnvironmentValue("APP_NAME", $appname);
    setEnvironmentValue("APP_URL", $url);

    header("LOCATION: index.php?step=4");

}

if (isset($_POST['installComposer'])) {
    $logs = "";
       $logs .= run_console(putenv('COMPOSER_HOME=' . dirname(__FILE__, 3) . '/vendor/bin/composer'));
        $logs .= run_console('composer install --no-dev --optimize-autoloader');
        $logs .= run_console('php artisan key:generate --force');
        $logs .= run_console('php artisan storage:link');

        $logsfile = fopen("logs.txt", "w") or die("Unable to open file!");
        fwrite($logsfile, $logs);
        fclose($logsfile);

        if(str_contains(getEnvironmentValue("APP_KEY"), "base64")){
                  header("LOCATION: index.php?step=3");
        }else{
                header("LOCATION: index.php?step=2.5&message=There was an error. Please check install/logs.txt !");
              }

}

if (isset($_POST['checkSMTP'])) {
    try {
        $mail = new PHPMailer(true);

        //Server settings
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host = $_POST['host'];                    // Set the SMTP server to send through
        $mail->SMTPAuth = true;                                   // Enable SMTP authentication
        $mail->Username = $_POST['user'];                     // SMTP username
        $mail->Password = $_POST['pass'];                               // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        $mail->Port = $_POST['port'];                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS`

        //Recipients
        $mail->setFrom($_POST['user'], $_POST['user']);
        $mail->addAddress($_POST['user'], $_POST['user']);     // Add a recipient

        // Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = 'It Worked!';
        $mail->Body = "Your E-Mail Settings are correct!";


        $mail->send();
    } catch (Exception $e) {
        header("LOCATION: index.php?step=4&message=Something wasnt right when sending the E-Mail!");
        die();
    }

    $values = [
        //SETTINGS::VALUE => REQUEST-VALUE (coming from the html-form)
        "MAIL_MAILER" => "method",
        "MAIL_HOST" => "host",
        "MAIL_PORT" => "port",
        "MAIL_USERNAME" => "user",
        "MAIL_PASSWORD" => "pass",
        "MAIL_ENCRYPTION" => "encryption",
        "MAIL_FROM_ADDRESS" => "user"
    ];

    foreach ($values as $key => $value) {
        $param = $_POST[$value];
        setEnvironmentValue($key, $param);
    }
    header("LOCATION: index.php?step=5");


}

if (isset($_POST['checkPtero'])) {
    $url = $_POST['url'];
    $key = $_POST['key'];

    if (substr($url, -1) === "/") {
        $url = substr_replace($url, "", -1);
    }


    $pteroURL = $url . "/api/application/users";
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $pteroURL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Accept: application/json",
        "Content-Type: application/json",
        "Authorization: Bearer " . $key
    ));
    $response = curl_exec($ch);
    $result = json_decode($response, true);
    curl_close($ch); // Close the connection


    if (!is_array($result) or in_array($result["errors"][0]["code"], $result)) {
        header("LOCATION: index.php?step=5&message=Couldnt connect to Pterodactyl. Make sure your API key has all read and write permissions!");
        die();
    } else {
        $query1 = "UPDATE `dashboard`.`settings` SET `value` = '$url' WHERE (`key` = 'SETTINGS::SYSTEM:PTERODACTYL:URL')";
        $query2 = "UPDATE `dashboard`.`settings` SET `value` = '$key' WHERE (`key` = 'SETTINGS::SYSTEM:PTERODACTYL:TOKEN')";

        $db = new mysqli(getEnvironmentValue("DB_HOST"), getEnvironmentValue("DB_USERNAME"), getEnvironmentValue("DB_PASSWORD"), getEnvironmentValue("DB_DATABASE"), getEnvironmentValue("DB_PORT"));
        if ($db->connect_error) {
            header("LOCATION: index.php?step=5&message=Could not connect to the Database");
            die();
        }

        if ($db->query($query1) && $db->query($query2)) {
            header("LOCATION: index.php?step=6");
        } else {
            header("LOCATION: index.php?step=5&message=Something went wrong when communicating with the Database!");
        }
    }


}

if (isset($_POST['createUser'])) {
    $db = new mysqli(getEnvironmentValue("DB_HOST"), getEnvironmentValue("DB_USERNAME"), getEnvironmentValue("DB_PASSWORD"), getEnvironmentValue("DB_DATABASE"), getEnvironmentValue("DB_PORT"));
    if ($db->connect_error) {
        header("LOCATION: index.php?step=6&message=Could not connect to the Database");
        die();
    }


    $name = $_POST['user'];
    $mail = $_POST['email'];
    $pteroID = $_POST['pteroID'];
    $pass = $_POST['pass'];
    $repass = $_POST['repass'];
    $key = $db->query("SELECT `value` FROM dashboard.settings WHERE `key` = 'SETTINGS::SYSTEM:PTERODACTYL:TOKEN'")->fetch_assoc();
    $pterobaseurl = $db->query("SELECT `value` FROM dashboard.settings WHERE `key` = 'SETTINGS::SYSTEM:PTERODACTYL:URL'")->fetch_assoc();


    $pteroURL = $pterobaseurl["value"] . "/api/application/users/" . $pteroID;
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $pteroURL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Accept: application/json",
        "Content-Type: application/json",
        "Authorization: Bearer " . $key["value"]
    ));
    $response = curl_exec($ch);
    $result = json_decode($response, true);
    curl_close($ch); // Close the connection


    if ($result["attributes"]["email"] !== $mail) {
        header("LOCATION: index.php?step=6&message=The Email is not the same as the one used on Pterodactyl");
        die();
    }
    if ($pass !== $repass) {
        header("LOCATION: index.php?step=6&message=The Passwords did not match!");
        die();
    }

    $pass = password_hash($pass, PASSWORD_DEFAULT);

    $pteroURL = $pterobaseurl["value"] . "/api/application/users/" . $pteroID;
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $pteroURL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Accept: application/json",
        "Content-Type: application/json",
        "Authorization: Bearer " . $key["value"]
    ));
    curl_setopt($ch, CURLOPT_POSTFIELDS, array(
        "email" => $mail,
        "username" => $name,
        "first_name" => $name,
        "last_name" => $name,
        "password" => $pass
    ));
    $response = curl_exec($ch);
    $result = json_decode($response, true);
    curl_close($ch); // Close the connection

    if (!is_array($result) or in_array($result["errors"][0]["code"], $result)) {
        header("LOCATION: index.php?step=5&message=Couldnt connect to Pterodactyl. Make sure your API key has all read and write permissions!");
        die();
    }


    $query1 = "INSERT INTO `dashboard`.`users` (`name`, `role`, `credits`, `server_limit`, `pterodactyl_id`, `email`, `password`) VALUES ('$name', 'admin', '250', '1', '$pteroID', '$mail', '$pass')";


    if ($db->query($query1)) {
        header("LOCATION: index.php?step=7");
    } else {
        header("LOCATION: index.php?step=6&message=Something went wrong when communicating with the Database!");
    }


}


?>
