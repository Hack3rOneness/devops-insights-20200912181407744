var Utils = require('./utils');
var $ = require('jquery');

module.exports = (function() {
  var $body = $('body');

  var LOAD_EXT = '.php',
      ACTIVE_CLASS = 'visible',
      POPUP_CLASSES = 'fb-modal-wrapper modal--popup',
      DEFAULT_CLASSES = 'fb-modal-wrapper modal--default',
      MODAL_DIR = 'inc/modals/',
      $modalContainer,
      $modal,
      $countryHover;

  /**
   * initialize the modal, including grabbing the modal div and
   *  setting up event listeners
   */
  function init() {
    $modal = $('#fb-modal');
    $modalContainer = $('#fb-main-content');
    $countryHover = $('#fb-country-popup');

    // close the modal
    $body.on('click', '.js-close-modal', close);
  }

  /**
   * close the modal
   *
   * @param event (object)
   *   - if this function call comes from an event listener,
   *      prevent the default action
   */
  function close(event) {
    if (event) {
      event.preventDefault();
    }

    $('div[id^="fb-modal"]').removeClass(ACTIVE_CLASS);
  }

  /**
   * close the poup
   *
   * @param event (object)
   *   - if this function call comes from an event listener,
   *      prevent the default action
   */
  function closeHoverPopup(event) {
    if (event) {
      event.preventDefault();
    }

    $countryHover.removeClass(ACTIVE_CLASS);
  }

  /**
   * call Utils.loadComponent for the modal content
   *
   * @param $modal (jquery object)
   *   - the modal jquery object to load the content into
   *
   * @param loadPath (string)
   *   - the path to the modal file
   *
   * @param cb (function)
   *   - the callback
   */
  function openAndLoad($modal, loadPath, cb) {
    Utils.loadComponent($modal, loadPath, function() {
      if (typeof cb === 'function') {
        cb();
      }
      $modal.addClass(ACTIVE_CLASS);
    });
  }

  /* --------------------------------------------
   * --the modal rendering
   * -------------------------------------------- */

  /**
   * there are two types of modals - default and popup. The
   *  default modal takes up a full page, while the poup modal
   *  creates a popup box for content. Both of these wrapper
   *  functions take the same parameters.
   *
   * @param modalName (string)
   *   - the filename of the modal content you're looking to
   *      load up, without the trailing .html
   *
   * @param cb (function)
   *   - a callback function for after the modal content loads
   */
  function loadPopup(modalName, cb) {
    _load(modalName, modalName, POPUP_CLASSES, MODAL_DIR, LOAD_EXT, cb);
  }

  function load(modalName, cb) {
    _load(modalName, modalName, DEFAULT_CLASSES, MODAL_DIR, LOAD_EXT, cb);
  }

  function loadController(modalName, originalName, cb) {
    _load(modalName, originalName, DEFAULT_CLASSES, '', '', cb);
  }

  function loadPopupController(modalName, originalName, cb) {
    _load(modalName, originalName, POPUP_CLASSES, '', '', cb);
  }

  /**
   * load and create
   *
   * @param modalName (string)
   *   - the filename of the modal content you're looking to
   *      load up
   *
   * @param modalClasses (string)
   *   - the classes for the modal
   *
   * @param loadDir (string)
   *   - the location in the filesystem where we're looking for
   *      the file
   *
   * @param cb (function)
   *   - a callback function for after the modal content loads
   *
   */
  function _load(modalName, className, modalClasses, loadDir, loadExt, cb) {
    var loadPath = loadDir + modalName + loadExt;
    closeHoverPopup();
    modalClasses += ' modal--' + className;

    if ($modal.length === 0) {
      $modal = $('<div id="fb-modal" class="' + modalClasses + '" />').appendTo($modalContainer);
    } else {
      $modal.removeAttr('class').addClass(modalClasses);
    }

    openAndLoad($modal, loadPath, cb);
  }

  /**
   * create a persistent modal. This is used to build a modal
   *  that is very specific, and should be loaded as quickly
   *  as possible after initially loaded, like the command line
   *  modal.
   *
   * @param modalName (string)
   *   - the name of the module being loaded
   *
   * @param cb (function)
   *   - callback funtion for after the persistent modal is loaded
   */
  function loadPersistent(modalPath, modalName, cb) {
    var loadPath = modalPath,
        modalId = 'fb-modal-persistent--' + modalName,
        $modal = $(modalId);

    if ($modal.length === 0) {
      $modal = $('<div id="' + modalId + '" class="' + POPUP_CLASSES + '" />').appendTo($modalContainer);
    }
    Utils.loadComponent($modal, loadPath, cb);
  }

  /**
   * open the persistent modal
   *
   * @param modalName (string)
   *   - the name of the modal to open
   */
  function openPersistent(modalName) {
    var modalId = '#fb-modal-persistent--' + modalName;
    $(modalId).addClass(ACTIVE_CLASS);
  }

  /**
   * a specific function for rendering the country hover popup
   *
   * @param cb (function)
   *   - a callback to render the country data in the popup
   */
  function countryHoverPopup(cb) {
    var loadPath = 'index.php?p=country&modal=popup';
    if ($countryHover.length === 0) {
      $countryHover = $('<div id="fb-country-popup" class="fb-popup-content popup--hover fb-section-border" />').appendTo($modalContainer);
    }
    openAndLoad($countryHover, loadPath, cb);
  }

  /**
   * a specific function for rendering a inactive country hover popup
   *
   * @param cb (function)
   *   - a callback to render the country data in the popup
   */
  function countryInactiveHoverPopup(cb) {
    var loadPath = 'index.php?p=country&modal=inactive';
    if ($countryHover.length === 0) {
      $countryHover = $('<div id="fb-country-popup" class="fb-popup-content popup--hover fb-section-border" />').appendTo($modalContainer);
    }
    openAndLoad($countryHover, loadPath, cb);
  }

  /**
   * the code for the popup in the view-only mode
   *
   * @param cb (function)
   *   - a callback to render the country data in the popup
   */
  function viewmodePopup(cb) {
    var loadPath = 'index.php?p=country&modal=viewmode';
    if ($countryHover.length === 0) {
      $countryHover = $('<div id="fb-country-popup" class="fb-popup-content popup--view-only" />').appendTo($modalContainer);
    }

    openAndLoad($countryHover, loadPath, cb);
  }

  return {
    init: init,
    // loads the basic modal
    load: load,
    // loads a persistent modal
    loadPersistent: loadPersistent,
    // open a persistent modal
    openPersistent: openPersistent,
    // load a popup modal
    loadPopup: loadPopup,
    loadController: loadController,
    loadPopupController: loadPopupController,
    // load and show the popup modal for a country hover
    countryHoverPopup: countryHoverPopup,
    // load and show the popup modal for an inactive country hover
    countryInactiveHoverPopup: countryInactiveHoverPopup,
    // load and show the view only country info
    viewmodePopup: viewmodePopup,
    // close the popup modal for a country hover
    closeHoverPopup: closeHoverPopup,
    // close the regular modal
    close: close
  };
})();
