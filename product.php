<?php

//product.php

include('rms.php');

$object = new rms();

if(!$object->is_login())
{
    header("location:".$object->base_url."");
}

if(!$object->is_master_user())
{
    header("location:".$object->base_url."dashboard.php");
}

$object->query = "
SELECT category_name FROM product_category_table 
WHERE category_status = 'Enable' 
ORDER BY category_name ASC
";

$category_result = $object->get_result();

include('header.php');
                
?>

                    <!-- Page Heading -->
                    <h1 class="h3 mb-4 text-gray-800">Product Management</h1>

                    <!-- DataTales Example -->
                    <span id="message"></span>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                        	<div class="row">
                            	<div class="col">
                            		<h6 class="m-0 font-weight-bold text-primary">Product List</h6>
                            	</div>
                            	<div class="col" align="right">
                            		<button type="button" name="add_product" id="add_product" class="btn btn-success btn-circle btn-sm"><i class="fas fa-plus"></i></button>
                            	</div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="product_table" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Product Name</th>
                                            <th>Product Price</th>
                                            <th>Product Category</th>
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

<div id="productModal" class="modal fade">
  	<div class="modal-dialog">
    	<form method="post" id="product_form">
      		<div class="modal-content">
        		<div class="modal-header">
          			<h4 class="modal-title" id="modal_title">Add Data</h4>
          			<button type="button" class="close" data-dismiss="modal">&times;</button>
        		</div>
        		<div class="modal-body">
        			<span id="form_message"></span>
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category_name" id="category_name" class="form-control" required data-parsley-trigger="change">
                            <option value="">Select Category</option>
                            <?php
                            foreach($category_result as $category)
                            {
                                echo '<option value="'.$category["category_name"].'">'.$category["category_name"].'</option>';
                            }
                            ?>
                        </select>
                    </div>
		          	<div class="form-group">
		          		<label>Product Name</label>
		          		<input type="text" name="product_name" id="product_name" class="form-control" required data-parsley-pattern="/^[a-zA-Z0-9 \s]+$/" data-parsley-trigger="keyup" />
		          	</div>
                    <div class="form-group">
                        <label>Product Price</label>
                        <input type="text" name="product_price" id="product_price" class="form-control" required data-parsley-pattern="^[0-9]*\.[0-9]{2}$" data-parsley-trigger="keyup" />
                    </div>
        		</div>
        		<div class="modal-footer">
          			<input type="hidden" name="hidden_id" id="hidden_id" />
          			<input type="hidden" name="action" id="action" value="Add" />
          			<input type="submit" name="submit" id="submit_button" class="btn btn-success" value="Add" />
          			<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        		</div>
      		</div>
    	</form>
  	</div>
</div>

<script>
$(document).ready(function(){

	var dataTable = $('#product_table').DataTable({
		"processing" : true,
		"serverSide" : true,
		"order" : [],
		"ajax" : {
			url:"product_action.php",
			type:"POST",
			data:{action:'fetch'}
		},
		"columnDefs":[
			{
				"targets":[4],
				"orderable":false,
			},
		],
	});

	$('#add_product').click(function(){
		
		$('#product_form')[0].reset();

		$('#product_form').parsley().reset();

    	$('#modal_title').text('Add Data');

    	$('#action').val('Add');

    	$('#submit_button').val('Add');

    	$('#productModal').modal('show');

    	$('#form_message').html('');

	});

	$('#product_form').parsley();

	$('#product_form').on('submit', function(event){
		event.preventDefault();
		if($('#product_form').parsley().isValid())
		{		
			$.ajax({
				url:"product_action.php",
				method:"POST",
				data:$(this).serialize(),
				dataType:'json',
				beforeSend:function()
				{
					$('#submit_button').attr('disabled', 'disabled');
					$('#submit_button').val('wait...');
				},
				success:function(data)
				{
					$('#submit_button').attr('disabled', false);
					if(data.error != '')
					{
						$('#form_message').html(data.error);
						$('#submit_button').val('Add');
					}
					else
					{
						$('#productModal').modal('hide');
						$('#message').html(data.success);
						dataTable.ajax.reload();

						setTimeout(function(){

				            $('#message').html('');

				        }, 5000);
					}
				}
			})
		}
	});

	$(document).on('click', '.edit_button', function(){

		var product_id = $(this).data('id');

		$('#product_form').parsley().reset();

		$('#form_message').html('');

		$.ajax({

	      	url:"product_action.php",

	      	method:"POST",

	      	data:{product_id:product_id, action:'fetch_single'},

	      	dataType:'JSON',

	      	success:function(data)
	      	{

	        	$('#category_name').val(data.category_name);

                $('#product_name').val(data.product_name);

                $('#product_price').val(data.product_price);

	        	$('#modal_title').text('Edit Data');

	        	$('#action').val('Edit');

	        	$('#submit_button').val('Edit');

	        	$('#productModal').modal('show');

	        	$('#hidden_id').val(product_id);

	      	}

	    })

	});

	$(document).on('click', '.status_button', function(){
		var id = $(this).data('id');
    	var status = $(this).data('status');
		var next_status = 'Enable';
		if(status == 'Enable')
		{
			next_status = 'Disable';
		}
		if(confirm("Are you sure you want to "+next_status+" it?"))
    	{

      		$.ajax({

        		url:"product_action.php",

        		method:"POST",

        		data:{id:id, action:'change_status', status:status, next_status:next_status},

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

	$(document).on('click', '.delete_button', function(){

    	var id = $(this).data('id');

    	if(confirm("Are you sure you want to remove it?"))
    	{

      		$.ajax({

        		url:"product_action.php",

        		method:"POST",

        		data:{id:id, action:'delete'},

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