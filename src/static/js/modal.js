var Utils = require('./utils');
var $ = require('jquery');

var ACTIVE_CLASS = 'visible',
    POPUP_CLASSES = 'fb-modal-wrapper modal--popup',
    DEFAULT_CLASSES = 'fb-modal-wrapper modal--default';

var $modalContainer,
    $modal,
    $countryHover;

function _load(modalParams, modalClasses, cb) {
  var loadPath = 'index.php?' + modalParams;
  closeHoverPopup();

  if ($modal.length === 0) {
    $modal = $('<div id="fb-modal" class="' + modalClasses + '" />').appendTo($modalContainer);
  } else {
    $modal.removeAttr('class').addClass(modalClasses);
  }

  openAndLoad($modal, loadPath, cb);
}

function closeHoverPopup(event) {
  if (event) {
    event.preventDefault();
  }

  $countryHover.removeClass(ACTIVE_CLASS);
}

function openAndLoad($modal, loadPath, cb) {
  Utils.loadComponent($modal, loadPath, function() {
    if (typeof cb === 'function') {
      cb();
    }
    $modal.addClass(ACTIVE_CLASS);
  });
}

module.exports = {
  init: function() {
    $modal = $('#fb-modal');
    $modalContainer = $('#fb-main-content');
    $countryHover = $('#fb-country-popup');

    $('body').on('click', '.js-close-modal', this.close);
  },

  close: function(event) {
    if (event) {
      event.preventDefault();
    }

    $('div[id^="fb-modal"]').removeClass(ACTIVE_CLASS);
  },

  /**
   * there are two types of modals - default and popup. The
   *  default modal takes up a full page, while the poup modal
   *  creates a popup box for content. Both of these wrapper
   *  functions take the same parameters.
   */

  load: function(modalName, className, cb) {
    var modalClasses = DEFAULT_CLASSES + ' modal--' + className;
    _load(modalName, modalClasses, cb);
  },

  loadPopup: function(modalParams, className, cb) {
    var modalClasses = POPUP_CLASSES + ' modal--' + className;
    _load(modalParams, modalClasses, cb);
  },

  /**
   * create a persistent modal. This is used to build a modal
   *  that is very specific, and should be loaded as quickly
   *  as possible after initially loaded, like the command line
   *  modal.
   */
  loadPersistent: function(modalParams, id, cb) {
    var loadPath = 'index.php?' + modalParams,
        modalId = 'fb-modal-persistent--' + id,
        $modal = $(modalId);

    if ($modal.length === 0) {
      $modal = $('<div id="' + modalId + '" class="' + POPUP_CLASSES + '" />').appendTo($modalContainer);
    }
    Utils.loadComponent($modal, loadPath, cb);
  },

  openPersistent: function(id) {
    var modalId = '#fb-modal-persistent--' + id;
    $(modalId).addClass(ACTIVE_CLASS);
  },

  countryHoverPopup: function(cb) {
    var loadPath = 'index.php?p=country&modal=popup';
    if ($countryHover.length === 0) {
      $countryHover = $('<div id="fb-country-popup" class="fb-popup-content popup--hover fb-section-border" />').appendTo($modalContainer);
    }
    openAndLoad($countryHover, loadPath, cb);
  },

  countryInactiveHoverPopup: function(cb) {
    var loadPath = 'index.php?p=country&modal=inactive';
    if ($countryHover.length === 0) {
      $countryHover = $('<div id="fb-country-popup" class="fb-popup-content popup--hover fb-section-border" />').appendTo($modalContainer);
    }
    openAndLoad($countryHover, loadPath, cb);
  },

  viewmodePopup: function(cb) {
    var loadPath = 'index.php?p=country&modal=viewmode';
    if ($countryHover.length === 0) {
      $countryHover = $('<div id="fb-country-popup" class="fb-popup-content popup--view-only" />').appendTo($modalContainer);
    }

    openAndLoad($countryHover, loadPath, cb);
  }
};
