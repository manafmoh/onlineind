<?php
class Site {
	
	public static function init() {
		define('SITEURL', get_option('siteurl'));
		define('STYLEURL', get_bloginfo('stylesheet_directory', false));
		add_action('parse_query', array(__CLASS__, 'parseQuery'));
		add_action('template_redirect', array(__CLASS__, 'templateRedirect'));
		add_filter('register_handler', array(__CLASS__, 'registerHandler'));
		self::__setBuffering();
	}
	
	public static function registerHandler($handlers) {
		$handlers[] = array(__CLASS__, '__userLogin');
		$handlers[] = array(__CLASS__, '__replyForm');
		$handlers[] = array(__CLASS__, '__userLogout');
		$handlers[] = array(__CLASS__, '__loadSubcategory');
		$handlers[] = array(__CLASS__, '__loadState');
		$handlers[] = array(__CLASS__, '__loadDistrict');
		$handlers[] = array(__CLASS__, '__loadDistrictDropDown');
		$handlers[] = array(__CLASS__, '__loadPlace');
		$handlers[] = array(__CLASS__, '__uploadFile');
		$handlers[] = array(__CLASS__, '__doDeleteAd');
		$handlers[] = array(__CLASS__, '__setLocationCookie');
		
		
		return($handlers);
	}
	
	public static function __setLocationCookie() {
		$loc = $_REQUEST['loc']; 
		self::setLocationCookie($loc);
	}
	
	public static function getLocationCookie() {
		if (isset($_COOKIE["__location"]) && $_COOKIE["__location"] != "/")
  			return $_COOKIE["__location"];
		else
  			return "all";
		}
	public static function setLocationCookie($value) { 
		setcookie("__location", $value, time()+3600*3600,"/");  
		}
	public static function parseQuery(&$query) { //echo "<pre>";var_dump($query->query_vars);
		self::__checkloggedIn($query->query_vars['pagename']);
		if($query->is_category && $query->query_vars['category_name'] == 'classifieds') { 
			header("Location:".get_option('siteurl')); exit;
		}else if($query->is_category && strstr($query->query_vars['category_name'], 'classified/')) {  
			//
		}
		elseif(isset($_REQUEST['__option'])) { 
			//$query->is_category = false;
			$query->query = "";
			$query->query_vars = array();
			$query->option = trim($_REQUEST['__option'], '/');
			self::__processParam(trim($_REQUEST['__param'], '/'), $query);
			unset($_REQUEST['__option']);
			unset($_REQUEST['__param']);
			self::__signInProtect($query->option, $query->slug);
			if(!self::__loadData()) {
				unset($query->option);
				$query->set_404();
				status_header(404);
				nocache_headers();
			}
		} else {
			self::__signInProtectPage($query->query_vars['pagename']);
		}
	}
	
	private static function __processParam($param, &$query) {
		if($param) {
			$param = explode('/', $param);
			//var_dump($param); exit;
			$query->slug = $param[0];
			for($i=1;$i<count($param);$i+=2) {
				$_REQUEST[$param[$i]] = $_GET[$param[$i]] = $param[$i+1];
			}
		}
	}
	
	
	public static function templateRedirect() {
		global $wp_query;
		if($wp_query->option) { 
			if($wp_query->option == 'classifieds' && $wp_query->slug=='search' ) {
				require_once TEMPLATEPATH.'/classifieds/search.php';
			}
			else if(file_exists(TEMPLATEPATH.'/classifieds/'.$wp_query->option.'.php')) {
				require_once TEMPLATEPATH.'/classifieds/'.$wp_query->option.'.php';
			}else if(file_exists(TEMPLATEPATH.'/state/'.$wp_query->option.'.php')) {
				require_once TEMPLATEPATH.'/state/'.$wp_query->option.'.php';
			} else {
				require_once TEMPLATEPATH.'/classifieds/index.php';
			}
			exit();
		}
	}
	
