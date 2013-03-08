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
		
			<li><a href="home.php">Home</a></li>
			<li><a href="calendar.php">Calendar</a></li>
			<li><a href="account.php">Edit Account</a></li>
			<li><a href="logout.php">Logout</a></li>
			
		</ul>
  </div>
	<div id="menubottom"></div>

	
	<div id="content">

		<!-- Normal content: Stuff that's not going to be put in the left or right column. -->
		<div id="normalcontent">
			<h3><strong>Cancel Appointment</strong></h3>
			<div class="contentarea">
				<!-- Normal content area start -->
				
               <h4>Are you sure you wish to cancel your appointment on {$dayname|capitalize:true}, {$date} at {$time}?</h4>
               <br />
               <p>There is no guarantee that this slot will remain available should you decide to cancel.</p>
              <form method="post" action="appointment.php">
					
                    <table cellspacing="10">
                      <tr>
                      <td>
                        <label><input type="radio" name="cancel" value="yes" checked="checked" /> Yes, cancel this appointment.</label>
                      </td>
                      </tr>
                      <tr>
                      <td>
                        <label><input type="radio" name="cancel" value="no" /> No, don't cancel it.</label>
                      </td>
                      </tr>
                </table>
                
                 <h4>


					  <br />
					  <br />
					  <div id="search">
					  <span class="contentarea">
					  <input type="submit" class="submit" value="Submit Cancelation" />
                      </span></div>
        {if isset($smarty.get.ahead) }
        <input type="hidden" name="ahead" value="1" />
        {/if}
        <input type="hidden" name="hour" value="{$smarty.get.appt}" />
        <input type="hidden" name="day" value="{$smarty.get.day}" />
        <input type="hidden" name="action" value="cancel" />
        <input type="hidden" name="submitted" value="1" />
			  </form>   


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