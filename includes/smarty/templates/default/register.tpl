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
		
			<!--<li><a href="#">Home</a></li>
			<li><a href="#" class="active">News</a></li>
			<li><a href="#">Blog</a></li>
			<li><a href="#">Gallery</a></li>
			<li><a href="#">About</a></li>
			<li><a href="#">Contact</a></li>-->
			
		</ul>
  </div>
	<div id="menubottom"></div>

	
	<div id="content">

		<!-- Normal content: Stuff that's not going to be put in the left or right column. -->
		<div id="normalcontent">
			<h3><strong>Register</strong></h3>
			<div class="contentarea">
				<!-- Normal content area start -->
                <h4>Please Enter Your Desired Account Information.</h4>
                {if isset($smarty.post.email) }
                {* this indicates that authentication failed for some reason *}
                <p align="center">
                <table cellspacing="0" cellpadding="5" border="0" class="error">
                <tr>
                  <th>Unable to Register</th>
                </tr>
                <tr>
                  <td>
                    Please double check that you have correctly filled in all
                    information to register.  All fields are required, and
                    both password fields must match exactly.
                  </td>
                </tr>
                </table>
                </p>
                {/if}
                <form method="post" action="register.php">
              <table cellspacing="10">
<tr>
						<td>E-Mail: </td>
                        <td><input type="text" value="{$email}" maxlength="64" name="email" /></td>
                </tr>
                <tr>
						<td>First Name: </td>
                        <td><input type="text" value="{$first}" maxlength="64" name="first" /></td>
                </tr>
                <tr>  
						<td>Last Name: </td>
                        <td><input type="text" value="{$last}" maxlength="64" name="last" /></td>
                </tr>
                <tr>  
						<td valign="top">Gender: </td>
						<td>
                        <label><input type="radio" name="sex" value="male" /> Male</label><br>
						<label><input type="radio" name="sex" value="female" /> Female</label></td>
                </tr>
                <tr></tr>
                <tr></tr>
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
						<td>Zip Code: </td>
                        <td><input type="text" value="{$zipcode}" maxlength="64" name="zipcode" /></td>
                </tr>
				
                <tr></tr>
                <tr></tr>
                <tr>
						<td>Cell Number: </td>
                        <td><input type="text" value="{$cellphone}" maxlength="64" name="cellphone" /></td>
                </tr>
                <tr>
						<td>Home Number: </td>
                        <td><input type="text" value="{$homephone}" maxlength="64" name="homephone" /></td>
                </tr>
                <tr></tr>
                <tr></tr>
                <tr>
						<td>Enter Your Password: </td>
                        <td><input type="password" value="{$pass1}" maxlength="64" name="pass1" /></td>
                </tr>
                <tr>
						<td>Re-enter Your Password: </td>
						  <td><input type="password" value="{$pass2}" maxlength="64" name="pass2" /></td>
						  <br />
				  <br />
                </tr>
              </table>
				
                
                <br />
                <br />
                <div id="search">
						<input type="submit" class="submit" value="Submit" />
              </div>
              </form>
                
                
                 
                

			  <!-- Normal content area end -->
			</div>
		</div>
        </div>

	
		
	<div id="footer">
			<center>&copy; 2008 RiverStone Church. All rights reserved.</center></br>
			<center>Designed and Created by The Introverted Champions</center></br>
	</div>
	


</body>
</html>