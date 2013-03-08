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
			<li><a href="calendar.php" class="active">Calendar</a></li>
			<li><a href="account.php">Edit Account</a></li>
			<li><a href="logout.php">Logout</a></li>
			
		</ul>
  </div>
	<div id="menubottom"></div>

	
	<div id="content">

		<!-- Normal content: Stuff that's not going to be put in the left or right column. -->
		<div id="normalcontent">
			<h3><strong>Calendar</strong></h3>
			{if isset($smarty.get.ahead) } 
			  <a href="calendar.php">&lt;&lt; {$smarty.now|date_format:"%B %Y"}</a> | 
			  <b>{$nextMonthYear}</b>
			{else}
			  <b>{$smarty.now|date_format:"%B %Y"}</b> | 
			  <a href="calendar.php?ahead">{$nextMonthYear} &gt;&gt;</a>
			{/if}
			<div class="contentarea">
				<!-- Normal content area start -->

                <table width="603" height="538" border="1" cellpadding="0" cellspacing="0" bordercolor="#999999">
  <tr>
    <td height="20" background="images/calBack.gif"><div align="center" class="style1">Sunday</div></td>
    <td height="20" background="images/calBack.gif"><div align="center" class="style1">Monday</div></td>
    <td height="20" background="images/calBack.gif"><div align="center" class="style1">Tuesday</div></td>
    <td height="20" background="images/calBack.gif"><div align="center" class="style1">Wednesday</div></td>
    <td height="20" background="images/calBack.gif"><div align="center" class="style1">Thursday</div></td>
    <td height="20" background="images/calBack.gif"><div align="center" class="style1">Friday</div></td>
    <td height="20" background="images/calBack.gif"><div align="center" class="style1">Saturday</div></td>
  </tr>
  
  {section name=week loop=$month}
  <tr>
    {section name=day loop=$month[week]}
    <td height="104" valign="top" background="images/calBack{if ($month[week][day].dayExists == 0) }Dark{/if}.gif">
      {if ($month[week][day].passed == 0 && $month[week][day].dayExists == 1) }
    <table border="0" cellspacing="0">
      <tr>
        <th scope="col"><div align="center"><span class="style5">{$month[week][day].dayNum}</span></div></th>
        <th scope="col">&nbsp;</th>
      </tr>
      <tr>
        {if ($month[week][day].nine == 1) }
        <td><img src="images/available.jpg" alt="" width="15" height="15" /></td>
        <td><span class="style1"><a href="appointment.php?day={$month[week][day].dayNum}&{if isset($smarty.get.ahead) }ahead=1&{/if}appt=9&action=create">9:00 am</a></span></td>
        {elseif ($month[week][day].nine == 2) }
        <td><img src="images/scheduled.jpg" alt="" width="15" height="15" /></td>
        <td><span class="style1"><a href="appointment.php?day={$month[week][day].dayNum}&{if isset($smarty.get.ahead) }ahead=1&{/if}appt=9&action=cancel">9:00 am</a></span></td>
        {elseif ($month[week][day].nine == 3) }
        <td><img src="images/unavailable.gif" alt="" width="15" height="15" /></td>
        <td><span class="style1"><a href="appointment.php?day={$month[week][day].dayNum}&{if isset($smarty.get.ahead) }ahead=1&{/if}appt=9&action=cancel">9:00 am</a></span></td>       
        {else}
        <td><img src="images/unavailable.gif" alt="" width="15" height="15" /></td>
        <td><span class="style1">9:00 am</span></td>
        {/if}
      </tr>
      <tr>
        {if ($month[week][day].twelve == 1) }
        <td><img src="images/available.jpg" alt="" width="15" height="15" /></td>
        <td><span class="style1"><a href="appointment.php?day={$month[week][day].dayNum}&{if isset($smarty.get.ahead) }ahead=1&{/if}appt=12&action=create">12:00 pm</a></span></td>
        {elseif ($month[week][day].twelve == 2) }
        <td><img src="images/scheduled.jpg" alt="" width="15" height="15" /></td>
        <td><span class="style1"><a href="appointment.php?day={$month[week][day].dayNum}&{if isset($smarty.get.ahead) }ahead=1&{/if}appt=12&action=cancel">12:00 pm</a></span></td>
        {elseif ($month[week][day].twelve == 3) }
        <td><img src="images/unavailable.gif" alt="" width="15" height="15" /></td>
        <td><span class="style1"><a href="appointment.php?day={$month[week][day].dayNum}&{if isset($smarty.get.ahead) }ahead=1&{/if}appt=12&action=cancel">12:00 pm</a></span></td>
        {else}
        <td><img src="images/unavailable.gif" alt="" width="15" height="15" /></td>
        <td><span class="style1">12:00 pm</span></td>
        {/if}
      </tr>
      <tr>
        {if ($month[week][day].three == 1) }
        <td><img src="images/available.jpg" alt="" width="15" height="15" /></td>
        <td><span class="style1"><a href="appointment.php?day={$month[week][day].dayNum}&{if isset($smarty.get.ahead) }ahead=1&{/if}appt=3&action=create">3:00 pm</a></span></td>
        {elseif ($month[week][day].three == 2) }
        <td><img src="images/scheduled.jpg" alt="" width="15" height="15" /></td>
        <td><span class="style1"><a href="appointment.php?day={$month[week][day].dayNum}&{if isset($smarty.get.ahead) }ahead=1&{/if}appt=3&action=cancel">3:00 pm</a></span></td>
        {elseif ($month[week][day].three == 3) }
        <td><img src="images/unavailable.gif" alt="" width="15" height="15" /></td>
        <td><span class="style1"><a href="appointment.php?day={$month[week][day].dayNum}&{if isset($smarty.get.ahead) }ahead=1&{/if}appt=3&action=cancel">3:00 pm</a></span></td>
        {else}
        <td><img src="images/unavailable.gif" alt="" width="15" height="15" /></td>
        <td><span class="style1">3:00 pm</span></td>
        {/if}
      </tr>
      <tr>
        {if ($month[week][day].six == 1) }
        <td><img src="images/available.jpg" alt="" width="15" height="15" /></td>
        <td><span class="style1"><a href="appointment.php?day={$month[week][day].dayNum}&{if isset($smarty.get.ahead) }ahead=1&{/if}appt=6&action=create">6:00 pm</a></span></td>
        {elseif ($month[week][day].six == 2) }
        <td><img src="images/scheduled.jpg" alt="" width="15" height="15" /></td>
        <td><span class="style1"><a href="appointment.php?day={$month[week][day].dayNum}&{if isset($smarty.get.ahead) }ahead=1&{/if}appt=6&action=cancel">6:00 pm</a></span></td>
        {elseif ($month[week][day].six == 3) }
        <td><img src="images/unavailable.gif" alt="" width="15" height="15" /></td>
        <td><span class="style1"><a href="appointment.php?day={$month[week][day].dayNum}&{if isset($smarty.get.ahead) }ahead=1&{/if}appt=6&action=cancel">6:00 pm</a></span></td>
        {else}
        <td><img src="images/unavailable.gif" alt="" width="15" height="15" /></td>
        <td><span class="style1">6:00 pm</span></td>
        {/if}
      </tr>
    </table>
      {else}
      <table border="0" cellspacing="0">
      <tr>
        <th scope="col"><div align="center"><span class="style5">{$month[week][day].dayNum}</span></div></th>
        <th scope="col">&nbsp;</th>
      </tr>
      </table>
      {/if}
    </td>
    {/section}
  </tr>
  {/section}
</table>

                
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