	private static function __loadData() {
		global $wp_query;
		switch($wp_query->option) {
			case 'classifieds':
				$wp_query->data['classifieds'] = Classified::getClassified($wp_query->slug);
				break;
			case 'classified':
				$wp_query->data['classified'] = Classified::load($wp_query->slug);
				break;
			case 'state':
				$wp_query->data['state'] = District::getAllDistricts($wp_query->slug);
				break;
			case 'my-account':
				switch($wp_query->slug) {
					case 'manage':
					case 'change-password':
					case 'saved-properties':
					case 'email-alerts':
						$wp_query->data = Customer::loadProfile();
						break;
				} 
		}
		return($wp_query->data);
	}
	private static function __signInProtect($option, $slug) { 
		$protectedSection = array('my-account'); 
		$protectedSection2 = array('register','forgot-password');
		if(in_array($option, $protectedSection) && !Customer::isLoggedIn()) {
			wp_redirect(SITEURL.'/sign-in/?_ru='.urlencode("/$option/$slug/"));
			exit();
		}
	}
	private static function __signInProtectPage($slug) { 
		$protectedSection = array('my-classified','edit-classified');
		if(in_array($slug, $protectedSection) && !Customer::isLoggedIn()) {
			wp_redirect(SITEURL.'/sign-in/?_ru='.urlencode("/$option/$slug/"));
			exit();
		}
	}
	
	
	private static function __checkLoggedIn($slug) {
		$protectedSection = array('register','forgot-password');
		if(in_array($slug, $protectedSection) && Customer::isLoggedIn()) {
			wp_redirect(SITEURL.'/');
			exit();
		}
	}
	public static function changeSidebar() {
		$pageNames =  array('post-classified');	
		$login =  Customer::current();
			if($login){
				require_once TEMPLATEPATH.'/sidebar-member.php';
			}
		}
	private static function __setBuffering() {
		if(isset($_REQUEST['_buffer'])) {
			ob_start();
		}
	}
	public static function drop_multiple_slashes($str) { 
		if(strpos($str,'//')!==false) {
     		return self::drop_multiple_slashes(str_replace('//','/',$str));
  		}
  	return $str;
}

	public static function cQ($string) {
		  if(get_magic_quotes_gpc())	  {
		    $string = stripslashes($string);
		  }
		  if (phpversion() >= '4.3.0')  {
		    $string = mysql_real_escape_string($string);
		  }
		  else  {
		    $string = mysql_escape_string($string);
		  }
		  return $string;
		}

	public static function __userLogout() {
		Customer::logout();
		}
	public static function __userLogin() {
		//extract($_POST);
		$email = strip_tags($_REQUEST['email']);
		$password = strip_tags($_REQUEST['password']);
		$isActive = Customer::isActive($email); 
		if($isActive) { 
			if(Customer::login($email, $password, $remember)) {  
				$login =  Customer::current(); 
				$data = "<label class='message'>Welcome ". $email ."</label>";
				$data .= "<a class='logout' href='javascript:logout();' onclick='logout();'>Logout</a>";
			} else {
				$data = "Invalid login, please try again";
			}
		} else {
			$data = "Invalid login, please try again";
		}
		echo $data;
	}
	public static function __replyForm() {
		//extract($_POST);
		$email = Site::cQ($_REQUEST['email']);		
		$telephone = Site::cQ($_REQUEST['telephone']);
		$message = Site::cQ($_REQUEST['message']);
		$recordid = Site::cQ($_REQUEST['recordid']);
		self::sendReplyMail($email, $telephone, $message, $recordid);
		echo "Your reply has been successfully sent.";
	}
	public static function __loadSubcategory() {
		if($_REQUEST['catid']) {
			$catid = $_REQUEST['catid'];
			$result = SubCategory::getSubCategory($catid);
			if($result) {
				echo FormField::select('subcategory_id', $result, '', '', array(''=>'Sub Category'));
			}
		} else {
			echo FormField::select('subcategory_id', array(''=>''), '', '', array(''=>'Sub Category'));	
			}
		
	}
	public static function __loadDistrict() {
		if($_REQUEST['id']) { 
			$id = $_REQUEST['id']; 
			$result = District::getDistrict($id);
			if($result) {
				echo FormField::select('district_id', $result, '', 'onchange="loadPlace(this.value)"', array(''=>'District')); 
			} else {
			echo FormField::select('district_id', array(''=>''), '', 'onchange="loadPlace(this.value)"', array(''=>'District')); 		
			}
		} else {
			echo FormField::select('district_id', array(''=>''), '', 'onchange="loadPlace(this.value)"', array(''=>'District')); 		
			}
		
	}
	public static function __loadDistrictDropDown() {
		if($_REQUEST['id']) { 
			$id = $_REQUEST['id']; 
			$result = District::getDistrictDropDown($id);
			if($result) {
				echo FormField::select('district_id', $result, '', 'onchange="loadPlace(this.value)"', array(''=>'District')); 
			} else {
			echo FormField::select('district_id', array(''=>''), '', 'onchange="loadPlace(this.value)"', array(''=>'District')); 		
			}
		} else {
			echo FormField::select('district_id', array(''=>''), '', 'onchange="loadPlace(this.value)"', array(''=>'District')); 		
			}
		
	}
	public static function __loadPlace() {
		if($_REQUEST['id']) {
			$id = $_REQUEST['id'];
			$result = Place::getPlace($id);
			if($result) {
				echo FormField::select('place_id', $result, '', '', array(''=>'Place')); 
			} else {
			echo FormField::select('place_id', array(''=>''), '', '', array(''=>'Place')); 		
			}
		} else {
			echo FormField::select('place_id', array(''=>''), '', '', array(''=>'Place')); 		
			}
	}
	public static function __uploadFile() {
		$uploaddir = ABSPATH.'wp-content/media/image/';
		$file = $uploaddir . basename($_FILES['uploadfile']['name']); 
		
		if (move_uploaded_file($_FILES['uploadfile']['tmp_name'], $file)) {
		  echo "success";
		} else {
			echo "error";
		}
	}
	public static function reIndexArray( $arr, $startAt=0 ) {
    return ( 0 == $startAt )
        ? array_values( $arr )
        : array_combine( range( $startAt, count( $arr ) + ( $startAt - 1 ) ), array_values( $arr ) );
	}

