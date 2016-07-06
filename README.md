# Push-Video-in-Angular
Developing Push Videos in Angular. Demo:
### DEMO
[Push Videos Demo](http://mildfun.com/videos/#/)


## THE APP.JS
```javascript
//Define an angular module for our app
var app = angular.module('vTCApp', ['ngRoute', 'ngYoutubeEmbed',
    'angularUtils.directives.dirDisqus'
]);
// configure our routes
app.config(function($routeProvider) {
    $routeProvider
    // route for the home page
        .when('/', {
            templateUrl: 'templates/tv.html',
            controller: 'vTCController'
        })
        // route for the about page
        .when('/about', {
            templateUrl: 'templates/tv.html',
            controller: 'aboutController'
        })
        // route for the contact page
        .when('/contact', {
            templateUrl: 'templates/tv.html',
            controller: 'contactController'
                // route for single page
        }).when('/media/:param', {
            templateUrl: 'templates/single.html',
            controller: 'vTCController1'
                // route  for any other page
        }).otherwise({
            controller: '404',
            templateUrl: 'templates/tv.html'
        });
});
app.controller('404', function($scope) {
    $scope.message = 'Page Doesnt Exist';
});
app.controller('aboutController', function($scope) {
    $scope.message = 'This is for the aboutController route.';
});
app.controller('contactController', function($scope) {
    $scope.message = 'This is for the contactController route.';
});
app.controller('vTCController', function($scope, $http, $window, $timeout) {
	
    //SETUP vtCController's Variables
    $scope.result = [];
    $scope.superMap = [];
    $scope.playlistArray = [];
	
    // DEBUG MODE
    // getStoredData retrieves the latest saved playlists and songs.
    // getStoredData();
    // getMyData loads the lasted playlists and songs.
    // getMyData();
    // Uncoment either getStoredData(); or getMyData();
	
    function getMyData() {
        var filteredResult = [];
        console.log(" --- GETTING ITEM ---- ");
        getItem();
        console.log(" --- STARTING TO GET PLAYLISTS ---- ");
        getAllPlaylists('');
        return filteredResult;
    };
	
    // DEPRECIATED FUNCTION
    // getItem Gets all of the playlists and songs from a mySQL query
    function getItem() {
        $http.post("http://localhost/TV/ajax/getItem.php").success(
            function(data) {
                $scope.items = data;
                $scope.linkVariable = $scope.items[0].ITEM;
                $scope.link = $scope.linkVariable;
            });
    };
	
    // getStoredData retrieves the latest saved playlists and songs.
    function getStoredData() {
        $http.post("/tv/app/data.json").success(function(data) {
            // retrieve objects from json file and set variables to be used in the template.
            $scope.results = data[1];
            $scope.result = data[2];
            $scope.link = data[2][17][0].id;
            $scope.title = data[2][17][0].title;
            $scope.linkVariable3 = data[2][17][0].description;
            getSongStats("" + $scope.linkVariable + "");
            // set up disqus account
            $scope.valUse = (
                "https://www.youtube.com/watch?v=" +
                $scope.linkVariable);
            $scope.disqusConfig = {
                disqus_shortname: 'smwarrenvideo',
                disqus_identifier: $scope.linkVariable,
                disqus_url: $scope.valUse
            };
        })
    };
    
	//getAllPlaylists of the channel UCx_AyGx0afkAzDdd7_YKZKw
    function getAllPlaylists(paging) {
        console.log("$scope.playlistArray size:" + $scope.playlistArray
            .length);
        console.log(" --- Paging.." + paging);
        $http.get(
            "https://www.googleapis.com/youtube/v3/playlists?part=snippet&channelId=UCx_AyGx0afkAzDdd7_YKZKw&key=AIzaSyCtu_Sqxtr70F5HzAjFFJAzctcAQDl64aQ&pageToken=" +
            paging).success(function(data) {
            var maxNum = data.pageInfo;
            var next = data.nextPageToken;
            //var total = data.resultsPerPage;
            //{totalResults: 26, resultsPerPage: 5} console.log(data);
            data.items.forEach(function(item) {
                //Build Playlist Array!
                $scope.playlistArray.push({
                    channelId: item.id,
                    playlist: item.snippet.title
                });
            });
            //Page if necessary
            console.log(" ------- next: ", next);
            if (next) {
                paging = next;
                console.log(" ------- PAGING AGAIN!");
                getAllPlaylists(paging);
            } else {
                console.log(
                    " --- Done paging playlists!.. $scope.playlistArray size:" +
                    $scope.playlistArray.length);
                postPaging();
            }
        });
        //TODO: !!!
        //        .error(function(err){
        //        	???
        //        })
    };

    function postPaging() {
        console.log(
            " --- Performing magic on $scope.superMap (cleanup 1).."
        );
        //console.log("hey"+JSON.stringify(storedData));
        for (var i = 0; i < $scope.playlistArray.length; i++) {
            for (var j = 0; j < Object.keys($scope.playlistArray[0])
                .length + 1; j++) {
                $scope.superMap[j] = $scope.superMap[j] || new Array();
                // console.log('result[' + j + '][' + i + ']' + ' = ' +
                //  storedData[i][Object.keys(storedData[i])[j]])
                $scope.superMap[j][i] = $scope.playlistArray[i][
                    Object.keys($scope.playlistArray[i])[j]
                ];
            }
        }
        console.log(" --- Retrieving songs!...");
        getAllSongs(0);
    }

    function getAllSongs(playlistNumber) {
        // getAllSongs retrieves all songs in from a playlist	
        $scope.message = (" --- Loading playlistNumber: " +
            playlistNumber + " of " + ($scope.playlistArray.length -
                1) + " ---");
        if (playlistNumber < $scope.playlistArray.length) {
            console.log(" --- REQUEST #" + (playlistNumber + 1));
            $http.get(
                    "https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&maxResults=50&playlistId=" +
                    $scope.superMap[0][playlistNumber] +
                    "&key=AIzaSyCtu_Sqxtr70F5HzAjFFJAzctcAQDl64aQ")
                .success(function(data) {
                    console.log(" --- REQUEST #" + (
                            playlistNumber + 1) +
                        " SUCCESS!");
                    var holder = new Array();
                    data.items.forEach(function(item) {
                        holder.push({
                            id: item.snippet.resourceId
                                .videoId,
                            title: item.snippet
                                .title,
                            description: item.snippet
                                .description
                        });
                    });
                    $scope.superMap[2][playlistNumber] = holder;
                    playlistNumber++;
                    if (playlistNumber < $scope.playlistArray.length) {
                        getAllSongs(playlistNumber);
                    } else {
                        console.log(
                            " --- Done retrieving songs!");
                        cleanUp();
                    }
                });
        } else {
            console.log(" --- THIS SHOULDNT BE CALLED?");
        }
    }

    function cleanUp() {
        console.log(" --- Filtering out (too) small lists...");
        for (var i = 0; i < $scope.superMap[2].length; i++) {
            (function(i) {
                // data[2][i].push(data[1][i]);
                var size = $scope.superMap[2][i].length;
                if (size < 5) {
                    $scope.superMap[2][i] = [];
                }
            })(i);
        }
        console.log(" --- Filtering out Private/Deleted...");
        for (var i = 0; i < $scope.superMap[2].length; i++) {
            (function(i) {
                // data[2][i].push(data[1][i]);
                $scope.superMap[2][i].forEach(function(item,
                    index) {
                    var private = item.title;
                    //console.log(private);
                    if (private == "Private video" ||
                        private == "Deleted video") {
                        $scope.superMap[2][i].splice(
                            index, 1);
                        //console.log(item);
                    }
                })
            })(i);
        }
        console.log(" --- Setting result and results!");
        if ($scope.superMap !== undefined && $scope.superMap.length >
            0) {
            $scope.results = $scope.superMap[1];
            $scope.result = $scope.superMap[2];
        }
        console.log(" --- Done!");
        console.log(JSON.stringify($scope.superMap));
    }
    $scope.deleteItem = function(item) {
        if (confirm("Are you sure to delete this item?")) {
            $http.post(
                "http://localhost/TV/ajax/deleteItem.php?itemID=" +
                item).success(function(data) {
                getItem();
            });
        }
    };
    $scope.playItem = function(item, item2, item3) {
        $scope.title = item2;
        $scope.link = item;
        $scope.linkVariable3 = item3;
        getSongStats("" + item + "");
        $scope.valUse = ("https://www.youtube.com/watch?v=" + item);
        $scope.disqusConfig = {
            disqus_shortname: 'smwarrenvideo',
            disqus_identifier: item,
            disqus_url: $scope.valUse
        };
        scroll(0, 0);
        console.log($scope.link);
    };
}).directive("owlCarousel", function() {
    return {
        restrict: 'E',
        transclude: false,
        link: function(scope) {
            scope.initCarousel = function(element) {
                // provide any default options you want
                var defaultOptions = {};
                var customOptions = scope.$eval($(element).attr(
                    'data-options'));
                // combine the two options objects
                for (var key in customOptions) {
                    defaultOptions[key] = customOptions[key];
                }
                // init carousel
                $(element).owlCarousel(defaultOptions);
            };
        }
    };
}).directive('owlCarouselItem', [

    function() {
        return {
            restrict: 'A',
            transclude: false,
            link: function(scope, element) {
                // wait for the last item in the ng-repeat then call init
                if (scope.$last) {
                    //console.log(scope.$last);
                    scope.initCarousel(element.parent());
                }
            }
        };
    }
]);
app.controller('vTCController1', function($scope, $http, $routeParams) {
    function getAllVideos() {
        $http.post(
            "http://localhost/TV/ajax/getSingleItem.php?itemID=" +
            item).success(function(data) {
            $scope.items = data;
            $scope.linkVariable = $scope.items[0].ITEM;
            $scope.link = $scope.linkVariable;
        });
    };
});
/* app.controller('vTCController12', function($scope, $http, $routeParams) {
    item = $routeParams.param;
    getSingleItem();
    function getSingleItem() {
        $http.post(
            "http://localhost/TV/ajax/getSingleItem.php?itemID=" +
            item).success(function(data) {
            $scope.items = data;
            $scope.linkVariable = $scope.items[0].ITEM;
            $scope.link = $scope.linkVariable;
        });
        $scope.valUse = ("https://www.youtube.com/watch?v=" +
            $scope.linkVariable);
        $scope.disqusConfig = {
            disqus_shortname: "smwarrenvideo",
            disqus_identifier: $scope.linkVariable,
            disqus_url: $scope.valUse
        };
    };
});
```
