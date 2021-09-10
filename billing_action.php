<?php

//billing_action.php

include('rms.php');

$object = new rms();

if(isset($_POST["action"]))
{
	if($_POST["action"] == 'fetch')
	{
		$order_column = array('order_table', 'order_number', 'order_date', 'order_time', 'order_waiter', 'order_status');

		$output = array();

		$main_query = "
		SELECT * FROM order_table ";

		$search_query = '';

		if(isset($_POST["search"]["value"]))
		{
			$search_query .= 'WHERE order_table LIKE "%'.$_POST["search"]["value"].'%" ';
			$search_query .= 'OR order_number LIKE "%'.$_POST["search"]["value"].'%" ';
			$search_query .= 'OR order_date LIKE "%'.$_POST["search"]["value"].'%" ';
			$search_query .= 'OR order_time LIKE "%'.$_POST["search"]["value"].'%" ';
			$search_query .= 'OR order_waiter LIKE "%'.$_POST["search"]["value"].'%" ';
			$search_query .= 'OR order_status LIKE "%'.$_POST["search"]["value"].'%" ';
		}

		if(isset($_POST["order"]))
		{
			$order_query = 'ORDER BY '.$order_column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'].' ';
		}
		else
		{
			$order_query = 'ORDER BY order_id DESC ';
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
			$sub_array[] = $row["order_table"];
			$sub_array[] = $row["order_number"];
			$sub_array[] = $row["order_date"];
			$sub_array[] = $row["order_time"];
			$sub_array[] = $row["order_waiter"];
			if($object->is_master_user())
			{
				$sub_array[] = $row["order_cashier"];
			}
			$status = '';
			$print = '';
			if($row["order_status"] == 'In Process')
			{
				$status = '<button type="button" name="status_button" class="btn btn-warning btn-sm">In Process</button>';
				$print = '';
			}
			else
			{
				$status = '<button type="button" name="status_button" class="btn btn-success btn-sm">Completed</button>';
				$print = '<a href="print.php?action=print&order_id='.$row["order_id"].'" class="btn btn-warning btn-sm btn-circle"><i class="fas fa-file-pdf"></i></a>&nbsp;';
			}
			$sub_array[] = $status;
			$sub_array[] = '
			<div align="center">
			<button type="button" name="view_button" class="btn btn-primary btn-circle btn-sm view_button" data-id="'.$row["order_id"].'"><i class="fas fa-eye"></i></button>
			&nbsp;
			'.$print.'
			<button type="button" name="delete_button" class="btn btn-danger btn-circle btn-sm delete_button" data-id="'.$row["order_id"].'"><i class="fas fa-times"></i></button>
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

	if($_POST["action"] == 'fetch_single')
	{
		$object->query = "
		SELECT * FROM order_item_table 
		WHERE order_id = '".$_POST['order_id']."' 
		ORDER BY order_item_id ASC
		";
		$result = $object->get_result();
		$html = '
		<table class="table table-striped table-bordered">
			<tr>
				<th>Sr#</th>
				<th>Item Name</th>
				<th>Quantity</th>
				<th>Rate</th>
				<th>Amount</th>
				<th>Action</th>
			</tr>
		';
		$count = 1;
		$gross_total = 0;
		$total_tax_amt = 0;
		$net_total = 0;
		foreach($result as $row)
		{
			$html .= '
			<tr>
				<td>'.$count.'</td>
				<td>'.$row["product_name"].'</td>
				<td><input type="number" class="form-control product_quantity" data-item_id="'.$row["order_item_id"].'" data-order_id="'.$row["order_id"].'" data-rate="'.$row["product_rate"].'" min="1" max="25" value="'.$row["product_quantity"].'" /></td>
				<td>'.$object->cur . $row["product_rate"].'</td>
				<td><span id="product_amount_'.$row["order_item_id"].'">'.$object->cur . $row["product_amount"].'</span></td>
				<td><button type="button" name="remove" class="btn btn-danger btn-sm remove_item" data-item_id="'.$row["order_item_id"].'" data-order_id="'.$row["order_id"].'"><i class="fas fa-minus-square"></i></button></td>
			</tr>
			';
			$count++;
			$gross_total += $row["product_amount"];
		}

		$html .= '
			<tr>
				<td colspan="4" class="text-right"><b>Total</b></td>
				<td colspan="2">'.$object->cur . number_format((float)$gross_total, 2, '.', '').'</td>
			</tr>
		';

		$object->query = "
		SELECT * FROM tax_table 
		WHERE tax_status = 'Enable' 
		ORDER BY tax_id ASC
		";

		$tax_result = $object->get_result();

		$object->query = "
		DELETE FROM order_tax_table 
		WHERE order_id = '".$_POST['order_id']."'
		";

		$object->execute();

		foreach($tax_result as $tax)
		{
			$tax_amt = ($gross_total * $tax["tax_percentage"])/100;
			$html .= '
			<tr>
				<td colspan="4" class="text-right"><b>'.$tax["tax_name"].' ('.$tax["tax_percentage"].'%)</b></td>
				<td colspan="2">'.$object->cur . number_format((float)$tax_amt, 2, '.', '').'</td>
			</tr>
			';
			$total_tax_amt += $tax_amt;

			$tax_data = array(
				':order_id'				=>	$_POST['order_id'],
				':order_tax_name'		=>	$tax["tax_name"],
				':order_tax_percentage'	=>	$tax["tax_percentage"],
				':order_tax_amount'		=>	$tax_amt
			);

			$object->query = "
			INSERT INTO order_tax_table 
			(order_id, order_tax_name, order_tax_percentage, order_tax_amount) 
			VALUES (:order_id, :order_tax_name, :order_tax_percentage, :order_tax_amount)
			";

			$object->execute($tax_data);
		}

		$net_total = $gross_total + $total_tax_amt;

		$order_data = array(
			':order_gross_amount'	=>	$gross_total,
			':order_tax_amount'		=>	$total_tax_amt,
			':order_net_amount'		=>	$net_total,
			':order_cashier'		=>	$object->Get_user_name($_SESSION['user_id'])
		);

		$object->query = "
		UPDATE order_table 
		SET order_gross_amount = :order_gross_amount, 
		order_tax_amount = :order_tax_amount, 
		order_net_amount = :order_net_amount, 
		order_cashier = :order_cashier 
		WHERE order_id = '".$_POST["order_id"]."'
		";

		$object->execute($order_data);

		$html .= '
			<tr>
				<td colspan="4" class="text-right"><b>Net Amount</b></td>
				<td colspan="2">'.$object->cur . number_format((float)$net_total, 2, '.', '').'</td>
			</tr>
		';

		$html .= '
		</table>
		';

		echo $html;
	}

	if($_POST["action"] == 'Edit')
	{
		$order_data = array(
			':order_date'		=>	date('Y-m-d'),
			':order_time'		=>	date('H:i:s'),
			':order_cashier'	=>	$object->Get_user_name($_SESSION['user_id']),
			':order_status'		=>	'Completed'
		);

		$object->query = "
		UPDATE order_table 
		SET order_date = :order_date, 
		order_time = :order_time, 
		order_cashier = :order_cashier, 
		order_status = :order_status 
		WHERE order_id = '".$_POST["hidden_order_id"]."'
		";

		$object->execute($order_data);

		echo $_POST["hidden_order_id"];
	}

	if($_POST["action"] == 'remove_bill')
	{
		$object->query = "
		DELETE FROM order_table 
		WHERE order_id = '".$_POST["order_id"]."'
		";
		$object->execute();

		$object->query = "
		DELETE FROM order_item_table 
		WHERE order_id = '".$_POST["order_id"]."'
		";
		$object->execute();

		$object->query = "
		DELETE FROM order_tax_table 
		WHERE order_id = '".$_POST["order_id"]."'
		";
		$object->execute();
		echo '<div class="alert alert-success">Order Remove Successfully...</div>';
	}
}

?>