<?php
session_start();
require_once "../database.php";

$statement = $pdo->prepare('SELECT * FROM users WHERE username = :username');
$statement->bindValue(':username', $_SESSION['username']);
$statement->execute();
$user = $statement->fetch(PDO::FETCH_OBJ);

$statement = $pdo->prepare('SELECT * FROM commentlikes WHERE user = :user');
$statement->bindValue(':user', $_SESSION['username']);
$statement->execute();
$commentlikes = $statement->fetchAll(PDO::FETCH_OBJ);

foreach($commentlikes as $commentlike){
  $statement = $pdo->prepare('SELECT * FROM comments WHERE comment_id = :comment_id');
  $statement->bindValue(':comment_id', $commentlike->comment_id);
  $statement->execute();
  $comment = $statement->fetch(PDO::FETCH_OBJ);
  if($comment){
    if($commentlike->action == 'liked'){
      $likes = $comment->likes - 1;
      $statement = $pdo->prepare('UPDATE comments SET likes = :likes WHERE comment_id = :comment_id');
      $statement->bindValue(':likes', $likes);
      $statement->bindValue(':comment_id', $commentlike->comment_id);
      $statement->execute();
    }else if($commentlike->action == 'disliked'){
      $likes = $comment->dislikes - 1;
      $statement = $pdo->prepare('UPDATE comments SET dislikes = :dislikes WHERE comment_id = :comment_id');
      $statement->bindValue(':dislikes', $likes);
      $statement->bindValue(':comment_id', $commentlike->comment_id);
      $statement->execute();
    }
  }
  $statement = $pdo->prepare('DELETE FROM commentlikes WHERE user = :user');
  $statement->bindValue(':user', $_SESSION['username']);
  $statement->execute();
}

$statement = $pdo->prepare('SELECT * FROM comments WHERE author_id = :author_id');
$statement->bindValue(':author_id', $user->id);
$statement->execute();
$comments = $statement->fetchAll(PDO::FETCH_OBJ);

foreach($comments as $comment){
  $statement = $pdo->prepare('DELETE FROM comments WHERE author_id = :author_id');
  $statement->bindValue(':author_id', $user->id);
  $statement->execute();
}

$statement = $pdo->prepare('SELECT * FROM likes WHERE user = :user');
$statement->bindValue(':user', $_SESSION['username']);
$statement->execute();
$likes = $statement->fetchAll(PDO::FETCH_OBJ);

foreach($likes as $like){
  $statement = $pdo->prepare('SELECT * FROM videos WHERE id = :video_id');
  $statement->bindValue(':video_id', $like->video_id);
  $statement->execute();
  $video = $statement->fetch(PDO::FETCH_OBJ);
  if($video){
    if($like->action == 'liked'){
      $vlikes = $video->likes - 1;
      $statement = $pdo->prepare('UPDATE videos SET likes = :likes WHERE id = :video_id');
      $statement->bindValue(':likes', $vlikes);
      $statement->bindValue(':video_id', $like->video_id);
      $statement->execute();
    }else if($like->action == 'disliked'){
      $vlikes = $video->dislikes - 1;
      $statement = $pdo->prepare('UPDATE videos SET dislikes = :dislikes WHERE id = :video_id');
      $statement->bindValue(':dislikes', $vlikes);
      $statement->bindValue(':video_id', $like->video_id);
      $statement->execute();
    }
  }
  $statement = $pdo->prepare('DELETE FROM likes WHERE user = :user');
  $statement->bindValue(':user', $_SESSION['username']);
  $statement->execute();
}

$statement = $pdo->prepare('SELECT * FROM subscriptions WHERE subscribing = :subscribing');
$statement->bindValue(':subscribing', $_SESSION['username']);
$statement->execute();
$subscribing = $statement->fetchAll(PDO::FETCH_OBJ);

foreach($subscribing as $subing){
  $statement = $pdo->prepare('SELECT * FROM users WHERE username = :subscribedto');
  $statement->bindValue(':subscribedto', $subing->subscribedto);
  $statement->execute();
  $sub = $statement->fetch(PDO::FETCH_OBJ);

  $statement = $pdo->prepare('UPDATE users SET subscribers = :subscribers WHERE username = :subscribedto');
  $statement->bindValue(':subscribedto', $subing->subscribedto);
  $subs = $sub->subscribers - 1;
  $statement->bindValue(':subscribers', $subs);
  $statement->execute();

  $statement = $pdo->prepare('DELETE FROM subscriptions WHERE subscribing = :user');
  $statement->bindValue(':user', $_SESSION['username']);
  $statement->execute();
}


$statement = $pdo->prepare('SELECT * FROM videos WHERE uploader = :uploader');
$statement->bindValue(':uploader', $_SESSION['username']);
$statement->execute();
$videos = $statement->fetchAll(PDO::FETCH_OBJ);

foreach($videos as $video){
  $file = $video->video;
  unlink($file);
  $file = $video->thumbnail;
  unlink($file);
  rmdir('../videos/'.$video->code.'/');
  rmdir('../images/thumbnails/'.$video->code.'/');

  $statement = $pdo->prepare('DELETE FROM videos WHERE uploader = :uploader');
  $statement->bindValue(':uploader', $_SESSION['username']);
  $statement->execute();
}

if($user->image){
  unlink($user->image);
}
rmdir('../images/channelpictures/'.$user->username.'/');
$statement = $pdo->prepare('DELETE FROM users WHERE id = :id');
$statement->bindValue(':id', $user->id);
$statement->execute();

session_destroy();
header('Location: ../index.php');