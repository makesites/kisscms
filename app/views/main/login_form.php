<form method="post" action="<?=myUrl('ops/login')?>">
<div>
<input type="hidden" name="LoginForm" value="1" />
  <table style="width:auto">
    <thead>
    <tr><th>LOGIN</th></tr>
    </thead>
    <tbody>
      <tr>
        <td>Username<br /><input style="width:120px" type="text" name="username" value="<?=$username?>" /></td>
      </tr>
      <tr>
        <td>Password<br /><input style="width:70px" type="password" name="password" value="" />
        <input class="button" style="width:45px;margin-left:5px" type="submit" name="submit" value="Login" />
        </td>
      </tr>
    </tbody>
  </table>
</div>
</form>