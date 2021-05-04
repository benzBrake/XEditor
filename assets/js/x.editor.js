class XEditor {
    constructor() {
        this.init_editArea();
        this.init_advancedPanel();
        this.init_slugTranslate(); // 感谢 BaiduSlug 插件
    }

    /**
     * 编辑框获取焦点
     */
    focus() {
        this.editor.focus();
    }

    /**
     * 获取选中内容
     * @param val
     * @returns {string|*}
     */
    getSelection(val = '') {
        let selection = this.editor.getSelection();
        if (selection === "")
            return val;
        return selection;
    }

    /**
     * 替换选中内容
     * @param val
     */
    replaceSelection(val) {
        if (this.getSelection() === "") {
            this.insertValue(val);
        } else {
            this.editor.updateValue(val);
        }
    }

    /**
     * 插入内容
     * @param val
     */
    insertValue(val) {
        this.editor.insertValue(val);
    }


    /**
     * 获取文本框内容
     * @returns {*}
     */
    getString() {
        return this.editor.getValue();
    }

    /**
     * 打开模态框
     * @param options
     */
    openModal(options = {}) {
        const _modalOptions = {
            title: '标题',
            innerHtml: '内容',
            hasFooter: true,
            confirm: () => {

            },
            handler: () => {
            }
        };
        let modalOptions = Object.assign(_modalOptions, options);
        if ($("#x-modal").length < 1) {
            $('body').append(`<div id="x-modal" class="x-modal">
    <div class="x-modal-frame">
    <div class="x-modal-header">
        <div class="x-modal-header-title"></div><div class="x-modal-header-close">X</div>
</div>
    <div class="x-modal-body">

    </div>
    <div class="x-modal-footer">
        <button type="button" class="x-modal-footer-button x-modal-footer-cancel">${XConf.i18n.cancel}</button><button type="button" class="x-modal-footer-button x-modal-footer-confirm">${XConf.i18n.ok}</button>
    </div>
</div>
</div>`);
        }
        $('.x-modal-header-title').html(modalOptions.title);
        $('.x-modal-body').html(modalOptions.innerHtml);
        modalOptions.hasFooter ? $(`.x-modal-footer`).show() : $('.x-modal-footer').hide();
        $('body').addClass('no-scroll');
        modalOptions.handler();
        $('.x-modal-footer-confirm').on('click', () => {
            modalOptions.confirm();
            $('body').removeClass('no-scroll');
        });
        $('.x-modal-header-close').on('click', () => {
            $('#x-modal').remove();
            $('body').removeClass('no-scroll');
        });
        $('.x-modal-footer-cancel').on('click', () => {
            $('.x-modal-header-title').html('');
            $('.x-modal-body').html('');
            $('#x-modal').remove();
            $('body').removeClass('no-scroll');
        });
        $('#x-modal').addClass('active');


    }

    /**
     * 请求JSON
     * @param filename
     * @returns {*}
     */
    getJSON(filename) {
        let url = `${XConf.options.pluginUrl}/assets/json/${filename}.json`;
        let _this = this;
        if (!this.cacheJSON.hasOwnProperty(filename)) {
            $.ajaxSetup({async: false});
            $.get(url, function (json) {
                _this.cacheJSON[filename] = json;
            });
            $.ajaxSetup({async: true});
        }
        return _this.cacheJSON[filename];
    }

    /**
     * 生成多标签内容
     * @param title
     * @param filename
     */
    charTabPrompt(title, filename) {
        let json = this.getJSON(filename),
            _this = this,
            tabTitle = $('<div class="switch-tab-wrap">'),
            tabContent = $('<div class="switch-tab-content-wrap">'),
            i = 0;
        $.each(json, function (key, value) {
            tabTitle.append(`<div class="switch-tab-title switch-tab-title-${i}" data-switch="switch-tab-content-${i}">${key}</div>`);
            let content = $(`<div class="switch-tab-content switch-tab-content-${i}">`);
            $.each(value.split(" "), function (i, item) {
                content.append(`<div class="click-to-insert">${item}</div>`);
            });
            tabContent.append(content);
            i++;
        });
        let html = $(`<div class="switch-tab"></div>`);
        html.append(tabTitle).append(tabContent);
        this.openModal({
            title: title,
            innerHTML: '',
            hasFooter: false,
            handler: () => {
                $('.x-modal-body').html('').append(html);
                let switchTab = $('#x-modal .switch-tab');
                switchTab.find('.switch-tab-title:first-child').addClass('active');
                switchTab.find('.switch-tab-content:first-child').addClass('active');
                $('.switch-tab-title', switchTab).click(function () {
                    $('.active', switchTab).removeClass('active');
                    $(this).addClass('active');
                    $('.' + $(this).data('switch')).addClass('active')
                });
                $('.click-to-insert-char', switchTab).click(function () {
                    _this.insertValue($(this).html());
                    $('.x-modal-header-close').trigger('click');
                });
            }
        });
    }

    owoTabPrompt(title, filename) {
        let json = this.getJSON(filename),
            _this = this,
            tabTitle = $('<div class="switch-tab-wrap">'),
            tabContent = $('<div class="switch-tab-content-wrap">'),
            i = 0;
        $.each(json, function (key, value) {
            tabTitle.append(`<div class="switch-tab-title switch-tab-title-${i}" data-switch="switch-tab-content-${i}">${key}</div>`);
            let content = $(`<div class="switch-tab-content switch-tab-content-${i}">`);
            $.each(value, function (i, item) {
                if (item.icon.indexOf('.png') > 0 || item.icon.indexOf('.jpg') > 0 || item.icon.indexOf('.gif') > 0) {
                    let name = item.data.replace('::', '').replace('(', '').replace(')', '');
                    content.append(`<div class="click-to-insert-data" data-text="${item.data}"><img title="${name}" alt="${name}" src="${XConf.options.pluginUrl}/${item.icon}"/></div>`);
                } else {
                    content.append(`<div class="click-to-insert-data text" data-text="${item.data}">${item.icon}</div>`);
                }
            });
            tabContent.append(content);
            i++;
        });
        let html = $(`<div class="switch-tab"></div>`);
        html.append(tabTitle).append(tabContent);
        this.openModal({
            title: title,
            innerHTML: '',
            hasFooter: false,
            handler: () => {
                $('.x-modal-body').html('').append(html);
                let switchTab = $('#x-modal .switch-tab');
                switchTab.find('.switch-tab-title:first-child').addClass('active');
                switchTab.find('.switch-tab-content:first-child').addClass('active');
                $('.switch-tab-title', switchTab).click(function () {
                    $('.active', switchTab).removeClass('active');
                    $(this).addClass('active');
                    $('.' + $(this).data('switch')).addClass('active')
                });
                $('.click-to-insert-data', switchTab).click(function () {
                    _this.insertValue($(this).data('text'));
                    $('.x-modal-header-close').trigger('click');
                });
            }
        });
    }

    /**
     * 参数弹窗
     * @param key
     */
    paramsPrompt(key) {
        if (this.toolbar.hasOwnProperty(key)) {
            let options = this.toolbar[key];
            let title = options.hasOwnProperty('title') ? options['title'] : options['tip'] ? options['tip'] : '对话框';
            let previewArea = (options.hasOwnProperty('previewArea') ? options['previewArea'] : 'n');
            if (options.hasOwnProperty('params')) {
                let html = $('<form class="params"></form>');
                $.each(options.params, function (key, param) {
                    let dom, label = $('<label></label>'), formItem = $('<div class="form-item"></div>');
                    if (typeof param == "string") {
                        label.attr('for', key).append(param);
                        dom = $('<input>').attr('name', key).attr('type', 'text').attr('placeholder', param);
                        formItem.append(label).append(dom);
                    } else if (typeof param == "object") {
                        if (param.hasOwnProperty('label')) {
                            label.attr('for', key).html(param.label);
                        }
                        if (!param.hasOwnProperty('tag')) {
                            dom = $('<input>').attr('type', 'text');
                        } else {
                            dom = $(`<${param.tag}>`);
                            // 选项处理
                            if (param.tag === 'select' && param.hasOwnProperty('options')) {
                                $.each(param.options, function (key, option) {
                                    let optionDom = $(`<option value="${key}">${option}</option>`);
                                    dom.append(optionDom);
                                });
                            }
                        }
                        if (param.hasOwnProperty('required') && param.required === true) {
                            dom.attr('required', true);
                        }
                        const autoConvertKey = ['type', 'class', 'placeholder', 'default'];
                        for (let i in autoConvertKey) {
                            if (param.hasOwnProperty(autoConvertKey[i])) {
                                dom.attr(autoConvertKey[i], param[autoConvertKey[i]]);
                            }
                        }
                        dom.attr('name', key);
                        formItem.append(label).append(dom);
                    }
                    html.append(formItem);
                });

                this.openModal({
                    title: title,
                    innerHtml: html.prop("outerHTML"),
                    handler() {
                        if (previewArea !== 'n') {
                            let template = window.XEditor.toolbar[key]['template'];
                            if (previewArea === 'c')
                                $(".x-modal-body").append('<div class="preview center"></div>');
                            else
                                $(".x-modal-body").append('<div class="preview"></div>');
                            $('.x-modal-body .preview').append(window.XEditor.xPreview.render(template));
                            let form = $("#x-modal .params");
                            $("input,select,textarea", form).on('change', function () {
                                let template = window.XEditor.toolbar[key]['template'];
                                let form = $('#x-modal .x-modal-body .params');
                                let params = form.serializeArray();
                                $.each(params, function (i, param) {
                                    let element = $(`#x-modal .params [name=${param.name}]`);
                                    let value = param.value !== "" ? param.value : (typeof element.attr('default') !== "undefined" ? element.attr('default') : '');
                                    let regExp = new RegExp("{" + $(element).attr('name') + "}", "g");
                                    template = template.replace(regExp, value);
                                });
                                $('.x-modal-body .preview').html(window.XEditor.xPreview.render(template));
                            });
                        }
                    },
                    confirm() {
                        const form = $('#x-modal .x-modal-body .params');
                        let params = form.serializeArray(), flag = true;
                        let template = window.XEditor.toolbar[key]['template'];
                        $.each(params, function (i, param) {
                            let element = $(`#x-modal .params [name=${param.name}]`);
                            if (element.prop('required') && param.value === "") {
                                flag = false;
                                element.addClass('required');
                                setTimeout(function () {
                                    element.removeClass('required');
                                }, 800);
                            }
                            let value = param.value !== "" ? param.value : (typeof element.attr('default') !== "undefined" ? element.attr('default') : '');
                            let regExp = new RegExp("{" + $(element).attr('name') + "}", "g");
                            template = template.replace(regExp, value);
                        });
                        if (flag) {
                            window.XEditor.insertValue(template);
                            $('#x-modal').remove();
                        }
                    }
                });
            }
        }
    }

    /**
     * HTML弹窗
     * @param key
     */
    htmlDialog(key) {
        if (this.toolbar.hasOwnProperty(key)) {
            let options = this.toolbar[key];
            if (options.hasOwnProperty('html')) {
                let title = options.hasOwnProperty('title') ? options['title'] : options['tip'] ? options['tip'] : '对话框';
                this.openModal({title: title, hasFooter: false, innerHtml: options['html']});
            }
        }
    }

    /**
     * 自动插入标签
     * @returns {boolean}
     * @private
     */
    _autoInsertTag() {
        let textarea = this.getString();
        let _this = this;
        $("#tags-selector span").each(function (index, element) {
            var name = $(element).data('tag');
            if (textarea.indexOf(name) > -1) {
                $('#tags').focus().tokenInput('add', {
                    'id': name,
                    'tags': name
                });
            }
        });
        return false;
    }

    init_slugTranslate() {
        const baiduSlug = function () {
            let title = $('#title');
            let slug = $('#slug');
            if (slug.val().length > 0 || title.val().length == 0) {
                return;
            }
            $.ajax({
                url: XConf.options.XAutoSlug.apiUrl + title.val(),
                success: function (data) {
                    if (data.result.length > 0) {
                        slug.val(data.result).focus();
                        slug.siblings('pre').text(data.result);
                    }
                }
            });
        };

        if (XConf.options.XAutoSlug.type !== 'none') {
            $('#title').blur(baiduSlug);
            $('#slug').blur(baiduSlug);
        }
    }


    init_editArea() {
        const originText = $('#text');
        this.xPreview = new XPreview();
        originText.before('<div id="vditor"></div>');
        this.editor = new Vditor('vditor', {
                "height": document.documentElement.clientHeight * 0.7,
                "cache": {
                    "enable": false,
                    "cid": $('input[name="cid"]').val()
                },
                "value": originText.val(),
                "mode": "sv",
                "preview": {
                    "mode": "both",
                    "actions": [],
                    transform(html) {
                        return window.XEditor.xPreview.render(html);
                    }
                },
                "toolbar": window.XToolbar,
                "toolbarConfig": {
                    "pin": true
                },
                "resize": {
                    "enable": true,
                    "position": "bottom"
                }
            }
        );

        this.cacheJSON = [];

        window.XEditor = this;
        let _this = this;
        // 取出要单独处理的按钮
        _this.toolbar = {};
        $.each(window.XToolbar, function (i, button) {
            if (typeof button === "object" && button.hasOwnProperty("name")) {
                _this.toolbar[button.name] = button;
            }
        });
        // 保存前保存数据到 #text
        originText.parents().find('form').on('submit', function () {
            originText.val(window.XEditor.getString());
            return true;
        });

        // 优化图片及文件附件插入 Thanks to Markxuxiao
        Typecho.insertFileToEditor = function (file, url, isImage) {
            if (isImage) {
                window.XEditor.replaceSelection("![" + file + "](" + url + ")");
            } else {
                window.XEditor.replaceSelection("[" + window.XConf.i18n.clickToDownload.replace("{url}", file) + "](" + url + " \"" + file + "\")");
            }
        };
    }

    init_advancedPanel() {
        if (XConf.options.XTagsSelector) {
            $('#tags').parents('.typecho-post-option').addClass('tags-wrapper').append($('.auto-insert')).append($('#tags-selector'));
            $('#tags-selector').css('width', $('#tags').parents('p').css('width'));
            $('#tags-selector span').on('click', function () {
                var name = $(this).data('tag');
                $('#tags').focus().tokenInput('add', {
                    'id': name,
                    'tags': name
                });
                return false;
            });
        }

        if (XConf.options.XAutoInsertTag) {
            setInterval(function () {
                XEditor._autoInsertTag();
            }, 10000);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => new XEditor());
$('#btn-submit').addClass('x-btn-success');
$(window).scroll(function () {
    let scrollY = window.pageYOffset || document.documentElement.scrollTop,
        submitArea = $('p.submit'),
        prevSection = submitArea.prev();
    if (submitArea.length > 0) {
        if (scrollY < (prevSection.offset().top + prevSection.height() - $(window).height())) {
            submitArea.addClass('fixed').css('width', prevSection.outerWidth(true) - 10).css('left', prevSection.offset().left);
        } else {
            submitArea.removeClass('fixed').css('width', 'unset').css('left', 'unset');
        }
    }
});