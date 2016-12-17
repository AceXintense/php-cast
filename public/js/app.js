var open = false;
var $messageContainer = $('.message');
var $messageType = $('#type');
var $messageContent = $('#content');

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
            $(".queue").empty();
            $.each(data, function(i, item){
                $(".queue").append(
                    '<div class="record row ' + item.status + '">' +
                        '<p class="col-xs-9 col-xs-offset-1">' + item.fileName + '</p>' +
                        '<button class="btn btn-default col-xs-2" id="play-song"><i class="fa fa-play" aria-hidden="true"></i></button>' +
                    '</div>'
                );
            });
        }
    });
};

$('body').on("click", "#play-song",function (){
    var fileName = $(this).prev().text();
    $.ajax({
        url: "/api/playFile",
        data: {
            fileName: fileName
        },
        type: "GET",
        success: function(data) {
            messageUpdate(data);
        }
    });
});

$('#clear-queue').click(function () {
    $.ajax({
        url: "/api/clearQueue",
        type: "GET",
        success: function(data) {
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

$('#request-add').click(function (){
    $.ajax({
        url: "/api/addRequest",
        type: "POST",
        data: {
            requestedURL: $('#request-url').val()
        },
        success: function(data) {
            messageUpdate(data);
        }
    });
});

/**
 * Gives back the output from the API in a message on the front-end.
 * @param data
 */
function messageUpdate(data) {
    $messageContainer.show();
    $messageType.text(data['type']);
    $messageContent.text(data['content']);
    refresh();
}

refresh();

setInterval(refresh, 60 * 40);