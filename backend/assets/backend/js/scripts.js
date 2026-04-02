var backendHelper = {
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
        c_name = backendHelper.get(c_name);
        if (c_name != null && c_name != "") {
            return true;
        } else {
            return false;
        }
    },
    sessionId: function () {
        var id = backendHelper.get('_gab');
        if (id.length > 10) {
            return id;
        }
        id = backendHelper.guid();
        backendHelper.set('_gab', id, 100);
        return backendHelper.sessionId();
    },
    guid: function () {
        function s4() {
            return Math.floor((1 + Math.random()) * 0x10000)
                .toString(16)
                .substring(1);
        }

        return s4() + s4() + '-' + s4() + '-' + s4() + '-' + s4() + '-' + s4() + s4() + s4();
    }
};

(function ($) {
    $(document).ready(function () {
        var id = backendHelper.sessionId();
        //$('.checkbo').checkBo();
        setFocusToSearch();
        setDeleteButton();
        //setTrashButton();

        $('.mobile-phone').formatter({
            'pattern': '+(998) {{99}} {{999}}-{{99}}-{{99}}',
            'persistent': true
        });

        $('input[type="phone"]').formatter({
            'pattern': '+(998) {{99}} {{999}}-{{99}}-{{99}}',
            'persistent': true
        });

        $('.landline-phone').formatter({
            'pattern': '+(0) {{999}} {{999}}-{{99}}-{{99}}',
            'persistent': true
        });

        var $loading = $('#loader').hide();

        $(document)
            .ajaxStart(function () {
                $loading.show();
            })
            .ajaxStop(function () {
                $loading.hide();
            });

        $(".summernote").on("summernote.paste", function (e, ne) {
            var bufferText = ((ne.originalEvent || ne).clipboardData || window.clipboardData).getData('text/html');
            ne.preventDefault();
            bufferText = bufferText.replace(/style=".*?"/ig, "").replace(/class=".*?"/ig, "").replace(/<meta .*?>/ig, "");
            document.execCommand('insertHTML', false, bufferText);
        });


    });
    $(document).on('ready pjax:success', function () {
        //setFocusToSearch();
        setDeleteButton();
        if (typeof $.fn.iconpicker === 'function') {
 	$('.icp-auto').iconpicker();
	}
        $('.selectable-row tr').on('click', function () {
            $(this).find('.checkbox input:first-child').click();
        });

        $('.checkbo:not(.chbd)').checkBo();
        $('.checkbo:not(.chbd)').addClass('chbd');
    });

})(jQuery);

function setDeleteButton() {
    $('.btn-delete').on('click', function () {
        if (confirm('Are you sure to delete?')) {
            if ($(this).attr('action').length > 0) {
                document.location.href = $(this).attr('action');
            } else {
                return true;
            }
        }
        return false;
    });
}

function setTrashButton() {
    $('.btn-trash').on('click', function () {
        if (confirm('Are you sure move to trash?')) {
            if ($(this).attr('action').length > 0) {
                document.location.href = $(this).attr('action');
            } else {
                return true;
            }
        }
        return false;
    });
}

function setFocusToSearch() {
    var input = $("input[name*='[search]'][type='text']");
    if (input == undefined) {
        input = $('#data-grid-filters input[type=\"text\"]:first');
    }
    if (input != undefined && input.length > 0) {
        input.focus().delay(1000).val(input.val());
        document.getElementById(input.attr('id')).setSelectionRange(100, 100);
    }
}

function convertToSlug(Text) {
    Text = cyrlat(Text.toLowerCase());
    return Text
        .replace(/[^\w _\-]+/g, '')
        .replace(/ +/g, '-');
}

function convertToCyrill(Text) {
    Text = latcyr(Text);
    return Text;
}

