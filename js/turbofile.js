"use strict";


var app = angular.module('TurboFileApp', ['ngAnimate','ngSanitize']);

app.controller('TurboFileCtrl', function ($scope, ajax, $location, $timeout, $window) {
	ajax('test', {num: 123}, function(data){
		$scope.data = data;
	});

	// Hax to trigger digest on resize, so the panels get
	// rearranged properly.
	var w = angular.element($window);
	w.bind('resize', function() {
		$scope.$apply();
	})

	$scope.panels = [];

	//Hax for avoiding initial loading animation
	$scope.animate = false;

	//Hax for avoiding races
	var updateNum = 1;
	function loadLocation() {
		var path = $location.path().split('/');
		path.pop();
		if($location.hash())
			path.push($location.hash());
		var pref = 0;
		while(pref < path.length && pref < $scope.panels.length
			&& $scope.panels[pref].name == path[pref])
			pref++;

		var subpaths = [];
		for(var i = pref+1; i <= path.length; i++) {
			var subpath = path.slice(0, i);
			subpath = subpath.join('/');
			subpaths.push(subpath);
		}

		if(subpaths.length == 0) {
			$scope.panels = $scope.panels.slice(0, pref);
		}
		else {
			var thisUpdateNum = ++updateNum;
			ajax('list', {paths: subpaths}, function(data) {
				// Avoid animation when switching between files in
				// the same directory, animations quickly get annoying.

				if(false && subpaths.length == 1
					&& pref == $scope.panels.length-1
					&& $scope.panels[$scope.panels.length-1].type == 'file'
					&& $location.hash()) {
					angular.copy(data[0], $scope.panels[$scope.panels.length-1]);
					return;
				}

				$scope.panels = $scope.panels.slice(0, pref);

				if(thisUpdateNum != updateNum) return;
				angular.forEach(data, function(dir) {
					$scope.panels.push(dir);
				});
				$timeout(function() {
					$scope.animate=true;
				}, 20);
			});
		}
	};

	$scope.calcLeft = function(i) {
		var width = $scope.panels.length*200;
		if($scope.panels.length != 0 &&
			$scope.panels[$scope.panels.length-1].type == 'file')
			width += 600;

		var win = $window.innerWidth;
		var disp = win-width;

		var pos = i*200;
		if(disp > 0) return pos;

		var compress = Math.min(win/2, width-win);
		var x = width-win+compress;
		var y = compress;

		var a = (x-2*y)/(x*x*x);
		var b = -(x-3*y)/(x*x);
		if(pos < x)
			return a*pos*pos*pos+b*pos*pos;
		else
			return pos-x+y;
	};

	//loadLocation();
	$scope.$on('$locationChangeSuccess', loadLocation);

	$scope.$location = {};
	angular.forEach("protocol host port path search hash".split(" "), function(method){
		$scope.$location[method] = function(){
			var result = $location[method].call($location);
			return angular.isObject(result) ? angular.toJson(result) : result;
		};
	});

});

app.config(function($locationProvider) {
  $locationProvider.html5Mode(true).hashPrefix('!');
});

app.factory('ajax', function($http) {
	return function (path, param, callback, error) {
		$http({
			method: 'POST',
			url: '/_turbofile_api/'+path,
			data: angular.toJson(param),
			transformResponse: []
		})
		.success(function(data, status, headers, config) {
			callback(angular.fromJson(data));
		})
		.error(function(data, status, headers, config) {
			if(error)
				error(data);
			else
				alert(data);
		});
	};
});

app.directive('reload', function($location) {
	var link = function(scope, element, attrs) {
		element.bind('click', function(event) {
			if(scope.reload()) {
				window.location = attrs.href;
				event.preventDefault();
			}
		});
	}

	return {
		link: link,
		restrict: 'A',
		scope: {
			reload: "&reload"
		}
	}
});