	public static function mysqlDateToTimestamp($date) {
		$parts = explode(' ', $date);
		$dateParts = explode('-', $parts[0]);
		$timeParts = explode(':', $parts[1]);
		return(mktime($timeParts[0], $timeParts[1], $timeParts[2], $dateParts[1], $dateParts[2], $dateParts[0]));
	}
	
	public static function getThumbUrl($file, $width, $height, $mode = 4) {
		return(SITEURL."/thumbnail/{$width}x{$height}x{$mode}/$file");
	}
	
	public static function getImageUrl($file) {
		return(SITEURL."/wp-content/media/image/{$file}");
	}
		
	
	
	public static  function get_interest_factor($year_term, $monthly_interest_rate) {
        global $base_rate;
        $factor      = 0;
        $base_rate   = 1 + $monthly_interest_rate;
        $denominator = $base_rate;
        for ($i=0; $i < ($year_term * 12); $i++) {
            $factor += (1 / $denominator);
            $denominator *= $base_rate;
           
        }
        return $factor;
    }        
	
	public static function getyear() {
		$year = array();
		for($i=2;$i<=30;$i++) {
			$year[$i] = $i." Years";
		}
		return $year;
	}
	
	public static function addCommant($id,$commant,$user) {
		$result = Classified::insertCommants($id,$commant,$user);
		 if($result >0){ 
		 	?>
		 	<p class="success-msg">Successfully saved. Your comment will be displayed on the site after moderation</p>
		 	<?php
		 } 
		//Site::loadDiscussions($id,$result);
	}
	
	public static function sendAccessDetails($email) {
		return(true);
	}
	
	public static function sendMail($template, $data, $to, $subject, $cc='') {
		$template = TEMPLATEPATH."/includes/templates/$template.html";
		if(file_exists($template)) {
			$data['[SITEURL]'] = SITEURL;
			$data['[STYLEURL]'] = STYLEURL;
			$data['[PACKAGE]'] = 180;
			$data['[YEAR]'] = date('Y');
			$data['[SITENAME]'] = get_bloginfo('name');
			$content = file_get_contents($template);
			$content = str_replace(array_keys($data), array_values($data), $content);
			
			require_once(ABSPATH.WPINC.'/class-phpmailer.php');
			$mail = new PHPMailer();
			$mail->From     = get_option('admin_email');
			$mail->FromName = get_bloginfo('name');
			$mail->AddAddress($to, $to);
			if($cc) {
				$mail->AddCC($cc, $cc);
			}
		
			$mail->IsHTML(true);
		//	$mail->IsSMTP();
			$mail->CharSet = "utf-8";
			$mail->Subject = $subject;
			$mail->Body = $content;
			
			$mail->AltBody = strip_tags($content);
			return($mail->Send());
		}
		return(false);
	}
	
