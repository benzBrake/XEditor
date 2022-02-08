/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!******************************!*\
  !*** ./assets/src/config.js ***!
  \******************************/
document.addEventListener('DOMContentLoaded', function () {
  /**
   * 应用函数到 DOMElement 数组
   * @param selector
   * @param func
   * @param parent
   */
  function qf(selector, func) {
    var parent = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : document;

    if (!parent) {
      parent = document;
    }

    var selectors = parent.querySelectorAll(selector);
    [].forEach.call(selectors, func);
  }
  /**
   * document.querySelector 缩写
   * @param selector
   * @param parent
   * @returns {*}
   */


  function qs(selector) {
    var parent = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : document;

    if (!parent) {
      parent = document;
    }

    return parent.querySelector(selector);
  }
  /**
   * 版本号对比
   * @returns {number}
   * @param serverVersion 服务器版本
   * @param currentVersion 当前版本
   */


  function compareVersion(serverVersion, currentVersion) {
    var arr1 = serverVersion.split('.');
    var arr2 = currentVersion.split('.');
    var length1 = arr1.length;
    var length2 = arr2.length;
    var minlength = Math.min(length1, length2);
    var i = 0;

    for (i; i < minlength; i++) {
      var a = parseInt(arr1[i]);
      var b = parseInt(arr2[i]);

      if (a > b) {
        return 1;
      } else if (a < b) {
        return -1;
      }
    }

    if (length1 > length2) {
      for (var j = i; j < length1; j++) {
        if (parseInt(arr1[j]) !== 0) {
          return 1;
        }
      }

      return 0;
    } else if (length1 < length2) {
      for (var _j = i; _j < length2; _j++) {
        if (parseInt(arr2[_j]) !== 0) {
          return -1;
        }
      }

      return 0;
    }

    return 0;
  }

  var xConfig = qs('.x-config'),
      xContent = qs('.x-content', xConfig); // 移动表单

  var form = xConfig.parentNode.querySelector('form');
  form.parentNode.removeChild(form);
  xContent.appendChild(form); // 恢复现场

  var xTabs = qs('.x-tabs', xConfig),
      active = sessionStorage.getItem('x-active');

  if (!active || active === "" && active === "undefined") {
    active = '.x-notice';
  }

  qf(active, function (el) {
    el.classList.add('active');
  }, xContent);
  qf("[data-class=\"".concat(active.replace('.', ''), "\"]"), function (el) {
    el.classList.add('active');
  }, xTabs); // 处理点击

  [].forEach.call(xTabs.querySelectorAll(':scope > li'), function (el) {
    el.addEventListener('click', function (el) {
      el.preventDefault();
      el.stopPropagation();
      window.scrollTo(0, 0);
      qf('.x-tabs li', function (el) {
        el.classList.remove('active');
      });
      el.target.classList.add('active');
      var className = '.' + el.target.dataset["class"];
      sessionStorage.setItem('x-active', className);
      [].forEach.call(xContent.querySelectorAll('.x-item'), function (el) {
        el.classList.remove('active');
      });
      [].forEach.call(xContent.querySelectorAll(className), function (el) {
        el.classList.add('active');
      });
    });
  }); // 加载日志

  fetch('https://api.vvhan.com/api/qqsc?key=95aa93d32e571b1579cfb15134b57dbd').then(function (response) {
    return response.json();
  }).then(function (json) {
    var serverVersion = json.title.substr(0, 5),
        xNotice = qs('.x-notice', xConfig),
        title = qs('.title', xConfig);
    qs('.loading', title).remove();
    qs('.latest.version', title).innerHTML = json.title;
    qs('.message', xNotice).innerHTML = json.content;
    qs('.latest.version', title).classList.add('active');

    if (compareVersion(serverVersion, title.dataset.version) > 0) {
      qs('.latest.found', title).classList.add('active');
    } else {
      qs('.latest', title).classList.add('active');
    }
  })["catch"](function (err) {
    return console.log('Request Failed', err);
  });
});
/******/ })()
;