<?php
require_once "database.php";


$search = $_GET['search'] ?? '';
if($search){
  $statement = $pdo->prepare('SELECT * FROM videos WHERE title LIKE :title ORDER BY create_date DESC');
  $statement->bindValue(':title', "%$search%");
}else{
  $statement = $pdo->prepare('SELECT * FROM videos ORDER BY create_date DESC');
}

$statement->execute();
$videos = $statement->fetchAll(PDO::FETCH_ASSOC);

include_once "partials/header.php";
include_once "partials/sidebar.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="stylesheet" href="styles/general.css">
  <link rel="stylesheet" href="styles/search.css">
  <title>Search &#183; <?php echo $_GET['search'] ?></title>
</head>
<body>
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
        <div class="missing-vid">There are no videos like that.</div>
      <?php } ?>
</body>
</html>