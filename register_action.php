<?php

//register_action.php

include('rms.php');

$object = new rms();

if(isset($_POST["user_email"]))
{
	sleep(2);

	$error = '';

	$message = '';

	$data = array(
		':user_email'	=>	$_POST["user_email"]
	);

	$object->query = "
	SELECT * FROM user_table 
	WHERE user_email = :user_email
	";

	$object->execute($data);

	if($object->row_count() > 0)
	{
		$error = '<div class="alert alert-danger">Email Already Exists</div>';
	}
	else
	{
		$restaurant_data = array(
			':restaurant_name'			=>	$object->clean_input($_POST["restaurant_name"]),
			':restaurant_tag_line'		=>	$object->clean_input($_POST["restaurant_tag_line"]),
			':restaurant_address'		=>	$object->clean_input($_POST["restaurant_address"]),
			':restaurant_contact_no'	=>	$object->clean_input($_POST["restaurant_contact_no"]),
			':restaurant_email'			=>	$_POST["user_email"],
			':restaurant_currency'		=>	$_POST["restaurant_currency"],
			':restaurant_timezone'		=>	$_POST["restaurant_timezone"],
			':restaurant_logo'			=>	''
		);

		$user_profile = $object->make_avatar(strtoupper($_POST["user_email"][0]));

		$master_user_data = array(
			':user_name'				=>	'',
			':user_contact_no'			=>	'',
			':user_email'				=>	$_POST["user_email"],
			':user_password'			=>	$_POST["user_password"],
			':user_profile'				=>	$user_profile,
			':user_type'				=>	'Master',
			':user_status'				=>	'Enable',
			':user_created_on'			=>	$object->get_datetime()
		);

		$object->query = "
		INSERT INTO restaurant_table 
		(restaurant_name, restaurant_tag_line, restaurant_address, restaurant_contact_no, restaurant_email, restaurant_currency, restaurant_timezone, restaurant_logo) 
		VALUES (:restaurant_name, :restaurant_tag_line, :restaurant_address, :restaurant_contact_no, :restaurant_email, :restaurant_currency, :restaurant_timezone, :restaurant_logo)
		";

		$object->execute($restaurant_data);

		$object->query = "
		INSERT INTO user_table 
		(user_name, user_contact_no, user_email, user_password, user_profile, user_type, user_status, user_created_on) 
		VALUES (:user_name, :user_contact_no, :user_email, :user_password, :user_profile, :user_type, :user_status, :user_created_on)
		";

		$object->execute($master_user_data);

		$_SESSION['success'] = '<div class="alert alert-success">Your Account Created, Now you can Login</div>';
	}

	$output = array(
		'error'		=>	$error
	);

	echo json_encode($output);
}

?>