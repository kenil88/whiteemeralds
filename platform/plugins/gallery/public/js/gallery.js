(()=>{"use strict";function t(e){return t="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},t(e)}function e(t,e){for(var o=0;o<e.length;o++){var r=e[o];r.enumerable=r.enumerable||!1,r.configurable=!0,"value"in r&&(r.writable=!0),Object.defineProperty(t,n(r.key),r)}}function n(e){var n=function(e,n){if("object"!=t(e)||!e)return e;var o=e[Symbol.toPrimitive];if(void 0!==o){var r=o.call(e,n||"default");if("object"!=t(r))return r;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===n?String:Number)(e)}(e,"string");return"symbol"==t(n)?n:n+""}var o=function(){return t=function t(){!function(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}(this,t)},(n=[{key:"init",value:function(){var t=document.getElementById("list-photo");t&&(imagesLoaded(t,(function(){new Masonry(t,{isOriginLeft:"rtl"!==$("body").prop("dir")})})),jQuery().lightGallery&&($(t).lightGallery({loop:!0,thumbnail:!0,fourceAutoply:!1,autoplay:!1,pager:!1,speed:300,scale:1,keypress:!0}),$(document).on("click",".lg-toogle-thumb",(function(){$(document).find(".lg-sub-html").toggleClass("inactive")}))))}}])&&e(t.prototype,n),o&&e(t,o),Object.defineProperty(t,"prototype",{writable:!1}),t;var t,n,o}();$((function(){(new o).init()}))})();