<?php // no direct accessdefined('_JEXEC') or die('Restricted access'); $helper = JPATH_SITE.DS.'media'.DS.'plg_jblibrary'.DS.'helpers'.DS.'image.php';if (file_exists($helper)){ 	require_once($helper); } else {	echo '<div style="font-size:12px;font-family: helvetica neue, arial, sans serif;width:600px;margin:0 auto;background: #f9f9f9;border:1px solid #ddd ;margin-top:100px;padding:40px"><h3>Ooops. It looks like JbLibrary plugin is not installed!</h3> <br />Please install it and ensure that you have enabled the plugin by navigating to extensions > plugin manager. <br /><br />JB Library is a free Joomla extension that you can download directly from the <a href="http://www.joomlabamboo.com/joomla-extensions/jb-library-plugin-a-free-joomla-jquery-plugin">Joomla Bamboo website</a>.</div>';}// Load css into the bodyif($loadCSS== 'body') { ?><link rel="stylesheet" href="<?php echo $modbase?>css/captifyContent.css" type="text/css" /><?php } if($loadJS == 'body' && $useCaptify == '2') { ?><script type="text/javascript" src="<?php echo $modbase?>js/captify.tiny.js"></script><?php }$transitionIn ="";$transitionOut ="";if ($transition =="fade") {$transitionIn = 'fadeIn';	$transitionOut = 'fadeOut';	}if ($transition == "slide" and $position == "bottom") {$transitionIn = 'slideUp';	$transitionOut = 'slideDown';	}if ($transition == "slide" and $position == "top") {$transitionIn = 'slideDown';	$transitionOut = 'slideUp';	}if ($useCaptify == '1' || $useCaptify == '2' || $fadeEffect) {		?>	<script type="text/javascript">	<!--//--><![CDATA[//><!--	jQuery.noConflict();	jQuery(document).ready(function(){	<?php if ($useCaptify == '1') { ?>		        jQuery('.viewport').mouseenter(function(e) {           	var titleSpan = jQuery(this).children('a').children('span');			if(titleSpan.is(':hidden')){            titleSpan.slideToggle(<?php echo $speed?>);};        }).mouseleave(function(e) {           	var titleSpan = jQuery(this).children('a').children('span');			if(titleSpan.is(':visible')){				titleSpan.slideToggle(<?php echo $speedOut?>);};        }); 	<?php }	if ($useCaptify == '2') {		if($loadJS == 'head') { 			$document->addScript($modbase . "js/captify.tiny.js"); 	}		?>				jQuery('img.captify<?php echo $module_id?>').captify({			speedOver: '<?php echo $speed?>',			speedOut: '<?php echo $speedOut?>',			hideDelay: 500,				animation: '<?php echo $transition?>',					prefix: '',					opacity: '<?php echo $opacity?>',								className: 'caption-bottom',				position: '<?php echo $position?>',			spanWidth: '100%'		});		<?php	}if ($fadeEffect){	?>				jQuery('img.captify').fadeIn(800); // This sets the opacity of the thumbs to fade down to 60% when the page loads		jQuery('img.captify').hover(function(){			jQuery(this).fadeTo('slow', 0.6); // This should set the opacity to 100% on hover		},function(){			jQuery(this).fadeTo('slow', 1.0); // This should set the opacity back to 60% on mouseout		});  	<?php } ?>	    });  	//--><!]]> 	</script>	<?php}$numMB = sizeof($list);$imageNumber = 0;$startDiv = 0;$firstImage = "";if ($type == "section"){ ?><div><div id="captifyContent<?php echo $module_id ?>" class="captifyContent cc<?php echo $background?>"><?phpforeach ($list as $item) :$sectionImage = $item->image;if (!($sectionImage == "")) : 	$imageNumber++;	$imgRightMargin = ($imageNumber % $imagesPerRow) ? $rightMargin.'px' : '0px';	$rowFlag = ($imageNumber % $imagesPerRow) ? 0 : 1;		if (($imageNumber == 1) or ($startDiv)) {		$startDiv = 0;			?>	<div class="ccRow">	<?php }?>	<div class="ccItem" style="margin-right:<?php echo $imgRightMargin ?>; margin-bottom:<?php echo $bottomMargin ?>px;">	<div class="viewport">    <a href="<?php echo JRoute::_(ContentHelperRoute::getSectionRoute($item->id).'&layout=blog'); ?>"> 	  <?php if (!($transition == "slide" and $position == "bottom")) :?><span class="<?php echo $background ?>"><?php echo $item->title;?></span><?php endif;?>		<img src="<?php echo resizeImageHelper::getResizedImage('/images/stories/'.$item->image, $image_width, $image_height, $option); ?>" class="captify captify<?php echo $module_id ?>" alt="<?php echo $item->title;?>" <?php if ($imageDimensions) { ?>style="height:<?php echo $image_height ?>px;width:<?php echo $image_width ?>px" <?php } ?> />			<?php if ($transition == "slide" and $position == "bottom") :?><span class="<?php echo $background ?> bottom"><?php echo $item->title;?></span><?php endif;?>	</a>	</div>	<?php if($titleBelow) {?>	<a class="captifyTitle" href="<?php echo $item->link;?>">		<?php echo $item->title;?>	</a>	<?php }?>	</div>	<?php 	if (($imageNumber == $numMB) or ($rowFlag))	{		$startDiv = 1;	?>		</div>		<div class="ccClear"></div>	<?php }?><?php endif; ?>	<?php endforeach; ?></div></div><?php }elseif ($type == "category" or $type == "k2category") { ?><div><div id="captifyContent<?php echo $module_id ?>" class="captifyContent cc<?php echo $background?>"><?phpforeach ($list as $item) :$sectionImage = $item->image;if (!($sectionImage == "")) : 	$imageNumber++;	$imgRightMargin = ($imageNumber % $imagesPerRow) ? $rightMargin.'px' : '0px';	$rowFlag = ($imageNumber % $imagesPerRow) ? 0 : 1;	if (($imageNumber == 1) or ($startDiv)) {		$startDiv = 0;	?>	<div class="ccRow">	<?php }?><div class="ccItem" style="margin-right:<?php echo $imgRightMargin ?>; margin-bottom:<?php echo $bottomMargin ?>px;">  <div class="viewport">	<a href="<?php echo $item->link;?>">		<?php if (!($transition == "slide" and $position == "bottom")) :?><span class="<?php echo $background ?>"><?php echo $item->title;?></span><?php endif;?>		<?php if($type == "category") {?>		<img src="<?php echo resizeImageHelper::getResizedImage('/images/stories/'.$item->image, $image_width, $image_height, $option); ?>" class="captify captify<?php echo $module_id ?>" alt="<?php echo $item->title;?>" <?php if ($imageDimensions) { ?>style="height:<?php echo $image_height ?>px;width:<?php echo $image_width ?>px" <?php } ?> />		<?php }else{?>		<img src="<?php echo resizeImageHelper::getResizedImage('/'.$item->image, $image_width, $image_height, $option); ?>" class="captify captify<?php echo $module_id ?>" alt="<?php echo $item->title;?>"  <?php if ($imageDimensions) { ?>style="height:<?php echo $image_height ?>px;width:<?php echo $image_width ?>px" <?php } ?>/>		<?php }?>				<?php if ($transition == "slide" and $position == "bottom") :?><span class="<?php echo $background ?> bottom"><?php echo $item->title;?></span><?php endif;?>			</a>	</div>	<?php if($titleBelow) {?>	<a class="captifyTitle" href="<?php echo $item->link;?>">		<?php echo $item->title;?>	</a>	<?php }?>	</div>	<?php 	if (($imageNumber == $numMB) or ($rowFlag))	{		$startDiv = 1;	?>		</div>		<div class="ccClear"></div>	<?php }?><?php endif; ?>	<?php endforeach; ?></div></div><?php } elseif ($type == "content" or $type == "k2") { ?><div><div id="captifyContent<?php echo $module_id ?>" class="captifyContent cc<?php echo $background?>"><?php foreach ($list as $item) : 				if($type == "k2" and $displayImages == "k2item")		{			$firstImage = $item->firstimage;		}		else		{			$html= $item->text;			$html .= "alt='...' title='...' />";			$pattern = '/<img[^>]+src[\\s=\'"]';			$pattern .= '+([^"\'>\\s]+)/is';				if(preg_match(			$pattern,			$html,			$match))			$firstImage = "$match[1]";		} if (!($firstImage == "")) { 	$imageNumber++;	$imgRightMargin = ($imageNumber % $imagesPerRow) ? $rightMargin.'px' : '0px';	$rowFlag = ($imageNumber % $imagesPerRow) ? 0 : 1;		if (($imageNumber == 1) or ($startDiv)) {		$startDiv = 0;			?>	<div class="ccRow">	<?php }?><div class="ccItem" style="margin-right:<?php echo $imgRightMargin ?>; margin-bottom:<?php echo $bottomMargin ?>px;width: <?php echo $image_width ?>px"> <div class="viewport">			<a href="<?php echo $item->link; ?>">			<?php if (!($transition == "slide" and $position == "bottom")) :?><span class="<?php echo $background ?>"><?php echo $item->title;?></span><?php endif;?>				<img src="<?php echo resizeImageHelper::getResizedImage('/'.$firstImage, $image_width, $image_height, $option); ?>" class="captify captify<?php echo $module_id ?>" alt="<?php echo $item->title; ?>" <?php if ($imageDimensions) { ?>style="height:<?php echo $image_height ?>px;width:<?php echo $image_width ?>px" <?php } ?> />					<?php if ($transition == "slide" and $position == "bottom") :?><span class="<?php echo $background ?> bottom"><?php echo $item->title;?></span><?php endif;?>			</a>		</div>			<?php if($titleBelow) {?>			<a class="captifyTitle" href="<?php echo $item->link;?>">				<?php echo $item->title;?>			</a>			<?php }?></div><?php if (($imageNumber == $numMB) or ($rowFlag)){	$startDiv = 1;?>	</div>	<div class="ccClear"></div><?php }?>		<?php } ?><?php endforeach; ?></div></div><?php } ?><div class="clear"></div>