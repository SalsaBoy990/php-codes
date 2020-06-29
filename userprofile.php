<?php
/**
 * A simple class implementation of an user profile
 * Creating uuids, passwords, hashes
 * Exporting data to csv, and to json
 */

/**
 * The phunction PHP framework (http://sourceforge.net/projects/phunction/) uses
 * the following function to generate valid version 4 UUIDs:
 * by Alix Axel
 * @see https://www.php.net/manual/en/function.com-create-guid
 */
function GUID()
{
    if (function_exists('com_create_guid') === true) {
        return trim(com_create_guid(), '{}');
    }

    return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}


/**
 * @param $filename with no extension like 'result'
 * @param $dataArray: array containing UserProfile class instances
 * Author: András Gulácsi
 */
function saveDataToCSV($dataArray, $filename)
{
    // add extension
    $filename = $filename . '.csv';
    try {
        if (($result = fopen($filename, 'x')) === false) {
            throw new Exception('Cannot create the file because it already exists. The existing file will be used and overwritten!<br />');
        }
    } catch (Exception $e) {
        echo $e->getMessage();
        // file already exists, so use write mode
        $result = fopen($filename, 'w') or die('Error occured when trying to use the file.');
    }

    fwrite($result, iconv('utf-8', 'ISO-8859-2', "Last name; First name; Age; Status; Password; UUID\r\n"));
    foreach ($dataArray as $item) {
        $tmp = $item->getAllPropsCSV(';') . "\r\n";
        fwrite($result, iconv('utf-8', 'ISO-8859-2', $tmp));
    }
    fclose($result);

    echo 'OK. Data saved to file.<br />';
    echo '<a href="' . $filename . '" download>Download ' .  $filename . '</a>';
    return;
}

function saveDataToJSON($dataArray, $filename)
{
    // add extension
    $filename = $filename . '.json';
    $json = "[";
    foreach ($dataArray as $item) {
        $json .= json_encode($item->jsonSerialize()) . ', ';
    }
    // remove trailing comma at the end
    $json = substr($json, 0, (strlen($json) - 2));
    $json .= "]";

    // print for testing purposes
    echo '<pre>';
    print_r($json);
    echo '</pre>';

    file_put_contents($filename, $json);
    echo 'OK. Data saved to a json file. <br />';
    echo '<a href="' . $filename . '" download>Download ' .  $filename . '</a>';

    // decode and print for testing purposes
    $jsonDecoded = json_decode($json);
    echo '<br><br />Json decoded:<br />';
    echo '<pre>';
    print_r($jsonDecoded);
    echo '</pre>';
}



class FirstNameError extends Exception
{
}
class LastNameError extends Exception
{
}
class AgeError extends Exception
{
}
class StatusError extends Exception
{
}
class EmptyError extends ErrorException
{
}

//                     0              1             2           3
$statusArr = array('single', 'in relationship', 'married', 'divorced');

// foreach($statusArr as $status) {
//     echo '<strong>Status array</strong<br />' . $status . '<br/>;
// }

$errorStack = array();

// Implements a simple user profile
// Author: András Gulácsi
class UserProfile implements JsonSerializable
{

    private $firstName;
    private $lastName;
    private $age;

    // single, married, divorced etc.
    private $status;

    // unique identifier for the user
    private $uuid;

    // password, not hashed, never use it in production, it is just for test
    private $pwd;

    // password hashed with PASSWORD_DEFAULT algorithm
    // @see: https://www.php.net/manual/en/function.password-hash.php
    private $password;

    // max length of generated password
    private const MAX_LENGTH_PASSWORD = 60;


