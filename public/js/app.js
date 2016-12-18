var open = false;
var $messageContainer = $('.message');
var $messageType = $('#type');
var $messageContent = $('#content');
var $messageIcon = $('.icon-message');

var spinner =
    '<div class="spinner">' +
        '<div class="double-bounce1"></div>' +
        '<div class="double-bounce2"></div>' +
    '</div>';

var playing = function () {
    $.ajax({
        url: "/api/getPlaying",
        type: "GET",
        success: function(data) {
            $.each(data, function(i, item) {
                $('#playing').text(item.fileName);
            });
        }
    });
};

var refresh = function () {
    playing();
    $.ajax({
        url: "/api/getRequestedURLs",
        type: "GET",
        success: function(data) {
            var icon;
            $(".queue").empty();
            $.each(data, function(i, item){
                if (item.status == 'Playing') {
                    icon = 'stop';
                    $(".queue").append(
                        '<div class="record row ' + item.status + '">' +
                            '<p class="col-xs-8 col-xs-offset-1">' + item.fileName + '</p>' +
                            '<button class="btn btn-default col-xs-2 play-song"><i class="fa fa-' + icon + '" aria-hidden="true"></i></button>' +
                        '</div>'
                    );
                } else {
                    icon = 'play';
                    $(".queue").append(
                        '<div class="record row ' + item.status + '">' +
                            '<button class="btn btn-danger col-xs-1 col-xs-offset-1 btn-outline delete-track"><i class="fa fa-times" aria-hidden="true"></i></button>' +
                            '<p class="col-xs-7">' + item.fileName + '</p>' +
                            '<button class="btn btn-default col-xs-2 play-song"><i class="fa fa-' + icon + '" aria-hidden="true"></i></button>' +
                        '</div>'
                    );
                }
            });
        }
    });
};

//noinspection JSJQueryEfficiency
$('body').on("click", ".delete-track",function (){
    var fileName = $(this).next().text();
    var $button = $(this).append(spinner);
    $(this).find('i').remove();
    $(this).attr('disabled', 'disabled');
    $.ajax({
        url: "/api/removeFile",
        data: {
            fileName: fileName
        },
        type: "GET",
        success: function(data) {
            $button.empty();
            $(this).removeAttr('disabled');
            messageUpdate(data);
        }
    });
});

//noinspection JSJQueryEfficiency
$('body').on("click", ".play-song",function (){
    var fileName = $(this).prev().text();
    var $button = $(this).append(spinner);
    $(this).find('i').remove();
    $(this).attr('disabled', 'disabled');
    $.ajax({
        url: "/api/playFile",
        data: {
            fileName: fileName
        },
        type: "GET",
        success: function(data) {
            $button.empty();
            $(this).removeAttr('disabled');
            messageUpdate(data);
        }
    });
});

$('#clear-queue').click(function () {
    $(this).attr('disabled', 'disabled');
    $.ajax({
        url: "/api/clearQueue",
        type: "GET",
        success: function(data) {
            $(this).removeAttr('disabled');
            messageUpdate(data);
        }
    });
});

$('#close').click(function (){
    $messageContainer.hide();
});

$('#more').click(function (){
    open = !open;

    if(open) {
        $messageContent.css('white-space', 'normal');
        $(this).text('Show Less');
    } else {
        $messageContent.css('white-space', 'nowrap');
        $(this).text('Show More');
    }
});

$('#volume').change(function() {
    $.ajax({
        url: "/api/changeVolume",
        data: {
          volume: $('#volume').val()
        },
        type: "POST",
        success: function() {
            console.log('Changed successfully!');
        }
    });
});

$('#request-add').click(function (){
    $(this).text('');
    var $button = $(this).append(spinner);
    $(this).attr('disabled', true);
    $.ajax({
        url: "/api/addRequest",
        type: "POST",
        data: {
            requestedURL: $('#request-url').val()
        },
        success: function(data) {
            $button.empty();
            $button.text('Add Response');
            $('#request-add').removeAttr('disabled');
            $('#request-url').val('');
            messageUpdate(data);
        }
    });
});

/**
 * Gives back the output from the API in a message on the front-end.
 * @param data
 */
function messageUpdate(data) {
    $messageIcon.removeClass('fa-exclamation-circle', 'fa-a-exclamation-triangle', 'fa-check');
    if (data['type'] == 'Success') {
        $messageIcon.addClass('fa-check');
    }
    if (data['type'] == 'Warning') {
        $messageIcon.addClass('fa-exclamation-triangle');
    }
    if (data['type'] == 'Error') {
        $messageIcon.addClass('fa-exclamation-circle');
    }
    $messageContainer.show();
    $messageType.text(data['type']);
    $messageContent.text(data['content']);
    refresh();
}

refresh();

setInterval(refresh, 60 * 20);