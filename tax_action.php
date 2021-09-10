<?php

//tax_action.php

include('rms.php');

$object = new rms();

if(isset($_POST["action"]))
{
	if($_POST["action"] == 'fetch')
	{
		$order_column = array('tax_name', 'tax_percentage', 'tax_status');

		$output = array();

		$main_query = "
		SELECT * FROM tax_table ";

		$search_query = '';

		if(isset($_POST["search"]["value"]))
		{
			$search_query .= 'WHERE tax_name LIKE "%'.$_POST["search"]["value"].'%" ';
			$search_query .= 'OR tax_percentage LIKE "%'.$_POST["search"]["value"].'%" ';
			$search_query .= 'OR tax_status LIKE "%'.$_POST["search"]["value"].'%" ';
		}

		if(isset($_POST["order"]))
		{
			$order_query = 'ORDER BY '.$order_column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'].' ';
		}
		else
		{
			$order_query = 'ORDER BY tax_id DESC ';
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
			$sub_array[] = html_entity_decode($row["tax_name"]);
			$sub_array[] = $row["tax_percentage"] . '%';
			$status = '';
			if($row["tax_status"] == 'Enable')
			{
				$status = '<button type="button" name="status_button" class="btn btn-primary btn-sm status_button" data-id="'.$row["tax_id"].'" data-status="'.$row["tax_status"].'">Enable</button>';
			}
			else
			{
				$status = '<button type="button" name="status_button" class="btn btn-danger btn-sm status_button" data-id="'.$row["tax_id"].'" data-status="'.$row["tax_status"].'">Disable</button>';
			}
			$sub_array[] = $status;
			$sub_array[] = '
			<div align="center">
			<button type="button" name="edit_button" class="btn btn-warning btn-circle btn-sm edit_button" data-id="'.$row["tax_id"].'"><i class="fas fa-edit"></i></button>
			&nbsp;
			<button type="button" name="delete_button" class="btn btn-danger btn-circle btn-sm delete_button" data-id="'.$row["tax_id"].'"><i class="fas fa-times"></i></button>
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
			':tax_name'	=>	$_POST["tax_name"]
		);

		$object->query = "
		SELECT * FROM tax_table 
		WHERE tax_name = :tax_name
		";

		$object->execute($data);

		if($object->row_count() > 0)
		{
			$error = '<div class="alert alert-danger">Tax Already Exists</div>';
		}
		else
		{
			$data = array(
				':tax_name'			=>	$object->clean_input($_POST["tax_name"]),
				':tax_percentage'	=>	$object->clean_input($_POST["tax_percentage"]),
				':tax_status'		=>	'Enable',
			);

			$object->query = "
			INSERT INTO tax_table 
			(tax_name, tax_percentage, tax_status) 
			VALUES (:tax_name, :tax_percentage, :tax_status)
			";

			$object->execute($data);

			$success = '<div class="alert alert-success">Tax Added</div>';
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
		SELECT * FROM tax_table 
		WHERE tax_id = '".$_POST["tax_id"]."'
		";

		$result = $object->get_result();

		$data = array();

		foreach($result as $row)
		{
			$data['tax_name'] = $row['tax_name'];
			$data['tax_percentage'] = $row['tax_percentage'];
		}

		echo json_encode($data);
	}

	if($_POST["action"] == 'Edit')
	{
		$error = '';

		$success = '';

		$data = array(
			':tax_name'		=>	$_POST["tax_name"],
			':tax_id'		=>	$_POST['hidden_id']
		);

		$object->query = "
		SELECT * FROM tax_table 
		WHERE tax_name = :tax_name 
		AND tax_id != :tax_id
		";

		$object->execute($data);

		if($object->row_count() > 0)
		{
			$error = '<div class="alert alert-danger">Tax Already Exists</div>';
		}
		else
		{

			$data = array(
				':tax_name'			=>	$object->clean_input($_POST["tax_name"]),
				':tax_percentage'	=>	$object->clean_input($_POST["tax_percentage"]),
			);

			$object->query = "
			UPDATE tax_table 
			SET tax_name = :tax_name, 
			tax_percentage = :tax_percentage  
			WHERE tax_id = '".$_POST['hidden_id']."'
			";

			$object->execute($data);

			$success = '<div class="alert alert-success">Tax Updated</div>';
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
			':tax_status'		=>	$_POST['next_status']
		);

		$object->query = "
		UPDATE tax_table 
		SET tax_status = :tax_status 
		WHERE tax_id = '".$_POST["id"]."'
		";

		$object->execute($data);

		echo '<div class="alert alert-success">Tax Status change to '.$_POST['next_status'].'</div>';
	}

	if($_POST["action"] == 'delete')
	{
		$object->query = "
		DELETE FROM tax_table 
		WHERE tax_id = '".$_POST["id"]."'
		";

		$object->execute();

		echo '<div class="alert alert-success">Tax Deleted</div>';
	}
}

?>