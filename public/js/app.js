var open = false;
var $messageContainer = $('.message');
var $messageType = $('#type');
var $messageContent = $('#content');
var $messageIcon = $('.icon-message');

var playThough = false;
var playThoughDirection = 'down';
var shuffle = false;
var run = false;
var difference = false;

var previousValues = {
    'volume': 0,
    'queue': [],
    'playing': ''
};

var spinner =
    '<div class="spinner">' +
        '<div class="double-bounce1"></div>' +
        '<div class="double-bounce2"></div>' +
    '</div>';

function getPlaying() {
    $.ajax({
        url: "/api/getPlaying",
        type: "GET",
        success: function(data) {
            previousValues.playing = data;
            $('#playing').text(data);
        }
    });
}

function isQueueDifferent() {
    $.ajax({
        url: "/api/isQueueDifferent",
        type: "GET",
        data: {
            'queue': previousValues.queue
        },
        success: function (data) {
            if (data['boolean'] == 'true') {
                difference = true;
            } else {
                difference = false;
            }
        }
    });
}

/*
 * All the Get API calls.
 */
function getAPICalls() {
    getPlaying();
    getShuffle();
    getPlayThrough();
    getPlayThroughDirection();
    getVolume();
}

var refresh = function () {

    getAPICalls();

    if (run) {
        isQueueDifferent();
    }

    if (difference || !run) {
        run = true;
        $.ajax({
            url: "/api/getRequestedURLs",
            type: "GET",
            success: function (data) {
                previousValues.queue = data;
                $(".queue").empty();
                $.each(data, function (i, item) {
                    if (item.status == 'Playing' || item.status == 'Paused') {
                        $(".queue").append(
                            '<div class="record row ' + item.status + '">' +
                            '<div class="col-xs-1"></div>' +
                            '<p class="col-xs-8">' + item.fileName + '</p>' +
                            '<button class="btn btn-default col-xs-2 stop-file"><i class="fa fa-stop" aria-hidden="true"></i></button>' +
                            '</div>'
                        );
                    } else {
                        $(".queue").append(
                            '<div class="record row ' + item.status + '">' +
                            '<button class="btn btn-danger col-xs-2 col-xs-offset-1 btn-outline delete-track"><i class="fa fa-times" aria-hidden="true"></i></button>' +
                            '<p class="col-xs-6">' + item.fileName + '</p>' +
                            '<button class="btn btn-default col-xs-2 play-file"><i class="fa fa-play" aria-hidden="true"></i></button>' +
                            '</div>'
                        );
                    }
                });
            }
        });
    }


    enabledButtons();

    isPaused();

};

refresh();

function enabledButtons() {
    if (shuffle) {
        $('#play-through').attr('disabled', 'disabled');
        $('#play-direction').hide();
    } else if (playThough) {
        $('#shuffle').attr('disabled', 'disabled');
        $('#play-direction').show();
    } else {
        $('#play-direction').hide();
        $('#play-through').removeAttr('disabled');
        $('#shuffle').removeAttr('disabled');
    }
}

function isPaused() {
    $.ajax({
        url: "/api/isPaused",
        type: "GET",
        success: function (data) {
            if (data == 'false') {
                $('#pause').removeClass('btn-danger').addClass('btn-default');
                $('#previous').removeAttr('disabled');
                $('#next').removeAttr('disabled');
                return false;
            } else {
                $('#pause').removeClass('btn-default').addClass('btn-danger');
                $('#previous').attr('disabled', 'disabled');
                $('#next').attr('disabled', 'disabled');
                return true;
            }
        }
    });
}

$('#previous').click(function () {
    $.ajax({
        url: "/api/skipToPrevious",
        type: "PUT",
        success: function () {
            refresh();
        }
    });
});

$('#pause').click(function () {
    $.ajax({
        url: "/api/setPaused",
        data: {
          fileName: previousValues.playing
        },
        type: "POST",
        success: function () {
            isPaused();
        }
    });
});

$('#next').click(function () {
    $.ajax({
        url: "/api/skipToNext",
        type: "PUT",
        success: function () {
            refresh()
        }
    });
});

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
$('body').on("click", ".play-file",function (){
    var fileName = $(this).prev().text();
    $(this).append(spinner);
    $(this).find('i').remove();
    $(this).attr('disabled', 'disabled');
    $.ajax({
        url: "/api/playFile",
        data: {
            fileName: fileName
        },
        type: "POST"
    });
});

