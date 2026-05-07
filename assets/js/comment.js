/**
 * Функция переключения лайка
 * @param {HTMLElement} btn - Элемент кнопки, на которую нажали
 * @param {number} newsId - ID новости из базы
 */
function toggleLike(btn, newsId) {
    // Отправляем запрос на сервер
    fetch('/actions/news/like.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'news_id=' + newsId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const countElem = btn.querySelector('.int-count');
            let currentCount = parseInt(countElem.innerText);

            if (data.action === 'added') {
                btn.classList.add('active');
                countElem.innerText = currentCount + 1;
            } else if (data.action === 'removed') {
                btn.classList.remove('active');
                countElem.innerText = currentCount - 1;
            }
        } else {
            // Если пользователь не авторизован (сервер вернул успех: false)
            alert(data.message || 'Чтобы ставить лайки, нужно авторизоваться');
            if (data.redirect) {
                window.location.href = '/auth/login.html';
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ошибка при соединении с сервером');
    });
}