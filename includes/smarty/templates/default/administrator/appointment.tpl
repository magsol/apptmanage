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
			<h3><strong>Confirm Appointments</strong></h3>
			<div class="contentarea">
				<!-- Normal content area start -->

               <h4>Pending Appointments and Available Counselors</h4>
               {if isset($smarty.post.submitted) }
               {* something went wrong *}
               <p><b>Error Submitting Form!</b> Please make sure you do not 
               schedule the same counselor as both Counselor 1 and Counselor 2 
               for the same appointment.</p>
               {/if}
              <form method="post" action="confirmappts.php">
					
                    <table cellspacing="10">
                    <tr>
                      <th>Date</th>
                      <th>Time</th>
                      <th>Client</th>
                      <th>Counselor 1</th>
                      <th>Counselor 2</th>
                    </tr>
                    {if isset($nine) }
                    <input type="hidden" name="nineid" value="{$nine.id}" />
                    <tr>
                      <td>{$date9|date_format:"%B %e"}</td>
                      <td>9:00am</td>
                      <td>
                          {$nine.first_name|capitalize:true} 
                          {$nine.last_name|capitalize:true}<br />
                          ({$nine.user_id})
                      </td>
                      <td>
                        {html_options name=9counselor1 options=$ninecounselors}
                      </td>
                      <td>
                        {html_options name=9counselor2 options=$ninecounselors}
                      </td>
                    </tr>
                    {/if}
                    {if isset($twelve) }
                    <input type="hidden" name="twelveid" value="{$twelve.id}" />
                    <tr>
                      <td>{$date12|date_format:"%B %e"}</td>
                      <td>12:00pm</td>
                      <td>
                          {$twelve.first_name|capitalize:true} 
                          {$twelve.last_name|capitalize:true}<br />
                          ({$twelve.user_id})
                      </td>
                      <td>
                        {html_options name=12counselor1 options=$twelvecounselors}
                      </td>
                      <td>
                        {html_options name=12counselor2 options=$twelvecounselors}
                      </td>
                    </tr>
                    {/if}
                    {if isset($three) }
                    <input type="hidden" name="threeid" value="{$three.id}" />
                    <tr>
                      <td>{$date3|date_format:"%B %e"}</td>
                      <td>3:00pm</td>
                      <td>
                          {$three.first_name|capitalize:true} 
                          {$three.last_name|capitalize:true}<br />
                          ({$three.user_id})
                      </td>
                      <td>
                        {html_options name=3counselor1 options=$threecounselors}
                      </td>
                      <td>
                        {html_options name=3counselor2 options=$threecounselors}
                      </td>
                    </tr>
                    {/if}
                    {if isset($six) }
                    <input type="hidden" name="sixid" value="{$six.id}" />
                    <tr>
                      <td>{$date6|date_format:"%B %e"}</td>
                      <td>6:00pm</td>
                      <td>
                          {$six.first_name|capitalize:true} 
                          {$six.last_name|capitalize:true}<br />
                          ({$six.user_id})
                      </td>
                      <td>
                        {html_options name=6counselor1 options=$sixcounselors}
                      </td>
                      <td>
                        {html_options name=6counselor2 options=$sixcounselors}
                      </td>
                    </tr>
                    {/if}
                </table>


					  <br />
					  <br />
					  <div id="search">
					  <span class="contentarea">
					  <input type="submit" class="submit" value="Confirm Appointments" />
                      </span></div>
        <input type="hidden" name="action" value="create" />
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