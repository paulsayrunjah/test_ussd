<?php


$sessionId = $_POST["sessionId"];
$phoneNumber = $_POST["phoneNumber"];
$serviceCode = $_POST["serviceCode"];
$text = $_POST["text"];
$account = "";

$DBH = null;
try {
    $host = "localhost";
    $dbname = "ussd";
    $user = "root";
    # MySQL with PDO_MYSQL
    $DBH = new PDO("mysql:host=$host;dbname=$dbname", $user, "");

} catch (PDOException $e) {
    echo $e->getMessage();
}

$level = 0;
//0 welcome screen
//1 choose signup type
//2 display signup type

$textArray = explode('*', $text);
$level = $text == "" ? 0 : count($textArray);
$userResponse = trim(end($textArray));

$user = checkIfNumberRegistered($DBH, $phoneNumber);

if ($user) {

    if ($level == 0) {
        $response = "CON Welcome back {$user["name"]} \n";
        $response .= "Select service \n 1. Buy Airtime \n 2. Buy Data";
    } elseif ($level == 1) {
        $response = "CON Enter amount";
    } elseif ($level == 2) {
        $type = $textArray[0] == "1" ? "Airtime" : "Data";
        $amount = $textArray[1];
        $response = "END Your have bought $type of $amount";
    }

} else {
    if ($level == 0) {
        $response = "CON Hi welcome \n";
        $response .= "1. Enter 1 to register";
    } else if ($level == 1) {
        $response = "CON Enter email \n";
    } else if ($level == 2) {
        $response = "CON Enter name";
    } else if ($level == 3) {
        $email = $textArray[1];
        $name = $textArray[2];
        $response = saveUser($DBH, $phoneNumber, $name, $email);
    }
}


header('Content-type: text/plain');
echo $response;

function checkIfNumberRegistered($DBH, $phone)
{
    if ($DBH) {
        $stmt = $DBH->prepare("SELECT * FROM users WHERE phone=:phone");
        $stmt->execute(['phone' => $phone]);
        $user = $stmt->fetch();
        return $user;
    }
}

function saveUser($DBH, $phone, $name, $email)
{

    if ($DBH) {

        // build sql statement
        $sth = $DBH->prepare("INSERT INTO users (name, email, phone) VALUES('$name','$email','$phone')");
        //execute insert query
        $sth->execute();
        if ($sth->errorCode() == 0) {
            return "END Thank you \n Email: $email \n Name: $name \n Phone: $phone";
        } else {
            //var_dump($sth->errorInfo());
            return "END Dear customer, the network is experiencing technical problems and your request was not processed. Please try again later.";
        }
    }
}















