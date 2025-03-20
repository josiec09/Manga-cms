$(document).ready(function () {
    // Submit comment form
    $("#comment_form").submit(function (e) {
        e.preventDefault();
        var comment = $("#comment").val();
        var parent_id = $("#parent_id").val();
        var manga_id = $("#manga_id").val();
        if (comment === '') {
            $("#msg_comments").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Please enter a comment.</div>');
            return;
        }
        $.ajax({
            type: "POST",
            url: "/includes/comments.php?act=comment",
            data: {
                comment: comment,
                parent_id: parent_id,
                manga_id: manga_id
            },
            success: function (response) {
                if (response == 'no_login') {
                    $("#msg_comments").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> You have to be logged in to post. Click here to <a href="/login/">login</a> or <a href="/register/">register</a>.</div>');
                } else if (response == 'success') {
                    $("#comments").load(location.href + " #comments");
                    document.getElementById('comment').value = "";
                    document.getElementById('parent_id').value = "0";
                    $('#comment').attr("placeholder", "Type your comment here...");
                    $('#cancel-reply').css("display", "none");
                } else {
                    $("#msg_comments").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> There was a problem posting this comment, please refresh page and try again.</div>');
                }
            }
        });
    });
    // Upvote
    $(document).on("click", ".upvote", function (event) {
        event.preventDefault();
        var button = this;
        var comment_id = $(this).attr("id");
        $("#msg_comments").html('');
        $.ajax({
            type: "POST",
            url: "/includes/comments.php?act=up",
            data: {
                id: comment_id
            },
            success: function (data) {
                if (data == "success") {
                    var upvote_count = $(button).find(".upvote-count");
                    upvote_count.text(parseInt(upvote_count.text()) + 1);
                } else {
                    $("#msg_comments").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> You have already voted on this comment.</div>');
                    $("html, body").animate({
                        scrollTop: $("#msg_comments").offset().top
                    }, 2000);
                }
            }
        });
    });
    // Downvote
    $(document).on("click", ".downvote", function (event) {
        event.preventDefault();
        var button = this;
        var comment_id = $(this).attr("id");
        $("#msg_comments").html('');
        $.ajax({
            type: "POST",
            url: "/includes/comments.php?act=down",
            data: {
                id: comment_id
            },
            success: function (data) {
                if (data == "success") {
                    var downvote_count = $(button).find(".downvote-count");
                    downvote_count.text(parseInt(downvote_count.text()) + 1);
                } else {
                    $("#msg_comments").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> You have already voted on this comment.</div>');
                    $("html, body").animate({
                        scrollTop: $("#msg_comments").offset().top
                    }, 2000);
                }
            }
        });
    });
    // Comment reply function
    $(document).on('click', '.reply', function () {
        var comment_id = $(this).attr("id");
        $('#parent_id').val(comment_id);
        $('#comment').attr("placeholder", "Type your reply for " + $(this).parent().find('.header b a').text() + " here...");
        $('#cancel-reply').removeAttr("style");
        $('html, body').animate({
            scrollTop: $("#comment").offset().top
        }, 1000);
        //$('#comment').focus();
    });
    // Cancel reply button
    $(document).on('click', '#cancel-reply', function () {
        $('#parent_id').val('');
        $('#comment').attr("placeholder", "Type your comment here...");
        $('#cancel-reply').css("display", "none");
    });

});