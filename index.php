
<!DOCTYPE html>
<html>

<head>
	<title>Intertwined</title>
	<meta charset="UTF-8">

	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link rel="shortcut icon" href="assets/images/favicon.ico" type="image/x-icon">
	<link rel="icon" type="image/icon" href="assets/images/favicon.ico">
	<meta content="Intertwined helps you set up your web servers securely, safely, and easily" name="description" />

	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="assets/css/index-bootstrap.min.css">
	<!-- Custom Styles -->
	<link rel="stylesheet" type="text/css" href="assets/css/index-style.css" />
	<link rel="stylesheet" type="text/css" href="assets/css/util.css" />
	
	<!-- Font Awesome for icons -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	
	<!-- Google Fonts -->
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat:300,400,500,600,700,800&display=swap">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800&display=swap">
	
	<!-- Swiper.js -->
	<link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />

	<!-- jQuery and Bootstrap JS -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

	<script>
	$("#navbarNav").on("click", "a", function() {
		$(".navbar-toggle").click();
	});
	</script>
</head>

<body>

	<nav id="navbar" class="navbar fixed-top navbar-expand-lg navbar-header navbar-mobile">
		<div class="navbar-container container">
			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation"> 
				<span class="navbar-toggler-icon"></span> 
			</button>
			<div id="top"></div>
			<div class="collapse navbar-collapse justify-content-around" id="navbarNav">
				<ul class="navbar-nav menu-navbar-nav">
					<li class="nav-item">
						<a class="nav-link" href="https://github.com/UntitledEntity/intertwined-web/blob/main/DOCS.md">
							<p class="nav-link-menu">Documentation</p>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="https://discord.gg/QZb96GqhGZ">
							<p class="nav-link-menu">Github</p>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="https://github.com/UntitledEntity/intertwined-web">
							<p class="nav-link-menu">Discord</p>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="login">
							<p class="nav-link-menu">Login</p>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="register">
							<p class="nav-link-menu">Register</p>
						</a>
					</li>
				</ul>
			</div>
		</div>
	</nav>

    <div class="wrapper">

		<div class="header">
			<div class="padding-25">
				<div class="container header-container fade-in">
						<div class="col-lg-5 order-lg-1">
							<p class="header-title">Intertwined</p>
							<p class="header-title-text">Intertwined is a <a href="https://php.net">php</a>-based <a href="https://github.com/UntitledEntity/intertwined-web">open-source</a> authentication service, that helps you set up your web and client applications securely.</p>
						</div>
						<div class="col-lg-6 order-lg-2">
							<div class="header-img-section">
							<img src="assets/images/dashboard.png" alt="Dash">
						</div>
					</div>
				</div>
			</div>
		</div>
		
		
		<div class="why-us">
			<div class="padding-25">
				<div class="container header-container fade-in">
					<div class="col-lg-6 header-title-section text-center">
						<h1 class="header-title">Why us?</h1>
					</div>					
				</div>
			</div>

			<div class="swiper-container">
				<div class="swiper-wrapper">

 					<!-- Privacy Slide -->
					<div class="swiper-slide">
						<div class="slide-title">Privacy</div>
						<div class="slide-content">
							<img src="assets/images/bcrypt.png" alt="Bcrypt">
							<div>Intertwined's main priority is privacy. That's why we hash both your and your user's passwords & HWID's with <a href="https://en.wikipedia.org/wiki/Bcrypt">Bcrypt</a>, and we have an extensive encrypted API to secure your data transmission from client to server.</div>
						</div>
					</div>

 					<!-- Ease of implementation Slide -->
					<div class="swiper-slide">
						<div class="slide-title">Ease of implementation</div>
						<div class="slide-content">
							<img src="assets/images/implementation.png" alt="Ease-Of-Implementation">
							<div>Our API is specifically designed for seamless introduction with your project. Additionally, we have documentation and examples on <a href="https://github.com/UntitledEntity/intertwined-web">Github</a>, and a support <a href="https://discord.gg/QZb96GqhGZ">Discord</a> to make integration as easy as possibly.</div>
						</div>
					</div>

					<!-- Slide 3 -->
					<div class="swiper-slide">
						<div class="slide-title">Open-Source</div>
						<div class="slide-content">
							<img src="assets/images/open-source.png" alt="Open-src">
							<div>We are <a href="https://en.wikipedia.org/wiki/Open_source">open-source</a>, which allows you to have a deeper understanding of how our code works and trust our service more. Additionally, we strongly about helping fellow developers and giving back to our community, which is why we've licensed the source code under the <a href="https://www.gnu.org/licenses/agpl-3.0.html">AGPL-3.0</a> license, which allows our code to be modified and repurposed when redistributed under this license.</div>
						</div>
					</div>

					<!-- Slide 4 -->
					<div class="swiper-slide">
						<div class="slide-title">End-to-End Encryption</div>
						<div class="slide-content">
							<img src="assets/images/end-to-end.png" alt="Open-src">
 							<div>Our encrypted API uses AES256 and SHA256, which are both used by the NSA, to encrypt your data end-to-end to give you peace of mind regarding security within your application.</div>
						</div>
					</div>
				</div>
			</div>
		</div>							

		<div class="padding-25 p-t-60">
			<div class="container header-container fade-in">
				<div class="col-lg-6 header-title-section text-center">
					<h1 class="header-title">Features</h1>
					<p class="header-title-text">Within our service, we have Applications. These applications act as your portable databases, which contain all of the features you'd need to run your project.</p>
				</div>					
			</div>
		</div>

		<div class="box-container">
			<div class="box">
				<div class="box-content">
					<div class="box-title">Server-Side Data</div>
					<div class="box-body">We allow you to access and call webhooks, get server-stored variables, and download files securely through our API.</div>
				</div>
			</div>
			<div class="box">
				<div class="box-content">
					<div class="box-title">Handle your user data</div>
					<div class="box-body">Through our service, we provide a UI for you to handle your user data including their usernames, passwords, login timestamps, tiers, ban status, IP and HWID, which can all be accessed through the dashboard. This userdata is then accessed through the API to connect to your project.</div>
				</div>
			</div>
			<div class="box">
				<div class="box-content">
					<div class="box-title">Data Management API</div>
					<div class="box-body">We additionally offer a Data Management API, which allows for you to manage your user data without even using the dashboard, which can be integrated into your own dashboard or UI.</div>
				</div>
			</div>
		</div>


		<div class="footer-section">
			<div class="container footer-container">
				<div class="col-lg-3 col-md-6 footer-subsection">
					<div class="footer-subsection-2-1">
						<h3 class="footer-subsection-title">Links</h3>
						<ul class="list-unstyled footer-list-menu">
							<li><a href="https://stats.uptimerobot.com/L1RAziPzlQ">Status</a></li>
							<li><a href="https://github.com/UntitledEntity/intertwined-web">Github</a></li>
							<li><a href="https://www.trustpilot.com/review/intertwined.solutions">Reviews</a></li>
						</ul>
					</div>
				</div>

				<div class="col-lg-3 col-md-6 footer-subsection">
					<div class="footer-subsection-2-1">
					    
						<h3 class="footer-subsection-title">Documentation</h3>
						<ul class="list-unstyled footer-list-menu">
							<li><a href="https://github.com/UntitledEntity/intertwined-web/blob/main/DOCS.md">ReadMe</a></li>
							<li><a href="https://github.com/UntitledEntity/Intertwined-CPP-Example">CPP Example</a></li>
							<li><a href="https://github.com/UntitledEntity/intertwined-api-example">Python Example</a></li>
						</ul>

					</div>
				</div>

				<div class="col-lg-3 col-md-6 footer-subsection">
					<div class="footer-subsection-2-1">
					    
						<h3 class="footer-subsection-title">Other</h3>
						<ul class="list-unstyled footer-list-menu">
							<li><a href="credits">Credits</a></li>
							<li><a href="terms">Terms</a></li>
						</ul>
					</div>
				</div>


				<div class="col-lg-3 col-md-6 footer-subsection">
					<div class="footer-subsection-2-1">
						<iframe src="https://discord.com/widget?id=1157392974729183346&theme=dark" width="250" height="300" allowtransparency="true" frameborder="0" sandbox="allow-popups allow-popups-to-escape-sandbox allow-same-origin allow-scripts"></iframe>	
					</div>
				</div>

			</div>
			<div class="container footer-credits">
				<p>&copy; 2024 <b>Intertwined</b> All rights reserved</p>
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

	<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
	<script>
		const swiper = new Swiper('.swiper-container', {
			slidesPerView: 2,
			spaceBetween: 20,
			loop: true,
			centeredSlides: true,
			breakpoints: {
				768: {
				slidesPerView: 1,
				}
			}
		});
  </script>

</body>

</html>
