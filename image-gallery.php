<?php


class GalleryImage implements JsonSerializable
{

  // to store the image path of uploaded image
  private $imagePath;

  // to store image title (used in image labels, and in alt attributes)
  private $imageTitle;

  // to store image size displayed in the gallery: "small", "medium", and "big"
  private $imageSize;

  public static $allowedExtensions;

  // to store error stack
  public $errorStack;

  public static $suppressErrorMessages;



  // getters
  public function getImagePath()
  {
    return $this->imagePath;
  }
  public function getImageTitle()
  {
    return $this->imageTitle;
  }
  public function getImageSize()
  {
    return $this->imageSize;
  }

  // setters
  public function setImagePath($path)
  {
    $this->imagePath = $path;
  }
  public function setImageTitle($title)
  {
    $this->imageTitle = $title;
  }
  public function setImageSize($size)
  {
    $this->imageSize = $size;
  }

  // constructor
  function __construct()
  {
    $this->imagePath = "";
    $this->imageTitle = "";
    $this->imageSize = "";
    $this->errorStack = array();
  }
  // desctructor
  function __destruct()
  {
  }

  /**
   *  The json_encode function will not show non-public properties.
   *  A Jsonserializable interface was added in PHP 5.4 which allows you to accomplish this.
   *  @see https://www.codebyamir.com/blog/object-to-json-in-php
   * @param bool $suppressErrorMessages true if you want to export the errorstack to json, false otherwise
   */
  public function jsonSerialize()
  {
    if (self::$suppressErrorMessages === false) {
      return [
        'guid' => $this->createGuid(),
        'imagePath'   => $this->getImagePath(),
        'imageTitle' => $this->getImageTitle(),
        'imageSize' => $this->getImageSize(),
        'errorStack' => $this->errorStack
      ];
    } else {
      return [
        'guid' => $this->createGuid(),
        'imagePath'   => $this->getImagePath(),
        'imageTitle' => $this->getImageTitle(),
        'imageSize' => $this->getImageSize()
      ];
    }
  }

  public function saveDataToJSON($filename)
  {
    // add extension to filename
    $filename = $filename . '.json';

    // check if file is emoty
    $emptyFile = file_get_contents($filename) ? false : true;
    // echo $emptyFile;

    // read file if empty
    if ($emptyFile) {
      try {
        if (($result = fopen($filename, 'w')) === false) {
          throw new Exception('Cannot read the file because it is busy. Try again later.<br />');
        }
      } catch (Exception $e) {
        echo $e->getMessage();
      }
      // add first item to the file
      $json = "[";
      $json .= json_encode($this->jsonSerialize());
      $json .= "]";

      fwrite($result, $json);
      fclose($result);
      echo 'OK. Image data saved to <a href="' . $filename  . '">' . $filename . '</a><br />';
      //--------------------------- File closed
      $this->testJson($json);
    }
    // append file if not empty
    else {
      try {
        if (($result = fopen($filename, 'r')) === false) {
          throw new Exception('Cannot read the file because the file is busy.<br />');
        }
      } catch (Exception $e) {
        echo $e->getMessage();
      }


      $tmp = stream_get_contents($result);
      echo $tmp;
      fclose($result);
      //--------------------------- File closed

      // remove ] from the end
      $tmp = substr($tmp, 0, (strlen($tmp) - 1));

      // add , after previous item
      $tmp .= ",";
      // append new item
      $tmp .= json_encode($this->jsonSerialize());
      // put ] back to the end
      $tmp .= "]";


      try {
        if (($result = fopen($filename, 'w')) === false) {
          throw new Exception('Error. Cannot write to the file.<br />');
        }
      } catch (Exception $e) {
        echo $e->getMessage();
      }

      fwrite($result, $tmp);
      fclose($result);
      echo 'OK. Image data saved to <a href="' . $filename  . '">' . $filename . '</a><br />';
      //--------------------------- File closed
      // $this->testJson($tmp);

    }
    return;
  }

  // read data from JSON
  public function readDataFromJson($filename)
  {
    // add extension to filename
    $filename = $filename . '.json';

    // check if file is emoty
    if ($json = file_get_contents($filename)) {
      $data = json_decode($json);
    } else {
      echo 'Error reading json file';
    }
    // echo '<pre>';
    // print_r($data);
    // echo '</pre>';

    return $data;
  }

