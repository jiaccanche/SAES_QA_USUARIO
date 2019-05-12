<?php
require_once 'vendor/autoload.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" ></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" ></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" ></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
    <script src="js/controller.js"></script>
    <title>Entradas/Usuario</title>
</head>
<body>
<div class="container">
		<div class="d-flex justify-content-center" style="border:1px solid blue;padding-top: 100px; padding-bottom: 20px">
			<div class="user_card border">
				<div class="d-flex justify-content-center">
				</div>
				<div class="d-flex justify-content-center form_container">
					<form id="formulario_empleado">
						<div class="input-group mb-3">
							<div class="input-group-append">
								<span class="input-group-text"><i class="fas fa-user"></i></span>
							</div>
							<input type="text" name="user" class="form-control input_user" value="" placeholder="username" required>
						</div>
						<div class="input-group mb-2">
							<div class="input-group-append">
								<span class="input-group-text"><i class="fas fa-key"></i></span>
							</div>
							<input type="password" name="pwd" class="form-control input_pass" value="" placeholder="password" required>
						</div>
                        <div class="d-flex justify-content-center mt-3 login_container">
                            <button  class="btn btn-outline-success" type="submit" name="button" class="btn login_btn">Aceptar</button>
                        </div>
					</form>
				</div>

			</div>
		</div>
	</div>
</body>
</html>