<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');
 header('Content-Type: application/json');
//table_action.php

include('rms.php');

$object = new rms();

if(isset($_POST["action"]))
{
	if($_POST["action"] == 'fetch')
	{
		$order_column = array('table_name', 'table_capacity', 'table_status');

		$output = array();

		$main_query = "
		SELECT * FROM table_data ";

		$search_query = '';

		if(isset($_POST["search"]["value"]))
		{
			$search_query .= 'WHERE table_name LIKE "%'.$_POST["search"]["value"].'%" ';
			$search_query .= 'OR table_capacity LIKE "%'.$_POST["search"]["value"].'%" ';
			$search_query .= 'OR table_status LIKE "%'.$_POST["search"]["value"].'%" ';
		}

		if(isset($_POST["order"]))
		{
			$order_query = 'ORDER BY '.$order_column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'].' ';
		}
		else
		{
			$order_query = 'ORDER BY table_id DESC ';
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
			$sub_array[] = html_entity_decode($row["table_name"]);
			$sub_array[] = $row["table_capacity"] . ' Person';
			$status = '';
			if($row["table_status"] == 'Enable')
			{
				$status = '<button type="button" name="status_button" class="btn btn-primary btn-sm status_button" data-id="'.$row["table_id"].'" data-status="'.$row["table_status"].'">Enable</button>';
			}
			else
			{
				$status = '<button type="button" name="status_button" class="btn btn-danger btn-sm status_button" data-id="'.$row["table_id"].'" data-status="'.$row["table_status"].'">Disable</button>';
			}
			$sub_array[] = $status;
			$sub_array[] = '
			<div align="center">
			<button type="button" name="edit_button" class="btn btn-warning btn-circle btn-sm edit_button" data-id="'.$row["table_id"].'"><i class="fas fa-edit"></i></button>
			&nbsp;
			<button type="button" name="delete_button" class="btn btn-danger btn-circle btn-sm delete_button" data-id="'.$row["table_id"].'"><i class="fas fa-times"></i></button>
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
			':table_name'	=>	$_POST["table_name"]
		);

		$object->query = "
		SELECT * FROM table_data 
		WHERE table_name = :table_name
		";

		$object->execute($data);

		if($object->row_count() > 0)
		{
			$error = '<div class="alert alert-danger">Table Already Exists</div>';
		}
		else
		{
			$data = array(
				':table_name'			=>	$object->clean_input($_POST["table_name"]),
				':table_capacity'		=>	$object->clean_input($_POST["table_capacity"]),
				':table_status'			=>	'Enable',
			);

			$object->query = "
			INSERT INTO table_data 
			(table_name, table_capacity, table_status) 
			VALUES (:table_name, :table_capacity, :table_status)
			";

			$object->execute($data);

			$success = '<div class="alert alert-success">Table Added</div>';
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
		SELECT * FROM table_data 
		WHERE table_id = '".$_POST["table_id"]."'
		";

		$result = $object->get_result();

		$data = array();

		foreach($result as $row)
		{
			$data['table_name'] = $row['table_name'];
			$data['table_capacity'] = $row['table_capacity'];
		}

		echo json_encode($data);
	}

	if($_POST["action"] == 'Edit')
	{
		$error = '';

		$success = '';

		$data = array(
			':table_name'	=>	$_POST["table_name"],
			':table_id'		=>	$_POST['hidden_id']
		);

		$object->query = "
		SELECT * FROM table_data 
		WHERE table_name = :table_name 
		AND table_id != :table_id
		";

		$object->execute($data);

		if($object->row_count() > 0)
		{
			$error = '<div class="alert alert-danger">Table Already Exists</div>';
		}
		else
		{

			$data = array(
				':table_name'		=>	$object->clean_input($_POST["table_name"])
			);

			$object->query = "
			UPDATE table_data 
			SET table_name = :table_name 
			WHERE table_id = '".$_POST['hidden_id']."'
			";

			$object->execute($data);

			$success = '<div class="alert alert-success">Table Updated</div>';
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
			':table_status'		=>	$_POST['next_status']
		);

		$object->query = "
		UPDATE table_data 
		SET table_status = :table_status 
		WHERE table_id = '".$_POST["id"]."'
		";

		$object->execute($data);

		echo '<div class="alert alert-success">Table Status change to '.$_POST['next_status'].'</div>';
	}

	if($_POST["action"] == 'delete')
	{
		$object->query = "
		DELETE FROM table_data 
		WHERE table_id = '".$_POST["id"]."'
		";

		$object->execute();

		echo '<div class="alert alert-success">Table Deleted</div>';
	}
}

?>