	public static function sendReplyMail($email, $telephone, $message, $recordid) {
		$template = TEMPLATEPATH."/includes/templates/reply.html";
		if(file_exists($template)) {
			$item = Classified::getClassifiedById($recordid);
			$data['[SITEURL]'] = SITEURL;
			$data['[STYLEURL]'] = STYLEURL;
			$data['[YEAR]'] = date('Y');
			$data['[SITENAME]'] = get_bloginfo('name');
			$data['[EMAIL]'] = $email;
			$data['[MESSAGE]'] = $message;
			$data['[TOEMAIL]'] = $item->email;
			$data['[SUBJECT]'] = $item->title;
			$data['[TELEPHONE]'] = $telephone;
			$content = file_get_contents($template);
			$content = str_replace(array_keys($data), array_values($data), $content);
			
			require_once(ABSPATH.WPINC.'/class-phpmailer.php');
			$mail = new PHPMailer();
			$mail->From     = $email;
			$mail->FromName = $email;
			$mail->AddAddress($item->email, $item->fullname);
		
			$mail->IsHTML(true);
		//	$mail->IsSMTP();
			$mail->CharSet = "utf-8";
			$mail->Subject = "You have received a reply to your Ad ".$subject;

			$mail->Body = $content;
			$mail->AltBody = strip_tags($content);
			return($mail->Send());
		}
		return(false);
	}
	public static  function paging($numposts,$page_size,$currpage) {
		$max_page = ceil($numposts / $page_size);
		$query_string = $_SERVER['QUERY_STRING'];
		$page_no = $_GET['page'];
		$query_string = str_replace("&page={$page_no}",'',$_SERVER['QUERY_STRING']);
		$limter = 5;
		if($currpage == '')	{
			$currpage = 1;
		}
		if($currpage + $limter >$max_page) 	{
			$endpage = $max_page;
		}
		else {
			$endpage = $currpage+($limter);
			
		}
		if($currpage-$limter <= 0) {
			$startpage = 1;
		}
		else {
			
			$startpage = $currpage-($limter);
		}
		echo "<div id=paging><label>Pages:</label>";
		if($max_page!=1) {	
			if($endpage!=$max_page) {
				$next = $currpage+1;
				echo "<a class=\"next-pagebar-a\" href=\"?page=$next\"></a>";
			}
			
			for($i = $startpage;$i<=$endpage;$i++) {	
			
				if($i!=$currpage) {	
					echo"<a href=\"?page=$i\">$i</a> ";
				}
				else {
					echo "<span>".$i."</span>";
				}
			
			}	
			if($startpage!=1){	
				$prev = $currpage-1;
				echo "<a class=\"prev-pagebar-a\" href=\"?page=$prev\"></a>";
			}
		}
		echo "</div>";
	}
	
	public static function showPrice($price) {
		$price = $price;
		$price = number_format($price);
		return("$price");
	}
	
	public static function getPrice($price) {
		$currencyCode = $_COOKIE['currency']?trim($_COOKIE['currency']):Currency::DEFAULT_CURRENCY;
		$price = Currency::convert($price, $currencyCode);
		return($price);
	}
	
	public static function getCurrenctCurrency() {
		$currencyCode = $_COOKIE['currency']?trim($_COOKIE['currency']):Currency::DEFAULT_CURRENCY;
		return($currencyCode);
	}
	
	public static function setCurrenctCurrency($currency) {
		setcookie('currency', $currency, time()+3600*24*7, '/');
		wp_redirect(str_replace('&action=changecurrency', '', $_SERVER['REQUEST_URI']));
		exit();
	}
	
	public static function getCurrencyList() {
		$currency = array();
		$current_currency = Site::getCurrenctCurrency();
		$currency_list = Currency::getList();
		foreach ($currency_list as $list) {
			$currency[$list->code] = $list->code."-".$list->name;
		}
		return $currency;
	}
	
	public static function getCurrentUserId() {
		 $user = Customer::current(); 
		 return $user->ID;
	}
	
	public static function saveClassified(){
		$cur_uid = Site::getCurrentUserId();
		if($_REQUEST['userId'] == $cur_uid) {
			$result = Classified::saveProperty($_REQUEST['userId'],$_REQUEST['propertyId']);	
			echo $result;
		}
		
	}
	
	public static function sendClassified(){
		$cur_uid = Site::getCurrentUserId();
		if($_REQUEST['userId'] == $cur_uid) {
			$result = Classified::sendProperty($_REQUEST['userId'],$_REQUEST['propertyId']);	
			echo $result;
		}
	}
	
	public static function deleteClassified(){
		$cur_uid = Site::getCurrentUserId();
		if($_REQUEST['userId'] == $cur_uid) {
			$result = Classified::removeProperty($cur_uid,$_REQUEST['propertyId']);
		}
	}
	
	public static function deleteEmailAlert() {
		$cur_uid = Site::getCurrentUserId();
		if($_REQUEST['userId'] == $cur_uid) {
			$result = Classified::removeEmailAlert($cur_uid,$_REQUEST['propertyId']);
		}
		
	}
	
	
	
