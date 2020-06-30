<?php

/**
 * Password class instance
 * Generate cryptographically safe passwords
 * Author: András Gulácsi
 */
class Password
{

  // store password
  private $password;

  // lowercase allowed
  private $smallCaps;
  // uppercase allowed
  private $bigCaps;
  // numbers allowed
  private $numbers;
  // special chars like &,@,$[,],'," allowed
  private $specialChars;

  private const MAX_LENGTH_PASSWORD = 99;

  public $errorStack = array();


  // Constructor
  function __construct(
    $password = '',
    $smallCaps = 0,
    $bigCaps = 0,
    $numbers = 0,
    $specialChars = 0
  ) {

    // inizialize
    $this->password = $password;
    $this->smallCaps = $smallCaps;
    $this->bigCaps = $bigCaps;
    $this->numbers = $numbers;
    $this->specialChars = $specialChars;
  }


  // getters
  public function getPassword()
  {
    return $this->password;
  }
  private function getSmallCaps()
  {
    return $this->smallCaps;
  }
  private function getBigCaps()
  {
    return $this->bigCaps;
  }
  private function getNumbers()
  {
    return $this->numbers;
  }
  private function getSpecialChars()
  {
    return $this->specialChars;
  }


  // setters
  private function setPassword($password)
  {
    $this->password = $password;
  }
  private function setSmallCaps($smallCaps)
  {
    $this->smallCaps = $smallCaps;
  }
  private function setBigCaps($bigCaps)
  {
    $this->bigCaps = $bigCaps;
  }
  private function setNumbers($numbers)
  {
    $this->numbers = $numbers;
  }
  private function setSpecialChars($specialChars)
  {
    $this->specialChars = $specialChars;
  }

  public function echoPasswordProps()
  {
    echo "small: " . $this->getSmallCaps() .
      ", big: " . $this->getBigCaps() .
      ", num: " . $this->getNumbers() .
      ", sym: " . $this->getSpecialChars() . '<br />';
  }


  // Destructor
  function __destruct()
  {
  }


  public function generateSecurePassword($pwdLength)
  {
    // simple error handling
    if (!is_numeric($pwdLength) && is_integer($pwdLength)) {
      throw new Exception('Password length arg should be an integer!');
    }
    if ($pwdLength > self::MAX_LENGTH_PASSWORD) {
      throw new Exception('Password length arg should be <= 99!');
    }

    // small letters
    $lowercase = 'abcdefghijklmnopqrstuvwxyz';
    // CAPITAL LETTERS            
    $uppercase  = strtoupper($lowercase);
    // numerics                
    $numbers   = '1234567890';
    // special characters                          
    $symbols = '`~!@#$%^&*()-_=+]}[{;:,<.>/?\'"\|';

    $charset = '';
    // Contains specific character groups
    if ($this->getSmallCaps() == true) {
      $charset .= $lowercase;
    }
    if ($this->getBigCaps() == true) {
      $charset .= $uppercase;
    }
    if ($this->getNumbers() == true) {
      $charset .= $numbers;
    }
    if ($this->getSpecialChars() == true) {
      $charset .= $symbols;
    }

    // echo $charset . '<br />';

    // store password
    $password = '';

    // Loop until the preferred length reached
    for ($i = 0; $i < $pwdLength; $i++) {
      // get randomized length                                 
      $_rand = random_int(0, strlen($charset) - 1);
      // returns part of the string                
      $password .= substr($charset, $_rand, 1);
    }

    // store password privately
    $this->setPassword($password);

    return;
  }

  public function validateInput()
  {

    if ($_SERVER['REQUEST_METHOD'] ===  'POST') {

      // Is lowercase checked
      if ($_POST['lowercase'] == true) {
        $this->setSmallCaps(1);
      } else {
        $this->setSmallCaps(0);
      }

      // Is uppercase checked?
      if ($_POST['uppercase'] == true) {
        $this->setBigCaps(1);
      } else {
        $this->setBigCaps(0);
        
      }

      // Is numbers checked?
      // if (!empty($_POST['number']) ) {
      if ($_POST['number'] == true) {
        $this->setNumbers(1);
      } else {
        $this->setNumbers(0);
      }

      // Is symbols checked?
      if ($_POST['symbol'] == true) {
        $this->setSpecialChars(1);
      } else {
        $this->setSpecialChars(0);
      }

      // $this->echoPasswordProps();


      if (isset($_POST['pwdlength']) && !empty($_POST['pwdlength'])) {
        $pwdLength = $_POST['pwdlength'];
        $pwdLength = trim($pwdLength); // Remove trailing whitespace
        // Only numbers!
        if (preg_match("/^[0-9]*$/", $pwdLength)) {
          $pwdLength = intval($_POST['pwdlength'], 10);
          $this->generateSecurePassword($pwdLength);
        } else {
          array_push($this->errorStack, 'You should supply an integer input. <br />');
        }
      } else {
        array_push($this->errorStack, 'Password length is not set. <br />');
      }
    }
  }
}

$myPassword = new Password();

$myPassword->validateInput();

echo $myPassword->getPassword();

?>