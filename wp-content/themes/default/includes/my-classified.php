<?php $user =  Customer::current();  ?>
<?php $items = Classified::getClassifiedByUserID($user->ID); ?>
<?php if($items): ?>
<table class="tablelist" >
	<tr>
		<th>Ad ID</th><th>Ad Title</th><th>Posted Time</th><th>Actions</th>
	</tr>
	<?php foreach($items as $item): ?>
		<tr>
			<td><?php echo Classified::getPropertyId($item->id) ?></td><td><?php echo $item->title ?></td><td><?php echo  $item->updated_date ?></td><td><a href="<?php echo Classified::getPermalink($item->slug);?>">View</a> | <a href="/edit-classified/?id=<?php echo $item->id ?>">Edit</a> | <a href="javascript:;" onclick="doDelete('<?php echo $item->id ?>');">Delete</a></td>
		</tr>
	<?php endforeach; ?>
</table>
<?php else: ?>
<p>No Ads found yet!</p>
<?php endif; ?>
<script type="text/javascript">
function doDelete(id) {
var where_to= confirm("Do you really want to delete?");
	if(where_to == true){
		$.get('/wp-handler.php?__class=Site&__proc=__doDeleteAd',{ajax:  "1", 'id': id},
				function(data){ 
					window.location="<?php echo get_option('siteurl') ?><?php echo $_SERVER['REQUEST_URI'] ?>";
					}	
			);
	}
}
</script>