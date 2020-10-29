<?php


$sessionId = $_POST["sessionId"];
$phoneNumber = $_POST["phoneNumber"];
$serviceCode = $_POST["serviceCode"];
$text = $_POST["text"];
$account = "";

$DBH = null;
try {
    $dns = "sqlite:./db.sqlite";
    $DBH = new PDO($dns);

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
        $response = "CON Welcome back {$user["name"]} to SayrunjahApp \n";
        $response .= "Select service \n 1. Buy Airtime \n 2. Buy Data \n 3. Delete Account";
    } elseif ($level == 1) {
        if($userResponse == "3") {
            $response = deleteUser($DBH, $phoneNumber);
        }else {
            $response = "CON Enter amount";
        }
    } elseif ($level == 2) {

            $type = $textArray[0] == "1" ? "Airtime" : "Data";
            $amount = $textArray[1];
            $response = "END Your have bought $type of $amount";

    }

} else {
    if ($level == 0) {
        $response = "CON Hi welcome to SayrunjahApp\n";
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
        $SQL = $DBH->prepare("INSERT INTO users (name, email, phone) VALUES('$name','$email','$phone')");
        //execute insert query
        $SQL->execute();
        if ($SQL->errorCode() == 0) {
            return "END Thank you \n Email: $email \n Name: $name \n Phone: $phone";
        } else {
            //var_dump($sth->errorInfo());
            return "END Dear customer, the network is experiencing technical problems and your request was not processed. Please try again later.";
        }
    }
}

function deleteUser($DBH, $phone)
{
    if($DBH) {
        $SQL = $DBH->prepare("DELETE FROM users WHERE phone=$phone");
        $SQL->execute();

        if ($SQL->errorCode() == 0) {
            return "END Thank you, account delete.";
        } else {
            //var_dump($sth->errorInfo());
            return "END Dear customer, the network is experiencing technical problems and your request was not processed. Please try again later.";
        }

    }
}















