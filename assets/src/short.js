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
				    <a class="x-btn x-btn-${this.options.type}" href="${this.options.href === "" || this.options.href === "{href}" ? "https://doufu.ru" : this.options.href}" target="_blank" rel="noopener noreferrer nofollow">
					    <span class="icon"><i class="fa ${(this.options.icon === "" || this.options.icon === "{icon}") ? "fa-link" : this.options.icon}"></i></span><span class="content">${(this.options.content === "" || this.options.content === "{content}") ? XConf.i18n.button : this.options.content}</span>
				    </a>
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
            if (this.options.src) this.innerHTML = `<div class="x-player"><iframe allowfullscreen="true" src="${this.options.player + this.options.src}"></iframe></div>`;
            else this.innerHTML = '播放地址未填写！';
        }
    }

    window.customElements.define('x-player', xPlayer);

    class xCard extends HTMLElement {
        constructor() {
            super();
            this.options = {
                title: this.getAttribute('title'),
                content: this.innerHTML,
                id: "x-card-" + Math.floor((Math.random() * 10000) + 1),
                fold: this.getAttribute('fold') === "on"
            };
            this.innerHTML = `
				<div class="x-card ${this.options.fold ? ' fold' : ''}" id="${this.options.id}">
				    <div class="title">${(this.options.title === '{title}' || this.options.title === '') ? '' : this.options.title}</div>
				    <div class="content">${this.options.content}</div>
				</div>
			`;

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