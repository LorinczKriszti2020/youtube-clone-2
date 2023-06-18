<!DOCTYPE html>
<html>
<head>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

  <script src="scripts/video-page.js" defer></script>
</head>
<body>
  <?php
  include_once "partials/header.php";

  $id = $_GET['id'] ?? null;
  if(!$id){
    header('Location: index.php');
  }
  $statement = $pdo->prepare('SELECT * FROM videos');
  $statement->execute();
  $videos = $statement->fetchAll(PDO::FETCH_ASSOC);

  $statement = $pdo->prepare('SELECT * FROM videos WHERE id = :id');
  $statement->bindValue(':id', $id);
  $statement->execute();
  $video = $statement->fetch(PDO::FETCH_ASSOC);

  $views = $video['views'] + 1;
  $statement = $pdo->prepare("UPDATE videos SET views = :views WHERE id = :video_id");
  $statement->bindValue(':views', $views);
  $statement->bindValue(':video_id', $video['id']);
  $statement->execute();

  $username = $video['uploader'];
  $statement = $pdo->prepare('SELECT * FROM users WHERE username = :username');
  $statement->bindValue(':username', $username);
  $statement->execute();
  $user = $statement->fetch(PDO::FETCH_ASSOC);

  $statement = $pdo->prepare('SELECT * FROM users');
  $statement->execute();
  $users = $statement->fetchAll(PDO::FETCH_ASSOC);

  $statement = $pdo->prepare('SELECT * FROM comments WHERE video_id = :video_id');
  $statement->bindValue(':video_id', $video['id']);
  $statement->execute();
  $comments = $statement->fetchAll(PDO::FETCH_ASSOC);

  $currentUser = '';
  $control = '';
  if(isset($_SESSION['username'])){
    $username = $_SESSION['username'];
    $statement = $pdo->prepare('SELECT * FROM users WHERE username = :username');
    $statement->bindValue(':username', $username);
    $statement->execute();
    $currentUser = $statement->fetch(PDO::FETCH_ASSOC);
    $currentChannelPicturePath = substr($currentUser['image'], 3);
  }

  $videoPath = substr($video['video'], 3);
  $channelPicturePath = substr($user['image'], 3);
  $missing = 'images/channelpictures/missing.svg';

  if(isset($_POST['subscribe'])){
    if(isset($_SESSION['username'])){
      $statement = $pdo->prepare("SELECT * FROM subscriptions WHERE subscribing=? AND subscribedto=?");
      $statement->execute(array($_SESSION['username'], $video['uploader']));
      $control = $statement->fetch(PDO::FETCH_OBJ);
      if($control){
        $statement = $pdo->prepare('DELETE FROM subscriptions WHERE subscribing = :subscribing AND subscribedto = :subscribedto');
        $statement->bindValue(':subscribing', $_SESSION['username']);
        $statement->bindValue(':subscribedto', $video['uploader']);
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
        $statement->bindValue(':subscribedto', $video['uploader']);
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

  if(isset($_POST['like'])){
    if(isset($_SESSION['username'])){
      $statement = $pdo->prepare("SELECT * FROM likes WHERE video_id = :video_id AND user = :user");
      $statement->bindValue(':video_id', $video['id']);
      $statement->bindValue(':user', $_SESSION['username']);
      $statement->execute();
      $control = $statement->fetch(PDO::FETCH_ASSOC);
      if($control){
        if($control['action'] == 'liked'){
          $statement = $pdo->prepare("UPDATE videos SET likes = :likes WHERE id = :id");
          $video['likes']--;
          $statement->bindValue(':likes', $video['likes']);
          $statement->bindValue(':id', $_GET['id']);
          $statement->execute();
          $statement = $pdo->prepare("UPDATE likes SET action = :action WHERE id = :id");
          $statement->bindValue(':action', '');
          $statement->bindValue(':id', $control['id']);
          $statement->execute();
        }else if($control['action'] == 'disliked'){
          $statement = $pdo->prepare("UPDATE videos SET likes = :likes, dislikes = :dislikes WHERE id = :id");
          $video['likes']++;
          $video['dislikes']--;
          $statement->bindValue(':likes', $video['likes']);
          $statement->bindValue(':dislikes', $video['dislikes']);
          $statement->bindValue(':id', $_GET['id']);
          $statement->execute();
          $statement = $pdo->prepare("UPDATE likes SET action = :action WHERE id = :id");
          $statement->bindValue(':action', 'liked');
          $statement->bindValue(':id', $control['id']);
          $statement->execute();
        }else{
          $statement = $pdo->prepare("UPDATE videos SET likes = :likes WHERE id = :id");
          $video['likes']++;
          $statement->bindValue(':likes', $video['likes']);
          $statement->bindValue(':id', $_GET['id']);
          $statement->execute();
          $statement = $pdo->prepare("UPDATE likes SET action = :action WHERE id = :id");
          $statement->bindValue(':action', 'liked');
          $statement->bindValue(':id', $control['id']);
          $statement->execute();
        }
      }else{
        $statement = $pdo->prepare("INSERT INTO likes (video_id, user, action) VALUES (:video_id, :user, :action)");
        $statement->bindValue(':user', $_SESSION['username']);
        $statement->bindValue(':video_id', $_GET['id']);
        $statement->bindValue(':action', 'liked');
        $statement->execute();
        $statement = $pdo->prepare("UPDATE videos SET likes = :likes WHERE id = :id");
        $video['likes']++;
        $statement->bindValue(':likes', $video['likes']);
        $statement->bindValue(':id', $_GET['id']);
        $statement->execute();
      }
    }
    else{
      header('Location: forms/login.php');
    }
  }

  if(isset($_POST['dislike'])){
    if(isset($_SESSION['username'])){
      $statement = $pdo->prepare("SELECT * FROM likes WHERE video_id = :video_id AND user = :user");
      $statement->bindValue(':video_id', $video['id']);
      $statement->bindValue(':user', $_SESSION['username']);
      $statement->execute();
      $control = $statement->fetch(PDO::FETCH_ASSOC);
      if($control){
        if($control['action'] == 'disliked'){
          $statement = $pdo->prepare("UPDATE videos SET dislikes = :dislikes WHERE id = :id");
          $video['dislikes']--;
          $statement->bindValue(':dislikes', $video['dislikes']);
          $statement->bindValue(':id', $_GET['id']);
          $statement->execute();
          $statement = $pdo->prepare("UPDATE likes SET action = :action WHERE id = :id");
          $statement->bindValue(':action', '');
          $statement->bindValue(':id', $control['id']);
          $statement->execute();
        }else if($control['action'] == 'liked'){
          $statement = $pdo->prepare("UPDATE videos SET dislikes = :dislikes, likes = :likes WHERE id = :id");
          $video['dislikes']++;
          $video['likes']--;
          $statement->bindValue(':dislikes', $video['dislikes']);
          $statement->bindValue(':likes', $video['likes']);
          $statement->bindValue(':id', $_GET['id']);
          $statement->execute();
          $statement = $pdo->prepare("UPDATE likes SET action = :action WHERE id = :id");
          $statement->bindValue(':action', 'disliked');
          $statement->bindValue(':id', $control['id']);
          $statement->execute();
        }else{
          $statement = $pdo->prepare("UPDATE videos SET dislikes = :dislikes WHERE id = :id");
          $video['dislikes']++;
          $statement->bindValue(':dislikes', $video['dislikes']);
          $statement->bindValue(':id', $_GET['id']);
          $statement->execute();
          $statement = $pdo->prepare("UPDATE likes SET action = :action WHERE id = :id");
          $statement->bindValue(':action', 'disliked');
          $statement->bindValue(':id', $control['id']);
          $statement->execute();
        }
      }else{
        $statement = $pdo->prepare("INSERT INTO likes (video_id, user, action) VALUES (:video_id, :user, :action)");
        $statement->bindValue(':user', $_SESSION['username']);
        $statement->bindValue(':video_id', $_GET['id']);
        $statement->bindValue(':action', 'disliked');
        $statement->execute();
        $statement = $pdo->prepare("UPDATE videos SET dislikes = :dislikes WHERE id = :id");
        $video['dislikes']++;
        $statement->bindValue(':dislikes', $video['dislikes']);
        $statement->bindValue(':id', $_GET['id']);
        $statement->execute();
      }
    }
    else{
      header('Location: forms/login.php');
    }
  }

  if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if(isset($_SESSION['username'])){
      if(isset($_POST['comment'])){
        $statement = $pdo->prepare('INSERT INTO comments (video_id, author_id, content, create_date) 
        VALUES(:video_id, :author_id, :content, :create_date)');
        $statement->bindValue('video_id', $video['id']);
        $statement->bindValue('author_id', $currentUser['id']);
        $statement->bindValue('content', $_POST['comment']);
        $statement->bindValue('create_date', date('Y-m-d H:i:s'));
        $statement->execute();
      }
    }else{
      header("Location: forms/login.php");
    }
  }

  foreach($comments as $comment){
    $str = 'like'.$comment['comment_id'];
    if(isset($_POST["$str"])){
      if(isset($_SESSION['username'])){
        $statement = $pdo->prepare("SELECT * FROM commentlikes WHERE comment_id = :comment_id AND user = :user");
        $statement->bindValue(':comment_id', $comment['comment_id']);
        $statement->bindValue(':user', $_SESSION['username']);
        $statement->execute();
        $control = $statement->fetch(PDO::FETCH_ASSOC);
        if($control){
          if($control['action'] == 'liked'){
            $statement = $pdo->prepare("UPDATE comments SET likes = :likes WHERE comment_id = :comment_id");
            $comment['likes']--;
            $statement->bindValue(':likes', $comment['likes']);
            $statement->bindValue(':comment_id', $comment['comment_id']);
            $statement->execute();
            $statement = $pdo->prepare("UPDATE commentlikes SET action = :action WHERE comment_id = :comment_id");
            $statement->bindValue(':action', '');
            $statement->bindValue(':comment_id', $control['comment_id']);
            $statement->execute();
          }else if($control['action'] == 'disliked'){
            $statement = $pdo->prepare("UPDATE comments SET likes = :likes, dislikes = :dislikes WHERE comment_id = :comment_id");
            $comment['likes']++;
            $comment['dislikes']--;
            $statement->bindValue(':likes', $comment['likes']);
            $statement->bindValue(':dislikes', $comment['dislikes']);
            $statement->bindValue(':comment_id', $comment['comment_id']);
            $statement->execute();
            $statement = $pdo->prepare("UPDATE commentlikes SET action = :action WHERE comment_id = :comment_id");
            $statement->bindValue(':action', 'liked');
            $statement->bindValue(':comment_id', $control['comment_id']);
            $statement->execute();
          }else{
            $statement = $pdo->prepare("UPDATE comments SET likes = :likes WHERE comment_id = :comment_id");
            $comment['likes']++;
            $statement->bindValue(':likes', $comment['likes']);
            $statement->bindValue(':comment_id', $comment['comment_id']);
            $statement->execute();
            $statement = $pdo->prepare("UPDATE commentlikes SET action = :action WHERE comment_id = :comment_id");
            $statement->bindValue(':action', 'liked');
            $statement->bindValue(':comment_id', $control['comment_id']);
            $statement->execute();
          }
        }else{
          $statement = $pdo->prepare("INSERT INTO commentlikes (comment_id, user, action) VALUES (:comment_id, :user, :action)");
          $statement->bindValue(':user', $_SESSION['username']);
          $statement->bindValue(':comment_id', $comment['comment_id']);
          $statement->bindValue(':action', 'liked');
          $statement->execute();
          $statement = $pdo->prepare("UPDATE comments SET likes = :likes WHERE comment_id = :comment_id");
          $comment['likes']++;
          $statement->bindValue(':likes', $comment['likes']);
          $statement->bindValue(':comment_id', $comment['comment_id']);
          $statement->execute();
        }
      }
      else{
        header('Location: forms/login.php');
      }
    }
    $str = 'dislike'.$comment['comment_id'];
    if(isset($_POST["$str"])){
      if(isset($_SESSION['username'])){
        $statement = $pdo->prepare("SELECT * FROM commentlikes WHERE comment_id = :comment_id AND user = :user");
        $statement->bindValue(':comment_id', $comment['comment_id']);
        $statement->bindValue(':user', $_SESSION['username']);
        $statement->execute();
        $control = $statement->fetch(PDO::FETCH_ASSOC);
        if($control){
          if($control['action'] == 'disliked'){
            $statement = $pdo->prepare("UPDATE comments SET dislikes = :dislikes WHERE comment_id = :comment_id");
            $comment['dislikes']--;
            $statement->bindValue(':dislikes', $comment['dislikes']);
            $statement->bindValue(':comment_id', $comment['comment_id']);
            $statement->execute();
            $statement = $pdo->prepare("UPDATE commentlikes SET action = :action WHERE comment_id = :comment_id");
            $statement->bindValue(':action', '');
            $statement->bindValue(':comment_id', $control['comment_id']);
            $statement->execute();
          }else if($control['action'] == 'liked'){
            $statement = $pdo->prepare("UPDATE comments SET likes = :likes, dislikes = :dislikes WHERE comment_id = :comment_id");
            $comment['likes']--;
            $comment['dislikes']++;
            $statement->bindValue(':likes', $comment['likes']);
            $statement->bindValue(':dislikes', $comment['dislikes']);
            $statement->bindValue(':comment_id', $comment['comment_id']);
            $statement->execute();
            $statement = $pdo->prepare("UPDATE commentlikes SET action = :action WHERE comment_id = :comment_id");
            $statement->bindValue(':action', 'disliked');
            $statement->bindValue(':comment_id', $control['comment_id']);
            $statement->execute();
          }else{
            $statement = $pdo->prepare("UPDATE comments SET dislikes = :dislikes WHERE comment_id = :comment_id");
            $comment['dislikes']++;
            $statement->bindValue(':dislikes', $comment['dislikes']);
            $statement->bindValue(':comment_id', $comment['comment_id']);
            $statement->execute();
            $statement = $pdo->prepare("UPDATE commentlikes SET action = :action WHERE comment_id = :comment_id");
            $statement->bindValue(':action', 'disliked');
            $statement->bindValue(':comment_id', $control['comment_id']);
            $statement->execute();
          }
        }else{
          $statement = $pdo->prepare("INSERT INTO commentlikes (comment_id, user, action) VALUES (:comment_id, :user, :action)");
          $statement->bindValue(':user', $_SESSION['username']);
          $statement->bindValue(':comment_id', $comment['comment_id']);
          $statement->bindValue(':action', 'disliked');
          $statement->execute();
          $statement = $pdo->prepare("UPDATE comments SET dislikes = :dislikes WHERE comment_id = :comment_id");
          $comment['dislikes']++;
          $statement->bindValue(':dislikes', $comment['dislikes']);
          $statement->bindValue(':comment_id', $comment['comment_id']);
          $statement->execute();
        }
      }
      else{
        header('Location: forms/login.php');
      }
    }
  }
  

  ?>

  <title><?php echo $video['title'] ?></title>

  <div class="main">
    <div class="theater-mode-container"></div>
    <div class="prim-sec">
      <div class="primary">
        <div id="video-container" class="video-container paused " data-volume-level="high">
          <div class="video-controls-container">
            <div class="timeline-container">
              <div class="timeline">
                <div class="thumb-indicator"></div>
              </div>
            </div>
            <div class="controls">
              <button class="play-pause-button">
                <svg class="play-icon" viewBox="0 0 24 24">
                  <path fill="currentColor" d="M8,5.14V19.14L19,12.14L8,5.14Z" />
                </svg>
                <svg class="pause-icon" viewBox="0 0 24 24">
                  <path fill="currentColor" d="M14,19H18V5H14M6,19H10V5H6V19Z" />
                </svg>
              </button>
              <div class="volume-container">
                <button class="mute-button">
                  <svg class="volume-high-icon" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M14,3.23V5.29C16.89,6.15 19,8.83 19,12C19,15.17 16.89,17.84 14,18.7V20.77C18,19.86 21,16.28 21,12C21,7.72 18,4.14 14,3.23M16.5,12C16.5,10.23 15.5,8.71 14,7.97V16C15.5,15.29 16.5,13.76 16.5,12M3,9V15H7L12,20V4L7,9H3Z" />
                  </svg>
                  <svg class="volume-low-icon" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M5,9V15H9L14,20V4L9,9M18.5,12C18.5,10.23 17.5,8.71 16,7.97V16C17.5,15.29 18.5,13.76 18.5,12Z" />
                  </svg>
                  <svg class="volume-muted-icon" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M12,4L9.91,6.09L12,8.18M4.27,3L3,4.27L7.73,9H3V15H7L12,20V13.27L16.25,17.53C15.58,18.04 14.83,18.46 14,18.7V20.77C15.38,20.45 16.63,19.82 17.68,18.96L19.73,21L21,19.73L12,10.73M19,12C19,12.94 18.8,13.82 18.46,14.64L19.97,16.15C20.62,14.91 21,13.5 21,12C21,7.72 18,4.14 14,3.23V5.29C16.89,6.15 19,8.83 19,12M16.5,12C16.5,10.23 15.5,8.71 14,7.97V10.18L16.45,12.63C16.5,12.43 16.5,12.21 16.5,12Z" />
                  </svg>
                </button>
                <input class="volume-slider" type="range" min="0" max="1" step="any" value="1">
              </div>
              <div class="duration-container">
                <div class="current-time">00:00</div>
                /
                <div class="total-time">10:00</div>
              </div>
              
              <button class="captions-button">
                <svg viewBox="0 0 24 24">
                  <path fill="currentColor" d="M18,11H16.5V10.5H14.5V13.5H16.5V13H18V14A1,1 0 0,1 17,15H14A1,1 0 0,1 13,14V10A1,1 0 0,1 14,9H17A1,1 0 0,1 18,10M11,11H9.5V10.5H7.5V13.5H9.5V13H11V14A1,1 0 0,1 10,15H7A1,1 0 0,1 6,14V10A1,1 0 0,1 7,9H10A1,1 0 0,1 11,10M19,4H5C3.89,4 3,4.89 3,6V18A2,2 0 0,0 5,20H19A2,2 0 0,0 21,18V6C21,4.89 20.1,4 19,4Z" />
                </svg>
              </button>
              <button class="speed-button wide-button">
                1x
              </button> 
              <button class="mini-player-button">
                <svg viewBox="0 0 24 24">
                  <path fill="currentColor" d="M21 3H3c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h18c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H3V5h18v14zm-10-7h9v6h-9z"/>
                </svg>
              </button>
              <button class="theater-button">
                <svg class="tall" viewBox="0 0 24 24">
                  <path fill="currentColor" d="M19 6H5c-1.1 0-2 .9-2 2v8c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm0 10H5V8h14v8z"/>
                </svg>
                <svg class="wide" viewBox="0 0 24 24">
                  <path fill="currentColor" d="M19 7H5c-1.1 0-2 .9-2 2v6c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V9c0-1.1-.9-2-2-2zm0 8H5V9h14v6z"/>
                </svg>
              </button>
              <button class="full-screen-button">
                <svg class="open" viewBox="0 0 24 24">
                  <path fill="currentColor" d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z"/>
                </svg>
                <svg class="close" viewBox="0 0 24 24">
                  <path fill="currentColor" d="M5 16h3v3h2v-5H5v2zm3-8H5v2h5V5H8v3zm6 11h2v-3h3v-2h-5v5zm2-11V5h-2v5h5V8h-3z"/>
                </svg>
              </button>
            </div>
          </div>
          <video src="<?php echo $videoPath ?>">
            <track kind="captions" srclang="en" src="subtitles.vtt">
          </video>
        </div>
        <p class="video-title"><?php echo $video['title'] ?></p>
        <div class="button-interactions">
          <a href="user-page.php?user=<?php echo $user['username'] ?>">
            <img class="channel-picture" src="<?php echo $channelPicturePath ?>" alt="channel-picture">
          </a>
          <div style="margin-right: 25px">
            <a href="user-page.php?user=<?php echo $user['username'] ?>" class="channel-name"><?php echo $video['uploader'] ?></a>
            <p class="subscriber-count"><?php echo $user['subscribers'].' subscribers' ?></p>
          </div>
          <div class="buttons">
            <form class="buttons-left" action="" method="post">
              <button type="submit" name="subscribe" class="subscribe-button"><?php
              if(isset($_SESSION['username'])){
                $statement = $pdo->prepare("SELECT * FROM subscriptions WHERE subscribing=? AND subscribedto=?");
                $statement->execute(array($_SESSION['username'], $video['uploader']));
                $control = $statement->fetch(PDO::FETCH_OBJ); 
              }
              if($control){
                echo 'Unsubscribe';}
              else{
                echo 'Subscribe';} ?>
              </button>
            </form>
            <form class="buttons-right" action="" method="post">
                <button class="like-button<?php $statement = $pdo->prepare("SELECT * FROM likes WHERE user = :user AND video_id = :video_id");
                                                $statement->bindValue(':user', $_SESSION['username']);
                                                $statement->bindValue(':video_id', $video['id']);
                                                $statement->execute();
                                                $control = $statement->fetchAll(PDO::FETCH_OBJ);
                                                if($control){
                                                  if($control[0]->action == 'liked'){
                                                    echo ' liked';
                                                  }
                                                }  ?>" type="submit" name="like">
                  <img class="like-icon" src="images/icons/like.svg" alt="like icon">
                  <img class="liked-icon" src="images/icons/liked.svg" alt="like icon">
                  <div><?php echo $video['likes'] ?></div>
                  <div class="tooltip">I like this</div>
                </button>
              <button class="dislike-button<?php $statement = $pdo->prepare("SELECT * FROM likes WHERE user = :user AND video_id = :video_id");
                                                $statement->bindValue(':user', $_SESSION['username']);
                                                $statement->bindValue(':video_id', $video['id']);
                                                $statement->execute();
                                                $control = $statement->fetchAll(PDO::FETCH_OBJ);
                                                if($control){
                                                  if($control[0]->action == 'disliked'){
                                                    echo ' disliked';
                                                  }
                                                }  ?>" type="submit" name="dislike">
                <img class="dislike-icon" src="images/icons/dislike.svg" alt="dislike icon">
                <img class="disliked-icon" src="images/icons/disliked.svg" alt="dislike icon">
                <div style="margin-left: 5px;"><?php echo $video['dislikes'] ?></div>
                <div class="tooltip">I dislike this</div>
              </button>
              <button class="share-button">
                <img class="share-icon" src="images/icons/share.svg" alt="share icon">
                Share
                <div class="tooltip">Share</div>
              </button>
              <button class="three-dots-button">. . .</button>
              </form>
          </div>
        </div>
        <div class="description">
          <span style="font-weight: 500;"><?php echo $video['views'].' views' ?> &#183; <?php echo $video['create_date'] ?></span> <br> <?php echo nl2br($video['description']) ?>
        </div>
        <div style=
          "display: flex;
          margin-bottom: 20px;">
          <img class="channel-picture pic" src="<?php if($currentUser){echo $currentChannelPicturePath;} else{echo $missing;}?>" alt="channel-picture">
          <form method="post" action="" style="width: 100%; display: flex; align-items:center; height: 34px;">
            <input name="comment" class="add-comment" type="text" placeholder="Add a comment...">
            <button style="margin-left: 10px;" class="subscribe-button" type="submit">Send</button>
          </form>
        </div>
        <?php
        foreach($comments as $comment){
          foreach($users as $userr){
            if($comment['author_id'] == $userr['id']){
              $commentUser = $userr;
            }
          }
          $commentUserPic = substr($commentUser['image'], 3) ?>
        <div class="comment">
          <a href="user-page.php?user=<?php echo $userr['username'] ?>">
            <img class="channel-picture" src="<?php echo $commentUserPic ?>" alt="chennel-picture">
          </a>
          <div class="comment-right-section">
            <div class="title-age">
              <a href="user-page.php?user=<?php echo $userr['username'] ?>" class="comment-channel-name"><?php echo $commentUser['username'] ?></a>
              <p class="comment-age"><?php echo $comment['create_date'] ?></p>
            </div>
            <div>
              <p class="comment-content"><?php echo $comment['content'] ?></p>
            </div>
            <form action="" method="post" style="margin-bottom: 15px;" class="buttons">
              <button name="<?php echo 'like'.$comment['comment_id']?>" class="comment-like-button<?php $statement = $pdo->prepare("SELECT * FROM commentlikes WHERE user = :user AND comment_id = :comment_id");
                                                                                                        $statement->bindValue(':user', $_SESSION['username']);
                                                                                                        $statement->bindValue(':comment_id', $comment['comment_id']);
                                                                                                        $statement->execute();
                                                                                                        $control = $statement->fetchAll(PDO::FETCH_OBJ);
                                                                                                        if($control){
                                                                                                          if($control[0]->action == 'liked'){
                                                                                                            echo ' liked';
                                                                                                          }
                                                                                                        }  ?>">
                <img class="like-icon" src="images/icons/like.svg" alt="like comment">
                <img class="liked-icon" src="images/icons/liked.svg" alt="like comment">
              </button>
              <p class="number-of-likes"><?php echo $comment['likes'] ?></p>
              <button name="<?php echo 'dislike'.$comment['comment_id'] ?>" class="comment-dislike-button<?php $statement = $pdo->prepare("SELECT * FROM commentlikes WHERE user = :user AND comment_id = :comment_id");
                                                                                                        $statement->bindValue(':user', $_SESSION['username']);
                                                                                                        $statement->bindValue(':comment_id', $comment['comment_id']);
                                                                                                        $statement->execute();
                                                                                                        $control = $statement->fetchAll(PDO::FETCH_OBJ);
                                                                                                        if($control){
                                                                                                          if($control[0]->action == 'disliked'){
                                                                                                            echo ' disliked';
                                                                                                          }
                                                                                                        }  ?>">
                <img class="dislike-icon" src="images/icons/dislike.svg" alt="dislike comment">
                <img class="disliked-icon" src="images/icons/disliked.svg" alt="dislike comment">
              </button>
              <p class="number-of-likes"><?php echo $comment['dislikes'] ?></p>
             </form>
            
          </div>
        </div>
        <?php } ?>
      </div>
  
      <div class="secondary">
        <?php foreach($videos as $vid){
           $thumbnailPath =  substr($vid['thumbnail'], 3);
           $videoPath =  substr($vid['video'], 3); ?>
        <div class="recommended">
          <a href="video-page.php?id=<?php echo $vid['id'] ?>" class="recom-thumbnail-container">
            <img class="recom-thumbnail" src="<?php echo $thumbnailPath ?>" alt="thumbnail picture">
            <div class="time"><?php echo $vid['length'] ?></div>
          </a>
          <div class="recom-text">
            <div>
              <a href="video-page.php?id=<?php echo $vid['id'] ?>" class="recom-title"><?php echo $vid['title'] ?></a>
            </div>
            <div>
              <a href="user-page.php?user=<?php echo $vid['uploader'] ?>" class="recom-channel-name"><?php echo $vid['uploader'] ?></a>
              <p class="recom-channel-name">
                <?php echo $vid['views'].' views' ?> &#183; <?php echo $vid['create_date'] ?>
              </p>
            </div>
          </div>
        </div>
        <?php } ?>
      </div>
    </div>
    </div>
    
  </div>
</body>
</html>