  // read data from JSON
  public function printGalleryFromJson($data)
  {

    echo '<h2>A képgalériám</h2>';
    echo '<div class="gallery">';
    foreach ($data as $elem) {
      echo  '<div class="gallery-item w-2 h-1">';
      echo   '<div class="image">';
      echo      '<img src="' . $elem->imagePath . '" alt="' . $elem->imageTitle . '" class="single-image">';
      echo    '</div>';
      echo    '<div class="text">' . $elem->imageTitle . '</div>';
      echo  '</div>';
    }
    echo  '</div>';
  }


  // print for testing purposes
  public function testJson($json)
  {
    echo '<pre style="background: white;">';
    print_r($json);
    echo '</pre>';
    // decode and print for testing purposes
    $jsonDecoded = json_decode($json);
    echo '<br><br />Json decoded:<br />';
    echo '<pre style="background: white;">';
    print_r($jsonDecoded);
    echo '</pre>';
  }



  /**
   * The phunction PHP framework (http://sourceforge.net/projects/phunction/) uses
   * the following function to generate valid version 4 UUIDs:
   * by Alix Axel
   * @see https://www.php.net/manual/en/function.com-create-guid
   * modified by András Gulácsi mt_rand() replaced with random_int()
   */
  public function createGuid()
  {
    if (function_exists('com_create_guid') === true) {
      return trim(com_create_guid(), '{}');
    }

    return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', random_int(0, 65535), random_int(0, 65535), random_int(0, 65535), random_int(16384, 20479), random_int(32768, 49151), random_int(0, 65535), random_int(0, 65535), random_int(0, 65535));
  }


  /**
   * Check $_FILES[][name]
   *
   * @param (string) $filename - Uploaded file name.
   * @author Yousef Ismaeil Cliprz
   */
  private function isFileNameValid($filename)
  {
    return (bool) ((preg_match("`^[-0-9A-Z_\.]+$`i", $filename)) ? true : false);
  }

  /**
   * @param (string) $filename - Uploaded file name.
   * @author Yousef Ismaeil Cliprz.
   */
  function isFileNameTooLong($filename)
  {
    return (bool) ((mb_strlen($filename, "UTF-8") > 225) ? true : false);
  }


