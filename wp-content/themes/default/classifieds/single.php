<?php get_header(); ?>
<?php if (have_posts()): while(have_posts()): the_post(); ?>
<div class="blog-spot-bg">
			<div class="inside-blog-spot">
				<h2>blog article</h2>
			</div>
		</div>
		<div id="inside-wrapper" class="clearfix">
			 <div class="container-left">
		         <div class="left-top">
		             <a href="/">Home</a>
		             <?php $cats = get_the_category($post->ID);?>
		             <a  class="category" href="/category/blog/<?php echo  $cats[0]->slug;?>"><?php echo $cats[0]->name;?></a>
		             
		         </div>
		         <div class="inside-banner">
		             <h3><?php echo get_the_title(); ?></h3>
		             <p><?php the_time('F j, Y \a\t g:i A',strtotime($post->post_date))?></p>
		             <?php the_content(); ?>
		             <div class="share_btn">
		             	<div class="tweets-block">
							<script src="http://platform.twitter.com/widgets.js" type="text/javascript"></script>
							<a href="http://twitter.com/share" class="twitter-share-button">Tweet</a>
						</div>
						<div class="fb-block">
							<a name="fb_share" type="button_count" href="http://www.facebook.com/sharer.php">Share</a>
							<script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script>
						</div>
		             </div>
		             
		             <div class="comment-block">
		             <?php comments_template( '', true ); ?>
		             </div>
		         </div>
		     </div>
		     <div class="sidebar side-inside">
		        <div class="side-top">
		             <form action="#" method="post" id="form-check">
		             <input class="text" type="text" name="search" alt="search" value="Keyword.." onfocus="if(this.value=='Keyword..')this.value='';else this.select();" onblur="if(this.value=='')this.value='Keyword..';" />
		             <input class="btn" type="image"  src="<?php echo STYLEURL;?>/image/search.png" name="search" alt="search"/>
		             </form>
		         </div>
		         <div class="side-bottom">
		             <h3>categories</h3>
		                
		                 
		       		 <?php
						$cat = get_query_var('cat');
						$cat_args=array(
						  'child_of' => $cat,
						 	'orderby' => name,
						   'title_li' => '',
						   );
						$categories=wp_list_categories($cat_args);
					?>
								
					<br />
					<br />	
					<br />	
		            <iframe src="//www.facebook.com/plugins/likebox.php?href=http%3A%2F%2Fwww.facebook.com%2Fpages%2FIaapz%2F360636410659194&amp;width=292&amp;height=290&amp;colorscheme=light&amp;show_faces=true&amp;border_color&amp;stream=false&amp;header=true" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:292px; height:290px;" allowTransparency="true"></iframe>
					
		          </div>
		      </div>			
		</div>
		<?php endwhile; endif; ?>
	 <?php get_footer(); ?>