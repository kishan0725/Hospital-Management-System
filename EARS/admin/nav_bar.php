			<nav class = "navbar navbar-header navbar-dark bg-dark">
			<div class = "container-fluid">
				<div class = "navbar-header">
					<p class = "navbar-text pull-right">Company Name</p>
				</div>
				<div class = "nav navbar-nav navbar-right">
					<a href="logout.php" class="text-light"><?php echo $user_name ?> <i class="fa fa-power-off"></i></a>
				</div>
			</div>
		</nav>
		<div id="sidebar" class="bg-dark">
			<div id="sidebar-field">
				<a href="home.php" class="sidebar-item">
						<div class="sidebar-icon"><i class="fa fa-dashboard"> </i></div>  Dashboard
				</a>
			</div>
			<div id="sidebar-field">
				<a href="employee.php" class="sidebar-item">
						<div class="sidebar-icon"><i class="fa fa-columns"> </i></div>  Employee
				</a>
			</div>
			<div id="sidebar-field">
				<a href="attendance.php" class="sidebar-item">
						<div class="sidebar-icon"><i class="fa fa-list"> </i></div>  Attendance
				</a>
			</div>
			<div id="sidebar-field">
				<a href="users.php" class="sidebar-item">
						<div class="sidebar-icon"><i class="fa fa-users"> </i></div>  Users
				</a>
			</div>

		</div>
		<script>
			$(document).ready(function(){
				var loc = window.location.href;
				loc.split('{/}')
				$('#sidebar a').each(function(){
				// console.log(loc.substr(loc.lastIndexOf("/") + 1),$(this).attr('href'))
					if($(this).attr('href') == loc.substr(loc.lastIndexOf("/") + 1)){
						$(this).addClass('active')
					}
				})
			})
			
		</script>