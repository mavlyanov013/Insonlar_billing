var cookieHelper = {
    // this gets a cookie and returns the cookies value, if no cookies it returns blank ""
    get: function (c_name) {
        if (document.cookie.length > 0) {
            var c_start = document.cookie.indexOf(c_name + "=");
            if (c_start != -1) {
                c_start = c_start + c_name.length + 1;
                var c_end = document.cookie.indexOf(";", c_start);
                if (c_end == -1) {
                    c_end = document.cookie.length;
                }
                return unescape(document.cookie.substring(c_start, c_end));
            }
        }
        return "";
    },

    // this sets a cookie with your given ("cookie name", "cookie value", "good for x days")
    set: function (c_name, value, expiredays) {
        var exdate = new Date();
        exdate.setDate(exdate.getDate() + expiredays);
        document.cookie = c_name + "=" + escape(value) + ((expiredays == null) ? "" : "; expires=" + exdate.toUTCString());
    },

    // this checks to see if a cookie exists, then returns true or false
    check: function (c_name) {
        c_name = cookieHelper.get(c_name);
        if (c_name != null && c_name != "") {
            return true;
        } else {
            return false;
        }
    }
};

function setButton() {
    var amount = parseInt($('#donate-amount').val());
    if (!isNaN(amount) && amount >= 500) {
        $('#donate-submit').attr('disabled', false);
    } else {
        $('#donate-submit').attr('disabled', true);
    }
}

function initPayment() {
    setButton();

    $('.values a').click(function () {
        $('#donate-amount').val($(this).data('value')).trigger('keyup');
        return false;
    });

    $('.to-donate').click(function () {
        if ($('#donate').length > 0) {
            $('html, body').stop().animate({
                scrollTop: $('#donate').offset().top + 5
            }, 1000);

            return false;
        }

        return true;
    });

    $('.radios input[name="method"]').click(function () {
        setButton();
    });

    $('#donate-amount').on('keyup', function () {
        setButton();
    });

    $("#donate-amount").keydown(function (e) {
        // Allow: backspace, delete, tab, escape, enter and .
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
            // Allow: Ctrl/cmd+A
            (e.keyCode == 65 && (e.ctrlKey === true || e.metaKey === true)) ||
            // Allow: Ctrl/cmd+C
            (e.keyCode == 67 && (e.ctrlKey === true || e.metaKey === true)) ||
            // Allow: Ctrl/cmd+X
            (e.keyCode == 88 && (e.ctrlKey === true || e.metaKey === true)) ||
            // Allow: home, end, left, right
            (e.keyCode >= 35 && e.keyCode <= 39)) {
            // let it happen, don't do anything
            return;
        }
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });
}

function mobilecheck() {
    if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
        return false;
    }
    return true;
}

function scrollToPos(elem, offset) {
    $('body,html').animate({scrollTop: $(elem).offset().top + offset}, 600);
}
$(document).ready(function () {
    var $name = $('#xs-donate-name');

    if (cookieHelper.check('_nm')) {
        $name.val(cookieHelper.get('_nm'));
    }

    $name.on('change', function () {
        cookieHelper.set('_nm', $(this).val(), 120);
    });

    $('#appeal-agreement').on('click', function (e) {
        var isChecked = $(this).is(':checked');
        if (isChecked) {
            $('#appeal_button').removeAttr('disabled')
        } else {
            $('#appeal_button').attr('disabled', true)
        }
    });

    $('#xs-appeal-form').on('change.yii', function (ev) {
        var atts = $(this).data('yiiActiveForm').attributes;
        $.ajax({
            url: $(this).attr('action'),
            type: $(this).attr('method'),
            data: $(this).serialize(),
            success: function (labels) {
                $.each(atts, function (i) {
                    $('label[for=' + atts[i]['id'] + ']').text(labels[atts[i]['id']])
                });
            }
        });
    })

    $('.xs-navs-button.dropdown').hover(function () {
        $(this).find('.dropdown-menu').first().stop(true, true).delay(150).slideDown();
    }, function () {
        $(this).find('.dropdown-menu').first().stop(true, true).delay(100).slideUp()
    });

    if (mobilecheck()) {
        $('.phone').formatter({
            'pattern': '+(998) {{99}} {{999}}-{{99}}-{{99}}',
            'persistent': true
        });

        if ($('.phone').length > 0) {
            setTimeout(function () {
                $('html, body').stop().animate({
                    scrollTop: 120
                }, 1000);
            }, 1000);
        }


    } else {
        $('.phone').each(function () {
            if ($(this).val().length == 0) {
                $(this).val('+998');
            }
        })
    }
});