	public static function ShowPages($numposts,$page_size,$currpage,$id,$function) {
		$max_page = ceil($numposts / $page_size);
		$limter = 5;
		if($currpage == '')	{
			$currpage = 1;
		}
		if($currpage + $limter >$max_page) 	{
			$endpage = $max_page;
		}
		else {
			$endpage = $currpage+($limter);
		}
		if($currpage-$limter <= 0) {
			$startpage = 1;
		}
		else {
			
			$startpage = $currpage-($limter);
		}
		echo "<div id=paging>";
		echo "<label>Pages:</label>";
		if($max_page!=1) {	
			if($endpage!=$max_page) {
				$next = $currpage+1;
				echo "<a class=\"next-pagebar-a\" href=\"javascript:;\" onclick=\"$function($('{$id}').get(0), {$next});\" >Next</a>";
			}
			for($i = $startpage;$i<=$endpage;$i++) {	
				if($i!=$currpage) {	
				echo"<a href=\"javascript:;\"  onclick=\"$function($('{$id}').get(0), {$i});\" >$i</a> ";
				}
				else {
				echo "<span>".$i."</span>";
				}	
			}
			if($startpage!=1){	
				$prev = $currpage-1;
				echo "<a class=\"prev-pagebar-a\" href=\"javascript:;\"  onclick=\"$function($('{$id}').get(0), {$prev});\" >Previous</a>";
			}
		}
		echo "</div>";
	}
	public static function recentPost() {
		$results = Classified::getRecentPosts();  ?>
		<ul class="listing prop-listing">
		<?php foreach ($results as $row) { ?>
			<li>
			<h1><a href="<?php echo Classified::getPermalink($row->slug);?>"><?php echo $row->title ?></a> <?php if($row->updated_date) { ?> [<small><?php echo $row->updated_date; ?></small>]<?php } ?></h1>
			<div class="block-content">
			<div class="description">
			<a href="<?php echo Classified::getPermalink($row->slug);?>" class="display_img">
			<?php $imggal = unserialize($row->image_gallery); ?>
				<?php if($imggal[0] ): ?>
				<img class="list-img" alt="<?php echo $row->title; ?>" title="<?php echo $row->title; ?>" src="<?php echo Site::getThumbUrl($imggal[0],45,45,3); ?>"/>
				<?php else: ?>	
				<img class="list-img" width="45" src="<?php echo STYLEURL.'/images/no-image.gif' ?>" alt="<?php echo $row->title; ?>" title="<?php echo $row->title; ?>" />
				<?php endif; ?>						
			</a>
				<div class="summary">
					<?php if($row->summary): ?>
					<p><?php echo substr(strip_tags($row->summary), 0, 150); ?>... <a class="readmore" href="<?php echo Classified::getPermalink($row->slug);?>">readmore</a></p>
					<?php else: ?>
					<p><?php echo substr(strip_tags($row->description), 0, 150); ?>... <a class="readmore" href="<?php echo Classified::getPermalink($row->slug);?>">readmore</a></p>
					<?php endif; ?>
				</div>
				</div>
				
				</div>
			</li>
			<?php } ?>
		</ul>
		<?php
	}
	
