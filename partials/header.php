<?php

$pdo = new PDO('mysql:host=localhost;port=3306;dbname=youtube_videos', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//require_once "../database.php";

session_start();

if(isset($_SESSION['username'])){
  $statement = $pdo->prepare("SELECT * FROM users WHERE username=?");
  $statement->execute(array($_SESSION['username']));
  $control = $statement->fetch(PDO::FETCH_OBJ);
  
  $imagePath = $control->image;
  $imagePath = substr($imagePath, 3);
}
?>

<link rel="stylesheet" href="styles/video-grid.css">
<link rel="stylesheet" href="styles/header.css">
<link rel="stylesheet" href="styles/sidebar.css">
<link rel="stylesheet" href="styles/video-page.css">
<link rel="stylesheet" href="styles/video-page-general.css">
<script src="scripts/header.js" defer></script>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body style="padding-right: 20px;">

<div class="header">
  <div class="left-section">
    <a href="index.php">
      <img style="margin-left: 25px;" class="youtube-logo" src="images/icons/youtube-logo.svg" alt="youtube logo">
    </a>
  </div>
  <form method="get" action="search.php" class="middle-section">
    <input name="search" class="search-bar" type="text" placeholder="Search" value="<?php if(isset($_GET['search'])){ echo $_GET['search']; } ?>">
    <button type="submit" class="search-button">
      <img class="search-icon" src="images/icons/search.svg" alt="search button">
      <div class="tooltip">Search</div>
    </button>
  </form>
  <div class="right-section">
    <a style="padding-left: 70px;" href="forms/upload.php" class="container">
      <img class="upload-icon" src="images/icons/upload.svg" alt="upload button">
      <div class="tooltip">Create</div>
    </a>

    <?php if(isset($_SESSION['username'])){?>
      <div style="position: relative; cursor: pointer;" id="img">
        <img class="my-channel" src="<?php echo $imagePath ?>">
        <div class="dropdown" id="dropdown">
          <div class="channel-info">
            <img class="my-channel" src="<?php echo $imagePath ?>">
            <p style="margin-left: 5px; font-weight: 500;"><?php echo $_SESSION['username'] ?></p>
          </div>
          <a style="width: 100%;" href="forms/update.php">
            <button class="dropdown-button">Change profile picture</button>
          </a>
          <a style="width: 100%;" href="forms/change-account.php">
            <button class="dropdown-button">Change account</button>
          </a>
          <a style="width: 100%;" href="forms/delete-account.php">
            <button class="dropdown-button">Delete account</button>
          </a>
          <a style="width: 100%;" href="forms/logout.php">
            <button class="dropdown-button">Log out</button>
          </a>
        </div>
    </div>
    <?php }else{?>
      <a href="forms/login.php">
        <button class="login-button">
          <img src="images/channelpictures/missing.svg" alt="missing channel picture">
          <p style="margin-left: 5px; margin-right: 5px;">Sign in</p>
        </button>
      </a>
    <?php } ?>
  </div>
</div>