//noinspection JSJQueryEfficiency
$('body').on("click", ".stop-file",function (){
    var fileName = $(this).prev().text();
    var $button = $(this).append(spinner);
    $(this).find('i').remove();
    $(this).attr('disabled', 'disabled');
    $.ajax({
        url: "/api/stopFile",
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
        data: {
            fileName: $('#getPlaying').text()
        },
        type: "GET",
        success: function(data) {
            $(this).removeAttr('disabled');
            $('#getPlaying').text(' Nothing is getPlaying.. ');
            messageUpdate(data);
        }
    });
});

$('#close').click(function (){
    $messageContainer.removeClass('animated slideInLeft');
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
        url: "/api/setVolume",
        data: {
          volume: $('#volume').val()
        },
        type: "POST",
        success: function() {
            $('#volume-text').text($('#volume').val() + '%');
            getShuffle();
        }
    });
});

/**
 * Gets the volume on the server and applies it to the #volume element.
 */
function getVolume() {
    $.ajax({
        url: "/api/getVolume",
        type: "GET",
        success: function(data) {
            var newVolume = parseInt(data);
            if (newVolume != previousValues.volume) {
                $('#volume').val(newVolume);
                $('#volume-text').text(newVolume + '%');
            }
            previousValues.volume = parseInt(data);
        }
    });
}

function toggleDirection(data) {
    if (data == 'up') {
        $('#play-direction i').removeClass('fa-long-arrow-down');
        $('#play-direction i').addClass('fa-long-arrow-up');
        playThoughDirection = 'down';
    } else {
        $('#play-direction i').addClass('fa-long-arrow-down');
        $('#play-direction i').removeClass('fa-long-arrow-up');
        playThoughDirection = 'up';
    }
}

function getPlayThroughDirection() {
    $.ajax({
        url: "/api/getPlayThroughDirection",
        type: "GET",
        success: function(data) {
            toggleDirection(data);
        }
    });
}

function getPlayThrough() {
    $.ajax({
        url: "/api/getPlayThrough",
        type: "GET",
        success: function(data) {
            if (data == 'true') {
                $('#play-through').removeClass('btn-danger').addClass('btn-success');
                playThough = true;
            } else {
                $('#play-through').removeClass('btn-success').addClass('btn-danger');
                playThough = false;
            }
        }
    });
}

function getShuffle() {
    $.ajax({
        url: "/api/getShuffle",
        type: "GET",
        success: function(data) {
            if (data == 'true') {
                $('#shuffle').removeClass('btn-danger').addClass('btn-success');
                if (!isPaused()) {
                    $('#previous').attr('disabled', 'disabled');
                    $('#next').attr('disabled', 'disabled');
                }
                shuffle = true;
            } else {
                $('#shuffle').removeClass('btn-success').addClass('btn-danger');
                if (!isPaused()) {
                    $('#previous').removeAttr('disabled');
                    $('#next').removeAttr('disabled');
                }
                shuffle = false;
            }
        }
    });
}

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

$('#shuffle').click(function () {
    $.ajax({
        url: "/api/toggleShuffle",
        type: "POST",
        success: function() {
            shuffle = !shuffle;
            enabledButtons();
        }
    });
});

$('#play-through').click(function () {
    $.ajax({
        url: "/api/togglePlayThrough",
        type: "POST",
        success: function() {
            playThough = !playThough;
            enabledButtons();
        }
    });
});

$('#play-direction').click(function () {
    $.ajax({
        url: "/api/togglePlayThroughDirection",
        type: "POST",
        success: function () {
            getPlayThroughDirection();
            toggleDirection(playThoughDirection);
        }
    });
});

$('#reset').click(function () {
    $.ajax({
        url: "/api/resetEnvironment",
        type: "GET",
        success: function() {
            refresh();
        }

    });
});

/**
 * Gives back the output from the API in a message on the front-end.
 * @param data
 */
function messageUpdate(data) {
    $messageIcon.removeClass('fa-exclamation-circle');
    $messageIcon.removeClass('fa-a-exclamation-triangle');
    $messageIcon.removeClass('fa-check');
    if (data['type'] == 'Success') {
        $messageIcon.addClass('fa-check');
    }
    if (data['type'] == 'Warning') {
        $messageIcon.addClass('fa-exclamation-triangle');
    }
    if (data['type'] == 'Error') {
        $messageIcon.addClass('fa-exclamation-circle');
    }
    $messageContainer.addClass('animated slideInLeft');
    $messageContainer.show();
    $messageType.text(data['type']);
    $messageContent.text(data['content']);
    refresh();
}

setInterval(refresh, 2000);