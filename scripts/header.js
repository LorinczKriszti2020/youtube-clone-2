// Toggle dropdown in Header

let img = document.getElementById("img");
let dropdown = document.getElementById("dropdown");

img.addEventListener("click", toggleMenu);
document.addEventListener("click", closeMenu);

function toggleMenu(event){
  dropdown.classList.toggle("down");
}

function closeMenu(event){
  if(event.target.classList[0] !== "my-channel"){
    dropdown.classList.remove("down");
  }
}