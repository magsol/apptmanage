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
			<li><a href="account.php" class="active">Edit Account</a></li>
			<li><a href="logout.php">Logout</a></li>
			
		</ul>
  </div>
	<div id="menubottom"></div>

	
	<div id="content">

		<!-- Normal content: Stuff that's not going to be put in the left or right column. -->
		<div id="normalcontent">
			<h3><strong>Edit Account</strong></h3>
			<div class="contentarea">
				<!-- Normal content area start -->
                {if isset($smarty.post.first) }
                <table cellspacing="0" cellpadding="5" border="1" class="error">
                <tr>
                  <th>An error has occurred</th>
                </tr>
                <tr>
                  <td>There was a problem with your information.  Please make
                  sure you have filled in all the fields properly.</td>
                </tr>
                </table>
                {/if}
				
               <h4>Your Current Information: </h4>
               
                <p>{$email}</p>
                <p>{$street}<br />
                {$city}, {$state}. {$zipcode}</p>
              <p>Cell: {$cellphone}<br />
                Home: {$homephone}                <br />
                <br />
              </p>
              <h4>Edit Your Information: </h4>
              <form method="post" action="account.php">
					
                    <table cellspacing="10">
                    <tr>
					  <td>First Name: </td>
					    <td><input type="text" value="{$first}" maxlength="64" name="first" /></td>
                      </tr>
                        <tr>
                      <td>Last Name: </td>
                        <td><input type="text" value="{$last}" maxlength="64" name="last" /></td>
                        </tr>
                        <tr>				  
                      <td>Street: </td>
                        <td><input type="text" value="{$street}" maxlength="64" name="street" /></td>
                        </tr>
                        <tr>          
                      <td>City: </td>
                        <td><input type="text" value="{$city}" maxlength="64" name="city" /></td>
                        </tr>
                        <tr>          
                      <td>State: </td>
                        <td>{html_options name=state options=$states selected=$stateSelected}</td>
                        </tr>
                        <tr>
                      <td>Cell Number: </td>
                        <td><input type="text" value="{$cellphone}" maxlength="64" name="cellphone" /></td>
                        </tr>
                        <tr>
                  	<td>Home Number: </td>
                        <td><input type="text" value="{$homephone}" maxlength="64" name="homephone" /></td>
                        </tr>
                </table>


					  <br />
					  <br />
					  <div id="search">
					  <span class="contentarea">
					  <input type="submit" class="submit" value="Submit Changes" />
                      </span></div>
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