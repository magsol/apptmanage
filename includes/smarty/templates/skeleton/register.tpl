{include file='header.tpl'}
<h1>New User Registration</h1>

{if isset($smarty.post.fname) }
<p align="center">
<table cellspacing="0" cellpadding="5" border="0" class="error">
<tr>
  <th>Unable to Create your Account</th>
</tr>
<tr>
  <td>
    Please check the fields below.  Any field marked with an asterick (*) indicates
an error.  Also, please double-check that the two passwords match exactly.
  </td>
</tr>
</table>
</p>

{/if}
<form action="register.php" method="post">
<table cellspacing="0" cellpadding="5" border="0" align="center">
<tr>
  <td>Enter your email address:</td>
  <td><input type="text" name="email" {if isset($smarty.post.email) }value="{$smarty.post.email}"{/if} id="textbox" /></td>
  <td>{if isset($smarty.post.email) && $smarty.post.email == "" }<font color="red"><b>*</b></font>{else}&nbsp;{/if}</td>
</tr>
<tr>
  <td>Enter your first name:</td>
  <td><input type="text" name="fname" {if isset($smarty.post.fname) }value="{$smarty.post.fname}"{/if} id="textbox" /></td>
  <td>{if isset($smarty.post.fname) && $smarty.post.fname == "" }<font color="red"><b>*</b></font>{else}&nbsp;{/if}</td>
</tr>
<tr>
  <td>Enter your last name:</td>
  <td><input type="text" name="lname" {if isset($smarty.post.lname) }value="{$smarty.post.lname}"{/if} id="textbox" /></td>
  <td>{if isset($smarty.post.lname) && $smarty.post.lname == "" }<font color="red"><b>*</b></font>{else}&nbsp;{/if}</td>
</tr>
<tr>
  <td>Create your password:</td>
  <td><input type="password" name="password1" id="textbox" /></td>
  <td>{if isset($smarty.post.password1) && $smarty.post.password1 == "" || 
                $smarty.post.password1 != $smarty.post.password2 }<font color="red"><b>*</b></font>{else}&nbsp;{/if}</td>
</tr>
<tr>
  <td>Re-enter your password:</td>
  <td><input type="password" name="password2" id="textbox" /></td>
  <td>{if isset($smarty.post.password2) && $smarty.post.password2 == "" ||
                $smarty.post.password2 != $smarty.post.password1 }<font color="red"><b>*</b></font>{else}&nbsp;{/if}</td>
</tr>
<tr>
  <td align="center" colspan="3"><input type="submit" value="Create my account" /></td>
</tr>
</table>

<p align="center">
<a href="index.php">Return to Login page</a>
</p>

{include file='footer.tpl'}
