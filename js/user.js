$(document).ready(function () {
    // Change Password
    $("#save_password").click(function (e) {
        $("#pass_msg").html('');
        $.ajax({
            type: 'POST',
            url: '/includes/user.php?act=savepass',
            data: {
                old_password: $("#old_password").val(),
                password: $("#password").val(),
                cpassword: $("#cpassword").val(),
            },
            success: function (response) {
                if (response == 'not_logged') {
                    $("#pass_msg").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Not sure how you got to this page with out being logged in???');
                }
                else if (response == 'not_long') {
                    $("#pass_msg").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Password must be between 5 and 20 characters long!</div>');
                }
                else if (response == 'no_match') {
                    $("#pass_msg").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Passwords do not match!</div>');
                }
                else if (response == 'success') {
                    $("#pass_msg").html('<div class="alert alert-success"><i class="fa fa-check" aria-hidden="true"></i> Password has been updated!</div>');
                }
                else {
                    $("#pass_msg").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Your current password entered does not match what we have.</div>');
                }
            },
            error: function (xhr, textStatus, error) {
                $("#pass_msg").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> There was a problem changing your password, please refresh page and try again.</div>');
            }
        });
        return false;
    });

    // Update info
    $("#save_info").click(function (e) {
        $("#info_msg").html('');
        $.ajax({
            type: 'POST',
            url: '/includes/user.php?act=saveinfo',
            data: {
                email_address: $("#email_address").val(),
                bio: $("#bio").val(),
                age: $("#age").val(),
                gender: $("#gender").val(),
            },
            success: function (response) {
                if (response == 'not_logged') {
                    $("#info_msg").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Not sure how you got to this page with out being logged in???');
                }
                else if (response == 'no_email') {
                    $("#info_msg").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> The input fields must not be empty!!</div>');
                }
                else if (response == 'valid_email') {
                    $("#info_msg").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Please provide a valid email address!</div>');
                }
                else if (response == 'exists') {
                    $("#info_msg").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> An account already exists with that email!</div>');
                }
                else if (response == 'activate') {
                    $("#info_msg").html('<div class="alert alert-warning"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> You have changed your email address! You need to re-activate your account!</div>');
                }
                else {
                    $("#info_msg").html('<div class="alert alert-success"><i class="fa fa-check" aria-hidden="true"></i> Profile Info has been updated!</div>');
                }
            }
        });
    });

    // Upload Avatar
    $("#save_avatar").click(function (e) {
        e.preventDefault();
        var file = $("#file")[0].files[0];
        if (file === undefined || file == null) {
            $("#avatar_msg").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Please select a file to upload.</div>');
            return;
        }
        var formData = new FormData();
        formData.append("file", file);
        $("#avatar_msg").html('');

        $.ajax({
            type: 'POST',
            url: '/includes/user.php?act=upload',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response == 'not_logged') {
                    $("#avatar_msg").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Not sure how you got to this page with out being logged in???');
                }
                else if (response == 'exists') {
                    $("#avatar_msg").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> You have already uploaded this file.</div>');
                }
                else if (response == 'no_file') {
                    $("#avatar_msg").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Please select a file to upload.</div>');
                }
                else if (response == 'filesize') {
                    $("#avatar_msg").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> The file you are trying to upload is too large.</div>');
                }
                else if (response == 'file_type') {
                    $("#avatar_msg").html('<div class="alert alert-warning"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Only these file typs are allowed for upload: jpg, .jpeg, .gif, .png</div>');
                }
                else if (response == 'success') {
                    $("#avatar_msg").html('<div class="alert alert-success"><i class="fa fa-check" aria-hidden="true"></i> Avatar upload!</div>');
                    $(".avt_div").load(location.href + " .avt_div");
                }
            },
            error: function (xhr, textStatus, error) {
                $("#avatar_msg").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> There was a problem uploading you avatar, please refresh page and try again.</div>');
            }
        });
    });

    $("#e_2_factor").click(function () {
        $.ajax({
            url: "/includes/enable2fa.php",
            success: function (response) {
                if (response == 'error') {
                    $("#msg_2fa").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> There was an error enabling 2FA. Try again later!</div>');
                }
                else if (response == "success") {
                    window.location.href = "/two-factor/add/";
                }
            }
        });
    });

    // Add to favorites
