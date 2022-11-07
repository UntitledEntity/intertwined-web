
<!DOCTYPE html>
<html>
    
<head>
	<title>Intertwined</title>
	<meta charset="UTF-8">
	
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	
	<link rel="shortcut icon" href="../../assets/images/favicon.ico" type="image/x-icon">
	<link rel="icon" type="image/icon" href="../../assets/images/favicon.ico">
	<meta content="Intertwined helps you set up your web servers securely, safely, and easily" name="description" />

	<link rel="stylesheet" href="../../assets/css/index-bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="../../assets/css/index-style.css" />
    <link rel="stylesheet" type="text/css" href="../../assets/css/index-accordian.css" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat:300,400,500,600,700,800&display=swap">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800&display=swap">
	<link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
	
	<script src="../../assets/javascript/smoothscroll.js"></script>
	
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
					<h1 class="header-title">API documentation</h1>
					<p class="header-title-text"><a href="https://github.com/UntitledEntity/intertwined-api-example">Python examples</a></p>
				</div>
			</div>
		</div>
		
		<div class="main-doc">
			<div class="container header-container fade-in">
				<div class="col-lg-6 header-title-section text-center">
	
					
		            <div id="init">
		            	<br></br>
		            
		            	<h1 class="header-title">Init</h1>
		            	<p class="header-title-text">Initiate an API session.</p>
		        
						<br>

						<p class="header-subtitle">Parameters</p>
						<p class="header-title-text"><b>{ 'type': "init", 'appid': "XXXXXXXX" }</b></p>
						
						<p class="header-title-text"><b>appid</b> - Your unique application ID, which can be found on the 'application' tab of the dashboard.</p>
						
						<br>

						<p class="header-subtitle">Returns</p>

						<p class="header-title-text">{ 'success': true, 'sessionid': "XXXXXXXX" }</p>
						<p class="header-title-text">{ 'success': false, 'error': "Lorem Ipsum" }</p>

						<br>

						<p class="header-subtitle">Errors</p>
						
						<p class="header-title-text"><b>Application disabled</b> - The application was disabled by the owner on the 'application' dashboard tab.</p>
						<p class="header-title-text"><b>Unable to open session</b> - Error within backend or your application. Contact an administrator if the error persists.</p>
						
					</div>

					<div id="login">
						<br></br>
		            
		            	<h1 class="header-title">Login</h1>
		            	<p class="header-title-text">Validate user credentials and get user data.</p>
		        
						<br>

						<p class="header-subtitle">Parameters</p>

						<p class="header-title-text"><b>{ 'type': "login", 'sid': "XXXXXXXX", 'user': "username", 'pass': "password" }</b></p>
						
						<p class="header-title-text"><b>sid</b> - The session ID which is returned when 'init' is called.</p>
						<p class="header-title-text"><b>user</b> - The username of the user you are trying to validiate.</p>
						<p class="header-title-text"><b>pass</b> - The password of the user you are trying to validiate.</p>

						<br>

						<p class="header-subtitle">Returns</p>

						<p class="header-title-text">{ 'success': true, 'data': { 'user': "username", 'expiry': "XXXXXXXXXX", 'level': 1, 'ip': "255.255.255.255" } }</p>
						<p class="header-title-text">{ 'success': false, 'error': "Lorem Ipsum" }</p>

						<br>

						<p class="header-subtitle">Errors</p>
						
						<p class="header-title-text"><b>Application disabled</b> - The application was disabled by the owner on the 'application' dashboard tab.</p>
						<p class="header-title-text"><b>Invalid session</b> - The session ID you provided is invalid.</p>
						<p class="header-title-text"><b>blacklisted</b> - The IP or HWID you are calling from is blacklisted.</p>
						<p class="header-title-text"><b>banned</b> - The user is banned.</p>
						<p class="header-title-text"><b>subscription_expired</b> - The user's subscription has expired. </p>
						<p class="header-title-text"><b>user_not_found</b> - The username that you've provied is invalid.</p>
						<p class="header-title-text"><b>password_mismatch</b> - The password that you've provided is invalid. </p>

					</div>

					<div id="register">
						<br></br>
					
						<h1 class="header-title">Register</h1>
		            	<p class="header-title-text">Registers a user and logs them in using the provided credentials.</p>
		            
		            	<br>
		        
						<p class="header-subtitle">Parameters</p>

						<p class="header-title-text"><b>{ 'type': "register", 'sid': "XXXXXXXX", 'user': "username", 'pass': "password", 'license': "license" }</b></p>
						
						<p class="header-title-text"><b>sid</b> - The session ID which is returned when 'init' is called.</p>
						<p class="header-title-text"><b>user</b> - The username of the user you are trying to register.</p>
						<p class="header-title-text"><b>pass</b> - The password of the user you are trying to register.</p>
						<p class="header-title-text"><b>license</b> - The license you are trying to register from.</p>

						<br>

						<p class="header-subtitle">Returns</p>

						<p class="header-title-text">{ 'success': true, 'data': { 'user': "username", 'expiry': "XXXXXXXXXX", 'level': 1, 'ip': "255.255.255.255" } }</p>
						<p class="header-title-text">{ 'success': false, 'error': "Lorem Ipsum" }</p>

						<br>

						<p class="header-subtitle">Errors</p>
						
						<p class="header-title-text"><b>Application disabled</b> - The application was disabled by the owner on the 'application' dashboard tab.</p>
						<p class="header-title-text"><b>Invalid session</b> - The session ID you provided is invalid.</p>
						<p class="header-title-text"><b>blacklisted</b> - The IP or HWID you are calling from is blacklisted.</p>
						<p class="header-title-text"><b>password_mismatch</b> - The password that you've provided is the same as the user or is less than 4 characters.</p>
						<p class="header-title-text"><b>user_already_taken</b> - The username you've provided is already taken by a user in the same application.</p>
						<p class="header-title-text"><b>invalid_license</b> - The license you've provided doesn't exist.</p>
						<p class="header-title-text"><b>license_already_used</b> - The license you've provided has already been claimed.</p>
						<p class="header-title-text"><b>expired_license</b> - The licenses time limit has expired.</p>
						<p class="header-title-text"><b>invalid_level</b> - There was an error in generating the license that you've provided.</p>
	

					</div>

					<div id="upgrade">
						<br></br>
					
						<h1 class="header-title">Upgrade</h1>
		            	<p class="header-title-text">Upgrades a users level and extends their expiry using a license.</p>
		            
		            	<br>
		        
						<p class="header-subtitle">Parameters</p>

						<p class="header-title-text"><b>{ 'type': "upgrade", 'sid': "XXXXXXXX", 'user': "username", 'license': "license" }</b></p>
						
						<p class="header-title-text"><b>sid</b> - The session ID which is returned when 'init' is called.</p>
						<p class="header-title-text"><b>user</b> - The username of the user you are trying to.</p>
						<p class="header-title-text"><b>license</b> - The license you are trying to upgrade with.</p>

						<br>

						<p class="header-subtitle">Returns</p>

						<p class="header-title-text">{ 'success': true, 'upgrade_data': { 'level': 1, 'expiry': "XXXXXXXXXX" } }</p>
						<p class="header-title-text">{ 'success': false, 'error': "Lorem Ipsum" }</p>

						<br>

						<p class="header-subtitle">Errors</p>

						<p class="header-title-text"><b>Application disabled</b> - The application was disabled by the owner on the 'application' dashboard tab.</p>
						<p class="header-title-text"><b>Invalid session</b> - The session ID you provided is invalid.</p>
						<p class="header-title-text"><b>user_not_found</b> - The username that you've provided is invalid.</p>
						<p class="header-title-text"><b>invalid_license</b> - The license you've provided doesn't exist.</p>
						<p class="header-title-text"><b>license_already_used</b> - The license you've provided has already been claimed.</p>
						<p class="header-title-text"><b>expired_license</b> - The licenses time limit has expired.</p>
						<p class="header-title-text"><b>invalid_level</b> - The level of the license is less than the level of the user.</p>
	
					</div>
					
					<div id="close">
						<br></br>
					
						<h1 class="header-title">Close</h1>
		            	<p class="header-title-text">Close an existing session.</p>
		            
		            	<br>

						<p class="header-subtitle">Parameters</p>
						<p class="header-title-text"><b>{ 'type': "close", 'sid': "XXXXXXXX" }</b></p>
						
						<p class="header-title-text"><b>sid</b> - The session ID which is returned when 'init' is called.</p>
						
						<br>

						<p class="header-subtitle">Returns</p>

						<p class="header-title-text">{ 'success': true, 'message': "successfully closed session." }</p>
						<p class="header-title-text">{ 'success': false, 'error': "Lorem Ipsum" }</p>

						<br>

						<p class="header-subtitle">Errors</p>

						<p class="header-title-text"><b>Invalid session</b> - The session ID you provided is invalid.</p>
						
					</div>

				</div>
			</div>
		</div>
		
		
       
	</div>
    
</body>

</html>