  // validate user input
  public function validateInputForm()
  {
    // validate in case of a post method
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      // echo '<pre style="background: white;">';
      // print_r($_POST);
      // echo '</pre>';

      echo '<pre style="background: white;">';
      print_r($_FILES['image']);
      echo '</pre>';


      // check file input
      if (isset($_FILES['image']) && !empty($_FILES['image'])) {
        // echo 'Kép url: ' . $_FILES['image']['name'] . '<br />';

        $noError = true;
        $galleryFolderPath = 'images/gallery/';

        // POST image error
        if ($_FILES['image']['error'] > 0) {
          $noError = false;
          $errorCode = $_FILES['image']['error'];

          /**
           * Error code explanations
           * @see https://www.php.net/manual/en/features.file-upload.errors.php
           */
          switch ($errorCode) {
            case 1:
              array_push($this->errorStack, 'The uploaded file exceeds the upload_max_filesize directive in php.ini.');
              break;
            case 2:
              array_push($this->errorStack, 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.');
              break;
            case 3:
              array_push($this->errorStack, 'The uploaded file was only partially uploaded.');
              break;
            case 4:
              array_push($this->errorStack, 'No file was uploaded.');
              break;
            case 6:
              array_push($this->errorStack, 'Missing a temporary folder.');
              break;
            case 7:
              array_push($this->errorStack, 'Failed to write file to disk.');
              break;
            case 8:
              array_push($this->errorStack, 'A PHP extension stopped the file upload.');
              break;
            default:
              array_push($this->errorStack, 'An unspecified PHP error occured.');
              break;
          }

          array_push($this->errorStack, 'Hiba a fájl feltöltésekor. Próbáld újra.');
        }

        // Check upload content to filter out some malicious code
        // read the header information of the image and will fail on an invalid image.
        if (!@getimagesize($_FILES['image']['tmp_name'])) {
          $noError = false;
          array_push($this->errorStack, 'A feltöltött kép érvénytelen.');
        }

        // check if filename contains illegal chars
        if ($this->isFileNameValid($_FILES['image']['name'] === false)) {
          $noError = false;
          array_push($this->errorStack, 'Fájlnév nem tartalmazhat ékezetes betűket, speciális karaktereket (pl. $, [, { stb.)');
        }

        // check if filename is not too long
        if ($this->isFileNameTooLong($_FILES['image']['name'] === true)) {
          $noError = false;
          array_push($this->errorStack, 'A fájlnév túl hosszú. Max. 250 karakter a megengedett hossz.');
        }

        // max 500 KB
        if ($_FILES['image']['size'] > 512000) {
          $noError = false;
          array_push($this->errorStack, 'A fájl mérete nem lehet nagyobb, mint 500 KB.');
        }

        // get image extension, note it will not stop malicious code embedded in the image
        $type = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        // echo $type  . '</br>';

        if (!in_array($type, self::$allowedExtensions)) {
          $noError = false;
          array_push($this->errorStack, 'A fájlnév kiterjesztése csak JPG, JPEG, PNG vagy GIF lehet.');
        }


        // split string to get filename without extension
        $explodedFilename = explode('.', $_FILES['image']['name']);

        // change uploaded filename datetime + random numbers + type
        $_FILES['image']['name'] = $explodedFilename[0] . '-' . date('Ymdhis') . random_int(6, 20) . '.' . $type;
        // echo $_FILES['image']['name'] . '</br>';


        // if file exists, stop moving file from temp to destination folder
        // there is a minimal chance of filename duplication, but unlikely after randomization
        if (file_exists($galleryFolderPath . $_FILES['image']['name'])) {
          $noError = false;
          array_push($this->errorStack, 'Ilyen nevű fájl már létezik.');
        }
      }


      // check title input
      if (isset($_POST['title']) && !empty($_POST['title'])) {
        // echo 'Képcím: ' . $_POST['title'] . '<br />';
        // remove whitespace, strip tags, convert html entities, rewmove slashes
        $title = $this->sanitizeInputText($_POST['title']);
      }

      // check size input
      if (isset($_POST['size']) && !empty($_POST['size'])) {
        // echo 'Képméret: ' . $_POST['size'] . '<br />';
        // remove whitespace, strip tags, convert html entities, rewmove slashes
        $size = $this->sanitizeInputText($_POST['size']);
      }


      if ($noError === true) {
        // temp file path
        $tmpPath = $_FILES['image']['tmp_name'];
        // copy file to here
        $movedPath = $galleryFolderPath . $_FILES['image']['name'];
        // perform file moving
        move_uploaded_file($tmpPath, $movedPath);

        $this->setImagePath($movedPath);
        $this->setImageTitle($title);
        $this->setImageSize($size);
      }

      return $noError;
    }
  }

  // some basic sanitizing: remove trailing whitespace, convert html entities, strip slashes and tags
  private function sanitizeInputText($str)
  {
    $str = trim($str);
    $str = strip_tags($str);
    $str = stripslashes($str);
    $str = htmlentities($str);
    return $str;
  }
}

GalleryImage::$allowedExtensions = array('jpg', 'jpeg', 'png', 'gif');



// instantiate
$myImage = new GalleryImage();

// $galleryData = $myImage->readDataFromJson('test');
// $myImage->printGalleryFromJson($galleryData);


// validate form submission
if ($myImage->validateInputForm() === true) {

  // include errorStack for the server response
  $myImage::$suppressErrorMessages = false;

  echo '<pre style="background: white;">';
  print_r($myImage->jsonSerialize());
  echo '</pre>';

  // do not store error message to json
  $myImage::$suppressErrorMessages = true;
  $myImage->saveDataToJSON('test');

  $myImage->readDataFromJson('test');
} else {
  echo '<pre style="background: white;">';
  print_r(!empty($myImage->errorStack)? $myImage->errorStack : '');
  echo '</pre>';
}



















?>








