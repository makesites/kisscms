<script type="text/javascript">
  function validateForm(f) {
    if (f.username.value == "") {
      alert("Please enter a username");
      f.username.focus();
      return false;
    }
    if (f.password.value == "") {
      alert("Please enter a password");
      f.password.focus();
      return false;
    }
    f.submit();
  }
</script>