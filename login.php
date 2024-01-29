<?php
session_start();
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
									<h1 class="text-gray-900 mb-4">Hola de nuevo!!</h1>
									<h4 class="text-gray-900 mb-4">Introduce tu nombre de usuario y contraseña</h3>
										<?php
								if(isset($_SESSION['estado']) && $_SESSION['estado'] != ''){
										echo '<div class="alert alert-danger" role="alert">'.$_SESSION['estado'].'</div>';
										unset($_SESSION['estado']);
									}
									?>
								</div>
								<form class="user" action="login_code.php" method="POST">
									<div class="form-group">
										<input type="username" name="username" class="form-control form-control-user" placeholder="Enter Email Address...">
									</div>
									<div class="form-group">
										<input type="password" name="password" class="form-control form-control-user" id="exampleInputPassword" placeholder="Password">
									</div>
									<button type="submit" name="login_btn" href="index.html" class="btn btn-primary btn-user btn-block">
										Login
									</button>
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
