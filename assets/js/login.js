$(document).ready(function () {
    const authTitle = $("#loginSlt");
    const authSubtitle = $("#authSubtitle");
    const messageBlock = $("#message");

    // Универсальная функция для уведомлений
    function showMessage(text, isSuccess) {
        messageBlock.stop(true, true) // Останавливаем прошлые анимации
            .text(text)
            .css("color", isSuccess ? "#009688" : "#d9534f")
            .fadeIn();

        // Исчезает через 6 секунд
        setTimeout(() => {
            messageBlock.fadeOut(1000, function() {
                $(this).text(""); // Очищаем текст после скрытия
            });
        }, 6000);
    }

    function getLng() {
        let hash = window.location.hash.substr(1).toLowerCase();
        return ['ru', 'en', 'kz'].includes(hash) ? hash : 'ru';
    }

    // Переключение на регистрацию
    $("#showRegisterForm").click(function () {
        let lng = getLng();
        $("#loginForm").hide();
        $("#registerForm").fadeIn();
        authTitle.text(langArr['regTitle'][lng]);
        authSubtitle.text(langArr['regSubtitle'][lng]);
    });

    // Переключение на вход
    $("#showLoginForm").click(function () {
        let lng = getLng();
        $("#registerForm").hide();
        $("#verification").hide();
        $("#loginForm").fadeIn();
        authTitle.text(langArr['loginSlt'][lng]);
        authSubtitle.text(langArr['authSubtitle'][lng]);
    });

    // Отправка регистрации
    $("#registerForm").submit(function (e) {
        e.preventDefault();
        let lng = getLng();
        $.ajax({
            type: "POST",
            url: "/auth/registration.php",
            data: { 
                name: $("#name").val(), 
                email: $("#registerEmail").val(), 
                password: $("#registerPassword").val(), 
                bio: $("#bio").val() 
            },
            dataType: "json",
            success: function (response) {
                showMessage(response.message, response.success); // Используем функцию
                if (response.success) {
                    $("#registerForm").hide();
                    $("#verification").fadeIn();
                    authTitle.text(langArr['verifTitle'][lng]);
                    authSubtitle.text(langArr['verifSubtitle'][lng]);
                }
            }
        });
    });

    // Проверка кода
    $("#verifyBtn").click(function () {
        let lng = getLng();
        let email = $("#registerEmail").val();
        let code = $("#verificationCode").val();

        $.ajax({
            type: "POST",
            url: "/auth/verify_code.php",
            data: { email: email, code: code },
            dataType: "json",
            success: function (response) {
                showMessage(response.message, response.success); // Используем функцию
                if (response.success) {
                    setTimeout(() => {
                        $("#verification").hide();
                        $("#loginForm").fadeIn();
                        authTitle.text(langArr['loginSlt'][lng]);
                        authSubtitle.text(langArr['authSubtitle'][lng]);
                    }, 2000);
                }
            }
        });
    });

    // Вход
    $("#loginForm").submit(function (e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: "/auth/login.php",
            data: { 
                email: $("#loginEmail").val(), 
                password: $("#loginPassword").val() 
            },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    // Если успех — сразу ведем дальше
                    if (response.force_change === 1) {
                        window.location.href = "/auth/change_pass.php" + window.location.hash;
                    } else {
                        window.location.href = "/pages/index.php" + window.location.hash;
                    }
                } else {
                    // Если ошибка — показываем её на 6 секунд
                    showMessage(response.message, false);
                }
            },
            error: function () {
                showMessage("Ошибка сервера", false);
            }
        });
    });
});