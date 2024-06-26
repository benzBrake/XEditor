document.addEventListener('DOMContentLoaded', () => {
    class xPost extends HTMLElement {
        constructor() {
            super();
            let _this = this;
            $.post(XConf.options.XActions.query.post, {cid: this.innerText}, function (data) {
                if (data.status && data.status === 200) {
                    _this.outerHTML = `<div class="shortcode shortcode-post"><div class="text-content"><div class="title"><a href="${data.permalink}">${data.title}</a></div><div class="content">${data.content}</div></div><div class="media-content"><img src="${data.thumb}"/></div></div>`;
                } else {
                    _this.outerHTML = `<div class="shortcode shortcode-post"><div class="title"><a href="#">文章标题</a></div><div class="content">文章摘要，此处省略一万字</div></div>`;
                }
            });
        }
    }

    window.customElements.define('x-post', xPost);

    class xPlayer extends HTMLElement {
        constructor() {
            super();
            this.options = {
                src: this.getAttribute('src'),
                player: this.getAttribute('player') || XConf.options.XPlayerUrl
            };
            this.render();
        }

        render() {
            if (this.options.src) this.innerHTML = `<iframe allowfullscreen="true" src="${this.options.player + this.options.src}"></iframe>`;
            else this.innerHTML = '播放地址未填写！';
        }
    }

    window.customElements.define('x-player', xPlayer);

    class xBilibili extends HTMLElement {
        constructor() {
            super();
            this.bvid = (this.getAttribute('bvid') || "").trim().replaceAll('"', "");
            this.render();
        }

        render() {
            if (this.bvid) this.innerHTML = `<iframe allowfullscreen="true" class="x-bilibili" src="//player.bilibili.com/player.html?bvid=${this.bvid}&autoplay=0"></iframe>`;
            else this.innerHTML = 'Bvid未填写！';
        }
    }

    window.customElements.define('x-bilibili', xBilibili);

    class xNetease extends HTMLElement {
        constructor() {
            super();
            this.options = {
                type: this.getAttribute('type') || 'song',
                id: this.getAttribute('id'),
                width: this.getAttribute('width') || '100%',
                autoplay: this.getAttribute('autoplay')
            };
            this.render();
        }

        render() {
            if (this.options.id) {
                if (this.options.type === 'song') {
                    this.innerHTML = `<iframe style="display: block; margin: 0 auto; border: 0;" width="${this.options.width}" height="86px" src="//music.163.com/outchain/player?type=2&id=${this.options.id}&auto=${this.options.autoplay}&height=66"></iframe>`;
                } else if (this.options.type === 'list') {
                    this.innerHTML = `<iframe style="display: block; margin: 0 auto; border: 0;" width="${this.options.width}" height="450px" src="//music.163.com/outchain/player?type=0&id=${this.options.id}&auto=${this.options.autoplay}&height=430"></iframe>`;
                }
            } else this.innerHTML = '网抑云歌曲/歌单ID未填写！';
        }
    }

    window.customElements.define('x-netease', xNetease);

    class xNeteaseList extends HTMLElement {
        constructor() {
            super();
            this.options = {
                id: this.getAttribute('id'),
                width: this.getAttribute('width') || '100%',
                autoplay: this.getAttribute('autoplay')
            };
            this.render();
        }

        get template() {
            return `<iframe style="display: block; margin: 0 auto; border: 0;" width="${this.options.width}" height="450px" src="//music.163.com/outchain/player?type=0&id=${this.options.id}&auto=${this.options.autoplay}&height=430"></iframe>`;
        }

        render() {
            if (this.options.id) this.innerHTML = this.template;
            else this.innerHTML = '网抑云歌单ID未填写！';
        }
    }

    window.customElements.define('x-mlist', xNeteaseList);

    class xBtn extends HTMLElement {
        constructor() {
            super();
            this.options = {
                icon: this.getAttribute('icon') || 'fa-link',
                href: this.getAttribute('href') || '#',
                type: /^primary$|^secondary$|^light$|^outline-light$|^outline-secondary$|^info$|^success$|^danger$|^dark$|^weibo$|^weixin$|^alipay$|^youku$|^toutiao$|^youtube$|^twitter$|^facebook$|^bilibili$|^ins$|^tumblr$/.test(this.getAttribute('type')) ? this.getAttribute('type') : 'primary',
                content: this.getAttribute('content') || '标签按钮'
            };
            this.outerHTML = `
				<div class="shortcode shortcode-x-btn">
				    <a class="x-btn x-btn-${this.options.type}" href="${this.options.href === "" || this.options.href === "{href}" ? "https://doufu.ru" : this.options.href}" target="_blank" rel="noopener noreferrer nofollow">
					    <span class="icon"><i class="fa ${(this.options.icon === "" || this.options.icon === "{icon}") ? "fa-link" : this.options.icon}"></i></span><span class="content">${(this.options.content === "" || this.options.content === "{content}") ? XConf.i18n.button : this.options.content}</span>
				    </a>
				</div>
			`;
        }
    }

    window.customElements.define('x-btn', xBtn);

    class xCard extends HTMLElement {
        constructor() {
            super();
            this.options = {
                title: this.getAttribute('title'),
                content: this.innerHTML,
                id: "x-card-" + Math.floor((Math.random() * 10000) + 1),
                fold: this.getAttribute('fold') === "on"
            };
            this.outerHTML = `
				<div class="shortcode shortcode-x-card${this.options.fold ? ' fold' : ''}" id="${this.options.id}">
				    <div class="title">${(this.options.title === '{title}' || this.options.title === '') ? XConf.i18n.XCard.title : this.options.title}</div>
				    <div class="content">${(this.options.content === '{content}' || this.options.content === '') ? XConf.i18n.XCard.content : this.options.content}</div>
				</div>
			`;
            let _this = this;
            document.querySelector('#' + this.options.id + ' .title').addEventListener('click', function (e) {
                let card = document.getElementById(e.target.parentElement.id);
                if (card.classList.contains('fold'))
                    card.classList.remove('fold');
                else
                    card.classList.add('fold');
            });
        }
    }

    window.customElements.define('x-card', xCard);
});
