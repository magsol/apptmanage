{include file='header.tpl'}
<h1>New User Registration</h1>

<p align="center">
<table cellspacing="0" cellpadding="5" border="0" class="error">
<tr>
  <th>Unable to Create your Account</th>
</tr>
<tr>
  <td>
  Please check the fields below.  Any fields marked with an asterick (*) indicate an error.  Also, please double-check that the two passwords match exactly.
  </td>
</tr>
</table>
</p>

<form action="register.php" method="post">
<table cellspacing="0" cellpadding="5" border="0" align="center">
<tr>
  <td>Enter your email address:</td>
  <td><input type="text" name="email" value="{$smarty.post.email}" id="textbox" /></td>
  <td>{if $smarty.post.email == "" }<font color="red"><b>*</b></font>{else}&nbsp;{/if}</td>
</tr>
<tr>
  <td>Enter your first name:</td>
  <td><input type="text" name="fname" value="{$smarty.post.fname}" id="textbox" /></td>
  <td>{if $smarty.post.fname == "" }<font color="red"><b>*</b></font>{else}&nbsp;{/if}</td>
</tr>
<tr>
  <td>Enter your last name:</td>
  <td><input type="text" name="lname" value="{$smarty.post.lname}" id="textbox" /></td>
  <td>{if $smarty.post.lname == "" }<font color="red"><b>*</b></font>{else}&nbsp;{/if}</td>
</tr>
<tr>
  <td>Create your password:</td>
  <td><input type="password" name="password1" id="textbox" /></td>
  <td>{if $smarty.post.password1 == "" }<font color="red"><b>*</b></font>{else}&nbsp;{/if}</td>
</tr>
<tr>
  <td>Re-enter your password:</td>
  <td><input type="password" name="password2" id="textbox" /></td>
  <td>{if $smarty.post.password2 == "" }<font color="red"><b>*</b></font>{else}&nbsp;{/if}</td>
</tr>
<tr>
  <td align="center" colspan="3"><input type="submit" value="Create my account" /></td>
</tr>
</table>
</form>

<p align="center">
<a href="index.php">Return to Login page</a>
</p>

{include file='footer.tpl'}
