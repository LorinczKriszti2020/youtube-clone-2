<?php 

require_once "../database.php";
session_start();

if(!is_dir('../images/channelpictures')){
  mkdir('../images/channelpictures');
}

$image = $_FILES['image'] ?? null;
  
$statement = $pdo->prepare("SELECT * FROM users WHERE username=?");
$statement->execute(array($_SESSION['username']));
$control = $statement->fetch(PDO::FETCH_OBJ);

$imagePath = $control->image;

if($image && $image['tmp_name']){

  if($control->image){
    if(file_exists('../images/channelpictures/'.$_SESSION['username'])){
      if(file_exists($control->image)){
        unlink($control->image);
        rmdir('../images/channelpictures/'.$_SESSION['username']);
      }
    }
  }
  
  $imagePath = '../images/channelpictures/'.$_SESSION['username'].'/'.$image['name'];

  $statement = $pdo->prepare("INSERT INTO users(username, password)
      VALUES(:username, :password)");
    
      $statement = $pdo->prepare("UPDATE users SET image = :image WHERE username = :username");
      $statement->bindValue(':image', $imagePath);
      $statement->bindValue(':username', $_SESSION['username']);
      $statement->execute();

  if(!is_dir('../images/channelpictures/'.$_SESSION['username'])){
   mkdir('../images/channelpictures/'.$_SESSION['username']);
  }
  move_uploaded_file($image['tmp_name'], $imagePath);
  header('Location: ../index.php');
}

?>

<!DOCTYPE html>
<html>
<head>
  <title>Update</title>
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
      <p style="margin-top: 20px; margin-bottom: 20px; font-weight: 500; font-size: 1.5rem ">Update</p>
      <p style="margin-bottom: 30px; font-size: 1.1rem ">an existing account</p>
    </div>
    <form class="forms" action="" method="post" enctype="multipart/form-data">
      <p class="p1">Username</p>
      <input disabled name="username" type="text" value="<?php echo $_SESSION['username'] ?>">
      <p class="p1">Password</p>
      <input disabled name="password" type="password" value="password">
      <p class="p2">Profile picture</p>
      <input type="file" name="image" style="border:none;">
      <div class="button-container">
        <a style="font-size: 0.9rem; color: rgb(26, 115, 232);" href="../index.php">
          Home page
        </a>
        <button type="submit" name="login-button" class="btn">Update</button>
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