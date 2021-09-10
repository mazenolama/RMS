<?php

include('rms.php');

$object = new rms();

if(!$object->is_login())
{
    header("location:".$object->base_url."");
}

if(!$object->is_cashier_user() && !$object->is_master_user())
{
    header("location:".$object->base_url."dashboard.php");
}

include('header.php');

?>

                    <!-- Page Heading -->
                    <h1 class="h3 mb-4 text-gray-800">Billing Management</h1>

                    <!-- DataTales Example -->
                    <span id="message"></span>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                        	<div class="row">
                            	<div class="col">
                            		<h6 class="m-0 font-weight-bold text-primary">Bill List</h6>
                            	</div>
                            	<div class="col" align="right">
                            	</div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="billing_table" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Table Number</th>
                                            <th>Order Number</th>
                                            <th>Order Date</th>
                                            <th>Order Time</th>
                                            <th>Waiter</th>
                                            <?php
                                            if($object->is_master_user())
                                            {
                                                echo '<th>Cashier</th>';
                                            }
                                            ?>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                <?php
                include('footer.php');
                ?>

<div id="billingModal" class="modal fade">
  	<div class="modal-dialog modal-xl">
    	<form method="post" id="billing_form">
      		<div class="modal-content">
        		<div class="modal-header">
          			<h4 class="modal-title" id="modal_title">Bill Details</h4>
          			<button type="button" class="close" data-dismiss="modal">&times;</button>
        		</div>
        		<div class="modal-body">
        			<div id="billing_detail"></div>
        		</div>
        		<div class="modal-footer">
          			<input type="hidden" name="hidden_order_id" id="hidden_order_id" />
          			<input type="hidden" name="action" id="action" value="Edit" />
          			<input type="submit" name="submit" id="submit_button" class="btn btn-success" value="Print" />
          			<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        		</div>
      		</div>
    	</form>
  	</div>
</div>

<script>
$(document).ready(function(){

	var dataTable = $('#billing_table').DataTable({
		"processing" : true,
		"serverSide" : true,
		"order" : [],
		"ajax" : {
			url:"billing_action.php",
			type:"POST",
			data:{action:'fetch'}
		},
		"columnDefs":[
			{
                <?php
                if($object->is_master_user())
                {
                ?>
                "targets":[7],
                <?php
                }
                else
                {
                ?>
				"targets":[6],
                <?php
                }
                ?>
				"orderable":false,
			},
		],
	});

    function fetch_order_data(order_id)
    {
        $.ajax({
            url:"billing_action.php",
            method:"POST",
            data:{order_id:order_id, action:'fetch_single'},
            success:function(data)
            {
                $('#billing_detail').html(data);
            }
        });
    }

    $(document).on('click', '.view_button', function(){
        var order_id = $(this).data('id');
        fetch_order_data(order_id);
        $('#hidden_order_id').val(order_id);
        $('#billingModal').modal('show');
    });

    $(document).on('change', '.product_quantity', function(){
        var quantity = $(this).val();
        var item_id = $(this).data('item_id');
        var order_id = $(this).data('order_id');
        var rate = $(this).data('rate');
        $.ajax({
            url:"order_action.php",
            method:"POST",
            data:{order_id:order_id, item_id:item_id, quantity:quantity, rate:rate, action:'change_quantity'},
            success:function(data)
            {
                fetch_order_data(order_id);
            }
        });
    });

    $(document).on('click', '.remove_item', function(){
        if(confirm("Are you sure you want to remove it?"))
        {
            var item_id = $(this).data('item_id');
            var order_id = $(this).data('order_id');
            $.ajax({
                url:"order_action.php",
                method:"POST",
                data:{order_id:order_id, item_id:item_id, action:'remove_item'},
                success:function(data)
                {
                    fetch_order_data(order_id);
                }
            });
        }
    });

    $('#billing_form').on('submit', function(event){
        event.preventDefault();
        $.ajax({
            url:"billing_action.php",
            method:"POST",
            data:$(this).serialize(),
            beforeSend:function()
            {
                $('#submit_button').attr('disabled', 'disabled');
                $('#submit_button').val('wait...');
            },
            success:function(data)
            {
                $('#submit_button').attr('disabled', false);
                $('#submit_button').val('Print');
                $('#billingModal').modal('hide');
                dataTable.ajax.reload();
                window.location.href="<?php echo $object->base_url; ?>print.php?action=print&order_id="+data
            }
        });
    });

	$(document).on('click', '.delete_button', function(){
        var order_id = $(this).data('id');
        if(confirm("Are you sure you want to remove this Order?"))
        {
            $.ajax({
                url:"billing_action.php",
                method:"POST",
                data:{order_id:order_id, action:"remove_bill"},
                success:function(data)
                {
                    $('#message').html(data);
                    dataTable.ajax.reload();
                    setTimeout(function(){
                        $('#message').html('');
                    }, 5000);
                }
            })
        }
    });

});
</script>