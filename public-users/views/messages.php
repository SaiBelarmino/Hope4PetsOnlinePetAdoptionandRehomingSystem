<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$pageTitle = 'Messages';
$hasShelter = !empty($_SESSION['has_shelter']) || !empty($_SESSION['shelter_id']) || !empty($_SESSION['user']['shelter_id']);
?>
<?php include __DIR__ . '/../include/header.php'; ?>
<?php include __DIR__ . '/../include/topbar.php'; ?>
<div class="pu-scroll-wrapper">
<div class="container-fluid">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
    <h3 class="mb-0"><?php echo htmlspecialchars($pageTitle); ?></h3>
    
  </div>
  <div class="card">
    <div class="card-body">
	<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
	<link href="../../assets/css/custom.css" rel="stylesheet">
      <div class="container bootstrap snippets bootdey">
    <div class="row">
        <div class="col-md-4 bg-white ">
            <div class=" row border-bottom padding-sm" style="height: 40px;">
            	Member
            </div>
            
            <!-- =============================================================== -->
            <!-- member list (adoption system context) -->
            <ul class="friend-list">
                <li class="active bounceInDown">
                  	<a href="#" class="clearfix">
                		<img src="../../assets/images/profile/user-1.jpg" alt="" class="img-circle">
                		<div class="friend-name">    
                			<strong>Hope Animal Shelter</strong>
                			<div class="small text-muted">Shelter • Taguig</div>
                		</div>
                		<div class="last-message text-muted">Thanks — we received your adoption inquiry about Luna.</div>
                		<small class="time text-muted">2d</small>
                		<small class="chat-alert label label-danger">2</small>
                	</a>
                </li>

                <li>
                 	<a href="#" class="clearfix">
                		<img src="../../assets/images/profile/user-2.jpg" alt="" class="img-circle">
                		<div class="friend-name">    
                			<strong>Maria Santos</strong>
                			<div class="small text-muted">Adopter</div>
                		</div>
                		<div class="last-message text-muted">I'm available to visit the shelter this Saturday.</div>
                		<small class="time text-muted">5h</small>
                 	<small class="chat-alert text-muted"><i class="fa fa-check"></i></small>
                 	</a>
                </li>

                <li>
                 	<a href="#" class="clearfix">
                		<img src="../../assets/images/profile/user-3.jpg" alt="" class="img-circle">
                		<div class="friend-name">    
                			<strong>Volunteer Coordinator</strong>
                			<div class="small text-muted">Shelter Volunteer</div>
                		</div>
                		<div class="last-message text-muted">Can you help with weekend transport for rescued dogs?</div>
                		<small class="time text-muted">1d</small>
                		<small class="chat-alert text-muted"><i class="fa fa-reply"></i></small>
                	</a>
                </li>

                <li>
                 	<a href="#" class="clearfix">
                		<img src="../../assets/images/profile/user-1.jpg" alt="" class="img-circle">
                		<div class="friend-name">    
                			<strong>Admin Team</strong>
                			<div class="small text-muted">System</div>
                		</div>
                		<div class="last-message text-muted">Your ID verification was approved.</div>
                		<small class="time text-muted">3d</small>
                		<small class="chat-alert text-muted"><i class="fa fa-check"></i></small>
                	</a>
                </li>

                <li>
                 	<a href="#" class="clearfix">
                		<img src="../../assets/images/profile/user-2.jpg" alt="" class="img-circle">
                		<div class="friend-name">    
                			<strong>Green Paws Rescue</strong>
                			<div class="small text-muted">Shelter • Manila</div>
                		</div>
                		<div class="last-message text-muted">Do you have photos of the dog available for fostering?</div>
                		<small class="time text-muted">4d</small>
                		<small class="chat-alert text-muted"><i class="fa fa-reply"></i></small>
                	</a>
                </li>

                <li>
                 	<a href="#" class="clearfix">
                		<img src="../../assets/images/profile/user-3.jpg" alt="" class="img-circle">
                		<div class="friend-name">    
                			<strong>Foster Volunteer</strong>
                			<div class="small text-muted">Volunteer</div>
                		</div>
                		<div class="last-message text-muted">I can foster for two weeks starting next Monday.</div>
                		<small class="time text-muted">6d</small>
                		<small class="chat-alert text-muted"><i class="fa fa-reply"></i></small>
                	</a>
                </li>

                <li>
                 	<a href="#" class="clearfix">
                		<img src="../../assets/images/profile/user-placeholder.png" alt="" class="img-circle">
                		<div class="friend-name">    
                			<strong>Dr. Reyes</strong>
                			<div class="small text-muted">Volunteer Vet</div>
                		</div>
                		<div class="last-message text-muted">Vaccinations for adopted pets are scheduled next week.</div>
                		<small class="time text-muted">1w</small>
                		<small class="chat-alert text-muted"><i class="fa fa-reply"></i></small>
                	</a>
                </li>
            </ul>
        </div>
        
        <!--=========================================================-->
        <!-- selected chat -->
    	<div class="col-md-8 bg-white ">
            <div class="chat-message">
                <ul class="chat">
                    <li class="left clearfix">
                    	<span class="chat-img pull-left">
                    		<img src="https://bootdey.com/img/Content/user_3.jpg" alt="User Avatar">
                    	</span>
                    	<div class="chat-body clearfix">
                    		<div class="header">
                    			<strong class="primary-font">John Doe</strong>
                    			<small class="pull-right text-muted"><i class="fa fa-clock-o"></i> 12 mins ago</small>
                    		</div>
                    		<p>
                    			Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                    		</p>
                    	</div>
                    </li>
                    <li class="right clearfix">
                    	<span class="chat-img pull-right">
                    		<img src="https://bootdey.com/img/Content/user_1.jpg" alt="User Avatar">
                    	</span>
                    	<div class="chat-body clearfix">
                    		<div class="header">
                    			<strong class="primary-font">Sarah</strong>
                    			<small class="pull-right text-muted"><i class="fa fa-clock-o"></i> 13 mins ago</small>
                    		</div>
                    		<p>
                    			Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur bibendum ornare dolor, quis ullamcorper ligula sodales at. 
                    		</p>
                    	</div>
                    </li>
                    <li class="left clearfix">
                        <span class="chat-img pull-left">
                    		<img src="https://bootdey.com/img/Content/user_3.jpg" alt="User Avatar">
                    	</span>
                    	<div class="chat-body clearfix">
                    		<div class="header">
                    			<strong class="primary-font">John Doe</strong>
                    			<small class="pull-right text-muted"><i class="fa fa-clock-o"></i> 12 mins ago</small>
                    		</div>
                    		<p>
                    			Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                    		</p>
                    	</div>
                    </li>
                    <li class="right clearfix">
                        <span class="chat-img pull-right">
                    		<img src="https://bootdey.com/img/Content/user_1.jpg" alt="User Avatar">
                    	</span>
                    	<div class="chat-body clearfix">
                    		<div class="header">
                    			<strong class="primary-font">Sarah</strong>
                    			<small class="pull-right text-muted"><i class="fa fa-clock-o"></i> 13 mins ago</small>
                    		</div>
                    		<p>
                    			Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur bibendum ornare dolor, quis ullamcorper ligula sodales at. 
                    		</p>
                    	</div>
                    </li>                    
                    <li class="left clearfix">
                        <span class="chat-img pull-left">
                    		<img src="https://bootdey.com/img/Content/user_3.jpg" alt="User Avatar">
                    	</span>
                    	<div class="chat-body clearfix">
                    		<div class="header">
                    			<strong class="primary-font">John Doe</strong>
                    			<small class="pull-right text-muted"><i class="fa fa-clock-o"></i> 12 mins ago</small>
                    		</div>
                    		<p>
                    			Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                    		</p>
                    	</div>
                    </li>
                    <li class="right clearfix">
                        <span class="chat-img pull-right">
                    		<img src="https://bootdey.com/img/Content/user_1.jpg" alt="User Avatar">
                    	</span>
                    	<div class="chat-body clearfix">
                    		<div class="header">
                    			<strong class="primary-font">Sarah</strong>
                    			<small class="pull-right text-muted"><i class="fa fa-clock-o"></i> 13 mins ago</small>
                    		</div>
                    		<p>
                    			Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur bibendum ornare dolor, quis ullamcorper ligula sodales at. 
                    		</p>
                    	</div>
                    </li>
                    <li class="right clearfix">
                        <span class="chat-img pull-right">
                    		<img src="https://bootdey.com/img/Content/user_1.jpg" alt="User Avatar">
                    	</span>
                    	<div class="chat-body clearfix">
                    		<div class="header">
                    			<strong class="primary-font">Sarah</strong>
                    			<small class="pull-right text-muted"><i class="fa fa-clock-o"></i> 13 mins ago</small>
                    		</div>
                    		<p>
                    			Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur bibendum ornare dolor, quis ullamcorper ligula sodales at. 
                    		</p>
                    	</div>
                    </li>                    
                </ul>
            </div>
            <div class="chat-box bg-white">
            	<div class="input-group">
            		<input class="form-control border no-shadow no-rounded" placeholder="Type your message here">
            		<span class="input-group-btn">
            			<button class="btn btn-success no-rounded" type="button">Send</button>
            		</span>
            	</div><!-- /input-group -->	
            </div>            
        </div>        
    </div>
    </div>
    </div>
  </div>
 </div>
</div>
<?php include __DIR__ . '/../include/footer.php'; ?>
