<?php

require_once "database.php";

include_once "partials/header.php";
include_once "partials/sidebar.php";

$user = $_GET;
if(!$user['user']){
  header('Location: index.php');
}


$statement = $pdo->prepare('SELECT * FROM videos WHERE uploader = :username');
$statement->bindValue(':username', $user['user']);
$statement->execute();
$videos = $statement->fetchAll(PDO::FETCH_ASSOC);

$statement = $pdo->prepare('SELECT * FROM users WHERE username = :username');
$statement->bindValue(':username', $user['user']);
$statement->execute();
$user = $statement->fetch(PDO::FETCH_ASSOC);

if(!$user){
  header('Location: index.php');
}

$img = substr($user['image'], 3);

$number = count($videos);

if(isset($_POST['subscribe'])){
  if(isset($_SESSION['username'])){
    $statement = $pdo->prepare("SELECT * FROM subscriptions WHERE subscribing=? AND subscribedto=?");
    $statement->execute(array($_SESSION['username'], $user['username']));
    $control = $statement->fetch(PDO::FETCH_OBJ);
    if($control){
      $statement = $pdo->prepare('DELETE FROM subscriptions WHERE subscribing = :subscribing AND subscribedto = :subscribedto');
      $statement->bindValue(':subscribing', $_SESSION['username']);
      $statement->bindValue(':subscribedto', $user['username']);
      $statement->execute();
      $statement = $pdo->prepare("UPDATE users SET subscribers = :subscribers WHERE username = :username");
      $user['subscribers']--;
      $statement->bindValue(':subscribers', $user['subscribers']);
      $statement->bindValue(':username', $user['username']);
      $statement->execute();
    }else{
      $statement = $pdo->prepare("INSERT INTO subscriptions(subscribing, subscribedto)
      VALUES(:subscribing, :subscribedto)");
      $statement->bindValue(':subscribing', $_SESSION['username']);
      $statement->bindValue(':subscribedto', $user['username']);
      $statement->execute();
      $statement = $pdo->prepare("UPDATE users SET subscribers = :subscribers WHERE username = :username");
      $user['subscribers']++;
      $statement->bindValue(':subscribers', $user['subscribers']);
      $statement->bindValue(':username', $user['username']);
      $statement->execute();
    }
  }
  else{
    header('Location: forms/login.php');
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="stylesheet" href="styles/general.css">
  <link rel="stylesheet" href="styles/user-page.css">
  <title><?php echo $user['username'] ?></title>
</head>
<body>
  <div class="main">
    <div class="img-container">
      <img class="channel-picture" src="<?php echo $img ?>" alt="channel picture">
    </div>
    <div class="details">
      <div class="left-section">
        <p class="username"><?php echo $user['username'] ?></p> <br>
        <p class="subs"><?php echo $user['subscribers'].' subscribers' ?></p> <br>
        <p class="subs"><?php echo $number.' videos' ?></p>
      </div>
      <form class="right-section" action="" method="post">
        <button type="submit" name="subscribe" class="subscribe-button"><?php
        if(isset($_SESSION['username'])){
          $statement = $pdo->prepare("SELECT * FROM subscriptions WHERE subscribing=? AND subscribedto=?");
          $statement->execute(array($_SESSION['username'], $user['username']));
          $control = $statement->fetch(PDO::FETCH_OBJ); 
        }
        if($control){
          echo 'Unsubscribe';}
        else{
          echo 'Subscribe';} ?>
         </button>
        </form>
    </div>
  </div>

  <div class="secondary">
        <?php foreach($videos as $vid){
           $thumbnailPath =  substr($vid['thumbnail'], 3);
           $videoPath =  substr($vid['video'], 3); ?>
        <div class="recommended">
          <a href="video-page.php?id=<?php echo $vid['id'] ?>" class="recom-thumbnail-container">
            <img class="recom-thumbnail" src="<?php echo $thumbnailPath ?>" alt="thumbnail picture">
            <div class="time"><?php echo $vid['length'] ?></div>
          </a href="video-page.php?id=<?php echo $vid['id'] ?>">
          <div class="recom-text">
            <a href="video-page.php?id=<?php echo $vid['id'] ?>">
              <p class="recom-title"><?php echo $vid['title'] ?></p>
            </a>
            <div>
              <p class="recom-channel-name"><?php echo $vid['uploader'] ?></p>
              <p class="recom-channel-name">
                <?php echo $vid['views'].' views' ?> &#183; <?php echo $vid['create_date'] ?>
              </p>
            </div>
          </div>
        </div>
        <?php } ?>
      </div>
      <?php if(empty($videos)){ ?>
        <div class="missing-vid">This channel has no videos yet.</div>
      <?php } ?>
</body>
</html>