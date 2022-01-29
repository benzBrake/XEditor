import $ from 'jquery';

class XPreview {
    constructor() {
        const _this = this;
        this.shortcodes = {};
        $.each(window.XConf.options.toolbarJson, function (tag, item) {
            if (item.hasOwnProperty('preview')) {
                _this.shortcodes[tag] = {
                    "regex": _this.getRegex(tag),
                    "replacement": item.preview
                }
            }
        });
        $.extend({
            replaceTag: function (currentElem, newTagObj, keepProps) {
                var $currentElem = $(currentElem);
                var i, $newTag = $(newTagObj).clone();
                if (keepProps) {//{{{
                    newTag = $newTag[0];
                    newTag.className = currentElem.className;
                    $.extend(newTag.classList, currentElem.classList);
                    $.extend(newTag.attributes, currentElem.attributes);
                }//}}}
                $currentElem.wrapAll($newTag);
                $currentElem.contents().unwrap();
                // return node; (Error spotted by Frank van Luijn)
                return this; // Suggested by ColeLawrence
            }
        });

        $.fn.extend({
            replaceTag: function (newTagObj, keepProps) {
                // "return" suggested by ColeLawrence
                return this.each(function () {
                    jQuery.replaceTag(this, newTagObj, keepProps);
                });
            }
        });
    }

    /**
     * 处理 HTML
     * @param html
     * @returns string
     */
    makeHtml(html) {
        $.each(this.shortcodes, function (name, value) {
            html = html.replace(value.regex, value.replacement);
        });
        // 表情
        let regExp = new RegExp("\\:\\:\\(\\s*(呵呵|哈哈|吐舌|太开心|笑眼|花心|小乖|乖|捂嘴笑|滑稽|你懂的|不高兴|怒|汗|黑线|泪|真棒|喷|惊哭|阴险|鄙视|酷|啊|狂汗|what|疑问|酸爽|呀咩爹|委屈|惊讶|睡觉|笑尿|挖鼻|吐|犀利|小红脸|懒得理|勉强|爱心|心碎|玫瑰|礼物|彩虹|太阳|星星月亮|钱币|茶杯|蛋糕|大拇指|胜利|haha|OK|沙发|手纸|香蕉|便便|药丸|红领巾|蜡烛|音乐|灯泡|开心|钱|咦|呼|冷|生气|弱|吐血|狗头)\\s*\\)", "g");
        html = html.replace(regExp, function ($0, $1) {
            let name = $0.replace('::', '').replace('(', '').replace(')', '');
            $1 = encodeURI($1).replace(/%/g, '');
            return `<img title="${name}" alt="${name}" class="owo" src="${window.XConf.options.pluginUrl}/assets/images/owo/paopao/${$1}_2x.png" />`;
        });
        html = html.replace(/\:\@\(\s*(高兴|小怒|脸红|内伤|装大款|赞一个|害羞|汗|吐血倒地|深思|不高兴|无语|亲亲|口水|尴尬|中指|想一想|哭泣|便便|献花|皱眉|傻笑|狂汗|吐|喷水|看不见|鼓掌|阴暗|长草|献黄瓜|邪恶|期待|得意|吐舌|喷血|无所谓|观察|暗地观察|肿包|中枪|大囧|呲牙|抠鼻|不说话|咽气|欢呼|锁眉|蜡烛|坐等|击掌|惊喜|喜极而泣|抽烟|不出所料|愤怒|无奈|黑线|投降|看热闹|扇耳光|小眼睛|中刀)\s*\)/g, function ($0, $1) {
            let name = $0.replace(':@', '').replace('(', '').replace(')', '');
            $1 = encodeURI($1).replace(/%/g, '');
            return `<img title="${name}" alt="${name}" class="owo" src="${window.XConf.options.pluginUrl}/assets/images/owo/aru/${$1}_2x.png" />`;
        });
        $.each(this.shortcodes, function (name, value) {
            html = html.replace(value.regex, value.replacement);
        });
        return html;
    }

    /**
     * 转换标签
     * @param preview
     */
    convertTag(preview) {
        let that = this;
        // Need Implement
    }

    /**
     * 来自 Wordpress
     * https://regex101.com/r/ja0b1p/1
     * $0→code $2→tag $3→attr $5→text
     * @param tag
     * @returns {RegExp}
     */
    getRegex(tag) {
        return new RegExp('\\[(\\[?)(' + tag + ')(?![\\w-])([^\\]\\/]*(?:\\/(?!\\])[^\\]\\/]*)*?)(?:(\\/)\\]|\\](?:([^\\[]*(?:\\[(?!\\/\\2\\])[^\\[]*)*)(\\[\\/\\2\\]))?)(\\]?)', 'g');
    }
}

document.addEventListener('DOMContentLoaded', () => window.XPreview = new XPreview());