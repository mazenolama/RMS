<?php

//user_action.php

include('rms.php');

$object = new rms();

if(isset($_POST["action"]))
{
	if($_POST["action"] == 'fetch')
	{
		$order_column = array('user_name', 'user_contact_no', 'user_email', 'user_password', 'user_type', 'user_status', 'user_created_on');

		$output = array();

		$main_query = "
		SELECT * FROM user_table 
		WHERE user_type != 'Master' 
		
		";

		$search_query = '';

		if(isset($_POST["search"]["value"]))
		{
			$search_query .= 'AND (user_name LIKE "%'.$_POST["search"]["value"].'%" ';
			$search_query .= 'OR user_contact_no LIKE "%'.$_POST["search"]["value"].'%" ';
			$search_query .= 'OR user_email LIKE "%'.$_POST["search"]["value"].'%" ';
			$search_query .= 'OR user_password LIKE "%'.$_POST["search"]["value"].'%" ';
			$search_query .= 'OR user_type LIKE "%'.$_POST["search"]["value"].'%" ';
			$search_query .= 'OR user_status LIKE "%'.$_POST["search"]["value"].'%" ';
			$search_query .= 'OR user_created_on LIKE "%'.$_POST["search"]["value"].'%") ';
		}

		if(isset($_POST["order"]))
		{
			$order_query = 'ORDER BY '.$order_column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'].' ';
		}
		else
		{
			$order_query = 'ORDER BY user_id DESC ';
		}

		$limit_query = '';

		if($_POST["length"] != -1)
		{
			$limit_query .= 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
		}

		$object->query = $main_query . $search_query . $order_query;

		$object->execute();

		$filtered_rows = $object->row_count();

		$object->query .= $limit_query;

		$result = $object->get_result();

		$object->query = $main_query;

		$object->execute();

		$total_rows = $object->row_count();

		$data = array();

		foreach($result as $row)
		{
			$sub_array = array();
			$sub_array[] = '<img src="'.$row["user_profile"].'" class="img-fluid img-thumbnail" width="75" height="75" />';
			$sub_array[] = html_entity_decode($row["user_name"]);
			$sub_array[] = $row["user_contact_no"];
			$sub_array[] = $row["user_email"];
			$sub_array[] = $row["user_password"];
			$sub_array[] = $row["user_type"];
			$sub_array[] = $row["user_created_on"];
			$status = '';
			$delete_button = '';
			if($row["user_status"] == 'Enable')
			{
				$delete_button = '<button type="button" name="delete_button" class="btn btn-danger btn-circle btn-sm delete_button" data-id="'.$row["user_id"].'" data-status="'.$row["user_status"].'"><i class="fas fa-times"></i></button>';
				$status = '<button type="button" name="status_button" class="btn btn-primary btn-sm">Enable</button>';
			}
			else
			{
				$delete_button = '<button type="button" name="delete_button" class="btn btn-danger btn-circle btn-sm delete_button" data-id="'.$row["user_id"].'" data-status="'.$row["user_status"].'"><i class="fas fa-check"></i></button>';
				$status = '<button type="button" name="status_button" class="btn btn-danger btn-sm">Disable</button>';
			}
			$sub_array[] = $status;
			$sub_array[] = '
			<div align="center">
			<button type="button" name="edit_button" class="btn btn-warning btn-circle btn-sm edit_button" data-id="'.$row["user_id"].'"><i class="fas fa-edit"></i></button>
			&nbsp;
			'.$delete_button.'
			</div>';
			$data[] = $sub_array;
		}

		$output = array(
			"draw"    			=> 	intval($_POST["draw"]),
			"recordsTotal"  	=>  $total_rows,
			"recordsFiltered" 	=> 	$filtered_rows,
			"data"    			=> 	$data
		);
			
		echo json_encode($output);

	}

	if($_POST["action"] == 'Add')
	{
		$error = '';

		$success = '';

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
			$error = '<div class="alert alert-danger">User Email Already Exists</div>';
		}
		else
		{
			$user_image = '';
			if($_FILES["user_image"]["name"] != '')
			{
				$user_image = upload_image();
			}
			else
			{
				$user_image = make_avatar(strtoupper($_POST["user_name"][0]));
			}

			$data = array(
				':user_name'		=>	$_POST["user_name"],
				':user_contact_no'	=>	$_POST["user_contact_no"],
				':user_email'		=>	$_POST["user_email"],
				':user_password'	=>	$_POST["user_password"],
				':user_profile'		=>	$user_image,
				':user_type'		=>	$_POST["user_type"],
				':user_status'		=>	'Enable',
				':user_created_on'	=>	$object->get_datetime()
			);

			$object->query = "
			INSERT INTO user_table 
			(user_name, user_contact_no, user_email, user_password, user_profile, user_type, user_status, user_created_on) 
			VALUES (:user_name, :user_contact_no, :user_email, :user_password, :user_profile, :user_type, :user_status, :user_created_on)
			";

			$object->execute($data);

			$success = '<div class="alert alert-success">User Added</div>';
		}

		$output = array(
			'error'		=>	$error,
			'success'	=>	$success
		);

		echo json_encode($output);

	}

	if($_POST["action"] == 'fetch_single')
	{
		$object->query = "
		SELECT * FROM user_table 
		WHERE user_id = '".$_POST["user_id"]."'
		";

		$result = $object->get_result();

		$data = array();

		foreach($result as $row)
		{
			$data['user_name'] = $row['user_name'];
			$data['user_contact_no'] = $row['user_contact_no'];
			$data['user_email'] = $row['user_email'];
			$data['user_password'] = $row['user_password'];
			$data['user_profile'] = $row['user_profile'];
			$data['user_type'] = $row['user_type'];
		}

		echo json_encode($data);
	}

	if($_POST["action"] == 'Edit')
	{
		$error = '';

		$success = '';

		$data = array(
			':user_email'	=>	$_POST["user_email"],
			':user_id'		=>	$_POST['hidden_id']
		);

		$object->query = "
		SELECT * FROM user_table 
		WHERE user_email = :user_email 
		AND user_id != :user_id
		";

		$object->execute($data);

		if($object->row_count() > 0)
		{
			$error = '<div class="alert alert-danger">User Email Already Exists</div>';
		}
		else
		{
			$user_image = $_POST["hidden_user_image"];
			if($_FILES["user_image"]["name"] != '')
			{
				$user_image = upload_image();
			}

			$data = array(
				':user_name'		=>	$_POST["user_name"],
				':user_contact_no'	=>	$_POST["user_contact_no"],
				':user_email'		=>	$_POST["user_email"],
				':user_password'	=>	$_POST["user_password"],
				':user_profile'		=>	$user_image,
				':user_type'		=>	$_POST["user_type"]
			);

			$object->query = "
			UPDATE user_table 
			SET user_name = :user_name, 
			user_contact_no = :user_contact_no, 
			user_email = :user_email, 
			user_password = :user_password, 
			user_profile = :user_profile, 
			user_type = :user_type 
			WHERE user_id = '".$_POST['hidden_id']."'
			";

			$object->execute($data);

			$success = '<div class="alert alert-success">User Details Updated</div>';
		}

		$output = array(
			'error'		=>	$error,
			'success'	=>	$success
		);

		echo json_encode($output);

	}

	if($_POST["action"] == 'delete')
	{
		$data = array(
			':user_status'		=>	$_POST['next_status']
		);

		$object->query = "
		UPDATE user_table 
		SET user_status = :user_status 
		WHERE user_id = '".$_POST["id"]."'
		";

		$object->execute($data);

		echo '<div class="alert alert-success">User Status change to '.$_POST['next_status'].'</div>';
	}

	if($_POST["action"] == 'profile')
	{
		sleep(2);

		$error = '';

		$success = '';

		$user_image = '';

		$data = array(
			':user_email'	=>	$_POST["user_email"],
			':user_id'		=>	$_SESSION['user_id']
		);

		$object->query = "
		SELECT * FROM user_table 
		WHERE user_email = :user_email 
		AND user_id != :user_id
		";

		$object->execute($data);

		if($object->row_count() > 0)
		{
			$error = '<div class="alert alert-danger">Email Already Exists</div>';
		}
		else
		{
			$user_image = $_POST["hidden_user_profile"];
			if($_FILES["user_image"]["name"] != '')
			{
				$user_image = upload_image();
			}

			$data = array(
				':user_name'		=>	$_POST['user_name'],
				':user_contact_no'	=>	$_POST['user_contact_no'],
				':user_email'		=>	$_POST['user_email'],
				':user_password'	=>	$_POST['user_password'],
				':user_profile'		=>	$user_image
			);

			$object->query = "
			UPDATE user_table 
			SET user_name = :user_name, 
			user_contact_no = :user_contact_no, 
			user_email = :user_email,  
			user_password = :user_password, 
			user_profile = :user_profile 
			WHERE user_id = '".$_SESSION['user_id']."'
			";

			$object->execute($data);

			$success = '<div class="alert alert-success">User Details Updated</div>';
		}

		$output = array(
			'error'				=>	$error,
			'success'			=>	$success,
			'user_name'			=>	$_POST["user_name"],
			'user_contact_no'	=>	$_POST["user_contact_no"],
			'user_email'		=>	$_POST["user_email"],
			'user_password'		=>	$_POST["user_password"],
			'user_profile'		=>	$user_image
		);

		echo json_encode($output);
	}

	/*if($_POST["action"] == 'change_password')
	{
		$error = '';
		$success = '';
		$visitor->query = "
		SELECT admin_password FROM admin_table 
		WHERE admin_id = '".$_SESSION["admin_id"]."'
		";

		$result = $visitor->get_result();

		foreach($result as $row)
		{
			if(password_verify($_POST["current_password"], $row["admin_password"]))
			{
				$data = array(
					':admin_password'	=>	password_hash($_POST["new_password"], PASSWORD_DEFAULT)
				);
				$visitor->query = "
				UPDATE admin_table 
				SET admin_password = :admin_password 
				WHERE admin_id = '".$_SESSION["admin_id"]."'
				";

				$visitor->execute($data);

				$success = '<div class="alert alert-success">Password Change Successfully</div>';
			}
			else
			{
				$error = '<div class="alert alert-danger">You have enter wrong current password</div>';
			}
		}
		$output = array(
			'error'		=>	$error,
			'success'	=>	$success
		);
		echo json_encode($output);
	}*/
}

function upload_image()
{
	if(isset($_FILES["user_image"]))
	{
		$extension = explode('.', $_FILES['user_image']['name']);
		$new_name = rand() . '.' . $extension[1];
		$destination = 'images/' . $new_name;
		move_uploaded_file($_FILES['user_image']['tmp_name'], $destination);
		return $destination;
	}
}

function make_avatar($character)
{
    $path = "images/". time() . ".png";
	$image = imagecreate(200, 200);
	$red = rand(0, 255);
	$green = rand(0, 255);
	$blue = rand(0, 255);
    imagecolorallocate($image, 230, 230, 230);  
    $textcolor = imagecolorallocate($image, $red, $green, $blue);
    imagettftext($image, 100, 0, 55, 150, $textcolor, 'font/arial.ttf', $character);
    imagepng($image, $path);
    imagedestroy($image);
    return $path;
}

?>