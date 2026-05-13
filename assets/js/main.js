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

// Mobile Navbar Toggler
document.addEventListener('DOMContentLoaded', () => {
    const toggler = document.querySelector('.navbar-toggler');
    const collapse = document.querySelector('.navbar-collapse');

    if (toggler && collapse) {
        toggler.addEventListener('click', () => {
            collapse.classList.toggle('show');
        });
    }
});

// Hero Carousel Logic
document.addEventListener('DOMContentLoaded', () => {
    const heroItems = document.querySelectorAll('.hero-item');
    const dots = document.querySelectorAll('.dot');
    const prevBtn = document.querySelector('.carousel-prev');
    const nextBtn = document.querySelector('.carousel-next');
    let currentIndex = 0;
    let autoPlayInterval;

    if (heroItems.length === 0) return;

    function showSlide(index) {
        heroItems.forEach(item => item.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));

        heroItems[index].classList.add('active');
        dots[index].classList.add('active');
        currentIndex = index;
    }

    function nextSlide() {
        let index = (currentIndex + 1) % heroItems.length;
        showSlide(index);
    }

    function prevSlide() {
        let index = (currentIndex - 1 + heroItems.length) % heroItems.length;
        showSlide(index);
    }

    if (nextBtn) nextBtn.addEventListener('click', () => {
        nextSlide();
        resetAutoPlay();
    });

    if (prevBtn) prevBtn.addEventListener('click', () => {
        prevSlide();
        resetAutoPlay();
    });

    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            showSlide(index);
            resetAutoPlay();
        });
    });

    // Touch Swipe Logic
    let touchStartX = 0;
    let touchEndX = 0;
    const heroCarousel = document.querySelector('.hero-carousel');

    if (heroCarousel) {
        heroCarousel.addEventListener('touchstart', e => {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        heroCarousel.addEventListener('touchend', e => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        }, { passive: true });
    }

    function handleSwipe() {
        const swipeThreshold = 50;
        if (touchEndX < touchStartX - swipeThreshold) {
            nextSlide();
            resetAutoPlay();
        } else if (touchEndX > touchStartX + swipeThreshold) {
            prevSlide();
            resetAutoPlay();
        }
    }

    function startAutoPlay() {
        autoPlayInterval = setInterval(nextSlide, 6000);
    }

    function resetAutoPlay() {
        clearInterval(autoPlayInterval);
        startAutoPlay();
    }

    startAutoPlay();
});