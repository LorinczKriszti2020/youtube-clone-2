<!DOCTYPE html>
<html>
  <head>
    <title>Youtube Clone</title>
    <link rel="stylesheet" href="styles/general.css">

    <?php 
    include_once "partials/header.php";
    include_once "partials/sidebar.php";

    $statement = $pdo->prepare('SELECT * FROM videos');
    $statement->execute();
    $videos = $statement->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <div class="video-grid">
      <?php foreach($videos as $i => $video){
        $thumbnailPath = substr($video['thumbnail'], 3);
        $videoPath = substr($video['video'], 3);

        $statement = $pdo->prepare('SELECT * FROM users WHERE username = :username');
        $statement->bindValue(':username', $video['uploader']);
        $statement->execute();
        $user = $statement->fetch(PDO::FETCH_ASSOC);
        $channelPicturePath = substr($user['image'], 3);?>

      <div class="video-card">
        <div class="thumbnail-container">
          <a href="video-page.php?id=<?php echo $video['id'] ?>">
            <img class="thumbnail" src="<?php echo $thumbnailPath ?>" alt="video thumbnail">
          </a>
          <div class="time"><?php echo $video['length'] ?></div>
        </div>
        <div class="video-info-grid">
          <div class="channel-picture-container">
            <a href="user-page.php?user=<?php echo $user['username'] ?>">
              <img class="channel-picture" src="<?php echo $channelPicturePath ?>" alt="channel picture">
            </a>
            <div class="tooltip">
              <div class="tooltip-channel-picture-container">
                <img class="channel-picture" src="<?php echo $channelPicturePath ?>" alt="channel picture">
              </div>
              <div class="stats">
                <p class="channel-name">
                  <?php echo $video['uploader'] ?>
                </p>
                <p class="tooltip-subscribers">
                <?php $statement = $pdo->prepare('SELECT * FROM users WHERE username = :username');
                      $statement->bindValue(':username', $video['uploader']);
                      $statement->execute();
                      $curUser = $statement->fetch(PDO::FETCH_ASSOC);
                      echo $curUser['subscribers'].' subscribers' ?>
                </p>
              </div>
            </div>
          </div>
          <div class="video-info">
            <a style="color: black" href="video-page.php?id=<?php echo $video['id'] ?>" class="video-title">
              <?php echo $video['title'] ?>
            </a>
            <a href="user-page.php?user=<?php echo $user['username'] ?>">
              <p class="channel-name">
                <?php echo $video['uploader'] ?>
              </p>
            </a>
            <p class="video-stats">
              <?php echo $video['views'].' views' ?> &#183; <?php echo $video['create_date'] ?>
            </p>
          </div>
        </div>
      </div>
      <?php } ?>
    </div>
  </body>
</html>