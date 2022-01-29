document.addEventListener('DOMContentLoaded', function () {
    /**
     * 应用函数到 DOMElement 数组
     * @param selector
     * @param func
     * @param parent
     */
    function qf(selector, func, parent = document) {
        if (!parent) {
            parent = document;
        }
        let selectors = parent.querySelectorAll(selector);
        [].forEach.call(selectors, func);
    }

    /**
     * document.querySelector 缩写
     * @param selector
     * @param parent
     * @returns {*}
     */
    function qs(selector, parent = document) {
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
        const arr1 = serverVersion.split('.')
        const arr2 = currentVersion.split('.')
        const length1 = arr1.length
        const length2 = arr2.length
        const minlength = Math.min(length1, length2)
        let i = 0
        for (i; i < minlength; i++) {
            let a = parseInt(arr1[i])
            let b = parseInt(arr2[i])
            if (a > b) {
                return 1
            } else if (a < b) {
                return -1
            }
        }
        if (length1 > length2) {
            for (let j = i; j < length1; j++) {
                if (parseInt(arr1[j]) !== 0) {
                    return 1
                }
            }
            return 0
        } else if (length1 < length2) {
            for (let j = i; j < length2; j++) {
                if (parseInt(arr2[j]) !== 0) {
                    return -1
                }
            }
            return 0
        }
        return 0
    }

    const xConfig = qs('.x-config'),
        xContent = qs('.x-content', xConfig);

    // 移动表单
    let form = xConfig.parentNode.querySelector('form');
    form.parentNode.removeChild(form);
    xContent.appendChild(form);


    // 恢复现场
    let xTabs = qs('.x-tabs', xConfig),
        active = sessionStorage.getItem('x-active');
    if (!active || active === "" && active === "undefined") {
        active = '.x-notice'
    }

    qf(active, el => {
        el.classList.add('active');
    }, xContent);
    qf(`[data-class="${active.replace('.', '')}"]`, el => {
        el.classList.add('active');
    }, xTabs);

    // 处理点击
    [].forEach.call(xTabs.querySelectorAll(':scope > li'), el => {
        el.addEventListener('click', el => {
            el.preventDefault();
            el.stopPropagation();
            window.scrollTo(0, 0);
            qf('.x-tabs li', el => {
                el.classList.remove('active');
            })
            el.target.classList.add('active');
            let className = '.' + el.target.dataset.class;
            sessionStorage.setItem('x-active', className);
            [].forEach.call(xContent.querySelectorAll('.x-item'), el => {
                el.classList.remove('active');
            });
            [].forEach.call(xContent.querySelectorAll(className), el => {
                el.classList.add('active');
            });
        });
    });

    // 加载日志
    fetch('https://api.vvhan.com/api/qqsc?key=95aa93d32e571b1579cfb15134b57dbd')
        .then(response => response.json())
        .then(json => {
            let serverVersion = json.title.substr(0, 5),
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
        })
        .catch(err => console.log('Request Failed', err));
});