$(document).on('click', '#add_fav', function (e) {
    $("#msg_gallery").html('');
    $.ajax({
        type: 'POST',
        url: '/includes/user.php?act=addfav',
        data: {
            gallery_id: $("#gallery_id").val(),
        },
        beforeSend: function () {
            $("#spinner_fv").css('display', 'inline-block');
            $("#add_fav").prop('disabled', true);
        },
        success: function (response) {
            var fv_txt = $("#add_fav").html();
            var fv_ct = fv_txt.replace('<i class="fa fa-heart"></i> Favorite ', '');
            var fv_ct = fv_ct.slice(fv_ct.indexOf('(') + 1, fv_ct.indexOf(')'));
            var n_fv_ct = parseInt(fv_ct) + 1;
            if (response == 'not_logged') {
                $("#msg_gallery").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> You have to be logged in for adding this gallery to favorites. Click here to <a href="/login/">login</a> or <a href="/register/">register</a>.</div>');
            }
            else if (response == 'already') {
                $("#msg_gallery").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> You already have this gallery to favorites.</div>');
            }
            else if (response == 'exceeded') {
                $("#msg_gallery").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> You have exceeded the number of favorites galleries.</div>');
            }
            else if (response == 'success') {
                $("#msg_gallery").html('<div class="alert alert-success"><i class="fa fa-check" aria-hidden="true"></i> You have added this gallery to favorites.</div>');
                $('#add_fav').attr('id', 'remove_fav').html('<i class="fa fa-heart-o"></i> Unfavorite (<span class="count">' + n_fv_ct + '</span>)');
            }
            else {
                $("#msg_gallery").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> There was a problem adding this gallery to favorites, please refresh page and try again.</div>');
            }
        },
        complete: function () {
            $("#spinner_fv").css('display', 'none');
            $("#add_fav").prop('disabled', false);
        },
        error: function (xhr, textStatus, error) {
            $("#msg_gallery").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> There was a problem adding this gallery to favorites, please refresh page and try again.</div>');
        }
    });
    return false;
});

    // Remove from favorites on manga page
    $(document).on('click', '#remove_fav', function (e) {
        $("#msg_gallery").html('');
        $.ajax({
            type: 'POST',
            url: '/includes/user.php?act=unfav',
            data: {
                gallery_id: $("#gallery_id").val(),
            },
            beforeSend: function () {
                $("#spinner_fv").css('display', 'inline-block');
                $("#remove_fav").prop('disabled', true);
            },
            success: function (response) {
                var fv_txt = $("#remove_fav .count").html();
                var n_fv_ct = parseInt(fv_txt) - 1;
                if (response == 'not_logged') {
                    $("#msg_gallery").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> You have to be logged in for removing this gallery from favorites. Click here to <a href="/login/">login</a> or <a href="/register/">register</a>.</div>');
                }
                else if (response == 'not_exists') {
                    $("#msg_gallery").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> You don\'t have this gallery to favorites.</div>');
                }
                else if (response == 'success') {
                    $("#msg_gallery").html('<div class="alert alert-success"><i class="fa fa-check" aria-hidden="true"></i> You removed this gallery from favorites.</div>');
                    $('#remove_fav').attr('id', 'add_fav').html('<i class="fa fa-heart"></i> Favorite (' + n_fv_ct + ')');
                }
                else {
                    $("#msg_gallery").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> There was a problem removing this gallery from favorites, please refresh page and try again.</div>');
                }
            },
            complete: function () {
                $("#spinner_fv").css('display', 'none');
                $("#remove_fav").prop('disabled', false);
            },
            error: function (xhr, textStatus, error) {
                $("#msg_gallery").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> There was a problem removing this gallery from favorites, please refresh page and try again.</div>');
            }
        });
        return false;
    });

    // Remove favorites from favorites list
    $("#favorites_list").on('click', '.remove_fav', function (e) {
        e.preventDefault();
        if (confirm("Are you sure you want to remove this gallery from your favorites?")) {
            $("#msg_favs").html('');
            $.ajax({
                url: '/includes/user.php?act=unfav',
                type: 'POST',
                data: {
                    gallery_id: $(this).attr('id'),
                },
                success: function (response) {
                    if (response == 'not_logged') {
                        $("#msg_favs").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> You have to be logged in for removing this gallery from favorites.</div>');
                    } else if (response == 'not_exists') {
                        $("#msg_gallery").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> You don\'t have this gallery to favorites.</div>');
                    } else if (response == 'success') {
                        var count_fav = $(".total_favs").html();
                        var count_fav_new = parseInt(count_fav) - 1;
                        $(".total_favs").html(count_fav_new);
                        if (count_fav_new == 0) {
                            $("#msg_favs").html('<div class="alert alert-warning"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> You don\'t have any favorited galleries. You can add one by clicking "Favorite" button found on each gallery page.</div>');
                        } else {
                            $("#msg_favs").html('<div class="alert alert-success"><i class="fa fa-check" aria-hidden="true"></i> You have removed this gallery from favorites.</div>');
                        }
                        $("#favorites_list").load(location.href + " #favorites_list");
                    } else {
                        $("#msg_favs").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> There was a problem removing this gallery from favorites, please refresh page and try again.</div>');
                    }
                },
                error: function (xhr, textStatus, error) {
                    $("#msg_favs").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> There was a problem removing this gallery from favorites, please refresh page and try again.</div>');
                }
            });
            return false;
        }
    });
});