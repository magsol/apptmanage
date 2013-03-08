{include file='header.tpl'}
<h1>Please Log In</h1>

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

<form action="index.php" method="post">
<!-- Login table -->
<table cellspacing="0" cellpadding="5" border="0" align="center">
<tr>
  <td>Your Email</td>
  <td><input type="text" name="email" id="textbox" /></td>
</tr>
<tr>
  <td>Password</td>
  <td><input type="password" name="password" id="textbox" /></td>
</tr>
<tr>
  <td align="center" colspan="2"><input type="submit" value="Log in" /></td>
</tr>
</table>
</form>

<p align="center">
<a href="register.php">Register!</a>
</p>
<p align="center">
<a href="password.php">Forgot your password?</a>
</p>

{include file='footer.tpl'}
