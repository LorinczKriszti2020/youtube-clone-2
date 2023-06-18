<?php 

require_once "../database.php";
session_start();
if(isset($_SESSION['username'])) header('Location: ../index.php');

$errors = [];

if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $username = $_POST['username'];
  $password = $_POST['password'];

  if(!$username){
    $errors[] = 'A username is required';
  }
  if(!$password){
    $errors[] = 'A password is required';
  }
    
  if(empty($errors)){

    $statement = $pdo->prepare("SELECT * FROM users WHERE username=? AND password=?");
    $statement->execute(array($username, $password));
    $control = $statement->fetch(PDO::FETCH_OBJ);
    if($control){
      $_SESSION['username'] = $username;
      header('Location: ../index.php');
    }else{
      $statement = $pdo->prepare("SELECT * FROM users WHERE username=?");
      $statement->execute(array($username));
      $control = $statement->fetch(PDO::FETCH_OBJ);
      if(!$control){
        $errors[] = "Incorrect username";
      }else{
        $errors[] = "Incorrect password";
      }
    }
  }
}?>

<!DOCTYPE html>
<html>
<head>
  <title>Login</title>
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
      <p style="margin-top: 20px; margin-bottom: 20px; font-weight: 500; font-size: 1.5rem ">Sign in</p>
      <p style="margin-bottom: 30px; font-size: 1.1rem ">to continue to YouTube</p>
    </div>
    <form class="forms" action="" method="post" enctype="multipart/form-data">
      <p class="p1">Username</p>
      <input name="username" type="text">
      <p class="p1">Password</p>
      <input name="password" type="password">
      <div class="button-container">
        <a style="font-size: 0.9rem; color: rgb(26, 115, 232);" href="register.php">
          Create an account
        </a>
        <button type="submit" class="btn">Login</button>
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
