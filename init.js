/**
 * Init
 * Author: Taigo Ito (https://qwel.design/)
 * Location: Fukui, Japan
 */

(() => {
  const elem = document.querySelector('.footer__copyright');
  if (!elem) return;
  const today = new Date();
  const year = today.getFullYear();
  elem.innerHTML = `&copy; 2020 - ${year} 海辺の農園宿 つかさ丸`;
  
})();

// Back To Top
new BackToTop();

// Drawer Menu
new DrawerMenu();

// Embed
new Embed();

// Fader
new Fader();

// Evil Icons
new EvilIcons();

// Preloader
new Preloader();

// Responsive Color
new ResponsiveColor();

// Slider
new Slider();
