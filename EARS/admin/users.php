<!DOCTYPE html>
<?php
	require_once 'auth.php';
?>
<html lang="eng">
	<head>
		<title>Users | user Attendance Record System</title>
		<meta charset="utf-8" />
		<?php include 'header.php' ?>
	</head>
	<body>
		<?php include 'nav_bar.php' ?>
		<div class="container-fluid admin" >
			
			<div class="modal fade" id="delete" tabindex="-1" role="dialog" aria-labelledby="manage_userlabel">
				<div class="modal-dialog" role="document">
					<div class="modal-content ">
						<div class="modal-body">
							<center><label class="text-danger">Are you sure you want to delete this record?</label></center>
							<br />
							<center><a class="btn btn-danger remove_id" ><span class="glyphicon glyphicon-trash"></span> Yes</a> <button type="button" class="btn btn-warning" data-dismiss="modal" aria-label="No"><span class="glyphicon glyphicon-remove"></span> No</button></center>
						</div>
					</div>
				</div>
			</div>
			<div class="modal fade" id="edit_admin" tabindex="-1" role="dialog" aria-labelledby="manage_userlabel">
				<div class="modal-dialog" role="document">
					<div class="modal-content panel-warning">
						<div class="modal-header panel-heading">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title" id="manage_userlabel">Edit Admin</h4>
						</div>
						<div id="edit_query"></div>
					</div>
				</div>
			</div>
			<div class="alert alert-primary">Administrator Accounts</div>
			<div class="well col-lg-12">
				<button type="button" class="btn btn-primary btn-sm" id="new_user"><i class="fa fa-plus"></i> Add New</button>
				<br />
				<br />
				<table id="table" class="table table-bordered table-striped">
					<thead>
						<tr>
							<th>Username</th>
							<th>Firstname</th>
							<th>Lastname</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
					<?php
						$user_qry=$conn->query("SELECT * FROM users") or die(mysqli_error());
						while($row=$user_qry->fetch_array()){
					?>	
						<tr>
							<td><?php echo $row['username']?></td>
							<td><?php echo $row['firstname']?></td>
							<td><?php echo htmlentities($row['lastname'])?></td>
							<td>
								<center>
								 <button class="btn btn-sm btn-outline-primary edit_user" data-id="<?php echo $row['id']?>" type="button"><i class="fa fa-edit"></i></button>
								<button class="btn btn-sm btn-outline-danger remove_user" data-id="<?php echo $row['id']?>" type="button"><i class="fa fa-trash"></i></button>
								</center>
							</td>
						</tr>
					<?php
						}
					?>
					</tbody>
				</table>
			<br />
			<br />	
			<br />		
			</div>
		</div>
		
			<div class="modal fade" id="manage_user" tabindex="-1" role="dialog" aria-labelledby="manage_userlabel">
				<div class="modal-dialog" role="document">
					<div class="modal-content panel-primary">
						<div class="modal-header panel-heading">
							<h4 class="modal-title"></h4>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						</div>
						<form id="user-frm">
							<div class ="modal-body">
								<input type="hidden" name='id'>
								<div class="form-group">
									<label>Username:</label>
									<input type="text" required="required" name="username" class="form-control" />
								</div>
								<div class="form-group">
									<label>Password:</label>
									<input type="password" maxlength="20" required="required" name="password" class="form-control" />
								</div>
								<div class="form-group">
									<label>Firstname:</label>
									<input type="text" name="firstname" required class="form-control" />
								</div>
								<div class="form-group">
									<label>Lastname:</label>
									<input type="text" name="lastname" required="required" class="form-control" />
								</div>
							</div>
							<div class="modal-footer">
								<button  class="btn btn-primary" name="save">Save</button>
							</div>
						</form>
					</div>
				</div>
			</div>
	</body>
	<script type="text/javascript">
		$(document).ready(function(){
			$('#table').DataTable();
		});
	</script>
	<script type="text/javascript">
		$(document).ready(function(){
			$('#user-frm').submit(function(e){
				e.preventDefault()
				$('#user-frm [name="submit"]').attr('disabled',true)
				$('#user-frm [name="submit"]').html('Saving')
				$.ajax({
					url:'save_user.php',
					method:"POST",
					data:$(this).serialize(),
					error:err=>console.log(),
					success:function(resp){
						if(typeof resp !=undefined){
							resp = JSON.parse(resp)
							if(resp.status == 1){
								alert(resp.msg);
								location.reload();
							}else{
								alert(resp.msg);
							}
						}
					}
				})
			})
			$('.remove_user').click(function(){
				var id=$(this).attr('data-id');
				var _conf = confirm("Are you sure to delete this data ?");
				if(_conf == true){
					$.ajax({
						url:'delete_user.php?id='+id,
						error:err=>console.log(err),
						success:function(resp){
							if(typeof resp != undefined){
								resp = JSON.parse(resp)
								if(resp.status == 1){
									alert(resp.msg);
									location.reload()
								}
							}
						}
					})
				}
			});
			$('.edit_user').click(function(){
				var $id=$(this).attr('data-id');
				$.ajax({
					url:'get_user.php',
					method:"POST",
					data:{id:$id},
					error:err=>console.log(),
					success:function(resp){
						if(typeof resp !=undefined){
							resp = JSON.parse(resp)
							$('[name="id"]').val(resp.id)
							$('[name="firstname"]').val(resp.firstname)
							$('[name="lastname"]').val(resp.lastname)
							$('[name="username"]').val(resp.username)
							$('#manage_user .modal-title').html('Edit User')
							$('#manage_user').modal('show')
						}
					}
				})
				
			});
			$('#new_user').click(function(){
				$('[name="id"]').val('')
				$('[name="firstname"]').val('')
				$('[name="lastname"]').val('')
				$('[name="username"]').val('')
				$('#manage_user .modal-title').html('Add New User')
				$('#manage_user').modal('show')
			})
		});
	</script>
</html>