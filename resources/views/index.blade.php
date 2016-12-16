<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta charset="UTF-8">
	<title>PHPCast Frontend</title>
	<link rel='stylesheet prefetch' href='https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css'>
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
			<button class="btn btn-default" id="close">Close</button>
			<h1 id="type">Error</h1>
			<p id="content">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque eu odio mauris. Ut ac dolor ut tellus pellentesque viverra. Aliquam sed venenatis odio. Aliquam rutrum nisl id mauris viverra, ac posuere enim iaculis. Pellentesque non nibh ut justo pulvinar porta et eget diam. Morbi lobortis lorem ac risus interdum, eget commodo purus interdum. Quisque at lectus a ante posuere convallis at porta arcu. Aliquam eu euismod libero.
Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Sed vitae diam pulvinar, volutpat felis eu, tempus massa. Suspendisse potenti. Nunc convallis sit amet lorem at rutrum. In et massa nisl. Integer non tortor porttitor, rutrum nulla eget, ultricies eros. Duis id libero nec dui bibendum malesuada. Maecenas eleifend porta purus auctor ornare. Nulla ut erat eget metus pellentesque facilisis. Etiam vestibulum magna ac nibh auctor, eu pellentesque tellus pretium. Aenean ex sapien, sodales in dignissim eget, rhoncus ac eros. Duis auctor in mauris fermentum maximus. Suspendisse ligula turpis, condimentum et leo nec, cursus rhoncus lacus. Phasellus magna libero, hendrerit vitae imperdiet consequat, posuere sed dolor.</p>
			<button class="btn btn-default" id="more">Show More</button>
		</div>
	</div>
	<div class="col-sm-6 animated fadeInUp">
		<div class="control">
			<h1>Cast Control</h1>
			
			<p>Now Playing : <span id="playing"></span></p>
			
			<div class="playback">
				<button class="btn btn-default"><i class="fa fa-backward" aria-hidden="true"></i></button>
				<button class="btn btn-default"><i class="fa fa-play" aria-hidden="true"></i></button>
				<button class="btn btn-default"><i class="fa fa-forward" aria-hidden="true"></i></button>
			</div>

			<div class="queue">
				<div class="record-container">

				</div>
			</div>
		</div>
	</div>
</div>

  <script src="https://use.fontawesome.com/041fd98d20.js"></script>
  <script src='./js/jquery.min.js'></script>
  <script src="./js/app.js"></script>

</body>
</html>
