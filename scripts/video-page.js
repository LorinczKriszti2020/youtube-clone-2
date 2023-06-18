const videoContainer = document.querySelector(".video-container");
const video = document.querySelector("video");

// Model

let numberOfLikes = 0;

const savedLikes = parseInt(localStorage.getItem('numOfLikes'));

function saveLikes(){
  localStorage.setItem('numOfLikes', numberOfLikes);
}

// View

// View Modes

const theaterBtn = document.querySelector(".theater-button");
const fullScreenBtn = document.querySelector(".full-screen-button");
const miniPlayerBtn = document.querySelector(".mini-player-button");
const primary = document.querySelector(".primary");
const destination = document.querySelector(".theater-mode-container");

theaterBtn.addEventListener("click", toggleTheater);
fullScreenBtn.addEventListener("click", toggleFullScreen);
miniPlayerBtn.addEventListener("click", toggleMiniPlayer);

let isFragmented = false;
function toggleTheater(){
  videoContainer.classList.toggle("theater");
  if(isFragmented){
    let fragment = document.createDocumentFragment();
    fragment.appendChild(videoContainer);
    isFragmented = true;
  }
  if(videoContainer.classList.contains("theater")){
    destination.appendChild(videoContainer);
  }else{
    primary.prepend(videoContainer);
  }
}

function toggleFullScreen(){
  if(document.fullscreenElement == null){
    videoContainer.requestFullscreen();
  }else{
    document.exitFullscreen();
  }
}

document.addEventListener("fullscreenchange", () => { 
  videoContainer.classList.toggle("full-screen", document.fullscreenElement)
});

function toggleMiniPlayer(){
  if(document.pictureInPictureElement == null){
    video.requestPictureInPicture();
  }else{
    document.exitPictureInPicture();
  }
}

// Controller

document.addEventListener("keydown", e => {
  const tagName = document.activeElement.tagName.toLocaleLowerCase();

  if(tagName === "input") return

  switch(e.key.toLowerCase()) {
    case " ":
      if(tagName === "button") return
    case "k":
      togglePlay()
      break
    case "f":
      toggleFullScreen()
      break
    case "t":
      toggleTheater()
      break
    case "i":
      toggleMiniPlayer()
      break
    case "m":
      toggleMute()
      break
    case "arrowleft":
    case "j":
      skip(-5)
      break
    case "arrowright":
    case "l":
      skip(5)
      break
    case "c":
      toggleCaptions()
      break
  }
});

// Play/Pause

const playPauseBtn = document.querySelector(".play-pause-button");

playPauseBtn.addEventListener("click", togglePlay);
video.addEventListener("click", togglePlay);

function togglePlay(){
  video.paused ? video.play() : video.pause();
}

video.addEventListener("play", () => {
  videoContainer.classList.remove("paused");
});

video.addEventListener("pause", () => {
  videoContainer.classList.add("paused");
});

// Volume Control

const volumeSlider = document.querySelector(".volume-slider");
const muteBtn = document.querySelector(".mute-button");

muteBtn.addEventListener("click", toggleMute);
volumeSlider.addEventListener("input", e => {
  video.volume = e.target.value;
  video.muted = e.target.value === 0;
})

function toggleMute(){
  video.muted = !video.muted;
}

video.addEventListener("volumechange", () => {
  volumeSlider.value = video.volume;
  let volumeLevel;
  if(video.muted || video.volume === 0){
    volumeSlider.value = 0;
    volumeLevel = "muted";
  } else if (video.volume <= .5){
    volumeLevel = "low";
  } else{
    volumeLevel = "high";
  }

  videoContainer.dataset.volumeLevel = volumeLevel;
});

// Duration

const currentTimeElem = document.querySelector(".current-time");
const totalTimeElem = document.querySelector(".total-time");

video.addEventListener("loadeddata", () => {
  totalTimeElem.textContent = formatDuration(video.duration);
});

video.addEventListener("timeupdate", () => {
  currentTimeElem.textContent = formatDuration(video.currentTime);
  const percent = video.currentTime / video.duration;
  timelineContainer.style.setProperty("--progress-position", percent);
});

const leadingZeroFormatter = new Intl.NumberFormat(undefined, {
  minimumIntegerDigits: 2
})