    function __construct(
        $firstName = "",
        $lastName = "",
        $age = null,
        $status = ""
    ) {
        try {

            // empty arg checks
            if ($this->isEmpty($firstName)) {
                throw new EmptyError('firstname is missing');
            }
            if ($this->isEmpty($lastName)) {
                throw new EmptyError('lastname is missing');
            }
            if ($this->isEmpty($age)) {
                throw new EmptyError('age is missing');
            }
            if ($this->isEmpty($status)) {
                throw new EmptyError('status is missing');
            }

            // expected arg types/formats
            if (!preg_match("/^[a-zA-ZáéíóöőüúűÁÉÍÓÖŐÜÚŰ]*$/", $firstName)) {
                throw new FirstNameError('Invalid firstname. Only alphabet letters allowed.');
            } else {
                $this->firstName = $firstName;
            }

            if (!preg_match("/^[a-zA-ZáéíóöőüúűÁÉÍÓÖŐÜÚŰ]*$/", $lastName)) {
                throw new LastNameError('Invalid lastname. Only alphabet letters allowed.');
            } else {
                $this->lastName = $lastName;
            }

            if (!is_numeric($age) && !is_integer($age)) {
                throw new LastNameError('Invalid age. Only integer number allowed.');
            } else {
                $this->age = $age;
            }

            // validation missing!!
            $this->status = $status;

            // create guid identifier
            $this->uuid = GUID();

            // create password, do not store it in a db like this :)
            // @see: https://www.php.net/manual/en/function.openssl-random-pseudo-bytes.php

            $randomLength = mt_rand(12, 20);
            $this->pwd = $this->generateSecurePassword($randomLength);

            // hashed password, store password like this in db
            $this->password = password_hash($this->pwd, PASSWORD_DEFAULT);
        } catch (FirstNameError $e) {
            echo $e->getMessage() . '<br />';
        } catch (LastNameError $e) {
            echo $e->getMessage() . '<br />';
        } catch (AgeError $e) {
            echo $e->getMessage() . '<br />';
        } catch (EmptyError $e) {
            echo $e->getMessage() . '<br />';
        }
    }
    /**
     *  The json_encode function will not show non-public properties.
     *  A Jsonserializable interface was added in PHP 5.4 which allows you to accomplish this.
     *  @see https://www.codebyamir.com/blog/object-to-json-in-php
     */
    public function jsonSerialize()
    {
        return
            [
                'uuid'   => $this->getUuid(),
                'lastname' => $this->getLastName(),
                'firstname' => $this->getFirstName(),
                'age' => $this->getAge(),
                'status' => $this->getStatus(),
                'password' => $this->getPwd()
            ];
    }

    // Getters
    public function getFirstName()
    {
        return $this->firstName;
    }
    public function getLastName()
    {
        return $this->lastName;
    }
    public function getAge()
    {
        return $this->age;
    }
    public function getStatus()
    {
        return $this->status;
    }
    public function getUuid()
    {
        return $this->uuid;
    }
    public function getPwd()
    {
        return $this->pwd;
    }

