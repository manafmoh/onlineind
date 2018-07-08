<?php $record = $wp_query->data["agent"]; ?>
<?php $current_user_id = Site::getCurrentUserId(); ?>
<?php get_header(); ?>
<div id="sortby">
			<?php if(isset($_SESSION['searchresult'])) { ?> <a href="<?php echo $_SESSION['searchresult']; ?>" class="back">Back to search results</a> <?php unset($_SESSION['searchresult']); } else { ?>
			<a  class="back" href="<?php echo SITEURL; ?>" >Back to home </a><?php } ?>
		</div>
		<div id="content">
			<div id="agent-details">
				<h1><?php echo $record->display_name; ?></h1>
				<img class="ins-logo" src="<?php echo Site::getImageUrl($record->logo);?>" alt="" title="" />
				<div>
					<p><a href="javascript:;" onclick="Site.loadAgentDetailWrap($('#agent_sale').get(0),1);" ><?php echo Agent::getBuyCount($record->ID);?> properties</a> For Sale</p>
					<p><a href="javascript:;" onclick="Site.loadAgentDetailWrap($('#agent_rent').get(0),1);" ><?php echo Agent::getRentCount($record->ID); ?> properties</a> For Rent</p>
					
					<div id="rating">
					<?php if(Customer::isLoggedIn()){?>
						<?php global $gRating; $gRating->insert_script(); $gRating->show('agent', $record->ID); ?>
					<?php } else { ?>
						<?php global $gRating; $gRating->getRatingResult('agent', $record->ID, 'Login to rate', true, true); ?>
					<?php } ?>
					</div>
					
					<a href="javascript:;" onclick="Site.showDialog('share-popup', 'shareAgent-Pop.php?type=agent&id=<?php echo $record->ID;?>&agent_slug=<?php echo $record->user_nicename;?>', 'login-ind');" class="btm-links">Refer this agency to a friend</a>
					<div style="position:relative;"><iframe allowtransparency="true" frameborder = "0" id="share-popup" class="popup-agent" scrolling="no" src =""></iframe></div>
				<?php if(Customer::isLoggedIn() && Agent::isSavedAgent($current_user_id,$record->ID)) { ?>	<a href="javascript:;" class="btm-links" onclick="Site.saveAgent(this,'<?php echo $current_user_id; ?>','<?php echo $record->ID; ?>')" >Send me listings by this agency</a> <?php } ?>
				</div>
				<ul id="agent-nav">
					<li class="current"><a href="javascript:;">Overview</a></li>
					<li><a href="javascript:;" id="agent_sale">For sale</a></li>
					<li><a href="javascript:;" id="agent_rent">For rent</a></li>
					<li><a href="javascript:;">Agency contacts</a></li>
				</ul>
				<input type="hidden"  id="agent_id" name="agent_id" value="<?php echo $record->ID; ?>" />
				<div id="agent_details">
				<?php Site::loadAgentOverview($record->ID); ?>
				</div>
			</div>	
			<?php require_once TEMPLATEPATH.'/includes/bottom-ad.php'; ?>
		</div>
<?php get_sidebar(); ?>
<?php if($record->google_analytics_code){ ?>
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
var pageTracker = _gat._getTracker("<?php echo $record->google_analytics_code; ?>");
pageTracker._trackPageview();
</script>				
<?php } ?>	
<?php get_footer(); ?>