<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Image Gallery</title>

  <link href="https://fonts.googleapis.com/css?family=Roboto:400,700" rel="stylesheet" type="text/css">
  <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="css/style.css">

  <style>
    html,
    body {
      height: 100%;
    }

    body {
      margin: 0;
      padding: 0;
      width: 100%;
      display: table;
      color: #f9f9f9;
      font-weight: 100;
      font-family: 'Roboto', sans-serif;
      /* background-color: rgb(75, 95, 120); */
      background-color: #333;
    }

    a {
      color: #ac4;
    }

    .red {
      background: #e47c4c;
    }

    .red {
      background: #e1e44c;
    }

    .red {
      background: #51e44c;
    }

    a:hover {
      color: #d8ff64;
    }


    .container {
      padding: 1rem 2rem;
      /* text-align: center; */
      text-align: left;
    }

    .container-max-width {
      max-width: 1440px;
      margin: 0 auto;
    }


    .title {
      font-size: 96px;
    }

    .opt {
      margin-top: 30px;
    }

    .opt a {
      text-decoration: none;
      font-size: 150%;
    }

    input[type="text"],
    input[type="submit"],
    input[type="button"] {
      height: 50px;
      font-family: 'Roboto', Arial, Helvetica, sans-serif;
      font-size: 16px;
      font-weight: 500;
      padding-left: 10px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    input[type="submit"],
    input[type="button"] {
      cursor: pointer;
      text-transform: uppercase;
      font-size: 14px;
      padding-right: 10px;
    }

    input[type="submit"]:hover,
    input[type="button"]:hover,
    input[type="submit"]:focus,
    input[type="button"]:focus,
    input[type="submit"]:active,
    input[type="button"]:active {
      background-color: #ECCF6B;
    }


    .table {
      margin-top: 1.5rem;
      margin-bottom: 1.5rem;
      font-size: 20px;
      border-collapse: collapse;
      border-spacing: 0;
      width: 100%;
    }

    .table tr:nth-child(odd) {
      padding: 10px;
      background-color: #eee;
    }

    .table td {
      padding: 10px;
      color: red;
    }

    tr.tr>th {
      background: gray;
      color: white;
      padding: 10px;
    }

    .no-bullets {
      list-style-type: none;
      list-style-image: none;
      margin: 0;
      padding: 0;
    }

    ul.no-bullets li {
      margin-bottom: 15px;
    }

    ul.no-bullets>li:last-child {
      margin-bottom: 0px;
    }

    .icon-24 {
      position: relative;
      stroke: #eee;
      top: 5px;
      margin-right: 2px;
      width: 24px;
      height: 24px;
    }


    .icon {
      position: relative;
      top: 10px;
      width: 36px;
      height: 36px;
    }

    .error-box {
      color: #ff7765;
      padding-top: 20px;
    }



    /* .gallery {
      -moz-column-count: 3;
      -webkit-count: 3;
      column-count: 3;
      overflow: hidden;
      column-gap: 10px;
      margin-top: 10px;
    } */

    .gallery {
      display: grid;
      grid-template-columns: repeat(6, 1fr);
      grid-auto-rows: 300px 500px;
      grid-auto-flow: dense;
      grid-gap: 1em;
      text-align: center;
    }

    /* @media screen and (max-width: 768px) {
      .gallery {
        grid-template-columns: repeat(2, 1fr);
       
      }
    } */

    @media screen and (max-width: 576px) {
      .gallery {
        grid-template-columns: repeat(1, 1fr);
      }

      .w-1,
      .w-2,
      .w-3,
      .w-4,
      .w-5,
      .w-6 {
        grid-column: span 1 !important;
      }

      .h-1,
      .h-2,
      .h-3,
      .h-4,
      .h-5,
      .h-6 {
        grid-row: span 1 !important;
      }
    }

    .gallery-item {
      width: 100%;
      height: 100%;
      position: relative;
    }

    .gallery-item .image {
      width: 100%;
      height: 100%;
      overflow: hidden;
    }

    .gallery-item .image img {
      width: 100%;
      height: 100%;
      -o-object-fit: cover;
      object-fit: cover;
      -o-object-position: 50% 50%;
      object-position: 50% 50%;
      opacity: 0.8;
      cursor: pointer;
      -o-transition: 0.5s ease-in-out;
      transition: 0.5s ease-in-out;
    }

    .gallery .image img:hover {
      opacity: 1;
      cursor: pointer;
      transform: scale(1.2);
    }

    .gallery .image img:active,
    .gallery .image img:focus {
      transition: 750ms;
      opacity: 1;
      cursor: pointer;
      border: 2px solid #fefefe;
    }

    .gallery-item .text {
      opacity: 0;
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      font-size: 20px;
      pointer-events: none;
      background-color: rgba(0, 0, 0, 0.2);
      z-index: 4;
      transition: 0.3s ease-in-out;
      -webkit-backdrop-filter: blur(2px) saturate(1.2);
      backdrop-filter: blur(2px) saturate(1.2);
    }

    .gallery-item:hover .text {
      opacity: 1;
      animation: move-down .4s linear;
      padding: 1em;
      width: 100%;
      /* outline: 2px solid red; */
    }

    @keyframes move-down {

      0% {
        top: 10%;
      }

      50% {
        top: 35%;
      }

      100% {
        top: 50%;
      }
    }

    /* Touch screens don't have hover */
    @media (hover: none) {
      .gallery-item .text {
        opacity: 1;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 20px;
        pointer-events: none;
        background-color: rgba(0, 0, 0, 0.2);
        z-index: 4;
        transition: 0.3s ease-in-out;
        -webkit-backdrop-filter: blur(2px) saturate(1.2);
        backdrop-filter: blur(2px) saturate(1.2);
      }
    }

    .w-1 {
      grid-column: span 1;
    }

    .w-2 {
      grid-column: span 2;
    }

    .w-3 {
      grid-column: span 3;
    }

    .w-4 {
      grid-column: span 4;
    }

    .w-5 {
      grid-column: span 5;
    }

    .w-6 {
      grid-column: span 6;
    }

    .h-1 {
      grid-row: span 1;
    }

    .h-2 {
      grid-row: span 2;
    }

    .h-3 {
      grid-row: span 3;
    }

    .h-4 {
      grid-row: span 4;
    }

    .h-5 {
      grid-row: span 5;
    }

    .h-6 {
      grid-row: span 6;
    }


    .modal-body {
      padding: 0;
    }

    .modal-img {
      margin: 0;
      padding: 0;
      width: 100%;
      height: 100%;
      display: table;
    }

    .modal-dialog {
      position: relative;
      pointer-events: fill;
      /* height: 100%;
      position: relative;
      margin: auto;
      display: flex;
      align-items: center;
      justify-content: center; */
    }

    .modal-content {
      border: none;
    }
  </style>
</head>

<body>
  <div class="container-max-width">
    <div class="container">
      <div>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data" style="background: #555; margin-bottom: 80px; padding-left: 10px;">
          <h1 style="margin-top: 40px; padding-top: 10px; padding-bottom: 10px;">Képfeltöltés</h1>
          <div class="form-group">
            <label for="image">Kép feltöltése</label><br />
            <input type="file" name="image" />
          </div>
          <div class="form-group">
            <label for="title">Kép címe</label><br />
            <input style="width: 298px" type="text" name="title" placeholder="pl. A rőzsehordó asszony" />
          </div>
          <div class="form-group">
            <label>Kép mérete a galériában</label><br />

            <div>
              <input type="radio" name="size" id="small" value="kicsi">
              <label for="small">Kicsi</label>
            </div>
            <div>
              <input type="radio" name="size" id="medium" value="közepes">
              <label for="medium">Közepes</label>
            </div>
            <div>
              <input type="radio" name="size" id="big" value="nagy">
              <label for="big">Nagy</label>
            </div>

          </div>

          <input id="upload-img" type="submit" name="submit" value="Feltölt" />
        </form>
      </div>



      <h2>A képgalériám</h2>
      <div class="gallery">

        <div class="gallery-item w-2 h-1">
          <div class="image">
            <img id="first-img" src="images/gallery/andi-jani.jpg" alt="andi-jani" class="single-image">
          </div>
          <div class="text">andi-jani</div>
        </div>

        <div class="gallery-item w-4 h-2">
          <div class="image">
            <img src="images/gallery/vasarhely-bachata-2020-03-12.jpg" alt="vasarhely-bachata-2020-03-12" class="single-image">
          </div>
          <div class="text">vasarhely-bachata-2020-03-12</div>
        </div>

        <div class="gallery-item w-2 h-1">
          <div class="image">
            <img src="images/gallery/meditalo-ferfi.jpg" alt="meditalo-ferfi" class="single-image">
          </div>
          <div class="text">meditalo-ferfi</div>
        </div>

        <div class="gallery-item w-2 h-1">
          <div class="image">
            <img src="images/gallery/dominikai-bachata.jpg" alt="dominikai-bachata" class="single-image">
          </div>
          <div class="text">dominikai-bachata</div>
        </div>

        <div class="gallery-item w-4 h-2">
          <div class="image">
            <img src="images/gallery/hmvh-bachata-workshop-2020-06-28.jpg" alt="hmvh-bachata-workshop-2020-06-28" class="single-image">
          </div>
          <div class="text">hmvh-bachata-workshop-2020-06-28</div>
        </div>

        <div class="gallery-item w-2">
          <div class="image">
            <img src="images/gallery/andi-jani-2.jpg" alt="andi-jani-2" class="single-image">
          </div>
          <div class="text">andi-jani-2</div>
        </div>

        <div class="gallery-item w-3 h-1">
          <div class="image">
            <img src="images/gallery/csopifoto.jpg" alt="csopifoto" class="single-image">
          </div>
          <div class="text">csopifoto</div>
        </div>

        <div class="gallery-item w-3">
          <div class="image">
            <img src="images/gallery/csoportos-meditacio-hold.jpg" alt="csoportos-meditacio-hold" class="single-image">
          </div>
          <div class="text">csoportos-meditacio-hold</div>
        </div>

        <div class="gallery-item w-6">
          <div class="image">
            <img id="last-img" src="images/gallery/szeged-sensual-bachata-ingyenes-ora-2020-06-24.jpg" alt="szeged-sensual-bachata-ingyenes-ora-2020-06-24" class="single-image">
          </div>
          <div class="text">szeged-sensual-bachata-ingyenes-ora-2020-06-24</div>
        </div>

      </div>
    </div>

    <div id="lightbox-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="image preview large lightbox" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-body">

          </div>
        </div>
      </div>

      <div style="z-index: 100000; position: absolute; top: 0; right: 0;">
        <button id="close-lighbox" type="button" class="btn btn-secondary">&times;</button>
      </div>
      <div style="z-index: 100000; position: absolute; top: 50%; right: 0;">
        <button id="next" type="button" class="btn btn-secondary">&rarr;</button>
      </div>
      <div style="z-index: 100000; position: absolute; top: 50%; left: 0;">
        <button id="prev" type="button" class="btn btn-secondary">
          &larr;
        </button>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous">
  </script>
  <script src="js/jquery-3.5.1.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script>
    $(document).ready(function() {

      // to store current next image src attribute, to have a reference to a variable
      var nextItem = null;

      var prevItem = null;
      // to store the first image
      var firstItem = $('#first-img');
      // to store the last image
      var lastItem = $('#last-img');


      $('.single-image').on('click', function() {
        var link = $(this).attr('src');
        var currentItem = $(this);

        var nextItem = null;
        var prevItem = null;

        // get next nested img sibling of current image clicked
        // to create a next image link for the modal
        nextItem = $('.gallery-item').find(currentItem).parent().parent().next().find('img');
        if (nextItem.attr('src') === undefined) {
          nextItem = firstItem;
        }

        console.log('Next: ' + nextItem.attr('src'));

        // get previous nested img sibling of current image clicked
        // to create a previous image link for the modal
        var prevItem = $('.gallery-item').find(currentItem).parent().parent().prev().find('img');
        if (prevItem.attr('src') === undefined) {
          prevItem = lastItem;
        }

        console.log('Prev: ' + prevItem.attr('src'));

        // put image link into src
        $('.modal-body').html('<img src="' + link + '" class="modal-img" alt="" />');
        // open modal
        $('#lightbox-modal').modal();



        $('#next').on('click', function() {
          // if we are at the last image in the gallery, the next item will be the very first item
          // if (nextItem.attr('src') === undefined) {
          //   $('.modal-body').html('<img src="' + firstItem.attr('src') + '" class="modal-img" alt="" />');
          //   prevItem = lastItem;
          //   nextItem = firstItem.parent().parent().next().find('img');
          //   console.log('Next: ' + nextItem.attr('src'));
          // } else {
          if (nextItem.attr('src') === undefined) {
            prevItem = lastItem;
            nextItem = firstItem;
          }

          currentItem = nextItem;
          $('.modal-body').html('<img src="' + currentItem.attr('src') + '" class="modal-img" alt="" />');
          prevItem = nextItem.parent().parent().prev().find('img');
          nextItem = nextItem.parent().parent().next().find('img');
          console.log('nextNext: ' + nextItem.attr('src'));
          console.log('nextPrev: ' + prevItem.attr('src'));


          // }

        });


        $('#prev').on('click', function() {
          // if we are at the first image in the gallery, the next item will be the very last item
          if (prevItem.attr('src') === undefined) {
            prevItem = lastItem;
            nextItem = firstItem;
          }

          currentItem = prevItem;
          $('.modal-body').html('<img src="' + currentItem.attr('src') + '" class="modal-img" alt="" />');
          prevItem = prevItem.parent().parent().prev().find('img');
          nextItem = nextItem.parent().parent().prev().find('img');
          console.log('iiiNext: ' + nextItem.attr('src'));
          console.log('iiiPrev: ' + prevItem.attr('src'));

        });

      });

      $('#close-lighbox').on('click', function() {
        $('#lightbox-modal').modal('hide');

      });
    });
  </script>
</body>

</html>