	public static function searching($state='',$district,$category='',$subctegory='',$type='',$keyword='',$place='') {
	$page = $_REQUEST['page'];
	$type = $_REQUEST['type'];
	if(!$page) {$page = 1;}
		if($keyword) {
		$results = Classified::getKeywordSearchResult($keyword,1); 
		$num = Classified::getKeywordSearchResultCount($keyword); 
		} else {
		$results = Classified::getSearchResult($state, $district,$place, $category, $subctegory, $type,$keyword,$page ); 
		$num = Classified::getSearchResultCount($state, $district, $place, $category, $subctegory, $type,$keyword); 
		}
		
		 global $gRating;
		?>
		<?php if($num >0) { ?>
		<?php $searchtitle =""; ?>
		<?php if($state): 
			$stateTitle = Place::getStateBySlug($place);
			$searchtitle.=" > ".$stateTitle;
		endif; ?>
		<?php if($district): 
			$districtTitle = District::getDistrictBySlug($district);
			$searchtitle.=" > ".$districtTitle;
		endif; ?>
		<?php if($category): 
			$categoryTitle = RootCategory::getRootCategoryBySlug($category);
			$searchtitle.=" > ".$categoryTitle;
		endif; ?>
		<?php if($subctegory): 
			$subctegoryTitle = SubCategory::getSubCategoryBySlug($subctegory);
			$searchtitle.=" > ".$subctegoryTitle;
		endif; ?>
		<?php if($keyword): 
			$searchtitle.=" > ".$keyword;
		endif; ?>
		<p class="breadcrumb"><?php echo $searchtitle ?></p>
				<ul class="listing prop-listing">
				<?php foreach ($results as $row) { ?>
					<li>
					<h1><a href="<?php echo Classified::getPermalink($row->slug);?>"><?php echo $row->title ?></a> <?php if($row->updated_date) { ?> [<small><?php echo $row->updated_date; ?></small>]<?php } ?></h1>
					<div class="block-content">
					<div class="description">
					<a href="<?php echo Classified::getPermalink($row->slug);?>">
					<?php $imggal = unserialize($row->image_gallery); ?>
						<?php if($imggal[0] ): ?>
						<img class="list-img" alt="<?php echo $row->title; ?>" title="<?php echo $row->title; ?>" src="<?php echo Site::getThumbUrl($imggal[0],80,80,3); ?>"/>
						<?php else: ?>	
						<img class="list-img" width="80" src="<?php echo STYLEURL.'/images/no-image.gif' ?>" alt="<?php echo $row->title; ?>" title="<?php echo $row->title; ?>" />
						<?php endif; ?>						
					</a>
						<div class="summary">
							<?php if($row->summary): ?>
							<p><?php echo substr(strip_tags($row->summary), 0, 500); ?>... <a class="readmore" href="<?php echo Classified::getPermalink($row->slug);?>">readmore</a></p>
							<?php else: ?>
							<p><?php echo substr(strip_tags($row->description), 0, 500); ?>... <a class="readmore" href="<?php echo Classified::getPermalink($row->slug);?>">readmore</a></p>
							<?php endif; ?>
						</div>
						</div>
						<div class="detail-section">
							<h4><label>Ad Id: </label><?php echo Classified::getPropertyId($row->id) ?></h4>
						<?php if($row->price) { ?>
						<?php $price = Site::showPrice($row->price); ?>
						<?php if($price) { ?>
							<h4><span class='WebRupee'>Rs</span> <?php echo $price  ?></h4>
						<?php } ?>
						<?php } ?>
						<?php if($row->type) { ?>
							<?php $exCategory = array(6,8,11,12); ?>
							<?php if(!in_array($row->category_id, $exCategory)){ ?>
							<h4> <?php echo ucfirst($row->type); ?></h4>
							<?php } ?>
						<?php } ?>
						<?php if($row->place_id) { ?>
						<?php $place =  Place::getPlaceById($row->place_id); ?>
							<h4><?php echo $place ?></h4>
						<?php } ?>
						<!-- <?php //if(Customer::isLoggedIn() && Customer::IssavedClassified($row->id)){?><a href="javascript:;" onclick="Site.saveProperty(this,<?php echo Site::getCurrentUserId();?>,<?php echo $row->id; ?>)" class="link">Save this property</a><?php // } ?> -->
							
							<p><span>Added by: </span><?php echo  $row->fullname; ?></p>
							<p><span>Mobile: </span><?php echo  $row->mobile; ?></p>
							<!--<p><span>Email: </span><?php //echo  $row->email; ?></p> -->
							<a href="<?php echo Classified::getPermalink($row->slug);?>#fragment-2" class="reply">Reply</a>
						</div>
						</div>
					</li>
					<?php } ?>
				</ul>
				
				<?php if($num >30) { ?>	<?php Site::showPaging($num,30,$page,''); } ?>				
				<?php } else { ?>
				<?php global $wp_query; ?>
				<?php if($wp_query->slug == 'all')$first = "Online India " ?>
				<?php
				$uri =  parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
				$uri_segments = split('/', Site::drop_multiple_slashes(ltrim($uri ,'/')));
				$level = count($uri_segments);
				switch ($level) {
				case 2:?>
					<?php $title = "Classified ads in ". ucfirst(str_replace('-', ' ', $first)) ." - ". ucfirst($wp_query->option);
					break;
				case 3:
					$categoryName = RootCategory::getRootCategoryBySlug($uri_segments[2]);
					?>
					<?php $title = "Classified ads in ". ucfirst(str_replace('-', ' ', $first)) ." - ". ucfirst($wp_query->option)." - ". ucfirst($categoryName);
					break;	
				case 4:
					$categoryName = RootCategory::getRootCategoryBySlug($uri_segments[2]);
					$SubCategoryName = SubCategory::getSubCategoryBySlug($uri_segments[3]);
					?>
					<?php $title = "Classified ads in ". ucfirst(str_replace('-', ' ', $first	)) ." - ". ucfirst($wp_query->option)." - ". ucfirst($categoryName) ." - ". ucfirst($SubCategoryName);
					break;				
				} ?>
				<h4> </h4>
				<h4 class="notice-msg">Your search criteria "<?php echo $title ?>" did not match any results. Please check your spelling and try again. You may browse through the recently posted ads below.</h4>
				<?php } ?>
		<?php
	}	
	
