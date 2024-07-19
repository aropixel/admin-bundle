document.addEventListener('DOMContentLoaded', (event) => {

  openSearchNav();
  closeSearchNav();
  closeSearchNavOnEscape();

  openFullScreenMenu();
  closeFullScreenMenu();

  filterMenuItem();
  goToFirstMenuItem();

});

const openSearchNav = () => {

  const input = document.querySelector('#search-nav input');
  const body = document.querySelector('#search-nav .body');

  input.addEventListener('click', (event) => {

    body.classList.add("active");

  })

}

const closeSearchNav = () => {

  const nav = document.getElementById('search-nav');
  const body = document.querySelector('#search-nav .body');

  let lastMouseDownX = 0;
  let lastMouseDownY = 0;
  let lastMouseDownWasOutside = false;

  const mouseDownListener = (event) => {

    lastMouseDownX = event.offsetX;
    lastMouseDownY = event.offsetY;
    lastMouseDownWasOutside = !$(event.target).closest(nav).length;

    if (lastMouseDownWasOutside) {
      body.classList.remove("active");
    }

  }

  document.addEventListener('mousedown', mouseDownListener);

}

const closeSearchNavOnEscape = () => {

  const body = document.querySelector('#search-nav .body');
  let input = document.querySelector('#search-nav input');

  input.addEventListener('keydown', function(event) {

    if (event.key === "Escape" && input.value.length === 0) {

      body.classList.remove("active");

    }

  });

}

const openFullScreenMenu = () => {

  const body = document.querySelector('body');
  const burger = document.getElementById('burgerMenu');
  const menu = document.getElementById('fullscreen-menu');

  burger.addEventListener('click', (event) => {

    menu.classList.remove("hide");
    body.style.overflowY = 'hidden';

  })

}

const closeFullScreenMenu = () => {

  const body = document.querySelector('body');
  const closeBtn = document.getElementById('close-fullscreen-menu');
  const menu = document.getElementById('fullscreen-menu');

  closeBtn.addEventListener('click', (event) => {

    menu.classList.add("hide");
    body.style.overflowY = 'scroll';

  })

}

const filterMenuItem = () => {

  let input, filter, ul, li, a, i, txtValue, quickLinksSection, searchSection, messageSection;
  input = document.querySelector('#search-nav input');
  ul = document.getElementById("sub-sections");
  quickLinksSection = document.getElementById("quick-links-section");
  searchSection = document.getElementById("search-section");
  messageSection = document.getElementById('no-result-section');

  if (ul) {

    li = ul.getElementsByTagName("li");

    input.addEventListener('keyup', function(event) {

      filter = input.value.toUpperCase();
      filter = filter.normalize('NFD').replace(/\p{Diacritic}/gu, "");

      if (!input.value) {

        searchSection.style.display = "none";
        messageSection.style.display = "none";
        quickLinksSection.style.display = "block";
        return;

      }

      for (i = 0; i < li.length; i++) {

        a = li[i].getElementsByTagName("a")[0];
        let txtValue = a.textContent || a.innerText;
        txtValue = txtValue.normalize('NFD').replace(/\p{Diacritic}/gu, "");


        if (!li[i].classList.contains('sub-section')) {

          if (txtValue.toUpperCase().indexOf(filter) > -1) {

            quickLinksSection.style.display = "none";
            searchSection.style.display = "block";

            li[i].style.display = "";
            li[i].parentElement.parentElement.style.display = "block";

          } else {

            li[i].style.display = "none";
            let subSection = li[i].parentElement.parentElement;

            hideEmptySubSection(subSection);

          }

        }

      }

      displayEmptySearchMessage();

    });

  }

}

const goToFirstMenuItem = () => {

  let input = document.querySelector('#search-nav input');

  input.addEventListener('keypress', function(event) {

    if (event.key === "Enter") {

      let subSections = document.querySelectorAll(".sub-section li");

      for (const section of subSections) {
        if (window.getComputedStyle(section).display !== "none") {

          document.location = section.getElementsByTagName('a')[0].href;
          break;

        }
      }

    }

  });

}


const hideEmptySubSection = (subSection) => {

  let li = subSection.getElementsByTagName("li");
  let divsArray = [].slice.call(li);

  let displayShow = divsArray.filter(function(el) {
    return getComputedStyle(el).display !== "none";
  });

  let itemsLength = displayShow.length;

  if (itemsLength === 0) {
    subSection.style.display = "none";
  }

}

const displayEmptySearchMessage = () => {

  let subSections = document.querySelectorAll(".sub-section li");
  let searchSection = document.getElementById("search-section");
  let sectionsArray = [].slice.call(subSections);
  let message = document.getElementById('no-result-section');

  let displayShow = sectionsArray.filter(function(el) {
    return getComputedStyle(el).display !== "none";
  });

  if (displayShow.length === 0) {

    message.style.display = "block";
    searchSection.style.display = "none";

  } else {

    message.style.display = "none";

  }

}