<?php
/**
 * Created by PhpStorm.
 * User: mostafa
 * Date: 1/29/2019
 * Time: 4:11 PM
 */

print_r($_FILES);

if(isset($_FILES["image"]["tmp_name"]))
{
  $validextensions = array("jpeg", "jpg", "png");
  $temporary = explode(".", $_FILES["image"]["name"]);
  $file_extension = end($temporary);
  if ((($_FILES["image"]["type"] == "image/png") || ($_FILES["image"]["type"] == "image/jpg") || ($_FILES["image"]["type"] == "image/jpeg")
    ) && ($_FILES["image"]["size"] < 100000)//Approx. 100kb files can be uploaded.
    && in_array($file_extension, $validextensions)) {
    if ($_FILES["image"]["error"] > 0)
    {
      echo "Return Code: " . $_FILES["image"]["error"] . "<br/><br/>";
    }
    else
    {
      $sourcePath = $_FILES['image']['tmp_name']; // Storing source path of the file in a variable
      $targetPath = '../html/pricing/images/uploaded/' . $_FILES['image']['name']; // Target path where file is to be stored
      move_uploaded_file($sourcePath,$targetPath) ; // Moving Uploaded file
      echo "<span id='success'>Image Uploaded Successfully...!!</span><br/>";
      echo "<br/><b>File Name:</b> " . $_FILES["image"]["name"] . "<br>";
      echo "<b>Type:</b> " . $_FILES["image"]["type"] . "<br>";
      echo "<b>Size:</b> " . ($_FILES["image"]["size"] / 1024) . " kB<br>";
      echo "<b>Temp file:</b> " . $_FILES["image"]["tmp_name"] . "<br>";
    }
  }
  else
  {
    echo "<span id='invalid'>***Invalid file Size or Type***<span>";
  }
} else {
  echo "File is empty";
}
?>