	public static function showPaging($numposts,$page_size,$currpage,$function, $flag = true) {
		$max_page = ceil($numposts / $page_size);
		$limter = 5;
		if($currpage == '')	{
			$currpage = 1;
		}
		if($currpage + $limter >$max_page) 	{
			$endpage = $max_page;
		}
		else {
			$endpage = $currpage+($limter);	
		}
		if($currpage-$limter <= 0) {
			$startpage = 1;
		}
		else {
			$startpage = $currpage-($limter);
		}
		if($flag) {
			echo "<div id=paging>";
			echo "<label>Pages:</label>";
		}
		if($max_page!=1) {	
			if($endpage!=$max_page) {
				$next = $currpage+1;
				echo "<a class=\"next-pagebar-a\" href=\"?page={$next}\" >Next</a>";
			}			
			for($i = $startpage;$i<=$endpage;$i++) {	
			if($i!=$currpage) {	
				echo"<a href=\"?page={$i}\" >$i</a> ";
			}
			else {
				echo "<span>".$i."</span>";
			}
		}
		if($startpage!=1){	
			$prev = $currpage-1;
				echo "<a class=\"prev-pagebar-a\" href=\"?page={$prev}\" >Previous</a>";
			}
		}
		if($flag) {
			echo "</div>";
		}
	}
	
	public static function loadSavedClassified() {
		global $gRating;
		$cur_uid = Site::getCurrentUserId();
		$num = Classified::getSavedPropertyCount($cur_uid); 
		$results = Classified::getSavedProperty($cur_uid,$_REQUEST['page']);?>
		<h2><?php the_ttftext("Saved ads",true,"search"); ?></h2>
		<?php if($results) { ?>
	<p>Remove the ads of your choice from the below list</p>
	<ul class="listing">
	<?php foreach ($results as $row) { ?>
		<li>
			<div class="hme-section">
			<?php $img = "noimage.gif"; if($row->image){ $img = $row->image; } ?>
				<a href="<?php echo Classified::getPermalink($row->slug); ?>"><img src="<?php echo Site::getThumbUrl($img,116,87,3); ?>" alt="<?php echo $row->title; ?>" title="<?php echo $row->title; ?>" /></a>
				<h3><a href="<?php echo Classified::getPermalink($row->slug); ?>"><?php echo $row->title;?><a href="<?php echo Classified::getPermalink($row->slug); ?>"></h3>
				<p><?php if($row->no_bedrooms){ echo  $row->no_bedrooms." Bedroom,";}if($row->area){echo $row->area." sq.ft,"; } echo $row->propertytype; ?></p>
				<a href="<?php echo Classified::getPermalink($row->slug);?>" class="link">Details</a>
			</div>
			<div class="price-section">
				<h4> <?php echo  Site::showPrice($row->price); if($row->type=="rent"){ echo "(/yr)"; } ?></h4>
				<?php $rating=$gRating->getRating('property', $row->id);
							$rate=$rating['full']; 	?>
				<img class="rating-star" src="<?php echo STYLEURL;?>/images/rank<?php echo $rate;?>.gif" border="0" alt="rating" width="54" height="10" />
				<p><span>Added by:</span><a href="<?php echo $row->agent_slug; ?>"><?php echo $row->agent_name; ?></a></p>
				<p class="remove-prop"><a href="javascript:;" onclick="Site.deleteProperty(this,<?php echo $cur_uid;?>,<?php echo $row->id; ?>)" >Remove property</a></p>
			</div>
		</li>
	<?php } ?>
	</ul>
		<div id="paging">
				<?php if($num >5) { ?>	<label>Pages:</label><?php Site::showPaging($num,5,$_REQUEST['page'],'Site.loadSavedClassified'); } ?>
		</div>
		<?php
		} else { ?>
		<p class="notice-msg">There is no saved ads</p>
		<?php }
	}
	