function formatDuration(time){
  const seconds = Math.floor(time % 60);
  const minutes = Math.floor(time / 60) % 60;
  const hours = Math.floor(time / 3600);

  if(hours === 0) {
    return `${minutes}:${leadingZeroFormatter.format(seconds)}`
  } else{
    return `${hours}:${leadingZeroFormatter.format(minutes)}:${leadingZeroFormatter.format(seconds)}`
  }
}

function skip(time){
  video.currentTime += time;
}

// Captions

const captionsBtn = document.querySelector(".captions-button");

const captions = video.textTracks[0];
captions.mode = "hidden";

captionsBtn.addEventListener("click", toggleCaptions);

function toggleCaptions(){
  const isHidden = captions.mode === "hidden";
  captions.mode = isHidden ? "showing" : "hidden";
  videoContainer.classList.toggle("captions", isHidden);
}

// Playback rate

const speedBtn = document.querySelector(".speed-button");

speedBtn.addEventListener("click", changePlaybackSpeed);

function changePlaybackSpeed(){
  let newPlaybackSpeed = video.playbackRate + 0.25;
  if(newPlaybackSpeed > 2){
    newPlaybackSpeed = 0.25;
  }
  video.playbackRate = newPlaybackSpeed;
  speedBtn.textContent = `${newPlaybackSpeed}x`;
}

// Timeline

const timelineContainer = document.querySelector(".timeline-container");

timelineContainer.addEventListener("mousemove", handleTimelineUpdate);
timelineContainer.addEventListener("mousedown", toggleScrubbing);
document.addEventListener("mouseup", e => {
  if(isScrubbing) toggleScrubbing(e);
});
document.addEventListener("mousemove", e => {
  if(isScrubbing) toggleScrubbing(e);
});

let isScrubbing = false;
let wasPaused;
function toggleScrubbing(e){
  const rect = timelineContainer.getBoundingClientRect();
  const percent = Math.min(Math.max(0, e.x - rect.x), rect.width) / rect.width;
  isScrubbing = (e.buttons & 1) === 1;
  videoContainer.classList.toggle("scrubbing", isScrubbing);
  if(isScrubbing){
    wasPaused = video.paused;
    video.pause();
  }else{
    video.currentTime = percent * video.duration;
    if(!wasPaused) video.play();
  }

  handleTimelineUpdate(e);
}

function handleTimelineUpdate(e){
  const rect = timelineContainer.getBoundingClientRect();
  const percent = Math.min(Math.max(0, e.x - rect.x), rect.width) / rect.width;
  timelineContainer.style.setProperty("--preview-position", percent);

  if(isScrubbing){
    e.preventDefault();
    timelineContainer.style.setProperty("--progress-position", percent);
  }
}

// Like Button

const likeButton = document.querySelector(".like-button");
const dislikeButton = document.querySelector(".dislike-button");
const commentLikeButton = document.querySelector(".comment-like-button");
const commentDislikeButton = document.querySelector(".comment-dislike-button");

if(savedLikes > 0){
  numberOfLikes = savedLikes;
}else{
  numberOfLikes = 0;
}

//console.log(likeButton.value);

function onLike(){
  if(dislikeButton.classList.contains("disliked"))  dislikeButton.classList.toggle("disliked");
  likeButton.classList.toggle("liked");

  numberOfLikes += 1;
  numOfLikesElement.innerText= numberOfLikes;

  saveLikes();
  render();
}

function onDislike(){
  if(likeButton.classList.contains("liked"))  likeButton.classList.toggle("liked");
  dislikeButton.classList.toggle("disliked");
}

// Comment like/dislike

commentLikeButton.addEventListener("click", () => {
  if(commentDislikeButton.classList.contains("disliked")) commentDislikeButton.classList.toggle("disliked");
  commentLikeButton.classList.toggle("liked");
});
commentDislikeButton.addEventListener("click", () => {
  if(commentLikeButton.classList.contains("liked")) commentLikeButton.classList.toggle("liked");
  commentDislikeButton.classList.toggle("disliked");
});

// Reply-form

const replyForm = document.querySelectorAll(".reply-form");
const replyButton = document.querySelectorAll(".reply-button");

let id;
replyButton.forEach(but => {
  id = but.id.slice(-1);
  but.addEventListener("click", function(id){
    replyForm.forEach(form => {
      if(form.id.slice(-1) == id.target.id.slice(-1)){
        form.classList.toggle("shown");
        console.log("button "+id.target.id.slice(-1));
      }
    });
  });
});