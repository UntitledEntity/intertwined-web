
<!DOCTYPE html>
<html>

<head>
	<title>Intertwined</title>
	<meta charset="UTF-8">

	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link rel="shortcut icon" href="assets/images/favicon.ico" type="image/x-icon">
	<link rel="icon" type="image/icon" href="assets/images/favicon.ico">
	<meta content="Intertwined helps you set up your web servers securely, safely, and easily" name="description" />

	<link rel="stylesheet" href="assets/css/index-bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="assets/css/index-style.css" />
    <link rel="stylesheet" type="text/css" href="assets/css/index-accordian.css" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat:300,400,500,600,700,800&display=swap">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800&display=swap">
	<link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />

	<script src="assets/javascript/smoothscroll.js"></script>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
	<script src="static/js/accordian.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="static/js/smoothscroll.js"></script>
	<script src="https://cdn.sellix.io/static/js/embed.js" ></script>

	<script>
	$("#navbarNav").on("click", "a", function() {
		$(".navbar-toggle").click();
	});
	</script>
</head>

<body>

	<nav id="navbar" class="navbar fixed-top navbar-expand-lg navbar-header navbar-mobile">
		<div class="navbar-container container">
			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation"> <span class="navbar-toggler-icon"></span> </button>
			<div id="top"></div>
			<div class="collapse navbar-collapse justify-content-around" id="navbarNav">
				<ul class="navbar-nav menu-navbar-nav">

					<li class="nav-item">
						<a class="nav-link" href="api/docs/">
							<p class="nav-link-menu">API documentation</p>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="login">
							<p class="nav-link-menu">Login</p></p>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="register">
							<p class="nav-link-menu">Register</p></p>
						</a>
					</li>
				</ul>
			</div>
		</div>
	</nav>

    <div class="wrapper">

        <div class="header">
			<div class="container header-container fade-in">
				<div class="col-lg-6 header-title-section text-center">
					<h1 class="header-title">Intertwined</h1>
					<p class="header-title-text">Intertwined is a php-based <a href="https://github.com/UntitledEntity/intertwined-web">open-source</a> authentication service, that helps you set up your web and client applications securely.</p>
				</div>
			</div>
		</div>

		<div class="why-us">
			<div class="container header-container fade-in">
				<div class="col-lg-6 header-title-section text-center">
					<h1 class="header-title">Why us?</h1>

					<p class="header-title-text">We offer end-to-end encryption, which ensures your data will be protected from web point A to web point B.</p>

					<div class="header-img-section">
					    <img src="assets/images/end-to-end.png" alt="End-To-End">
					</div>

					<p class="header-title-text">We hash our passwords and HWIDs with <a href="https://en.wikipedia.org/wiki/Bcrypt">Bcrypt</a>. </p>

					<p class="header-title-text">We use <a href="https://www.cloudflare.com/">cloudflare</a> to help mitigate agianst DDOS attacks.</p>

					<p class="header-title-text">We have <a href="https://intertwined.solutions/api/docs/">documentation</a> and <a href="https://github.com/UntitledEntity/intertwined-api-example">examples</a> for our API.</p>
				</div>
			</div>
		</div>




		<div class="footer-section">
			<div class="container footer-container">
				<div class="col-lg-3 col-md-6 footer-subsection">
					<div class="footer-subsection-2-1">
					    <h3 class="footer-subsection-title">Terms</h3>
						<ul class="list-unstyled footer-list-menu">
							<li><a href="terms">Terms of service & privacy</a></li>
							<li><a href="credits">Credits</a></li>
						</ul>

					</div>
				</div>
			</div>
			<div class="container footer-credits">
				<p>&copy; 2022 <b>Intertwined</b> All rights reserved</p>
			</div>
		</div>
	</div>
</div>

    <script type="text/javascript">
	$(window).scroll(function() {
		if($(this).scrollTop() > 20) {
			$('#navbar').fadeOut();
		} else {
            $('#navbar').fadeIn();
		}
	});
	</script>

</body>

</html>
