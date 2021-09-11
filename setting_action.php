<?php

//setting_action.php

include('rms.php');

$object = new rms();

if(isset($_POST["restaurant_name"]))
{
	$error = '';

	$success = '';

	$restaurant_logo = $_POST["hidden_restaurant_logo"];
			
	if($_FILES["restaurant_logo"]["name"] != '')
	{
		$restaurant_logo = upload_image();
	}

	$data = array(
		':restaurant_name'		=>	$_POST["restaurant_name"],
		':restaurant_tag_line'	=>	$_POST["restaurant_tag_line"],
		':restaurant_address'	=>	$_POST["restaurant_address"],
		':restaurant_contact_no'=>	$_POST["restaurant_contact_no"],
		':restaurant_email'		=>	$_POST["restaurant_email"],
		':restaurant_currency'	=>	$_POST["restaurant_currency"],
		':restaurant_timezone'	=>	$_POST["restaurant_timezone"],
		':restaurant_logo'		=>	$restaurant_logo
	);
	$object->query = "
	UPDATE restaurant_table 
	SET restaurant_name = :restaurant_name, 
	restaurant_tag_line = :restaurant_tag_line, 
	restaurant_address = :restaurant_address, 
	restaurant_contact_no = :restaurant_contact_no, 
	restaurant_email = :restaurant_email, 
	restaurant_currency = :restaurant_currency, 
	restaurant_timezone = :restaurant_timezone, 
	restaurant_logo = :restaurant_logo
	";

	$object->execute($data);

	$object->query = "SELECT * FROM restaurant_table";

	$result = $object->get_result();

	$data = array();

	foreach($result as $row)
	{
		$data['restaurant_name'] = $row['restaurant_name'];
		$data['restaurant_tag_line'] = $row['restaurant_tag_line'];
		$data['restaurant_address'] = $row['restaurant_address'];
		$data['restaurant_contact_no'] = $row['restaurant_contact_no'];
		$data['restaurant_email'] = $row['restaurant_email'];
		$data['restaurant_currency'] = $row['restaurant_currency'];
		$data['restaurant_timezone'] = $row['restaurant_timezone'];
		$data['restaurant_logo'] = $row['restaurant_logo'];
	}

	$data['success'] = '<div class="alert alert-success">Details Updated Successfully</div>';

	echo json_encode($data);
}

function upload_image()
{
	if(isset($_FILES["restaurant_logo"]))
	{
		$extension = explode('.', $_FILES['restaurant_logo']['name']);
		$new_name = rand() . '.' . $extension[1];
		$destination = 'images/' . $new_name;
		move_uploaded_file($_FILES['restaurant_logo']['tmp_name'], $destination);
		return $destination;
	}
}

?>