{include file='header.tpl'}
{*
 * This screen can be shown as part of one of many states.
 *
 * 1) The user is being prompted for an email, indicating the account
 *    for which a password reset request is being made.
 * 2) A password reset request has been submitted, and the user must
 *    be shown a confirmation screen with further instructions to complete
 *    the reset.
 *    2a) Check that the email address exists in the registered user database!
 * 3) The user clicked a link provided in an email to complete the password
 *    reset.  Using the hash through the GET variable, the user will be 
 *    presented with a text field to type in a new password.
 * 4) The new password has been submitted, and the user should be informed
 *    that their password has been successfully reset.
 *    4a) Check that a password with length > 0 was submitted!
 *}

{* CASE 4 *}
{if isset($smarty.post.newpassword) }
{$SUBTITLE}
{$MESSAGE}
Reset successful!

{* CASE 3 *}
{elseif isset($smarty.get.passhash) }
{if isset($VALID_RESET_HASH) }
<h1>Account Password Reset</h1>

<form action="password.php?passhash={$smarty.get.passhash}" method="post">
<table cellspacing="0" cellpadding="5" border="0" align="center">
<tr>
  <td>You are:</td>
  <td><b>{$USERNAME}</b></td>
</tr>
<tr>
  <td>Enter your new account password:</td>
  <td><input type="password" name="newpassword" id="textbox" 
       {if isset($smarty.post.newpassword)}value="{$smarty.post.newpassword}"{/if} /></td>
</tr>
<tr>
  <td align="center" colspan="2"><input type="submit" value="Reset Password" /></td>
</tr>
</table>
</form>
{else}
<h1>Invalid Password Reset</h1>

<p align="center">
<b style="color: #FF0000;">Error: Invalid password reset attempt.</b>
</p>
<p align="center">
<a href="index.php">Return to Login page</a>
</p>
{/if}
{* CASE 2 *}
{elseif isset($smarty.post.email) }
<h1>{$SUBTITLE}</h1>

<p align="center">
<b>{$MESSAGE}</b>
</p>
<p align="center">
<a href="index.php">Return to Login page</a>
</p>

{* CASE 1 *}
{else}
<h1>Reset your Password</h1>

<form action="password.php" method="post">
<table cellspacing="0" cellpadding="5" border="0" align="center">
<tr>
  <td>Enter the email address you registered with:</td>
  <td><input type="text" name="email" id="textbox" /></td>
</tr>
<tr>
  <td align="center" colspan="2"><input type="submit" value="Send Request" /></td>
</tr>
</table>
</form>

<p align="center">
<a href="index.php">Return to Login page</a>
</p>
{/if}
{include file='footer.tpl'}
