<?php
session_start();

require_once "Autenticacion.php";
require_once "Clases.php";

$auth = new Auth();
$db_handle = new DBController();
$util = new Util();

require_once "ValidacionSesion.php";

if ($isLoggedIn) {
    $util->redirect("PanelControl.php");
}

if (! empty($_POST["login"])) {
    $isAuthenticated = false;
    
    $username = $_POST["usuario_nombre"];
    $password = $_POST["usuario_password"];
    
    $user = $auth->getMemberByUsername($username);
    if (password_verify($password, $user[0]["usuario_password"])) {
        $isAuthenticated = true;
    }
    
    if ($isAuthenticated) {
        $_SESSION["usuario_id"] = $user[0]["usuario_id"];
        
        // Set Auth Cookies if 'Remember Me' checked
        if (! empty($_POST["remember"])) {
            setcookie("login_usuario", $username, $cookie_expiration_time);
            
            $random_password = $util->getToken(16);
            setcookie("random_password", $random_password, $cookie_expiration_time);
            
            $random_selector = $util->getToken(32);
            setcookie("random_selector", $random_selector, $cookie_expiration_time);
            
            $random_password_hash = password_hash($random_password, PASSWORD_DEFAULT);
            $random_selector_hash = password_hash($random_selector, PASSWORD_DEFAULT);
            
            $expiry_date = date("Y-m-d H:i:s", $cookie_expiration_time);
            
            // mark existing token as expired
            $userToken = $auth->getTokenByUsername($username, 0);
            if (! empty($userToken[0]["id"])) {
                $auth->markAsExpired($userToken[0]["id"]);
            }
            // Insert new token
            $auth->insertToken($username, $random_password_hash, $random_selector_hash, $expiry_date);
        } else {
            $util->clearAuthCookie();
        }
        $util->redirect("PanelControl.php");
    } else {
        $mensaje = "Credenciales incorrectos, Por favor intente nuevamente.";
    }
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content="">
<meta name="author" content="">
<link rel="icon" href="favicon.ico">
<title>Login seguro usando PHP Session y Cookies - BaulPHP</title>

<!-- Bootstrap core CSS -->
<link href="dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Custom styles for this template -->
<link href="assets/sticky-footer-navbar.css" rel="stylesheet">
<style>
.error-mensaje {
    text-align: center;
    color: #FF0000;
}
</style>
</head>

<body>
<header> 
  <!-- Fixed navbar -->
  <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark"> <a class="navbar-brand" href="#">BaulPHP</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation"> <span class="navbar-toggler-icon"></span> </button>
    <div class="collapse navbar-collapse" id="navbarCollapse">
      <ul class="navbar-nav mr-auto">
        <li class="nav-item active"> <a class="nav-link" href="index.php">Inicio <span class="sr-only">(current)</span></a> </li>
      </ul>
      <form class="form-inline mt-2 mt-md-0">
        <input class="form-control mr-sm-2" type="text" placeholder="Buscar" aria-label="Search">
        <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Busqueda</button>
      </form>
    </div>
  </nav>
</header>

<!-- Begin page content -->

<div class="container">
  <h3 class="mt-5">Login seguro usando PHP Session y Cookies</h3>
  <hr>
  <div class="row">
    <div class="col-12 col-md-12"> 
      <!-- Contenido -->


<form action="" method="post" id="frmLogin">
    <div class="error-mensaje"><?php if(isset($mensaje)) { echo $mensaje; } ?></div>

    
  <div class="form-group">
    <label for="usuario">Usuario</label>
<input name="usuario_nombre" type="text" value="<?php if(isset($_COOKIE["login_usuario"])) { echo $_COOKIE["login_usuario"]; } ?>" class="form-control">
  </div>
    
    
<div class="form-group">
    <label for="password">Contraseña</label>
<input name="usuario_password" type="password" value="<?php if(isset($_COOKIE["usuario_password"])) { echo $_COOKIE["usuario_password"]; } ?>" class="form-control">
      
    </div>
    
<div class="form-check">
       
<input type="checkbox" name="remember" id="remember" <?php if(isset($_COOKIE["login_usuario"])) { ?> checked
<?php } ?>  class="form-check-input"/> 
<label for="remember-me">Recordarme</label>
    </div>
    
<div class="form-group">

<input type="submit" name="login" value="Iniciar Sesión"
                class="btn btn-primary">
           
    </div>
</form>

      <!-- Fin Contenido --> 
    </div>
  </div>
  <!-- Fin row --> 
  
</div>
<!-- Fin container -->
<footer class="footer">
  <div class="container"> <span class="text-muted">
    <p>Códigos <a href="https://www.baulphp.com/" target="_blank">BaulPHP</a></p>
    </span> </div>
</footer>
<script src="assets/jquery-1.12.4-jquery.min.js"></script> 
<script src="assets/jquery.validate.min.js"></script> 
<script src="assets/ValidarRegistro.js"></script> 
<!-- Bootstrap core JavaScript
    ================================================== --> 
<!-- Placed at the end of the document so the pages load faster --> 

<script>window.jQuery || document.write('<script src="assets/js/vendor/jquery-slim.min.js"><\/script>')</script> 
<script src="assets/js/vendor/popper.min.js"></script> 
<script src="dist/js/bootstrap.min.js"></script>
</body>
</html>