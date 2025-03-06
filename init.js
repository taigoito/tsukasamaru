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
import BackToTop from './js/_backToTop.js';
new BackToTop();

// Drawer Menu
import DrawerMenu from './js/_drawerMenu.js';
new DrawerMenu();

// Embed
import Embed  from './js/_embed.js';
new Embed();

// Fader
import Fader from './js/_fader.js';
new Fader();

// Evil Icons
import EvilIcons from './js/_evilIcons.js';
new EvilIcons();

// Preloader
//import Preloader from './js/_preloader.js';
//new Preloader();

// Responsive Color
//import ResponsiveColor from './js/_responsiveColor.js';
//new ResponsiveColor();

// Slider
import Slider from './js/_slider.js';
new Slider();
