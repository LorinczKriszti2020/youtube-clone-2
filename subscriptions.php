<?php

require_once "database.php";
include_once "partials/header.php";
include_once "partials/sidebar.php";

$statement = $pdo->prepare('SELECT * FROM subscriptions WHERE subscribing = :subscribing');
$statement->bindValue(':subscribing', $_SESSION['username']);
$statement->execute();
$users = $statement->fetchAll(PDO::FETCH_ASSOC);

$videos = [];
foreach($users as $user){
  $statement = $pdo->prepare('SELECT * FROM videos WHERE uploader = :uploader');
  $statement->bindValue(':uploader', $user['subscribedto']);
  $statement->execute();
  $vids = $statement->fetchAll(PDO::FETCH_ASSOC);
  foreach($vids as $vid){
    array_push($videos, $vid);
  }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="stylesheet" href="styles/general.css">
  <title>Subscriptions</title>
</head>
<body>
  <div style="font-weight: 500; font-size: 1.5rem; padding-bottom: 15px; border-bottom: 1px solid black; margin-bottom: 10px";>Subscriptions</div>
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