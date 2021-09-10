<?php

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
else
{
    $object->query = "
    SELECT * FROM restaurant_table";

    $result = $object->get_result();
}
include('header.php');

?>

                    <!-- Page Heading -->
                    <h1 class="h3 mb-4 text-gray-800">Setting</h1>

                    <!-- DataTales Example -->
                    <span id="message"></span>
                    <form method="post" id="setting_form" enctype="multipart/form-data">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <div class="row">
                                    <div class="col">
                                        <h6 class="m-0 font-weight-bold text-primary">Setting</h6>
                                    </div>
                                    <div clas="col" align="right">
                                        <button type="submit" name="edit_button" id="edit_button" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i> Edit</button>
                                        &nbsp;&nbsp;
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Restaurant Name</label>
                                            <input type="tex" name="restaurant_name" id="restaurant_name" class="form-control" />
                                        </div>
                                        <div class="form-group">
                                            <label>Restaurant Email</label>
                                            <input type="tex" name="restaurant_email" id="restaurant_email" class="form-control" />
                                        </div>
                                        <div class="form-group">
                                            <label>Restaurant Contact No.</label>
                                            <input type="tex" name="restaurant_contact_no" id="restaurant_contact_no" class="form-control" />
                                        </div>
                                        <div class="form-group">
                                            <label>Restaurant Address</label>
                                            <input type="tex" name="restaurant_address" id="restaurant_address" class="form-control" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Tag Line</label>
                                            <input type="tex" name="restaurant_tag_line" id="restaurant_tag_line" class="form-control" />
                                        </div>
                                        <div class="form-group">
                                            <label>Currency</label>
                                            <?php 
                                            echo $object->Currency_list();
                                            ?>
                                        </div>
                                        <div class="form-group">
                                            <label>Timezone</label>
                                            <?php 
                                            echo $object->Timezone_list();
                                            ?>
                                        </div>
                                        <div class="form-group">
                                            <label>Select Logo</label><br />
                                            <input type="file" name="restaurant_logo" id="restaurant_logo" />
                                            <br />
                                            <span class="text-muted">Only .jpg, .png file allowed for upload</span><br />
                                            <span id="uploaded_logo"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                <?php
                include('footer.php');
                ?>

<script>
$(document).ready(function(){

    <?php
    foreach($result as $row)
    {
    ?>
    $('#restaurant_name').val("<?php echo $row['restaurant_name']; ?>");
    $('#restaurant_email').val("<?php echo $row['restaurant_email']; ?>");
    $('#restaurant_contact_no').val("<?php echo $row['restaurant_contact_no']; ?>");
    $('#restaurant_address').val("<?php echo $row['restaurant_address']; ?>");
    $('#restaurant_currency').val("<?php echo $row['restaurant_currency']; ?>");
    $('#restaurant_timezone').val("<?php echo $row['restaurant_timezone']; ?>");
    $('#restaurant_tag_line').val("<?php echo $row['restaurant_tag_line']; ?>");
    <?php
        if($row["restaurant_logo"] != '')
        {
    ?>
    $('#uploaded_logo').html('<img src="<?php echo $row["restaurant_logo"]; ?>" class="img-thumbnail" width="100" /><input type="hidden" name="hidden_restaurant_logo" value="<?php echo $row["restaurant_logo"]; ?>" />');
    <?php
        }
    }
    ?>

    $('#restaurant_logo').change(function(){
        var extension = $('#restaurant_logo').val().split('.').pop().toLowerCase();
        if(extension != '')
        {
            if(jQuery.inArray(extension, ['png','jpg']) == -1)
            {
                alert("Invalid Image File");
                $('#restaurant_logo').val('');
                return false;
            }
        }
    });

    $('#setting_form').parsley();

	$('#setting_form').on('submit', function(event){
		event.preventDefault();
		if($('#setting_form').parsley().isValid())
		{		
			$.ajax({
				url:"setting_action.php",
				method:"POST",
				data:new FormData(this),
                dataType:'json',
                contentType:false,
                processData:false,
				beforeSend:function()
				{
					$('#edit_button').attr('disabled', 'disabled');
					$('#edit_button').html('wait...');
				},
				success:function(data)
				{
					$('#edit_button').attr('disabled', false);
                    $('#edit_button').html('<i class="fas fa-edit"></i> Edit');

                    $('#restaurant_name').val(data.restaurant_name);
                    $('#restaurant_email').val(data.restaurant_email);
                    $('#restaurant_contact_no').val(data.restaurant_contact_no);
                    $('#restaurant_address').val(data.restaurant_address);
                    $('#restaurant_currency').val(data.restaurant_currency);
                    $('#restaurant_timezone').val(data.restaurant_timezone);
                    $('#restaurant_tag_line').val(data.restaurant_tag_line);

                    if(data.restaurant_logo != '')
                    {
                        $('#uploaded_logo').html('<img src="'+data.restaurant_logo+'" class="img-thumbnail" width="100" /><input type="hidden" name="hidden_restaurant_logo" value="'+data.restaurant_logo+'" />');
                    }
						
                    $('#message').html(data.success);

					setTimeout(function(){

				        $('#message').html('');

				    }, 5000);
				}
			})
		}
	});

});
</script>