	public static function loademailAlertProperty() {
		global $gRating;
		$cur_uid = Site::getCurrentUserId(); 
		$num = Classified::getEmailAlertPropertyCount($cur_uid); 
   		$results = Classified::getEmailAlertProperty($cur_uid, $_REQUEST['page']);
		?>
		<h2><?php the_ttftext("Email Alerts", true, "search"); ?></h2>
	<p>Remove the ads of your choice from the below list</p>
	<?php if($results) { ?>
	<ul class="listing">
	<?php foreach ($results as $row) { ?>
		<li>
			<div class="hme-section">
			<?php $img = "noimage.gif"; if($row->image){ $img = $row->image; } ?>
				<a href="<?php echo Classified::getPermalink($row->slug);?>" ><img src="<?php echo Site::getThumbUrl($img,116,87,3); ?>" alt="<?php echo $row->title;?>" title="<?php echo $row->title;?>" /></a>
				<h3><a href="<?php echo Classified::getPermalink($row->slug); ?>" ><?php echo $row->title;?></a></h3>
				<p><?php if($row->no_bedrooms){ echo  $row->no_bedrooms." Bedroom,";}if($row->area){echo $row->area." sq.ft,"; } echo $row->propertytype; ?></p>
				<a href="<?php echo Classified::getPermalink($row->slug);?>" class="link">Details</a>
				<?php if(Customer::IssavedClassified($row->id)){?><a href="javascript:;" onclick="Site.saveProperty(this,<?php echo $cur_uid;?>,<?php echo $row->id; ?>)" class="link">Save this property</a><?php } ?>
			</div>
			<div class="price-section">
				<h4><?php echo  Site::showPrice($row->price); if($row->type=="rent"){ echo "(/yr)"; } ?></h4>
				<?php $rating=$gRating->getRating('property', $row->id);
							$rate=$rating['full']; 	?>
				<img class="rating-star" src="<?php echo STYLEURL;?>/images/rank<?php echo $rate;?>.gif" border="0" alt="rating" width="54" height="10" />
				<p><span>Added by:</span><a href="<?php echo $row->agent_slug; ?>"><?php echo $row->agent_name;  ?></a></p>
				<p class="remove"><a href="javascript:;" onclick="Site.deleteEmailAlert(this,<?php echo $cur_uid;?>,<?php echo $row->id; ?>)">Remove alert</a></p>
			</div>
		</li>
		<?php } ?>
	</ul>
	<?php if($num >5) { ?>
	<div id="paging">
					<label>Pages:</label><?php Site::showPaging($num,5,$_REQUEST['page'],'Site.loademailAlertProperty');  ?>
		</div>
		<?php } }else{ ?>
		<p class="notice-msg">There is no email alerts ads</p>
	<?php
		}
	}
	
	public static function showExecutives() {
		$exlist = Executive::getExecutive();
		foreach ($exlist as $executive) {
			$photo = "executive-img.gif";
			if($executive->photo){
				$photo = $executive->photo;
			}
			?>
			<li>
				<img src="<?php echo  Site::getThumbUrl($photo, 136, 136, 3); ?>" />
				<h4><?php echo $executive->name; ?></h4>
				<p class="desig"><?php echo $executive->designation; ?></p>
				<p><?php echo $executive->description; ?></p>
				</li>
			<?php
		}
	}
	public static function getClassified($id) {
		global $wpdb;
		$record = $wpdb->get_row("SELECT * FROM {$wpdb->classified} WHERE id='$id';");
		if($record) {
			return $record;
		} 
	}
	public static function __doDeleteAd() {
		$id = $_REQUEST['id'];
		$user =  Customer::current(); 
		$user_id = $user->ID;
		global $wpdb;
		$result = $wpdb->query("DELETE FROM $wpdb->classified WHERE id='{$id}' and user_id = '{$user_id}' ");
		return $result;
	}
	
	public static function getClassifiedByID($id) {
		global $wpdb;
		$record = $wpdb->get_row("SELECT * FROM {$wpdb->classified} WHERE id='$id';",  ARRAY_A);
		if($record) {
			return $record;
		} 
	}
	public static function getClassifiedByIDANDUSERID($id,$userId) {
		global $wpdb;
		$record = $wpdb->get_row("SELECT * FROM {$wpdb->classified} WHERE id='$id' AND user_id='$userId';",  ARRAY_A);
		if($record) {
			return $record;
		} 
	}
	public static function showMessage($message) {
		echo "<div id=\"message\" class=\"updated fade\"><p><strong>$message</strong></p></div>";	
		}
	public static function getDisplayImage($imgArr) {
		if($imgArr) {
			 $data = @unserialize($imgArr);
		    if( $data === false ) {
		        return $imgArr;
		    } else {
		        return $imgArr[0];
		    } 		
		}
	}
	public static function processContent($content) {
		$regex = '$\b(https?|ftp|file)://[-A-Z0-9+&@#/%?=~_|!:,.;]*[-A-Z0-9+&@#/%=~_|]$i';
		preg_match_all($regex, $content, $result, PREG_PATTERN_ORDER);
		foreach ($result[0] as $key=>$value ){
			$content = str_replace($value,"<span class='url'>$value</span>",$content);
		}
		$content = nl2br($content);
		$content = self::wordCleanup($content);
		return $content;
	}
	public static function wordCleanup ($str) {
	    $pattern = "/<(\w+)>(\s|&nbsp;)*<\/\1>/";
	    $str = preg_replace($pattern, '', $str);
	    return mb_convert_encoding($str, 'HTML-ENTITIES', 'UTF-8');
	}
}



Site::init();
