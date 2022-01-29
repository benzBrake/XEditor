import $ from 'jquery';

class XEditor {
    constructor(textarea, preview) {
        // 增强 Textarea
        $.fn.extend({
            getSelection: function () {

                var e = this.get(0);
                if (!e) {
                    return null;
                }

                return (

                    /* mozilla / dom 3.0 */
                    ('selectionStart' in e && function () {
                        var l = e.selectionEnd - e.selectionStart;
                        return {
                            start: e.selectionStart,
                            end: e.selectionEnd,
                            length: l,
                            text: e.value.substr(e.selectionStart, l)
                        };
                    }) ||

                    /* other */
                    (window.getSelection() && function () {
                        var selection = window.getSelection(), range = selection.getRangeAt(0);

                        return {
                            start: range.startOffset,
                            end: range.endOffset,
                            length: range.endOffset - range.startOffset,
                            text: range.toString()
                        };

                    }) ||

                    /* exploder */
                    (document.selection && function () {

                        e.focus();

                        var r = document.selection.createRange();
                        if (r === null) {
                            return {start: 0, end: e.value.length, length: 0}
                        }

                        var re = e.createTextRange();
                        var rc = re.duplicate();
                        re.moveToBookmark(r.getBookmark());
                        rc.setEndPoint('EndToStart', re);

                        return {
                            start: rc.text.length,
                            end: rc.text.length + r.text.length,
                            length: r.text.length,
                            text: r.text
                        };
                    }) ||

                    /* browser not supported */
                    function () {
                        return null;
                    }

                )();
            },

            setSelection: function (start, end) {
                var e = this.get(0);
                if (!e) {
                    return;
                }

                if (e.setSelectionRange) {
                    e.focus();
                    e.setSelectionRange(start, end);
                } else if (e.createTextRange) {
                    var range = e.createTextRange();
                    range.collapse(true);
                    range.moveEnd('character', end);
                    range.moveStart('character', start);
                    range.select();
                }
            },

            replaceSelection: function () {

                var e = this.get(0);
                if (!e) {
                    return null;
                }

                var text = arguments[0] || '';

                return (

                    /* mozilla / dom 3.0 */
                    ('selectionStart' in e && function () {
                        e.value = e.value.substr(0, e.selectionStart) + text + e.value.substr(e.selectionEnd, e.value.length);
                        return this;
                    }) ||

                    /* exploder */
                    (document.selection && function () {
                        e.focus();
                        document.selection.createRange().text = text;
                        return this;
                    }) ||

                    /* browser not supported */
                    function () {
                        e.value += text;
                        return jQuery(e);
                    }

                )();
            }
        });
        this.textarea = $(textarea);
        this.options = window.XConf.options;
        this.text = window.XConf.i18n;
        this.cacheJSON = [];
        this.editor = window._editor;
        delete window._editor;
        this.initToolbar();
    }

    /**
     * 获取选中文本
     * @returns {boolean}
     */
    getSelection() {
        return this.textarea.getSelection();
    }

    /**
     * 替换内容
     * @param text
     */
    replaceSelection(text) {
        let sel = this.getSelection(),
            offset = (sel ? sel.start : 0) + text.length;
        this.textarea.replaceSelection(text);
        this.textarea.focus();
        this.textarea.trigger('input');
        this.textarea.setSelection(offset, offset);
        this.editor.refreshPreview();
    }

