<!DOCTYPE html>
<html lang = "eng">
	<head>
		<title>Employee Attendance Record System</title>
		<?php include('header.php') ?>
	</head>
	<body>
		<div id="main" class="bg-info">
		<div class = "container-fluid admin2">
			
			<div class="attendance_log_field">

				<div id="company-logo-field" class="mb-4 ">
					<h4>Attendence Management</h4>
					<!-- <img src="company_logo.jpg" alt=""> -->
				</div>
				<div class="col-md-4 offset-md-4">
					<div class="card">
						<div class="card-title">
							

						</div>
						<div class="card-body">
							<div class="text-center">
								<h4><?php echo date('F d,Y') ?> <span id="now"></span></h4>
							</div>
							<div class="col-md-12">
								<div class="text-center mb-4" id="log_display"></div>
									<form action="" id="att-log-frm" >
										<div class="form-group">
											<label for="eno" class="control-label">Enter your Employee Email</label>
											<input type="text" id="eno" name="eno" class="form-control col-sm-12">
										</div>
										<center>
											<button type="button" class='btn btn-sm btn-primary log_now col-sm-2' data-id="1">IN</button>
											<button type="button" class='btn btn-sm btn-primary log_now col-sm-2' data-id="2">OUT</button>
										</center>
										<div class="loading" style="display: none"><center>Please wait...</center></div>
										
									</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		</div>
	</body>
	<script>
		$(document).ready(function(){
			setInterval(function(){
				var time = new Date();
				var now = time.toLocaleString('en-US', { hour: 'numeric', minute: 'numeric', second: 'numeric', hour12: true })
				$('#now').html(now)
			},500)
			console.log()

			$('.log_now').each(function(){
				$(this).click(function(){
					var _this = $(this)
					var eno = $('[name="eno"]').val()
					if(eno == ''){
						alert("Please enter your employee email");
					}else{
						$('.log_now').hide()		
						$('.loading').show()
						$.ajax({
							url:'./admin/time_log.php',
							method:'POST',
							data:{type:_this.attr('data-id'),eno:$('[name="eno"]').val()},
							error:err=>console.log(err),
							success:function(resp){
								if(typeof resp != undefined){
									resp = JSON.parse(resp)

									if(resp.status == 1){
										$('[name="eno"]').val('')
										$('#log_display').html(resp.msg)
										$('.log_now').show()		
										$('.loading').hide()
										setTimeout(function(){
										$('#log_display').html('')
										},5000)
									}else{
										alert(resp.msg)
										$('.log_now').show()		
										$('.loading').hide()
									}
								}
							}
						})		
					}
				})
			})
		})
	</script>
</html>