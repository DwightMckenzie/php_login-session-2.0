<?php

	/**
	 * global navigation bar
	 */

	$user_id = $_SESSION['id'];
	$user_name = $_SESSION['username'];

?>

<!-- header container -->
<div class="container">
	<div class="header pt-3">
		<div class="row">
			<div class="col-lg-10">
				<p><strong>User:</strong> <?= e($user_name) ?> <br> <strong>User Id:</strong> <?= $user_id ?></p>
			</div>
			<div class="col-lg-2 logout-btn">
				<?php if (!empty($nav_add_user)): ?>
					<div class="button" style="margin-right:10px;">
						<button type="button" class="btn btn-sm btn-success"
								data-toggle="modal" data-target="#createModal">Add User
						</button>
					</div>
				<?php endif; ?>
				<div class="button">
					<a href="logout.php?status=logged-in" class="btn btn-sm btn-info">Logout</a>
				</div>
			</div>
		</div>
	</div>
</div><!--End .conatiner -->