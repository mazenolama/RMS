<?php

//order_action.php

include('rms.php');

$object = new rms();

if(isset($_POST["action"]))
{
	if($_POST["action"] == 'reset')
	{
		$object->query = "
		SELECT * FROM table_data 
		WHERE table_status = 'Enable' 
		ORDER BY table_id ASC
		";

		$table_result = $object->get_result();

		$html = '';

		foreach($table_result as $table)
		{
			$object->query = "
			SELECT * FROM order_table 
			WHERE order_table = '".$table['table_name']."' 
			AND order_status = 'In Process'
			";
			
			$object->execute();

			if($object->row_count() > 0)
			{
				$order_result = $object->statement_result();
				foreach($order_result as $order)
				{
					$html .= '
					<button type="button" name="table_button" id="table_'.$table["table_id"].'" class="btn btn-warning mb-4 table_button" data-index="'.$table["table_id"].'" data-order_id="'.$order["order_id"].'" data-table_name="'.$table["table_name"].'">'.$table["table_name"].'<br />'.$table["table_capacity"].' Person</button>
					';
				}
			}
			else
			{
				$html .= '
				<button type="button" name="table_button" id="table_'.$table["table_id"].'" class="btn btn-secondary mb-4 table_button" data-index="'.$table["table_id"].'" data-order_id="0" data-table_name="'.$table["table_name"].'">'.$table["table_name"].'<br />'.$table["table_capacity"].' Person</button>
				';
			}
		}
		echo $html;
	}

	if($_POST["action"] == 'load_product')
	{
		$object->query = "
		SELECT * FROM product_table 
		WHERE category_name = '".$_POST['category_name']."' 
		AND product_status = 'Enable'
		";
		$result = $object->get_result();
		$html = '<option value="">Select Product</option>';
		foreach($result as $row)
		{
			$html .= '<option value="'.$row["product_name"].'" data-price="'.$row["product_price"].'">'.$row["product_name"].'</option>';
		}
		echo $html;
	}

	if($_POST["action"] == 'Add')
	{
		if($_POST['hidden_order_id'] > 0)
		{
			$product_amount = $_POST['product_quantity'] * $_POST['hidden_product_rate'];

			$item_data = array(
				':order_id'			=>	$_POST['hidden_order_id'],
				':product_name'		=>	$_POST['product_name'],
				':product_quantity'	=>	$_POST['product_quantity'],
				':product_rate'		=>	$_POST['hidden_product_rate'],
				':product_amount'	=>	$product_amount
			);

			$object->query = "
			INSERT INTO order_item_table 
			(order_id, product_name, product_quantity, product_rate, product_amount) 
			VALUES (:order_id, :product_name, :product_quantity, :product_rate, :product_amount)
			";
			$object->execute($item_data);
			echo $_POST['hidden_order_id'];
		}
		else
		{
			$order_data = array(
				':order_number'			=>	$object->Generate_order_no(),
				':order_table'			=>	$_POST['hidden_table_name'],
				':order_gross_amount'	=>	0,
				':order_tax_amount'		=>	0,
				':order_net_amount'		=>	0,
				':order_date'			=>	date('Y-m-d'),
				':order_time'			=>	date('H:i:s'),
				':order_waiter'			=>	$object->Get_user_name($_SESSION['user_id']),
				':order_cashier'		=>	'',
				':order_status'			=>	'In Process'
			);

			$object->query = "
			INSERT INTO order_table 
			(order_number, order_table, order_gross_amount, order_tax_amount, order_net_amount, order_date, order_time, order_waiter, order_cashier, order_status) 
			VALUES (:order_number, :order_table, :order_gross_amount, :order_tax_amount, :order_net_amount, :order_date, :order_time, :order_waiter, :order_cashier, :order_status)
			";
			$object->execute($order_data);

			$order_id = $object->connect->lastInsertId();

			$product_amount = $_POST['product_quantity'] * $_POST['hidden_product_rate'];

			$item_data = array(
				':order_id'			=>	$order_id,
				':product_name'		=>	$_POST['product_name'],
				':product_quantity'	=>	$_POST['product_quantity'],
				':product_rate'		=>	$_POST['hidden_product_rate'],
				':product_amount'	=>	$product_amount
			);

			$object->query = "
			INSERT INTO order_item_table 
			(order_id, product_name, product_quantity, product_rate, product_amount) 
			VALUES (:order_id, :product_name, :product_quantity, :product_rate, :product_amount)
			";
			$object->execute($item_data);
			echo $order_id;
		}
	}

	if($_POST["action"] == "fetch_order")
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
				<th>Item Name</th>
				<th>Quantity</th>
				<th>Rate</th>
				<th>Amount</th>
				<th>Action</th>
			</tr>
		';
		foreach($result as $row)
		{
			$html .= '
			<tr>
				<td>'.$row["product_name"].'</td>
				<td><input type="number" class="form-control product_quantity" data-item_id="'.$row["order_item_id"].'" data-order_id="'.$row["order_id"].'" data-rate="'.$row["product_rate"].'" min="1" max="25" value="'.$row["product_quantity"].'" /></td>
				<td>'.$object->cur . $row["product_rate"].'</td>
				<td><span id="product_amount_'.$row["order_item_id"].'">'.$object->cur . $row["product_amount"].'</span></td>
				<td><button type="button" name="remove" class="btn btn-danger btn-sm remove_item" data-item_id="'.$row["order_item_id"].'" data-order_id="'.$row["order_id"].'"><i class="fas fa-minus-square"></i></button></td>
			</tr>
			';
		}
		$html .= '
		</table>
		';
		echo $html;
	}

	if($_POST['action'] == 'change_quantity')
	{
		$object->query = "
		UPDATE order_item_table 
		SET product_quantity = '".$_POST["quantity"]."', 
		product_amount = '".$_POST["quantity"] * $_POST["rate"]."' 
		WHERE order_id = '".$_POST["order_id"]."' 
		AND order_item_id = '".$_POST["item_id"]."'
		";
		$object->execute();
	}

	if($_POST['action'] == 'remove_item')
	{
		$object->query = "
		DELETE FROM order_item_table 
		WHERE order_id = '".$_POST["order_id"]."' 
		AND order_item_id = '".$_POST["item_id"]."'
		";

		$object->execute();

		$object->query = "
		SELECT order_item_id FROM order_item_table 
		WHERE order_id = '".$_POST["order_id"]."'
		";

		$object->execute();

		echo $object->row_count();

		if($object->row_count() == 0)
		{
			$object->query = "
			DELETE FROM order_table 
			WHERE order_id = '".$_POST["order_id"]."'
			";
			$object->execute();
		}
	}

	if($_POST["action"] == 'dashboard_reset')
	{
		$object->query = "
		SELECT * FROM table_data 
		WHERE table_status = 'Enable' 
		ORDER BY table_id ASC
		";

		$table_result = $object->get_result();

		$html = '<div class="row">';

		foreach($table_result as $table)
		{
			$object->query = "
			SELECT * FROM order_table 
			WHERE order_table = '".$table['table_name']."' 
			AND order_status = 'In Process'
			";
			
			$object->execute();

			if($object->row_count() > 0)
			{
				$order_result = $object->statement_result();
				foreach($order_result as $order)
				{
					$html .= '
					<div class="col-lg-2 mb-3">
						<div class="card bg-info text-white shadow">
							<div class="card-body">
								'.$table["table_name"].'
								<div class="mt-1 text-white-50 small">Booked</div>
							</div>
						</div>
					</div>
					';
				}
			}
			else
			{
				$html .= '
				<div class="col-lg-2 mb-3">
					<div class="card bg-light text-black shadow">
						<div class="card-body">
							'.$table["table_name"].'
							<div class="mt-1 text-black-50 small">'.$table["table_capacity"].' Person</div>
						</div>
					</div>
				</div>
				';
			}
		}
		echo $html;
	}

}

?>