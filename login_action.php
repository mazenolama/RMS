<?php

//login_action.php

include('rms.php');

$object = new rms();

if(isset($_POST["user_email"]))
{
	sleep(2);
	$error = '';
	$data = array(
		':user_email'	=>	$_POST["user_email"]
	);

	$object->query = "
		SELECT * FROM user_table 
		WHERE user_email = :user_email
	";

	$object->execute($data);

	$total_row = $object->row_count();

	if($total_row == 0)
	{
		$error = '<div class="alert alert-danger">Wrong Email Address</div>';
	}
	else
	{
		//$result = $statement->fetchAll();

		$result = $object->statement_result();

		foreach($result as $row)
		{
			if($row["user_status"] == 'Enable')
			{
				if($_POST["user_password"] == $row["user_password"])
				{
					$_SESSION['user_id'] = $row['user_id'];
					$_SESSION['user_type'] = $row['user_type'];
				}
				else
				{
					$error = '<div class="alert alert-danger">Wrong Password</div>';
				}
			}
			else
			{
				$error = '<div class="alert alert-danger">Sorry, Your account has been disable, contact Admin</div>';
			}
		}
	}

	$output = array(
		'error'		=>	$error
	);

	echo json_encode($output);
}

?>