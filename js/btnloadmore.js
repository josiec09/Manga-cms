
/* Button Load more - v1.0.0 - 2018-02-28
* Copyright (c) 2018 NTTPS; */

(function ($) {
    $.fn.btnLoadmore = function (options) {
        var defaults = {
            showItem: 6,
            whenClickBtn: 10,
            textBtn: 'Loadmore ...',
            classBtn: '',
            IDBtn: '',
            setCookies: false,
            delayToScroll: 2000,
            textLoadAll: 'Load All',
            IDBtn2: '',
        },
            options = $.extend(defaults, options);

        this.each(function () {

            var $this = $(this),
                $childrenClass = $($this.children());

            // Get Element Of contents to hide
            $childrenClass.hide();

            //Show Element from Options
            $childrenClass.slice(0, defaults.showItem).show();

            //Show Button when item in contents != 0
            if ($childrenClass.filter(":hidden").length > 0) {
                $this.after('<div style="text-align: center;"> <button type="button" class="btn-loadmore ' + defaults.classBtn + '" id="' + defaults.IDBtn + '"><i class="fa fa-angle-down"></i> ' + defaults.textBtn + '</button> <button type="button" class="btn-loadall ' + defaults.classBtn + '" id="' + defaults.IDBtn2 + '"><i class="fa fa-angle-double-down"></i> ' + defaults.textLoadAll + '</button></div>')
            }

            $(document).on('click', '.btn-loadmore', function (e) {
                e.preventDefault();
                $childrenClass.filter(':hidden').slice(0, defaults.whenClickBtn).slideDown();
                if ($childrenClass.filter(":hidden").length == 0) {
                    $(".btn-loadmore").fadeOut('slow');
                    $(".btn-loadall").fadeOut('slow');
                }

            });

            $(document).on('click', '.btn-loadall', function (e) {
                e.preventDefault();
                $childrenClass.filter(':hidden').show();
                $(".btn-loadmore").fadeOut('slow');
                $(".btn-loadall").fadeOut('slow');
            });

            //function scrollDown() {
                //$('html, body').animate({
                   // scrollTop: $childrenClass.filter(":visible").last().offset().top
               // }, defaults.delayToScroll);
            //}
        });

    }

}(jQuery));