$(document).ready(function () {
    function getLng() {
        let hash = window.location.hash.substr(1).toLowerCase();
        return ['ru', 'en', 'kz'].includes(hash) ? hash : 'ru';
    }

    $("#resetEmailBtn").click(function () {
        let lng = getLng();
        let email = $("#resetEmailInput").val().trim(); 
        let $message = $("#message");
        let btn = $(this);

        if (email === "") {
            let errorText = (window.langArr && langArr['error-empty-email']) 
                            ? langArr['error-empty-email'][lng] 
                            : "Введите Email!";
            $message.text(errorText).css("color", "red");
            return;
        }

        // Блокируем кнопку
        btn.prop('disabled', true);
        if (window.langArr && langArr['btn-sending']) {
            btn.text(langArr['btn-sending'][lng]);
        }

        $.ajax({
            type: "POST",
            url: "/auth/reset_pass.php", // Убедись, что путь к файлу верный
            data: { email: email },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    $message.text(response.message).css("color", "#009688");
                    setTimeout(function() {
                        window.location.href = "/auth/login.html" + window.location.hash;
                    }, 3000);
                } else {
                    $message.text(response.message).css("color", "red");
                    btn.prop('disabled', false).text("Отправить инструкции");
                }
            },
            error: function () {
                $message.text("Ошибка сервера").css("color", "red");
                btn.prop('disabled', false).text("Отправить инструкции");
            }
        });
    });
})