<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta charset="UTF-8">
	<title>PHPCast Frontend</title>

	<link rel="stylesheet" href="/bower_components/angular/angular-csp.css">
	<link rel="stylesheet" href="/bower_components/angular-material/angular-material.min.css">
	<link rel="stylesheet" href="/bower_components/material-design-icons/iconfont/material-icons.css">
	<link rel="stylesheet" href="/css/app.css">

	<script src="/bower_components/angular/angular.min.js"></script>
	<script src="/bower_components/angular-material/angular-material.min.js"></script>
	<script src="/bower_components/angular-animate/angular-animate.min.js"></script>
	<script src="/bower_components/angular-aria/angular-aria.min.js"></script>
	<script src="/bower_components/angular-messages/angular-messages.min.js"></script>

</head>

<body>

<div ng-controller="PHPCastController" ng-cloak >

	<div layout-sm="column" layout-gt-sm="row">
		<div flex="80" flex-offset="10" layout="row">
			<h1 id="title" flex>PHPCast</h1>

			<md-button class="md-accent settings" ng-click="toggleSettings()">
				<span>Settings</span>
				<i class="material-icons">settings</i>
			</md-button>
		</div>
	</div>

	<div layout-sm="column" layout-gt-sm="row" ng-show="settings">
		<div flex="80" flex-offset="10" layout="row">
			<md-card flex>
				<md-card-title>
					<md-card-title-text>
						<span class="md-headline">PHPCast Settings</span>
						<span class="md-subhead">Configure PHPCast.</span>
					</md-card-title-text>
				</md-card-title>
				<md-card-content>


					<md-button class="md-raised md-warn" ng-click="toggleSettings()">
						<span>Close</span>
						<i class="material-icons">close</i>
					</md-button>
				</md-card-content>
			</md-card>
		</div>
	</div>

	<div ng-show="!settings">
		<div layout="row" layout-xs="column">
			<div flex="80" flex-offset="10" layout-sm="column" layout-gt-sm="row" layout-align="space-between">
				<div flex-gt-sm="50">
					<md-card md-theme="default" md-colors="{background: 'default-warn'}" ng-repeat="(id, error) in errors">
						<md-card-title>
							<div layout="column" layout-gt-sm="row" flex="100">
								<i class="material-icons error-icon" flex-gt-sm="10">error</i>
								<md-card-title-text class="error-content" flex>
									<span class="md-headline">@{{ error.title }}</span>
									<span class="md-subhead">@{{ error.description }}</span>
								</md-card-title-text>
								<md-button flex-gt-sm="10" ng-click="closeError(id)">Close</md-button>
							</div>
						</md-card-title>
					</md-card>

					<md-button class="close-all-errors" ng-show="errors.length > 1" ng-click="closeAllErrors()">Close All</md-button>

					<md-card>
						<md-card-title>
							<md-card-title-text>
								<span class="md-headline">Request</span>
							</md-card-title-text>
						</md-card-title>
						<md-card-content>
							<md-input-container class="md-block" flex-gt-sm>
								<label>Request URL</label>
								<input ng-model="requestedURL">
								<md-progress-linear md-mode="indeterminate" ng-show="requesting"></md-progress-linear>
							</md-input-container>
							<md-button class="md-raised" ng-click="addRequest()">Add Request</md-button>
						</md-card-content>
					</md-card>
				</div>

				<div flex-gt-sm="50">
					<md-card>
						<md-card-title>
							<md-card-title-text>
								<span class="md-headline">Cast Control</span>
							</md-card-title-text>
						</md-card-title>
						<md-card-content>
							<div layout="row" layout-xs="column" layout-align="center">
								<md-card flex="25">
									<i class="material-icons song-icon">music_note</i>
								</md-card>
								<md-card flex>
									<span class="song-playing">@{{ currentlyPlaying }}</span>
								</md-card>
							</div>

							<div class="sound-control" layout="row" layout-align="center" flex="100">
								<md-button class="currentVolume" flex="10" ng-click="toggleMute()">
									<i class="material-icons" ng-show="volume <= 0">volume_off</i>
									<i class="material-icons" ng-show="volume > 0 && volume <= 33">volume_mute</i>
									<i class="material-icons" ng-show="volume > 33 && volume <= 66">volume_down</i>
									<i class="material-icons" ng-show="volume > 66 && volume <= 100">volume_up</i>
								</md-button>
								<md-slider flex class="md-primary" ng-model="volume" step="1" min="0" max="100" aria-label="volume"></md-slider>
								<md-button class="md-headline" md-no-ink flex="10"><span>@{{ volume }}%</span></md-button>
							</div>

							<div class="music-controls" layout="row" flex="100" layout-align="center" ng-show="currentlyPlaying != 'No song is currently playing.'">
								<md-button flex="25" class="md-raised" ng-click="skipToPrevious()"><i class="material-icons">fast_rewind</i></md-button>
								<md-button flex="50" class="md-raised" ng-click="pauseSong()"><i class="material-icons" ng-show="songPaused == 'false'">pause</i><i class="material-icons" ng-show="songPaused == 'true'">play_arrow</i></md-button>
								<md-button flex="25" class="md-raised" ng-click="skipToNext()"><i class="material-icons">fast_forward</i></md-button>
							</div>
						</md-card-content>
					</md-card>

					<div class="queue">
						<div ng-repeat="queueItem in queueItems">
							<md-card class="@{{queueItem.status}}" layout="row">
								<md-card-content layout="row" flex>
									<md-button flex="10" ng-click="playSong(queueItem.fileName)" ng-hide="queueItem.status == 'Playing' || queueItem.status == 'Paused' "><i class="material-icons">play_arrow</i></md-button>
									<md-button flex="10" ng-click="stopPlaying(queueItem.fileName)" ng-show="queueItem.status == 'Playing'"><i class="material-icons">stop</i></md-button>
									<md-button flex="10" ng-click="pauseSong()" ng-show="queueItem.status == 'Paused'"><i class="material-icons">play_arrow</i></md-button>
									<span class="md-subhead queue-filename" flex>@{{ queueItem.fileName }}</span>
									<md-button flex="10" ng-click="removeSong(queueItem.fileName)"><i class="material-icons">delete</i></md-button>
								</md-card-content>
							</md-card>
						</div>
					</div>

					<div class="queue-actions" layout="row" layout-align="end">
						<md-button class="md-raised" ng-click="toggleModes()"><i class="material-icons"  ng-show="mode == 'shuffle'">shuffle</i><i class="material-icons" ng-show="mode == 'through'">trending_flat</i></md-button>
						<md-button class="md-raised" ng-show="mode == 'through'" ng-click="togglePlayThroughDirection()"><i class="material-icons" ng-show="playThroughDirection == 'down'">arrow_downward</i><i class="material-icons" ng-show="playThroughDirection == 'up'">arrow_upward</i></md-button>
						<div class="spacer" flex></div>
						<md-button class="md-raised" ng-click="getQueue()">
							<span>Refresh</span>
							<i class="material-icons">refresh</i>
						</md-button>
						<md-button class="md-raised md-warn" ng-click="clearQueue()">
							<span>Clear</span>
							<i class="material-icons">clear_all</i>
						</md-button>
					</div>

				</div>
			</div>
		</div>
	</div>
</div>

  <script src="./js/app.js"></script>

</body>
</html>
