<?php
session_start();
$_SESSION['username'] = 'registrando';
//$_SESSION['startPage'] = 'register.php';
$_SESSION['paginas_permitidas'] = array(
	'register.php',
	'register_code.php');
include('includes/header.php');
?>


<div class="container">
	<!-- Outer Row -->
	<div class="row justify-content-center">
		<div class="col-xl-10 col-lg-12 col-md-9">
			<div class="card o-hidden border-0 shadow-lg my-5">
				<div class="card-body p-0">
					<!-- Nested Row within Card Body -->
					<div class="row">
						<!--						<div class="col-lg-6 d-none d-lg-block bg-login-image"></div>-->
						<div class="col-lg-6 bg-login-image"></div>
						<div class="col-lg-6">
							<div class="p-5">
								<div class="text-center">
									<h1 class="text-gray-900 mb-4">Hola!!</h1>
									<h4 class="text-gray-900 mb-4">Vamos a crear tu cuenta para SINCRM</h3>
								</div>
								<?php
									if (isset($_SESSION['estado']) && $_SESSION['estado'] != '') {
										echo '<div class="alert alert-danger" role="alert">'.$_SESSION['estado'].'</div>';
										unset($_SESSION['estado']);
									}
									?>
								<form class="user" action="login_code.php" method="POST">
									<div class="form-group">
<!--										<label for="username">Nombre de usuario</label>-->
										<input type="hidden" name="username" class="form-control form-control-user" placeholder="Nombre de usuario..." maxlength="40" value="deprecated username" required>
									</div>
									<div class="form-group">
  										<label for="email" class="form-label">Email</label>
  										<input type="email" name="email" class="form-control form-control-user" id="email" placeholder="name@example.com" maxlength="40" required>
									</div>
									<div class="form-group">
										<label for="password">Contraseña</label>
										<input type="password" name="password" class="form-control form-control-user" id="InputPassword" placeholder="Password" required>
										<label for="password_r">Repite tu contraseña</label>
										<input type="password" name="password_r" class="form-control form-control-user" id="InputPassword_r" placeholder="Password" required>
									</div>
 										<div class="form-group">
  										<label for="telefono" class="form-label">Teléfono</label>
  										<input type="phone" name="telefono" class="form-control form-control-user" id="telefono" placeholder="Número móvil" required>
									</div>
 										<div class="form-group">
  										<label for="comentario" class="form-label">Comentario</label>
  										<input type="text" class="form-control form-control-user" id="comentario" name="comentario" placeholder="Cuentanos brevemente quien eres" required>
									</div>
									<div class="form-group">
										<button type="submit" name="register_btn" href="register_code.php" class="btn btn-info btn-user btn-block">
										Registrarse
										</button>
									</div>
									<hr>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>

	</div>

</div>



<?php
include('includes/scripts.php');
include('includes/footer.php');
?>
