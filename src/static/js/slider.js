var $ = require('jquery');

module.exports = (function() {
  var selector = '.fb-slider';

  /**
   * init the slider
   *
   * @param cb (function)
   *   - an optional callback function to run after
   *      the slider loads
   */
  function init(cb) {
    var itemWidth = $(selector).closest('#fb-modal').length > 0 ? 90 : 120;

    $(selector).flexslider({
      namespace: "fb-slider-",
      animation: "slide",
      selector: ".slides > li",
      slideshow: false,
      minItems: 2,
      itemWidth: itemWidth,
      maxItems: 7,
      move: 1,
      controlNav: false,
      start: function() {
        if (typeof cb === 'function') {
          cb();
        }
      }
    });
  }

  return {
    init: init
  };
})();
