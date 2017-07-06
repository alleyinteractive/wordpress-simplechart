/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// identity function for calling harmony imports with the correct context
/******/ 	__webpack_require__.i = function(value) { return value; };
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 5);
/******/ })
/************************************************************************/
/******/ ({

/***/ 5:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


/* eslint-disable */
/**
 * On the plugins page, make sure we don't accidentally
 * try to update Simplechart as if it was hosted on WP.org
 */
jQuery(document).ready(function ($) {
	if ('plugins' !== window.pagenow) {
		return;
	}

	var simplechartInput = $('input[value="wordpress-simplechart/simplechart.php"]');
	var disabledForBulkActions = false;

	/**
  * Uncheck and disable Simplechart for Bulk Actions on Plugins page
  */
	function _disableForBulkActions() {
		if (simplechartInput.length) {
			simplechartInput.attr({ disabled: true, checked: false }).css('cursor', 'default');
			disabledForBulkActions = true;
		}
	}

	/**
  * Enable Simplechart for Bulk Actions on Plugins page
  *
  * @param bool checked Whether it should be checked
  */
	function _enableForBulkActions(checked) {
		if (simplechartInput.length) {
			simplechartInput.attr({ disabled: false, checked: checked }).css('cursor', 'pointer');
			disabledForBulkActions = false;
		}
	}

	if (simplechartInput.length) {

		// Make sure Simplechart is unchecked if Update is selected as Bulk Action
		$('.bulkactions [name^="action"]').on('change', function (evt) {
			if ('update-selected' === $(evt.target).val()) {
				// If Update is selected, uncheck and disable
				_disableForBulkActions();
			} else if (disabledForBulkActions) {
				// If changing from Update to another Bulk Action,
				// un-disable and fallback to "check all" checkbox value
				var allChecked = 'undefined' !== typeof $('#cb-select-all-1').attr('checked');
				_enableForBulkActions(allChecked);
			}
		});

		// Make extra sure Simplechart is unchecked when applying bulk action to Update
		$('.bulkactions [type="submit"]').on('click', function (evt) {
			var select = $(evt.target).siblings('select').first();
			if (select.length && 'update-selected' === select.val()) {
				_disableForBulkActions();
			}
		});
	}
});

/***/ })

/******/ });
//# sourceMappingURL=plugin.js.map