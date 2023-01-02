
<!DOCTYPE html>
<html>
    
<head>
	<title>Intertwined</title>
	<meta charset="UTF-8">
	
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	
	<link rel="shortcut icon" href="https://intertwined.solutions/assets/images/favicon.ico" type="image/x-icon">
	<link rel="icon" type="image/icon" href="https://intertwined.solutions/assets/images/favicon.ico">
	<meta content="Intertwined helps you set up your web servers securely, safely, and easily" name="description" />

	<link rel="stylesheet" href="https://intertwined.solutions/assets/css/index-bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="https://intertwined.solutions/assets/css/index-style.css" />
    <link rel="stylesheet" type="text/css" href="https://intertwined.solutions/assets/css/index-accordian.css" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat:300,400,500,600,700,800&display=swap">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800&display=swap">
	<link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
	
	<script src="https://intertwined.solutions/assets/javascript/smoothscroll.js"></script>
	
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
						<a class="nav-link" href="#init">
							<p class="nav-link-menu">Init</p>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="#login">
							<p class="nav-link-menu">Login</p></p>
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="#close">
							<p class="nav-link-menu">Close</p></p>
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
					<h1 class="header-title">Manager API documentation</h1>
					<p class="header-title-text"><a href="https://github.com/UntitledEntity/intertwined-api-example">Python Example</a></p>
				</div>
			</div>
		</div>
		
		<div class="main-doc">
			<div class="container header-container fade-in">
				<div class="col-lg-6 header-title-section text-center">
	
					
		        	<div id="getusers">
						<br></br>

						<h1 class="header-title">Get Users</h1>
						<p class="header-title-text">Return all users from an applicaiton in a JSON array.</p>

						<br>

						<p class="header-subtitle">Parameters</p>
						<p class="header-title-text"><b>license</b> - Your license that you used to register</p>

						<br>

						<p class="header-subtitle">Example</p>
						<p class="header-title-text">https://intertwined.solutions/api/manager/?license={license}&type=getusers</p>


					</div>

					<div id="deleteuser">
						<br></br>

						<h1 class="header-title">Delete User</h1>
						<p class="header-title-text">Delete user from an application.</p>

						<br>

						<p class="header-subtitle">Parameters</p>
						<p class="header-title-text"><b>license</b> - Your license that you used to register</p>
						<p class="header-title-text"><b>user</b> - The username of the user that you are deleting</p>

						<br>

						<p class="header-subtitle">Example</p>
						<p class="header-title-text">https://intertwined.solutions/api/manager/?license={license}&type=deleteuser&user={user}</p>


					</div>

					<div id="genlicense">
						<br></br>

						<h1 class="header-title">Generate license(s)</h1>
						<p class="header-title-text">Generate license(s) for an application.</p>

						<br>

						<p class="header-subtitle">Parameters</p>
						<p class="header-title-text"><b>license</b> - Your license that you used to register</p>
						<p class="header-title-text"><b>expiry</b> - The expiration for the license</p>
						<p class="header-title-text"><b>level</b> - The level for the license</p>
						<p class="header-title-text"><b>amount</b> - Amount of licenses to generate</p>

						<br>

						<p class="header-subtitle">Example</p>
						<p class="header-title-text">https://intertwined.solutions/api/manager/?license={license}&type=genlicense&expiry={expiry}&level={level}&amount={amount}</p>


					</div>
					
					<div id="userdata">
						<br></br>

						<h1 class="header-title">User Data</h1>
						<p class="header-title-text">Get data for a specific user in an application.</p>

						<br>

						<p class="header-subtitle">Parameters</p>
						<p class="header-title-text"><b>license</b> - Your license that you used to register</p>
						<p class="header-title-text"><b>expiry</b> - The expiration for the license</p>
						<p class="header-title-text"><b>level</b> - The level for the license</p>
						<p class="header-title-text"><b>amount</b> - Amount of licenses to generate</p>

						<br>

						<p class="header-subtitle">Example</p>
						<p class="header-title-text">https://intertwined.solutions/api/manager/?license={license}&type=genlicense&expiry={expiry}&level={level}&amount={amount}</p>


					</div>


				</div>
			</div>
		</div>
		
	</div>
    
</body>

</html>