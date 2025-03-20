$(document).ready(function () {
    // Nav bar for phones
    $("#nav_btn").click(function () {
        $(".navbar_links").slideToggle();
    });
    $(document).on("click", function (event) {
        if (!$(event.target).closest("#nav_btn, .navbar_links").length) {
            $(".navbar_links").hide();
        }
    });
    // Dropdown for nav
    $("#drop_btn").click(function () {
        $("#dropdown_menu").toggle();
    });
    $(document).on("click", function (event) {
        if (!$(event.target).closest("#drop_btn, #dropdown_menu").length) {
            $("#dropdown_menu").hide();
        }
    });
    // User dropdown for forums
    $(".drop_user").on("click", function (event) {
        event.preventDefault();
        $(".user_menu").hide();
        var id = $(this).data('id');
        $(".user_menu[data-id='" + id + "']").show();
    });
    // Dropdown for post topic
    $("#drop_post").click(function (event) {
        event.preventDefault();
        $("#postform").toggle();
    });
    $(document).on("click", function (event) {
        if (!$(event.target).closest(".drop_user, .user_menu").length) {
            $(".user_menu").hide();
        }
    });
    // Load more and all buttons
    $(document).ready(function () {
        $('.append_thumbs').btnLoadmore({
            showItem: 10,
            whenClickBtn: 10,
            textBtn: 'View More',
            textLoadAll: 'View All',
            classBtn: 'btn',
            IDBtn2: 'load_all',
            IDBtn: 'load_more'
        });
    });
    // Reload the captcha
    $("#reloadCaptcha").click(function () {
        var captchaImage = $('#captcha_image').attr('src');
        captchaImage = captchaImage.substring(0, captchaImage.lastIndexOf("?"));
        captchaImage = captchaImage + "?rand=" + Math.random() * 1000;
        $('#captcha_image').attr('src', captchaImage);
    });
    // Quote button for forums
    $(document).on('click', '.quote', function (event) {
        event.preventDefault();
        var postID = $(this).data('post-id');
        //var display_name = $(this).parent().parent().find('.display_name').text();
        $.ajax({
            url: '../../includes/quote.php',
            type: 'post',
            data: { postID: postID },
            success: function (response) {
                var postData = JSON.parse(response);
                var user_name = postData.username;
                var post = postData.post;
                var current_text = $('#reply').val();
                var new_text = '[quote=' + user_name + ']\n' + post + '\n[/quote]\n' + current_text;
                $('#reply').val(new_text);
                $('html, body').animate({
                    scrollTop: $("#reply").offset().top
                }, 1000);
            }
        });
    });
    // Submit post form
    $("#post_form").submit(function (e) {
        e.preventDefault();
        var forum_id = $("#forum_id").val();
        var title = $("#title").val();
        var post = $("#post").val();
        var pinned = 0;
        if ($('#pinned').length && $('#pinned').is(':checked')) {
            pinned = 1;
        }
        var locked = 0;
        if ($('#locked').length && $('#locked').is(':checked')) {
            locked = 1;
        }
        if (post === '') {
            $("#msg_post").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Please enter a post.</div>');
            return;
        }
        $.ajax({
            type: "POST",
            url: "/includes/comments.php?act=post",
            data: {
                forum_id: forum_id,
                title: title,
                post: post,
                pinned: pinned,
                locked: locked
            },
            success: function (response) {
                if (response == 'no_login') {
                    $("#msg_post").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> You have to be logged in to post. Click here to <a href="/login/">login</a> or <a href="/register/">register</a>.</div>');
                } else if (response == 'success') {
                    $("#forums").load(location.href + " #forums");
                    document.getElementById('title').value = "";
                    document.getElementById('post').value = "";
                    $('#pinned').prop('checked', false);
                    $('#locked').prop('checked', false);
                } else {
                    $("#msg_post").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> There was a problem making this post, please refresh page and try again.</div>');
                }
            }
        });
    });
    // Submit reply form
    $("#reply_form").submit(function (e) {
        e.preventDefault();
        var parent_id = $("#parent_id").val();
        var forum_id = $("#forum_id").val();
        var title = $("#title").val();
        var reply = $("#reply").val();
        if (reply === '') {
            $("#msg_replies").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Please enter a reply.</div>');
            return;
        }
        $.ajax({
            type: "POST",
            url: "/includes/comments.php?act=reply",
            data: {
                parent_id: parent_id,
                forum_id: forum_id,
                title: title,
                reply: reply
            },
            success: function (response) {
                if (response == 'no_login') {
                    $("#msg_replies").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> You have to be logged in to post. Click here to <a href="/login/">login</a> or <a href="/register/">register</a>.</div>');
                } else if (response == 'success') {
                    // Assuming the server returns the updated replies HTML
                    $.ajax({
                        url: location.href,
                        success: function (data) {
                            var newReplies = $(data).find("#replies").html();
                            $("#replies").html(newReplies);
                            var newPagination = $(data).find(".pagination").html();
                            $(".pagination").html(newPagination);
                        }
                    });
                    document.getElementById('reply').value = "";
                } else {
                    $("#msg_replies").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> There was a problem posting this reply, please refresh page and try again.</div>');
                }
            }
        });
    });
    // Forum reply function
    $(document).on('click', '#replybtn', function () {
        $('html, body').animate({
            scrollTop: $("#reply").offset().top
        }, 1000);
    });

    document.getElementById('notification-bell').addEventListener('click', function (event) {
        event.preventDefault();
        var notificationBox = document.getElementById('notification-box');
        var bellIcon = document.getElementById('notification-bell');
        if (notificationBox.style.display === 'none') {
            notificationBox.style.display = 'block';
            bellIcon.classList.add('selected');
        } else {
            notificationBox.style.display = 'none';
            bellIcon.classList.remove('selected');
        }
    });

    // Hide notification dropdown when clicking outside
    document.addEventListener('click', function (event) {
        var notificationBox = document.getElementById('notification-box');
        var bellIcon = document.getElementById('notification-bell');
        if (!notificationBox.contains(event.target) && !bellIcon.contains(event.target)) {
            notificationBox.style.display = 'none';
            bellIcon.classList.remove('selected');
        }
    });

});