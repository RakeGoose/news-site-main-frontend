const allLang = ['ru', 'en', 'kz'];

// 1. Смена языка при клике
document.querySelectorAll('.lang-item').forEach(link => {
    link.addEventListener('click', function (e) {
        e.preventDefault();
        const newLang = this.getAttribute('href').replace('#', '');
        localStorage.setItem('lang', newLang);
        // Добавляем параметр lang в URL для PHP и хеш для JS
        window.location.href = window.location.pathname + "?lang=" + newLang + "#" + newLang;
    });
});

function changeLanguage() {
    let hash = window.location.hash.substr(1).toLowerCase();
    
    // Если хеша нет, берем из localStorage или ставим ru
    if (!allLang.includes(hash)) {
        hash = localStorage.getItem('lang') || 'ru';
        window.location.hash = hash;
    }

    // Сохраняем актуальный выбор
    localStorage.setItem('lang', hash);

    // 1. Перевод заголовка
    if (document.querySelector('title') && langArr['unit']) {
        document.querySelector('title').innerHTML = langArr['unit'][hash];
    }

    // 2. Подсветка активной кнопки языка
    document.querySelectorAll('.lang-item').forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === '#' + hash) {
            link.classList.add('active');
        }
    });

    // 3. Перевод элементов по ID
    for (let key in langArr) {
        let elem = document.getElementById(key);
        if (elem) {
            if (elem.tagName === 'INPUT' || elem.tagName === 'TEXTAREA') {
                elem.placeholder = langArr[key][hash];
            } else {
                elem.innerHTML = langArr[key][hash];
            }
        }
    }

    // САМОЕ ВАЖНОЕ: Показываем страницу после перевода
    document.body.style.opacity = "1";
}

// Запускаем проверку немедленно, не дожидаясь DOMContentLoaded
// Это минимизирует время "белого экрана"
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', changeLanguage);
} else {
    changeLanguage();
}

window.addEventListener('hashchange', changeLanguage);