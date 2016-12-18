<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta charset="UTF-8">
	<title>PHPCast Frontend</title>
	<link rel='stylesheet prefetch' href='https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css'>
	<link rel='stylesheet' href='./css/font-awesome.css'>
	<link rel='stylesheet prefetch' href='https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css'>
	<link rel="stylesheet" href="./css/app.css">
</head>

<body>
  <div class="container">
	<h1 id="title">PHPCast</h1>
	<div class="col-sm-6 animated fadeInUp">
		<div class="ui">
			<h1>Request</h1>
			<div class="input-group">
				<input type="text" id="request-url" class="form-control" placeholder="https://....">
				<span class="input-group-btn">
					<button class="btn btn-default" type="button" id="request-add">Add Request</button>
				</span>
			 </div>
		</div>
		<div class="message">
			<i class="fa fa-check icon-message" aria-hidden="true"></i>
			<h1 id="type">Issue!</h1>
			<p id="content">Something has broken the API is not overriding this text!</p>
			<button class="btn btn-danger btn-outline" id="close">Close</button>
			<button class="btn btn-default" id="more">Show More</button>
		</div>
	</div>
	<div class="col-sm-6 animated fadeInUp">
		<div class="control">
			<h1>Cast Control</h1>
			
			<p>Now Playing : <span id="playing"></span></p>
			
			<div class="playback">
                <label for="volume">Volume</label>
                <input type="range" id="volume" name="volume" min="0" max="11000" step="100">
				<button class="btn btn-default"><i class="fa fa-backward" aria-hidden="true"></i></button>
				<button class="btn btn-default"><i class="fa fa-play" aria-hidden="true"></i></button>
				<button class="btn btn-default"><i class="fa fa-forward" aria-hidden="true"></i></button>
				<button class="btn btn-default" id="clear-queue">Clear Queue</button>
			</div>


			<div class="queue">

			</div>
		</div>
	</div>
</div>

  <script src='./js/jquery.min.js'></script>
  <script src="./js/app.js"></script>

</body>
</html>
