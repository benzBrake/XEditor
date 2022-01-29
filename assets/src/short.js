document.addEventListener('DOMContentLoaded', () => {
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


    class xPlayer extends HTMLElement {
        constructor() {
            super();
            this.options = {
                src: this.getAttribute('src'),
                player: this.getAttribute('player') || window.XConf.options.playerUrl
            };
            this.render();
        }

        render() {
            if (this.options.src) this.innerHTML = `<iframe allowfullscreen="true" src="${this.options.player + this.options.src}"></iframe>`;
            else this.innerHTML = '播放地址未填写！';
        }
    }

    window.customElements.define('x-player', xPlayer);
});