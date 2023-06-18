<?php 

require_once "../database.php";
session_start();

$errors = [];

$image = $_FILES['image'] ?? null;

$missing = 'E:\XAMPP\htdocs\youtube-clone\images\channelpictures\missing.svg';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $username = $_POST['username'];
  $password = $_POST['password'];
if($image && $image['tmp_name']){
  $imagePath = '../images/channelpictures/'.$username.'/'.$image['name'];
}else{
  $imagePath = '../images/channelpictures/'.$username.'/missing.svg';
}

  //var_dump($_FILES['image']);exit;

  if(!$username){
    $errors[] = 'A username is required';
  }
  if(!$password){
    $errors[] = 'A password is required';
  }
    
  if(empty($errors)){

    $statement = $pdo->prepare("SELECT * FROM users WHERE username=?");
    $statement->execute(array($username));
    $control = $statement->fetch(PDO::FETCH_OBJ);
    if($control){
      $errors[] = "Username already exists";
    }else{
      $statement = $pdo->prepare("INSERT INTO users(username, password, image)
      VALUES(:username, :password, :image)");
    
      $statement->bindValue(':username', $username);
      $statement->bindValue(':password', $password);
      $statement->bindValue(':image', $imagePath);
      $statement->execute();
  
      $_SESSION['username'] = $username;

      if($image && $image['tmp_name']){
        if(!is_dir('../images/channelpictures/'.$_SESSION['username'])){
          mkdir('../images/channelpictures/'.$_SESSION['username']);
        }
        move_uploaded_file($image['tmp_name'], $imagePath);
      }else{
        if(!is_dir('../images/channelpictures/'.$_SESSION['username'])){
          mkdir('../images/channelpictures/'.$_SESSION['username']);
        }
        copy($missing, $imagePath);
      }
      header('Location: ../index.php');
    }
  }
}?>

<!DOCTYPE html>
<html>
<head>
  <title>Register</title>
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
      <p style="margin-top: 20px; margin-bottom: 20px; font-weight: 500; font-size: 1.5rem ">Register</p>
      <p style="margin-bottom: 30px; font-size: 1.1rem ">create an account</p>
    </div>
    <form class="forms" action="" method="post" enctype="multipart/form-data">
      <p class="p1">Username</p>
      <input name="username" type="text">
      <p class="p1">Password</p>
      <input name="password" type="password">
      <p class="p2">Profile picture</p>
      <input name="image" type="file" style="border:none;">
      <div class="button-container">
        <a style="font-size: 0.9rem; color: rgb(26, 115, 232);" href="login.php">
          Login to an existing account
        </a>
        <button type="submit" class="btn">Register</button>
      </div>
    </form>
    <?php
      if(!empty($errors)) { ?>
        <div class="alert">
          <?php foreach ($errors as $error) { ?>
            <p style="font-size: 0.9rem; font-color: black;"><?php echo $error ?></p>
          <?php } ?>
        </div>
      <?php } ?>
  </div>
</body>
</html>