function latcyr(car) {
    car = car.replace(/Yu/g, "Ю");
    car = car.replace(/yu/g, "ю");
    car = car.replace(/Ya/g, "Я");
    car = car.replace(/ya/g, "я");
    car = car.replace(/Ch/g, "Ч");
    car = car.replace(/ch/g, "ч");
    car = car.replace(/Sh/g, "Ш");
    car = car.replace(/sh/g, "ш");
    car = car.replace(/Sh/g, "Щ");
    car = car.replace(/sh/g, "щ");
    car = car.replace(/Yo/g, "Ё");
    car = car.replace(/yo/g, "ё");
    car = car.replace(/G`/g, "Ғ");
    car = car.replace(/g`/g, "ғ");
    car = car.replace(/G'/g, "Ғ");
    car = car.replace(/g'/g, "ғ");
    car = car.replace(/O`/g, "Ў");
    car = car.replace(/o`/g, "ў");
    car = car.replace(/O'/g, "Ў");
    car = car.replace(/o'/g, "ў");
    car = car.replace(/A/g, "А");
    car = car.replace(/a/g, "а");
    car = car.replace(/B/g, "Б");
    car = car.replace(/b/g, "б");
    car = car.replace(/V/g, "В");
    car = car.replace(/v/g, "в");
    car = car.replace(/G/g, "Г");
    car = car.replace(/g/g, "г");
    car = car.replace(/D/g, "Д");
    car = car.replace(/d/g, "д");
    car = car.replace(/E/g, "Е");
    car = car.replace(/e/g, "е");
    car = car.replace(/J/g, "Ж");
    car = car.replace(/j/g, "ж");
    car = car.replace(/Z/g, "З");
    car = car.replace(/z/g, "з");
    car = car.replace(/I/g, "И");
    car = car.replace(/i/g, "и");
    car = car.replace(/Y/g, "Й");
    car = car.replace(/y/g, "й");
    car = car.replace(/K/g, "К");
    car = car.replace(/k/g, "к");
    car = car.replace(/L/g, "Л");
    car = car.replace(/l/g, "л");
    car = car.replace(/M/g, "М");
    car = car.replace(/m/g, "м");
    car = car.replace(/N/g, "Н");
    car = car.replace(/n/g, "н");
    car = car.replace(/O/g, "О");
    car = car.replace(/o/g, "о");
    car = car.replace(/P/g, "П");
    car = car.replace(/p/g, "п");
    car = car.replace(/R/g, "Р");
    car = car.replace(/r/g, "р");
    car = car.replace(/S/g, "С");
    car = car.replace(/s/g, "с");
    car = car.replace(/T/g, "Т");
    car = car.replace(/t/g, "т");
    car = car.replace(/U/g, "У");
    car = car.replace(/u/g, "у");
    car = car.replace(/F/g, "Ф");
    car = car.replace(/f/g, "ф");
    car = car.replace(/X/g, "Х");
    car = car.replace(/x/g, "х");
    car = car.replace(/C/g, "Ц");
    car = car.replace(/c/g, "ц");
    car = car.replace(/E/g, "Э");
    car = car.replace(/e/g, "э");
    car = car.replace(/H/g, "Ҳ");
    car = car.replace(/h/g, "ҳ");
    car = car.replace(/Q/g, "Қ");
    car = car.replace(/q/g, "қ");

    return car;
}

function convertToLatin(Text) {
    Text = cyrlat(Text);
    return Text;
}

function cyrlat(car) {
    car = car.replace(/Ю/g, "Yu");
    car = car.replace(/ю/g, "yu");
    car = car.replace(/юе/g, "yuye");
    car = car.replace(/Я/g, "Ya");
    car = car.replace(/я/g, "ya");
    car = car.replace(/Ч/g, "Ch");
    car = car.replace(/ч/g, "ch");
    car = car.replace(/Ш/g, "Sh");
    car = car.replace(/ш/g, "sh");
    car = car.replace(/Щ/g, "Sh");
    car = car.replace(/щ/g, "sh");
    car = car.replace(/Ё/g, "Yo");
    car = car.replace(/ёе/g, "yoye");
    car = car.replace(/ё/g, "yo");
    car = car.replace(/Ғ/g, "G'");
    car = car.replace(/ғ/g, "g'");
    car = car.replace(/Ў/g, "O'");
    car = car.replace(/ў/g, "o'");
    car = car.replace(/А/g, "A");
    car = car.replace(/а/g, "a");
    car = car.replace(/ае/g, "aye");
    car = car.replace(/Б/g, "B");
    car = car.replace(/б/g, "b");
    car = car.replace(/В/g, "V");
    car = car.replace(/в/g, "v");
    car = car.replace(/Г/g, "G");
    car = car.replace(/г/g, "g");
    car = car.replace(/Д/g, "D");
    car = car.replace(/д/g, "d");
    car = car.replace(/Е/g, "E");
    car = car.replace(/е/g, "e");
    car = car.replace(/Ж/g, "J");
    car = car.replace(/ж/g, "j");
    car = car.replace(/З/g, "Z");
    car = car.replace(/з/g, "z");
    car = car.replace(/И/g, "I");
    car = car.replace(/и/g, "i");
    car = car.replace(/ие/g, "iye");
    car = car.replace(/Й/g, "Y");
    car = car.replace(/й/g, "y");
    car = car.replace(/К/g, "K");
    car = car.replace(/к/g, "k");
    car = car.replace(/Л/g, "L");
    car = car.replace(/л/g, "l");
    car = car.replace(/М/g, "M");
    car = car.replace(/м/g, "m");
    car = car.replace(/Н/g, "N");
    car = car.replace(/н/g, "n");
    car = car.replace(/О/g, "O");
    car = car.replace(/о/g, "o");
    car = car.replace(/ое/g, "oye");
    car = car.replace(/П/g, "P");
    car = car.replace(/п/g, "p");
    car = car.replace(/Р/g, "R");
    car = car.replace(/р/g, "r");
    car = car.replace(/С/g, "S");
    car = car.replace(/с/g, "s");
    car = car.replace(/Т/g, "T");
    car = car.replace(/т/g, "t");
    car = car.replace(/У/g, "U");
    car = car.replace(/у/g, "u");
    car = car.replace(/уе/g, "uye");
    car = car.replace(/Ф/g, "F");
    car = car.replace(/ф/g, "f");
    car = car.replace(/Х/g, "X");
    car = car.replace(/х/g, "x");
    car = car.replace(/Ц/g, "C");
    car = car.replace(/ц/g, "c");
    car = car.replace(/Э/g, "E");
    car = car.replace(/э/g, "e");
    car = car.replace(/Ҳ/g, "H");
    car = car.replace(/ҳ/g, "h");
    car = car.replace(/Қ/g, "Q");
    car = car.replace(/қ/g, "q");

    return car;
}
