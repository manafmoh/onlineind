		<div id="footer" class="clearfix">
		<div class="footermenu">
		<?php $results = State::getAllStates(); ?>
		<?php foreach ($results as $row): ?>
			<a href="/state/<?php echo $row->slug ?>.html"><?php echo $row->name ?></a> | 
		<?php endforeach; ?>
		</div>
			<div class="footermenu">
			<a rel="nofollow" target="_parent" href="/about-us">About Us</a> | 
			<a rel="nofollow" target="_parent" href="/contact-us">Contact Us</a> | 
			<a rel="nofollow" target="_parent" href="/listing-policy">Listing Policy</a> | 
			<a rel="nofollow" target="_parent" href="/terms-of-use">Terms of Use</a> | 
			<a rel="nofollow" target="_parent" href="/privacy-policy">Privacy Policy</a> 
			</div>
			<p><small>Copyright © 2010 <?php echo bloginfo('name') ?></small></p>
		</div>
	</div>
<script src="http://cdn.webrupee.com/js" type="text/javascript"></script>
<?php wp_footer(); ?>
</body>
</html>
