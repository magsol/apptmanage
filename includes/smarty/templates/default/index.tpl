<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
<title>SoZo Ministry of RiverStone Church</title>
<meta name="keywords" content="" />
<meta name="description" content="" />
<link rel="stylesheet" type="text/css" href="default.css" />
</head>
<body>

<div id="upbg"></div>

<div id="outer">


	<div id="header">
		<div id="headercontent">
			<h1>SoZo Ministry of RiverStone Church</h1>
			<h2>Registration and Scheduling</h2>
		</div>
	</div>


	<!-- <div id="headerpic"></div> -->
	
	<div id="menu">
		<ul>
		<!--
			<li><a href="#">Home</a></li>
			<li><a href="#" class="active">News</a></li>
			<li><a href="#">Blog</a></li>
			<li><a href="#">Gallery</a></li>
			<li><a href="#">About</a></li>
			<li><a href="#">Contact</a></li>
			-->
		</ul>
  </div>
	<div id="menubottom"></div>

	
	<div id="content">

		<!-- Normal content: Stuff that's not going to be put in the left or right column. -->
		<div id="normalcontent">
			<h3><strong>Login</strong></h3>
			<div class="contentarea">
				<!-- Normal content area start -->
				<center>
                
                {if isset($smarty.post.email) }
                {* this indicates that authentication failed for some reason *}
                <p align="center">
                <table cellspacing="0" cellpadding="5" border="0" class="error">
                <tr>
                  <th>Unable to Log In</th>
                </tr>
                <tr>
                  <td>
                    Please double check that you have correctly entered your username and password,
                and that you have registered with the system, and try again.  If you have forgotten
                your password, try the "I Forgot My Password" link.
                  </td>
                </tr>
                </table>
                </p>
                {/if}
                </br>
                </br>

				<p>
				<form method="post" action="index.php">
					<div id="search">
						Username: <input type="text" value="{$email}" maxlength="64" name="email" />
					</div>
				</p>
				
				<p>
					<div id="search">
						Password: <input type="password" value="{$password}" maxlength="64" name="password" />
					</div>
				</p>
				
				<p>
				
					<div id="search">
						<input type="submit" class="submit" value="Submit" />
					</div>
				</form></p>
                
                <p><br />
                  <br />
                <form method="post" action="register.php">
                	<div id="search">
						<input type="submit" class="submit" value="Register a New Account" />
					</div>
                 </form>
                 </p>
                 
                 <p>
                <form method="post" action="forgot.php">
                	<div id="search">
						<input type="submit" class="submit" value="Forgot Password?" />
					</div>
                 </form>
                 </p>
                 
                 
				
				
				
				<p></p>
				
				</center>

				<!-- Normal content area end -->
			</div>
		</div>
        </div>

	
		
	<div id="footer">
			<center>&copy; 2008 RiverStone Church. All rights reserved.</center></br>
			<center>Designed and Created by The Introverted Champions</center></br>
	</div>
	
</div>

</body>
</html>