    // Setters
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return;
    }
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        return;
    }
    public function setAge($age)
    {
        $this->age = $age;
        return;
    }
    public function setStatus($status)
    {
        $this->status = $status;
        return;
    }
    // generate and hash it!
    public function setNewPassword()
    {
        $oldPassword = $this->pwd;
        $oldPasswordHash = $this->password;
        $randomLimit = mt_rand(12, 20);
        // password in plain text
        $this->pwd = $this->generateSecurePassword($randomLimit);

        // hashed password
        $this->password = password_hash($this->pwd, PASSWORD_DEFAULT);
        echo 'New password created for ' . $this->lastName . ' ' . $this->firstName . ', uid: ' . $this->uuid . '<br />';
        echo 'Old password was: ' . $oldPassword . '<br />';
        echo 'Old password hash was: ' . $oldPasswordHash . '<br />';
        echo 'New password is: ' . $this->pwd . '<br />';
        echo 'New password hash is: ' . $this->password . '<br />';
        return;
    }


    function __destruct()
    {
    }

    /**
     * Generate secure password using openssl pseudo-random bytes, hashing and subsetting
     * @param $pwdLength: must be <= 60, as the bcrypt algorithm used for hashing returns 60 chars (or maybe a bit more)
     * @see: https://www.php.net/manual/en/function.password-hash
     * @see: https://www.php.net/manual/en/function.openssl-random-pseudo-bytes.php
     * Author: András Gulácsi 2020
     */
    private function generateSecurePassword($pwdLength)
    {
        // simple error handling
        if (!is_numeric($pwdLength) && is_integer($pwdLength)) {
            throw new Exception('Password length arg should be an integer!');
        }
        if ($pwdLength > self::MAX_LENGTH_PASSWORD) {
            throw new Exception('Password length arg should be <= 60!');
        }

        // max number of bytes needed for password, round it up if it is an odd number
        $maxBytes = ceil($pwdLength / 2);

        // openssl pseudo-random bytes
        $pwd = openssl_random_pseudo_bytes($maxBytes, $cstrong);

        // to hexadecimal string
        $pwd = bin2hex($pwd);

        // create hash that contains uppercase, lowercase letters, numbers,
        // and sometimes symbols
        $pwd = password_hash($pwd, PASSWORD_DEFAULT);

        // store length of password needed
        $length = strlen($pwd);

        // we should not exceed the index limits
        $maxLimit = ($length - $pwdLength);

        // generate random number as start index for substr()
        $randNum = random_int(0, $maxLimit);
        // echo ($randNum);

        // subset a $pwdLength-sized part randomly from the hash
        $pwd = substr($pwd, $randNum, $pwdLength);

        return $pwd;
    }

    // check if arg is supplied
    private function isEmpty($arg)
    {
        if (!isset($arg) || empty($arg)) {
            return true;
        } else {
            return false;
        }
    }

    // return all object props, except for hashed password
    public function getAllPropsCSV($deliminator)
    {
        return $this->getLastName() . $deliminator . ' ' . $this->getFirstName() .
            $deliminator . ' ' . $this->getAge() . $deliminator . ' ' . $this->getStatus() . $deliminator . ' ' . $this->pwd . $deliminator . ' ' . $this->uuid;
    }

    // print all object props as list, except for hashed password and uuid
    public function echoAllPropsTableRow()
    {
        echo '<tr>';
        echo '<td>' . $this->getLastName() . '</td>';
        echo '<td>' . $this->getFirstName() . '</td>';
        echo '<td>' . $this->getAge() . '</td>';
        echo '<td>' . $this->getStatus() . '</td>';
        echo '<td>' . $this->pwd . '</td>';
        echo '<td>' . $this->uuid . '</td>';
        echo '</tr>';
    }

    // sort by firstname abc order
    public static function sortByFirstName($a, $b)
    {
        $a = strtolower($a->firstName);
        $b = strtolower($b->firstName);
        if ($a == $b) {
            return 0;
        }
        return ($a > $b) ? +1 : -1;
    }

    // sort by lastname abc order
    public static function sortByLastName($a, $b)
    {
        $a = strtolower($a->lastName);
        $b = strtolower($b->lastName);
        if ($a == $b) {
            return 0;
        }
        return ($a > $b) ? +1 : -1;
    }

    // sort by age ascending order
    public static function sortByAge($a, $b)
    {
        $a = $a->age;
        $b = $b->age;
        return $a - $b;
    }
}


$userArr = array();
array_push($userArr, new UserProfile('Andi', 'Szlavati', '41', $statusArr[3]));
array_push($userArr, new UserProfile('Jani', 'Vicsek', 38, $statusArr[0]));
array_push($userArr, new UserProfile('Orsi', 'Hajdú', 25, $statusArr[1]));
array_push($userArr, new UserProfile('Szilvi', 'Nagy', 40, $statusArr[1]));
array_push($userArr, new UserProfile('Kamilla', 'Gőgös', 23, $statusArr[2]));
array_push($userArr, new UserProfile('Judit', 'Faragó', 35, $statusArr[1]));
array_push($userArr, new UserProfile('Gábor', 'Németh', 36, $statusArr[2]));



// set new password
$userArr[0]->setNewPassword();

usort($userArr, array('UserProfile', 'sortByLastName'));
// $userArr = array_reverse($userArr);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>User table</title>
</head>

<body>
    <h1>User Table</h1>
    <?php
    echo '<table>';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Last name</th>';
    echo '<th>First name</th>';
    echo '<th>Age</th>';
    echo '<th>Status</th>';
    echo '<th>Password</th>';
    echo '<th>uuid</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    foreach ($userArr as $user) {
        $user->echoAllPropsTableRow();
    }
    echo '</tbody>';
    echo '</table>';

    saveDataToCSV($userArr, 'result');

    saveDataToJSON($userArr, 'result');

    ?>

</body>

</html>