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
		
			<li><a href="home.php" class="active">Home</a></li>
			<li><a href="calendar.php">Calendar</a></li>
			<li><a href="account.php">Edit Account</a></li>
			<li><a href="logout.php">Logout</a></li>
			
		</ul>
  </div>
	<div id="menubottom"></div>

	
	<div id="content">

            
		<!-- Normal content: Stuff that's not going to be put in the left or right column. -->
		<div id="normalcontent"> <!-- Normal content area start -->
			<h3><strong>Home</strong></h3>
           
		  <div class="contentarea">
				<!-- content area start -->

                <h4>Welcome, {$first|capitalize:true} {$last|capitalize:true}!</h4>
                <br />
              <br />
               
                <p>There {if $appointments != 1}are{else}is{/if} {$appointments} appointment{if $appointments != 1}s{/if} awaiting your approval.
                <form method="post" action="confirmappts.php">
              <div id="search">{if $appointments > 0}
					<input type="submit" class="submit" value="View Pending Appointments" />
					{/if}
                  </div> <!-- Search end -->
			  </form> 
			  </p>
			  
		     <p><br />
	         <br />Currently, there {if $counselors != 1}are{else}is{/if} {$counselors} counselor{if $counselors != 1}s{/if} and {$clients} client{if $clients != 1}s{/if} registered in the system.
			   <form method="post" action="userpermissions.php">
                	<div id="search">{if $counselors != 0 || $clients != 0}
						<input type="submit" class="submit" value="Edit User Permissions" />
						{/if}
					</div>
              </form>
               </p> 

			  
		         <br />
		  </div><!-- Content area end -->
		</div><!-- Normal content area end -->                   
        
        </div><!-- Content end-->
        

	
		
	<div id="footer">
			<center>&copy; 2008 RiverStone Church. All rights reserved.</center></br>
			<center>Designed and Created by The Introverted Champions</center></br>
	</div> <!-- end footer -->
	
</div> <!-- end outer -->

</body>
</html>