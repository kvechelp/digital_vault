<!DOCTYPE html>
<html lang="en" data-ng-app="app">
<head>
	<meta charset="UTF-8">
	<title></title>
	<link rel="stylesheet" href="assets/css/bootstrap.min.css" crossorigin="anonymous">
	<link rel="stylesheet" href="assets/css/bootstrap-theme.min.css">
	<link href='https://fonts.googleapis.com/css?family=Roboto:300,400,300italic' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
	<style>
		body{font-family: 'Roboto', sans-serif; font-size: 16px; color: #777; font-weight: 100;}
		body:after {
			content : "";
			display: block;
			position: absolute;
			top: 0;
			left: 0;
			background-image: url(http://cdn2.hubspot.net/hub/171972/file-2654595575-jpg/blog-files/vault.jpg);
			background-size: 100% 100%;
			width: 100%;
			height: 100%;
			opacity : 0.4;
			z-index: -1;
		}
		#page{margin-top: 60px; box-shadow: 0 0 1px 1px #333;}
		#header{padding: 20px; background-color: #252525; color: #999; padding-top: 44px;}
		#digicon{width: 60px; height: 60px; margin-top: -26px; margin-right: 36px;}
		#content{background-color: #eee; padding: 20px; min-height: 400px;}
		#page-title{font-weight: 300; color: #ccc;}
		#upload-label{}
		#upload-zone{
			background: #fff;
			cursor: pointer;
			padding: 20px;
			border: 2px dashed #888;
			margin-bottom: 30px;
		}
		#upload-preview{
			max-width: 100%;
			height: 80px;
		}
        #file-icon{font-size: 32px;}
		.btn{font-weight: 100; margin-top: 40px;}
		#spinner{font-size: 32px}
		#status-message-container{margin-top: 40px;}
	</style>
</head>
<body>

<div data-ng-controller="AppCtrl">
	<div class="container">
		<div class="row">
			<div class="col-xs-8 col-xs-offset-2">
				<div id="page">
					<div id="header" class="text-center">
						<img src="assets/digipolis_icon.png" alt="digipolis icon" id="digicon">
						<span id="page-title" class="h1">Digital Vault</span>
					</div>
					<div id="content">
						<form name="form">
							<div class="row">
								<div id="upload-label" class="col-xs-4 col-xs-offset-1">Select a file to upload:</div>
								<div id="upload-zone" class="text-center col-xs-6" data-ngf-select data-ng-model="file"
									 data-name="file" data-ngf-max-size="20MB">
									Click to select
								</div>
							</div>
							<div class="row" data-ng-if="file">
								<div class="col-xs-offset-5 col-xs-6">
                                    <div class="row">
                                        <div>
                                            <img id="upload-preview" data-ng-if="isImage(file)" src="" alt="{{file.$ngfName}}"
                                                 data-ngf-thumbnail="file"/>
                                        </div>
                                        <div class="text-success">
                                            <i id="file-icon" class="fa fa-file" data-ng-if="! isImage(file)"></i>
                                            <span data-ng-bind="file.name"></span>,
                                            <span data-ng-bind="file.size"></span> bytes
                                        </div>
                                    </div>
								</div>
							</div>
							<div class="row">
								<div class="col-xs-offset-5">
									<button data-ng-if="file" type="submit" class="btn btn-lg btn-primary" data-ng-disabled="currentEvent !== null" data-ng-click="submit()">
										process
									</button>
								</div>
							</div>

							<div id="status-message-container">
								<div data-ng-if="currentEvent" class="text-center">
									<i id="spinner" class="fa fa-spinner fa-spin"></i>
									<div data-ng-bind="currentEvent"></div>
								</div>
								<div data-ng-if="processResult" style="font-weight:bold;">
									<div data-ng-if="processResult.result">
										<div class="text-success">
											Notification successfully sent!
											<div>File name: <span data-ng-bind="processResult.data.file_name"></span></div>
											<div>Vault ID: <span data-ng-bind="processResult.data.vault_id"></span></div>
											<div>Download URI:
												<a data-ng-href="{{processResult.data.download_uri}}" target="_blank">
													<span data-ng-bind="processResult.data.download_uri"></span>
												</a>
											</div>
										</div>
									</div>
									<div data-ng-if="! processResult.result">
										<div class="text-danger" data-ng-bind="processResult.data.message"></div>
									</div>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>

</div>

<script src="assets/js/lib/angular.min.js"></script>
<script src="assets/js/lib/jquery-1.11.3.min.js"></script>
<script src="assets/js/lib/bootstrap.min.js"></script>
<script src="assets/js/lib/ng-file-upload-bower-12.0.4/ng-file-upload-shim.min.js"></script>
<script src="assets/js/lib/ng-file-upload-bower-12.0.4/ng-file-upload.min.js"></script>

<script>

	var app = angular.module('app', ['ngFileUpload']);

	app.controller('AppCtrl', function($scope, Upload, $timeout){
		var events = [
			'requesting vault upload',
			'uploading to vault',
			'requesting download ID',
			'requesting e-mail notification'
		];
		var currentEventSpeed;
		var slowEventSpeed = 1000;
		var fasterEventSpeed = 300;
		var processResult = false;

		$scope.currentEvent = null;
		$scope.processResult = null;
		$scope.processing = false;

		$scope.isImage = function isImage(file){
			return file.type && file.type.substr(0,6) === 'image/';
		};

		$scope.submit = function() {
			if ($scope.form.file.$valid && $scope.file) {
				$scope.upload($scope.file);
			}
		};

		$scope.upload = function (file) {
			if ($scope.processing) {
				return;
			}
			$scope.processing = true;
			$scope.processResult = false;
			processResult = false;
			currentEventSpeed = slowEventSpeed;
			showEvent(true);

			Upload.upload({
				url: '<?=$_SERVER['REQUEST_URI']?>upload.php',
				data: {file: file}
			}).then(function (resp) {
				$scope.processing = false;
				currentEventSpeed = fasterEventSpeed;
				processResult = resp.data.success ? {result: true, data: resp.data} : {result: false, data: {message: resp.data}};
			}, function (resp) {
				$scope.processing = false;
				processResult = {result: false, data: {message: 'Error on local machine'}};
			}, function (evt) {
				// var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
				// console.log('progress: ' + progressPercentage + '% ' + evt.config.data.file.name);
			});
		};

		function showEvent(start){
			if (events.indexOf($scope.currentEvent) === events.length-1) {
				$scope.currentEvent = null;
				if (processResult) {
					$scope.processResult = processResult;
				}
				return;
			}

			$scope.currentEvent = start ? events[0] : events[events.indexOf($scope.currentEvent) + 1];
			$timeout(function(){showEvent(false);}, currentEventSpeed);
		}

	});
</script>
</body>
</html>
