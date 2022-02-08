/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!*****************************!*\
  !*** ./assets/src/short.js ***!
  \*****************************/
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); Object.defineProperty(Constructor, "prototype", { writable: false }); return Constructor; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } Object.defineProperty(subClass, "prototype", { value: Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }), writable: false }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function _createSuperInternal() { var Super = _getPrototypeOf(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _getPrototypeOf(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _possibleConstructorReturn(this, result); }; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } else if (call !== void 0) { throw new TypeError("Derived constructors may only return object or undefined"); } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _wrapNativeSuper(Class) { var _cache = typeof Map === "function" ? new Map() : undefined; _wrapNativeSuper = function _wrapNativeSuper(Class) { if (Class === null || !_isNativeFunction(Class)) return Class; if (typeof Class !== "function") { throw new TypeError("Super expression must either be null or a function"); } if (typeof _cache !== "undefined") { if (_cache.has(Class)) return _cache.get(Class); _cache.set(Class, Wrapper); } function Wrapper() { return _construct(Class, arguments, _getPrototypeOf(this).constructor); } Wrapper.prototype = Object.create(Class.prototype, { constructor: { value: Wrapper, enumerable: false, writable: true, configurable: true } }); return _setPrototypeOf(Wrapper, Class); }; return _wrapNativeSuper(Class); }

function _construct(Parent, args, Class) { if (_isNativeReflectConstruct()) { _construct = Reflect.construct; } else { _construct = function _construct(Parent, args, Class) { var a = [null]; a.push.apply(a, args); var Constructor = Function.bind.apply(Parent, a); var instance = new Constructor(); if (Class) _setPrototypeOf(instance, Class.prototype); return instance; }; } return _construct.apply(null, arguments); }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); return true; } catch (e) { return false; } }

function _isNativeFunction(fn) { return Function.toString.call(fn).indexOf("[native code]") !== -1; }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

document.addEventListener('DOMContentLoaded', function () {
  var xBtn = /*#__PURE__*/function (_HTMLElement) {
    _inherits(xBtn, _HTMLElement);

    var _super = _createSuper(xBtn);

    function xBtn() {
      var _this;

      _classCallCheck(this, xBtn);

      _this = _super.call(this);
      _this.options = {
        icon: _this.getAttribute('icon') || 'fa-link',
        href: _this.getAttribute('href') || '#',
        type: /^primary$|^secondary$|^light$|^outline-light$|^outline-secondary$|^info$|^success$|^danger$|^dark$|^weibo$|^weixin$|^alipay$|^youku$|^toutiao$|^youtube$|^twitter$|^facebook$|^bilibili$|^ins$|^tumblr$/.test(_this.getAttribute('type')) ? _this.getAttribute('type') : 'primary',
        content: _this.getAttribute('content') || '标签按钮'
      };
      _this.outerHTML = "\n\t\t\t\t    <a class=\"x-btn x-btn-".concat(_this.options.type, "\" href=\"").concat(_this.options.href === "" || _this.options.href === "{href}" ? "https://doufu.ru" : _this.options.href, "\" target=\"_blank\" rel=\"noopener noreferrer nofollow\">\n\t\t\t\t\t    <span class=\"icon\"><i class=\"fa ").concat(_this.options.icon === "" || _this.options.icon === "{icon}" ? "fa-link" : _this.options.icon, "\"></i></span><span class=\"content\">").concat(_this.options.content === "" || _this.options.content === "{content}" ? XConf.i18n.button : _this.options.content, "</span>\n\t\t\t\t    </a>\n\t\t\t");
      return _this;
    }

    return _createClass(xBtn);
  }( /*#__PURE__*/_wrapNativeSuper(HTMLElement));

  window.customElements.define('x-btn', xBtn);

  var xPlayer = /*#__PURE__*/function (_HTMLElement2) {
    _inherits(xPlayer, _HTMLElement2);

    var _super2 = _createSuper(xPlayer);

    function xPlayer() {
      var _this2;

      _classCallCheck(this, xPlayer);

      _this2 = _super2.call(this);
      _this2.options = {
        src: _this2.getAttribute('src'),
        player: _this2.getAttribute('player') || window.XConf.options.playerUrl
      };

      _this2.render();

      return _this2;
    }

    _createClass(xPlayer, [{
      key: "render",
      value: function render() {
        if (this.options.src) this.innerHTML = "<div class=\"x-player\"><iframe allowfullscreen=\"true\" src=\"".concat(this.options.player + this.options.src, "\"></iframe></div>");else this.innerHTML = '播放地址未填写！';
      }
    }]);

    return xPlayer;
  }( /*#__PURE__*/_wrapNativeSuper(HTMLElement));

  window.customElements.define('x-player', xPlayer);

  var xCard = /*#__PURE__*/function (_HTMLElement3) {
    _inherits(xCard, _HTMLElement3);

    var _super3 = _createSuper(xCard);

    function xCard() {
      var _this3;

      _classCallCheck(this, xCard);

      _this3 = _super3.call(this);
      _this3.options = {
        title: _this3.getAttribute('title'),
        content: _this3.innerHTML,
        id: "x-card-" + Math.floor(Math.random() * 10000 + 1),
        fold: _this3.getAttribute('fold') === "on"
      };
      _this3.innerHTML = "\n\t\t\t\t<div class=\"x-card ".concat(_this3.options.fold ? ' fold' : '', "\" id=\"").concat(_this3.options.id, "\">\n\t\t\t\t    <div class=\"title\">").concat(_this3.options.title === '{title}' || _this3.options.title === '' ? '' : _this3.options.title, "</div>\n\t\t\t\t    <div class=\"content\">").concat(_this3.options.content, "</div>\n\t\t\t\t</div>\n\t\t\t");
      document.querySelector('#' + _this3.options.id + ' .title').addEventListener('click', function (e) {
        var card = document.getElementById(e.target.parentElement.id);
        if (card.classList.contains('fold')) card.classList.remove('fold');else card.classList.add('fold');
      });
      return _this3;
    }

    return _createClass(xCard);
  }( /*#__PURE__*/_wrapNativeSuper(HTMLElement));

  window.customElements.define('x-card', xCard);
});
/******/ })()
;