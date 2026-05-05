<?php
// index.php — landing page redirect.
// All "HOME" nav links point to register.php, so we send visitors there.
header("Location: register.php");
exit();