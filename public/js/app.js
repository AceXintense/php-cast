angular.module('PHPCast', ['ngMaterial', 'ngMessages', 'chart.js'])
    .config(function($mdThemingProvider) {
        $mdThemingProvider.theme('default')
            .primaryPalette('green')
            .accentPalette('grey', {'default': '50'})
            .warnPalette('red', {'default': '700'});

        $mdThemingProvider.theme('playlist')
            .primaryPalette('grey', {'default': '50'})
            .accentPalette('green', {'default': '500'})
            .warnPalette('red', {'default': '700'})
    })
    .config(function (ChartJsProvider) {
        ChartJsProvider.setOptions({
            scales: {
                xAxes: [{
                    display: true,
                    scaleLabel: {
                        display: true,
                        labelString: 'Songs'
                    },
                    barPercentage: .9
                }],
                yAxes: [{
                    display: true,
                    ticks: {
                        beginAtZero: true,
                        steps: 1,
                        stepValue: 1
                    }
                }]
            },
        });
    })
    .controller('PHPCastController', ['$scope', '$http', function ($scope, $http) {

        $scope.mode = 'shuffle';
        $scope.volume = 0;
        $scope.errors = [];
        $scope.songPaused = 'false';
        $scope.playThroughDirection = 'down';
        $scope.currentlyPlaying = '';
        $scope.queueItems = [];

        $scope.colors = ['#45b7cd', '#ff6384', '#ff8e72'];

        $scope.chartLabels = [];
        $scope.chartPlayData = [];
        $scope.datasetOverride = [
            {
                label: "",
                borderWidth: 1,
                type: 'bar',
                scaleBeginAtZero: true
            },
            {
                label: "Line chart",
                borderWidth: 3,
                hoverBackgroundColor: "rgba(255,99,132,0.4)",
                hoverBorderColor: "rgba(255,99,132,1)",
                type: 'line'
            }
        ];

        $scope.closeError = function (id) {
            $scope.errors.splice(id, 1);
        };

        $scope.closeAllErrors = function () {
            $scope.errors = [];
        };

        $scope.toggleModes = function () {
            $http({
                method: 'POST',
                url: '/api/toggleShuffle',
                data: {
                }
            }).then(
                function successCallback(response) {
                    $scope.getShuffleMode();
                },
                function errorCallback(response) {
                    console.log(response);
                }
            )
        };

        $scope.getChartData = function () {
            $http({
                method: 'GET',
                url: '/api/getChartData'
            }).then(
                function successCallback(response) {
                    console.log(response.data.plays);
                    $scope.chartLabels = response.data.songs;
                    $scope.chartPlayData = response.data.plays;
                },
                function errorCallback(response) {
                    console.log(response);
                }
            )
        };

        $scope.toggleMute = function () {
            if ($scope.volume == 0) {
                $scope.volume = 100;
            } else {
                $scope.volume = 0;
            }
        };

        $scope.$watch('volume', function () {
            $http({
                method: 'POST',
                url: '/api/setVolume',
                data: {
                    volume: $scope.volume
                }
            }).then(
                function successCallback(response) {
                    //Success!
                },
                function errorCallback(response) {
                    console.log(response);
                }
            )
        });

        $scope.toggleSettings = function () {
            $scope.settings = !$scope.settings;
        };

        $scope.getShuffleMode = function () {
            $http({
                method: 'GET',
                url: '/api/getShuffle'
            }).then(
                function successCallback(response) {
                    if (response.data == 'false') {
                        $scope.mode = 'through';
                    } else {
                        $scope.mode = 'shuffle';
                    }
                },
                function errorCallback(response) {
                    console.log(response);
                }
            )
        };

        $scope.getVolume = function () {
            $http({
                method: 'GET',
                url: '/api/getVolume'
            }).then(
                function successCallback(response) {
                    if ($scope.volume != response.data) {
                        $scope.volume = parseInt(response.data);
                    }
                },
                function errorCallback(response) {
                    console.log(response);
                }
            )
        };

        $scope.togglePlayThroughDirection = function () {
            $http({
                method: 'POST',
                url: '/api/togglePlayThroughDirection'
            }).then(
                function successCallback(response) {
                    $scope.getPlayThroughDirection();
                },
                function errorCallback(response) {
                    console.log(response);
                }
            )
        };

        $scope.getPlayThroughDirection = function () {
            $http({
                method: 'GET',
                url: '/api/getPlayThroughDirection'
            }).then(
                function successCallback(response) {
                    $scope.playThroughDirection = response.data;
                },
                function errorCallback(response) {
                    console.log(response);
                }
            )
        };

        $scope.clearQueue = function () {
            $http({
                method: 'POST',
                url: '/api/clearQueue',
                data: {
                    fileName: $scope.currentlyPlaying + '.mp3'
                }
            }).then(
                function successCallback(response) {
                    if (response.data.type == 'Warning') {
                        $scope.errors.push({
                            title: 'Warning!',
                            description: response.data.content
                        });
                    }
                },
                function errorCallback(response) {
                    console.log(response);
                }
            )
        };


        $scope.stopPlaying = function (fileName) {
            $http({
                method: 'POST',
                url: '/api/stopFile',
                data: {
                    fileName: fileName
                }
            }).then(
                function successCallback(response) {
                    //Success!
                },
                function errorCallback(response) {
                    console.log(response);
                }
            )
        };

        $scope.isPaused = function () {
            $http({
                method: 'GET',
                url: '/api/isPaused'
            }).then(
                function successCallback(response) {
                    $scope.songPaused = response.data;
                },
                function errorCallback(response) {
                    console.log(response);
                }
            )
        };

        $scope.skipToPrevious = function () {
            $http({
                method: 'PUT',
                url: '/api/skipToPrevious'
            }).then(
                function successCallback() {
                    $scope.refresh();
                },
                function errorCallback(response) {
                    console.log(response);
                }
            )
        };

        $scope.skipToNext = function () {
            $http({
                method: 'PUT',
                url: '/api/skipToNext'
            }).then(
                function successCallback() {
                    $scope.refresh();
                },
                function errorCallback(response) {
                    console.log(response);
                }
            )
        };

        $scope.addRequest = function () {
            $scope.requesting = true;
            $http({
                method: 'POST',
                url: '/api/addRequest',
                data: {
                    requestedURL: $scope.requestedURL
                }
            }).then(
                function successCallback(response) {
                    $scope.requesting = false;
                    $scope.requestedURL = '';
                    $scope.getQueue();
                    if (response.data.type == 'Error') {
                        $scope.errors.push({
                            title: 'Error!',
                            description: response.data.content
                        });
                    }
                },
                function errorCallback(response) {
                    $scope.requesting = false;
                }
            )
        };

        $scope.removeSong = function (fileName) {
            $http({
                method: 'POST',
                url: '/api/removeFile',
                data: {
                    fileName: fileName
                }
            }).then(
                function successCallback(response) {
                },
                function errorCallback(response) {
                    console.log(response);
                }
            )
        };

        $scope.pauseSong = function () {
            $http({
                method: 'POST',
                url: '/api/setPaused',
                data: {
                    fileName: $scope.currentlyPlaying
                }
            }).then(
                function successCallback(response) {
                    $scope.isPaused();
                },
                function errorCallback(response) {
                    console.log(response);
                }
            );
        };

        $scope.isQueueDifferent = function () {
            $http({
                method: 'POST',
                url: '/api/isQueueDifferent',
                data: {
                    queue: $scope.queueItems
                }
            }).then(
                function successCallback(response) {
                    if (response.data.boolean == 'true') {
                        $scope.getQueue();
                    }
                },
                function errorCallback(response) {
                    console.log(response);
                }
            );
        };

        $scope.getQueue = function () {
            $http({
                method: 'GET',
                url: '/api/getRequestedURLs'
            }).then(
                function successCallback(response) {
                    $scope.queueItems = response.data;
                },
                function errorCallback(response) {
                    console.log(response);
                }
            );
        };

        $scope.getPlaying = function () {
            $http({
                method: 'GET',
                url: '/api/getPlaying'
            }).then(
                function successCallback(response) {
                    $scope.currentlyPlaying = response.data;
                },
                function errorCallback(response) {
                    console.log(response);
                }
            );
        };

        $scope.playSong = function (fileName) {
            $http({
                method: 'POST',
                url: '/api/playFile',
                data: {
                    fileName: fileName
                }
            }).then(
                function successCallback(response) {
                    $scope.refresh();
                },
                function errorCallback(response) {
                    console.log(response);
                }
            );
        };

        $scope.getQueue();

        $scope.refresh = function () {
            $scope.isPaused();
            $scope.getVolume();
            $scope.isQueueDifferent();
            $scope.getShuffleMode();
            $scope.getPlayThroughDirection();
            $scope.getPlaying();
            $scope.getChartData();
        };

        setInterval(function(){
            $scope.refresh();
        }, 1000);
        $scope.refresh();

    }]);

    angular.element(function() {
        angular.bootstrap(document, ['PHPCast']);
    });