<?php

//discount_action.php

include('rms.php');

$object = new rms();

if(isset($_POST["action"]))
{
	if($_POST["action"] == 'fetch')
	{
		$order_column = array('discount_name', 'discount_percentage', 'discount_status');

		$output = array();

		$main_query = "
		SELECT * FROM discount_table ";

		$search_query = '';

		if(isset($_POST["search"]["value"]))
		{
			$search_query .= 'WHERE discount_name LIKE "%'.$_POST["search"]["value"].'%" ';
			$search_query .= 'OR discount_percentage LIKE "%'.$_POST["search"]["value"].'%" ';
			$search_query .= 'OR discount_status LIKE "%'.$_POST["search"]["value"].'%" ';
		}

		if(isset($_POST["order"]))
		{
			$order_query = 'ORDER BY '.$order_column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'].' ';
		}
		else
		{
			$order_query = 'ORDER BY discount_id DESC ';
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
			$sub_array[] = html_entity_decode($row["discount_name"]);
			$sub_array[] = $row["discount_percentage"] . '%';
			$status = '';
			if($row["discount_status"] == 'Enable')
			{
				$status = '<button type="button" name="status_button" class="btn btn-primary btn-sm status_button" data-id="'.$row["discount_id"].'" data-status="'.$row["discount_status"].'">Enable</button>';
			}
			else
			{
				$status = '<button type="button" name="status_button" class="btn btn-danger btn-sm status_button" data-id="'.$row["discount_id"].'" data-status="'.$row["discount_status"].'">Disable</button>';
			}
			$sub_array[] = $status;
			$sub_array[] = '
			<div class="btnAction" align="center">
			<button type="button" name="edit_button" class="btn btn-warning btn-circle btn-sm edit_button" data-id="'.$row["discount_id"].'"><i class="fas fa-edit"></i></button>
			&nbsp;
			<button type="button" name="delete_button" class="btn btn-danger btn-circle btn-sm delete_button" data-id="'.$row["discount_id"].'"><i class="fas fa-times"></i></button>
			</div>
			';
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
			':discount_name'	=>	$_POST["discount_name"]
		);

		$object->query = "
		SELECT * FROM discount_table 
		WHERE discount_name = :discount_name
		";

		$object->execute($data);

		if($object->row_count() > 0)
		{
			$error = '<div class="alert alert-danger">Discount Already Exists</div>';
		}
		else
		{
			$data = array(
				':discount_name'			=>	$object->clean_input($_POST["discount_name"]),
				':discount_percentage'	=>	$object->clean_input($_POST["discount_percentage"]),
				':discount_status'		=>	'Enable',
			);

			$object->query = "
			INSERT INTO discount_table 
			(discount_name, discount_percentage, discount_status) 
			VALUES (:discount_name, :discount_percentage, :discount_status)
			";

			$object->execute($data);

			$success = '<div class="alert alert-success">Discount Added</div>';
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
		SELECT * FROM discount_table 
		WHERE discount_id = '".$_POST["discount_id"]."'
		";

		$result = $object->get_result();

		$data = array();

		foreach($result as $row)
		{
			$data['discount_name'] = $row['discount_name'];
			$data['discount_percentage'] = $row['discount_percentage'];
		}

		echo json_encode($data);
	}

	if($_POST["action"] == 'Edit')
	{
		$error = '';

		$success = '';

		$data = array(
			':discount_name'		=>	$_POST["discount_name"],
			':discount_id'		=>	$_POST['hidden_id']
		);

		$object->query = "
		SELECT * FROM discount_table 
		WHERE discount_name = :discount_name 
		AND discount_id != :discount_id
		";

		$object->execute($data);

		if($object->row_count() > 0)
		{
			$error = '<div class="alert alert-danger">Discount Already Exists</div>';
		}
		else
		{

			$data = array(
				':discount_name'			=>	$object->clean_input($_POST["discount_name"]),
				':discount_percentage'	=>	$object->clean_input($_POST["discount_percentage"]),
			);

			$object->query = "
			UPDATE discount_table 
			SET discount_name = :discount_name, 
			discount_percentage = :discount_percentage  
			WHERE discount_id = '".$_POST['hidden_id']."'
			";

			$object->execute($data);

			$success = '<div class="alert alert-success">Discount Updated</div>';
		}

		$output = array(
			'error'		=>	$error,
			'success'	=>	$success
		);

		echo json_encode($output);

	}

	if($_POST["action"] == 'change_status')
	{
		$data = array(
			':discount_status'		=>	$_POST['next_status']
		);

		$object->query = "
		UPDATE discount_table 
		SET discount_status = :discount_status 
		WHERE discount_id = '".$_POST["id"]."'
		";

		$object->execute($data);

		echo '<div class="alert alert-success">Discount Status change to '.$_POST['next_status'].'</div>';
	}

	if($_POST["action"] == 'delete')
	{
		$object->query = "
		DELETE FROM discount_table 
		WHERE discount_id = '".$_POST["id"]."'
		";

		$object->execute();

		echo '<div class="alert alert-success">Discount Deleted</div>';
	}
}

?>