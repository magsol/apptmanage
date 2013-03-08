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


		 <table>
            <tr>
            <td>
            
		<!-- Normal content: Stuff that's not going to be put in the left or right column. -->
		<div id="normalcontent"> <!-- Normal content area start -->
			<h3><strong>Home</strong></h3>
            
            
           
			<div class="contentarea">
				<!-- content area start -->

                <h4>Welcome, {$first|capitalize:true} {$last|capitalize:true}!</h4>    
               
                <p>{$email}</p>
                <p>{$street}<br />
                {$city}, {$state}. {$zipcode}</p>
                <p>Cell: {$cellphone}<br/>
                Home: {$homephone}<br />
                <br />
                </p>
                <form method="post" action="account.php">
              <div id="search">
					<input type="submit" class="submit" value="Edit Account Details" />
                  </div> <!-- Search end -->
			  </form>   

			  
		  </div><!-- Content area end -->
		</div><!-- Normal content area end -->
        </td>
        
        <td>
         <div id="secondarycontent">
         <div class="box">
              <p><h4>Upcoming Appointments</h4></p>
            {*  {html_table loop=$upcomingAppts} *}
		<p><h4>Upcoming Exceptions</h4></p>
		{* {html_table loop=$upcomingExceptions} *}
              </div> <!-- end box -->
              </div> <!-- end secondary content -->
              </td>
              </tr>
              </table>
                   
        
        </div><!-- Content end-->
        

	
		
	<div id="footer">
			<center>&copy; 2008 RiverStone Church. All rights reserved.</center></br>
			<center>Designed and Created by The Introverted Champions</center></br>
	</div> <!-- end footer -->
	
</div> <!-- end outer -->

</body>
</html>
