<?php

require_once "../database.php";
require_once "../functions.php";
require_once "../getid3/getid3.php";

session_start();

if(!isset($_SESSION['username'])){
  header('Location: ../index.php');
}

$errors = [];

$uploader = $_SESSION['username'] ?? '';
$title = $_POST['title'] ?? '';
$thumbnail = $_FILES['thumbnail'] ?? '';
$video = $_FILES['video'] ?? '';
$description = $_POST['description'] ?? '';

//var_dump($_FILES);
//var_dump($title);
//var_dump($thumbnail);
//?>  <?php
//var_dump($video);

if($_SERVER['REQUEST_METHOD'] === 'POST'){
  if(!$title){
    $errors[] = 'A title is required';
  }
  if(!$thumbnail){
    $errors[] = 'A thumbnail is required';
  }
  if(!$video){
    $errors[] = 'A video is required';
  }

  

  if(empty($errors)){
    $getID3 = new getID3;
    $analyzed = $getID3->analyze($video['tmp_name']);
    $length = $analyzed['playtime_string'];

    $rand = randomString(8);
    $thumbnailPath = '../images/thumbnails/'.$rand.'/'.$thumbnail['name'];
    $videoPath = '../videos/'.$rand.'/'.$video['name'];

    $statement = $pdo->prepare("INSERT INTO videos(uploader, title, thumbnail, video, length, code, create_date, description)
        VALUES(:uploader, :title, :thumbnail, :video, :length, :code, :create_date, :description)");
      
        $statement->bindValue(':uploader', $uploader);
        $statement->bindValue(':title', $title);
        $statement->bindValue(':thumbnail', $thumbnailPath);
        $statement->bindValue(':video', $videoPath);
        $statement->bindValue(':length', $length);
        $statement->bindValue(':code', $rand);
        $statement->bindValue(':create_date', date('Y-m-d'));
        $statement->bindValue(':description', $description);
        $statement->execute();

        mkdir('../images/thumbnails/'.$rand);
        mkdir('../videos/'.$rand);
        move_uploaded_file($thumbnail['tmp_name'], $thumbnailPath);
        move_uploaded_file($video['tmp_name'], $videoPath);
        header('Location: ../index.php');
  }
}

    


?>

<!DOCTYPE html>
<html>
<head>
  <title>Upload</title>
  <link rel="stylesheet" href="../styles/login-page.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
  <div class="login">
    <div class="top">
      <a style="display: flex; align-items: center; justify-content: center;" href="../index.php">
        <img class="youtube-logo" src="../images/icons/youtube-logo.svg" alt="youtube logo">
      </a>
      <p style="margin-top: 20px; margin-bottom: 20px; font-weight: 500; font-size: 1.5rem ">Upload a video</p>
    </div>
    <form class="forms" action="" method="post" enctype="multipart/form-data">
      <p class="p1">Title</p>
      <input name="title" type="text">
      <p class="p1">Thumbnail</p>
      <input name="thumbnail" type="file" style="border: none">
      <p class="p1">Video</p>
      <input name="video" type="file" style="border: none">
      <p class="p1">Description</p>
      <textarea class="description" name="description"></textarea>
      <div class="button-container">
        <p></p>
        <button type="submit" class="btn">Upload</button>
      </div>
    </form>
    <?php
      if(!empty($errors)) { ?>
        <div class="alert">
          <?php foreach ($errors as $error) { ?>
            <p style="color: black; font-size: 0.9rem" class="alert-text"><?php echo $error ?></p>
          <?php } ?>
        </div>
      <?php } ?>
  </div>
</body>
</html>