    /**
     * 打开模态框
     * @param options
     */
    openModal(options = {}) {
        const _modalOptions = {
            title: this.text.title,
            innerHtml: this.text.content,
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
        <button type="button" class="x-modal-footer-button x-modal-footer-cancel">${this.text.cancel}</button><button type="button" class="x-modal-footer-button x-modal-footer-confirm">${this.text.ok}</button>
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
            let flag = modalOptions.confirm();
            if (flag)
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
     * 工具栏初始化
     */
    initToolbar() {
        let that = this;
        if (this.options.toolbarJson) {
            that.toolbarJson = this.options.toolbarJson;
            $.each(that.toolbarJson, function (tag, item) {
                if (tag.startsWith('spacer')) {
                    let spacer = $(`<li class="wmd-spacer wmd-${tag}" id="wmd-${tag}"></li>`);
                    if (item.hasOwnProperty('insertBefore')) {
                        spacer.insertBefore($(item['insertBefore']));
                    } else {
                        spacer.appendTo($('#wmd-button-row'));
                    }
                } else {
                    if (item.hasOwnProperty('remove')) {
                        $(item['remove']).remove();
                    }
                    if (item.hasOwnProperty('move')) {
                        $(item['move']['match']).detach().insertBefore(item['move']['insertBefore']);
                    }
                    if (item.hasOwnProperty('icon')) {
                        if (item.hasOwnProperty('name')) {
                            let button = $('<li>').attr('class', 'wmd-button custom-button').attr('title', item['name'])
                                .attr('id', 'wmd-' + tag + '-button');
                            if (item.hasOwnProperty('onclick')) {
                                button.attr('onclick', item['onclick']);
                            } else {
                                button.attr('onclick', `window.XEditor.handleButtonClick('${tag}')`);
                            }
                            button.html(item['icon']);
                            if (item.hasOwnProperty('replace')) {
                                $(item['replace']).replaceWith(button);
                            } else if (item.hasOwnProperty('insertBefore')) {
                                button.insertBefore($(item['insertBefore']));
                            } else {
                                button.appendTo($('#wmd-button-row'));
                            }
                        } else {
                            $(`#wmd-${tag}-button`).html(item['icon']);
                        }
                    }
                }
            });
        }
    }

    /**
     * 处理按钮点击
     * @param key
     */
    handleButtonClick(key) {
        let that = this;
        if (this.toolbarJson.hasOwnProperty(key)) {
            let buttonOptions = that.toolbarJson[key],
                title = buttonOptions.hasOwnProperty('name') ? buttonOptions['name'] : that.text.dialog,
                previewArea = (buttonOptions.hasOwnProperty('previewArea') ? buttonOptions['previewArea'] : 'n');
            if (buttonOptions.hasOwnProperty('params')) {
                let html = $('<form class="params"></form>');
                $.each(buttonOptions.params, function (key, param) {
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
                            label.append($('<i class="required-star">*</i>'))
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
                            let template = that.toolbarJson[key]['template'];
                            if (previewArea === 'c')
                                $(".x-modal-body").append('<div class="preview center"></div>');
                            else
                                $(".x-modal-body").append('<div class="preview"></div>');
                            $('.x-modal-body .preview').append(window.XPreview.makeHtml(template));
                            let form = $("#x-modal .params");
                            $("input,select,textarea", form).on('change', function () {
                                let template = that.toolbarJson[key]['template'];
                                let form = $('#x-modal .x-modal-body .params');
                                let params = form.serializeArray();
                                $.each(params, function (i, param) {
                                    let element = $(`#x-modal .params [name=${param.name}]`);
                                    let value = param.value !== "" ? param.value : (typeof element.attr('default') !== "undefined" ? element.attr('default') : '');
                                    let regExp = new RegExp("{" + $(element).attr('name') + "}", "g");
                                    template = template.replace(regExp, value);
                                });
                                $('.x-modal-body .preview').html(indow.XPreview.makeHtml(template));
                            });
                        }
                    },
                    confirm() {
                        const form = $('#x-modal .x-modal-body .params');
                        let params = form.serializeArray(), flag = true;
                        let template = window.XEditor.toolbarJson[key]['template'];
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
                            that.replaceSelection(template);
                            $('#x-modal').remove();
                        }
                        return flag;
                    }
                });
            } else if (buttonOptions.hasOwnProperty('template')) {
                let sel = that.getSelection(),
                    text = buttonOptions['template'].replace('%s', (sel ? sel.text : ''));
                that.replaceSelection(text);
            }
        }
    }


    /**
     * 请求JSON
     * @param filename
     * @returns {*}
     */
    getJSON(filename) {
        let url = `${this.options.pluginUrl}/assets/json/${filename}.json`;
        let that = this;
        if (!this.cacheJSON.hasOwnProperty(filename)) {
            $.ajaxSetup({async: false});
            $.get(url, function (json) {
                that.cacheJSON[filename] = json;
            });
            $.ajaxSetup({async: true});
        }
        return that.cacheJSON[filename];
    }

    /**
     * 生成多标签内容
     * @param title
     * @param filename
     */
    charTabPrompt(title, filename) {
        let json = this.getJSON(filename),
            that = this,
            tabTitle = $('<div class="switch-tab-wrap">'),
            tabContent = $('<div class="switch-tab-content-wrap">'),
            i = 0;
        $.each(json, function (key, value) {
            tabTitle.append(`<div class="switch-tab-title switch-tab-title-${i}" data-switch="switch-tab-content-${i}">${key}</div>`);
            let content = $(`<div class="switch-tab-content switch-tab-content-${i}">`);
            $.each(value.split(" "), function (i, item) {
                content.append(`<div class="click-to-insert-char">${item}</div>`);
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
                    that.replaceSelection($(this).html());
                    $('.x-modal-header-close').trigger('click');
                });
            }
        });
    }

    owoTabPrompt(title, filename) {
        let json = this.getJSON(filename),
            that = this,
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
                    that.replaceSelection($(this).data('text'));
                    $('.x-modal-header-close').trigger('click');
                });
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', function () {
    window.XEditor = new XEditor("#text");
});