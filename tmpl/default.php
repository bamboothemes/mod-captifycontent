<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 
global $mainframe;
$document =& JFactory::getDocument();
$modbase = ''.JURI::base().'modules/mod_captifyContent/';
$config =& JFactory::getConfig();
$url = $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();
$count	= intval($params->get('count', 5));
$type = $params->get( 'type','category');
$module_id = $params->get('module_id','1');
$ccCSS = $params->get('ccCSS','head');
$ccJS = $params->get('ccJS','head');

// Image Size and container
$imageDimensions = (int)$params->get( 'imageDimensions', '1');
$crop_height = (int)$params->get( 'crop_height', '1');
$crop_width = (int)$params->get( 'crop_width','2');
$image_width = (int)$params->get( 'image_width','234');
$image_height = (int)$params->get( 'image_height','100');
$rightMargin = (int)$params->get('rightMargin','0');
$bottomMargin = (int)$params->get('bottomMargin','0');
$imagesPerRow = (int)$params->get('imagesPerRow', '4');
if ($imagesPerRow < 1) $imagesPerRow = 1;
$colour = $params->get('colour', 'white');
$background = $params->get('background', 'light-background');
// Fade Effects
$fadeEffect = $params->get('fadeEffect','1');

// Captify Parameters
$useCaptify = $params->get( 'useCaptify','0');
$speed = $params->get( 'speed', '800');
$speedOut = $params->get( 'speedOut', '800');
$transition = $params->get( 'transition', 'fade');
$opacity = $params->get( 'opacity', '0.8');
$position = $params->get( 'position', 'bottom');
$displayImages = $params->get('displayImages','k2item');
$titleBelow = $params->get('titleBelow','0');

// Load css into the head
if($ccCSS == 'head') $document->addStyleSheet($modbase.'css/captifyContent.css');

$transitionIn ="";
$transitionOut ="";

if ($transition =="fade") {
$transitionIn = 'fadeIn';	
$transitionOut = 'fadeOut';	
}

if ($transition == "slide" and $position == "bottom") {
$transitionIn = 'slideUp';	
$transitionOut = 'slideDown';	
}

if ($transition == "slide" and $position == "top") {
$transitionIn = 'slideDown';	
$transitionOut = 'slideUp';	
}



if ($useCaptify == '1' || $useCaptify == '2' || $fadeEffect) {

	
	?>
	<script type="text/javascript">
	<!--//--><![CDATA[//><!--
	jQuery.noConflict();
	jQuery(document).ready(function(){

	<?php if ($useCaptify == '1') { ?>
	
	
        jQuery('.viewport').mouseenter(function(e) {
           	var titleSpan = jQuery(this).children('a').children('span');
			if(titleSpan.is(':hidden')){
            titleSpan.<?php echo $transitionIn?>(<?php echo $speed?>);};
        }).mouseleave(function(e) {
           	var titleSpan = jQuery(this).children('a').children('span');
			if(titleSpan.is(':visible')){
				titleSpan.<?php echo $transitionOut?>(<?php echo $speedOut?>);};
        });
 
	<?php }
	
if ($useCaptify == '2') {

	
	if($ccJS == 'head') { 
			$document->addScript($modbase . "js/captify.tiny.js"); 
	}

		?>
		
		jQuery('img.captify<?php echo $module_id?>').captify({
			speedOver: '<?php echo $speed?>',
			speedOut: '<?php echo $speedOut?>',
			hideDelay: 500,	
			animation: '<?php echo $transition?>',		
			prefix: '',		
			opacity: '<?php echo $opacity?>',					
			className: 'caption-bottom',	
			position: '<?php echo $position?>',
			spanWidth: '100%'
		});

		<?php

	}

if ($fadeEffect)
{
	?>
		
		jQuery('img.captify').fadeIn(800); // This sets the opacity of the thumbs to fade down to 60% when the page loads
		jQuery('img.captify').hover(function(){
			jQuery(this).fadeTo('slow', 0.6); // This should set the opacity to 100% on hover
		},function(){
			jQuery(this).fadeTo('slow', 1.0); // This should set the opacity back to 60% on mouseout
		});
  

	<?php } ?>
	    });
  	//--><!]]> 
	</script>
	<?php
}

$numMB = sizeof($list);
$imageNumber = 0;
$startDiv = 0;
$firstImage = "";

if ($type == "section"){ 
?>
<div>
<div id="captifyContent<?php echo $module_id ?>" class="captifyContent cc<?php echo $background?>">
<?php
foreach ($list as $item) :
$sectionImage = $item->image;
if (!($sectionImage == "")) : 
	$imageNumber++;
	$imgRightMargin = ($imageNumber % $imagesPerRow) ? $rightMargin.'px' : '0px';
	$rowFlag = ($imageNumber % $imagesPerRow) ? 0 : 1;
	
	if (($imageNumber == 1) or ($startDiv)) {
		$startDiv = 0;
		
	?>
	<div class="ccRow">
	<?php }?>
	<div class="ccItem" style="margin-right:<?php echo $imgRightMargin ?>; margin-bottom:<?php echo $bottomMargin ?>px;">
	<div class="viewport">
    <a href="<?php echo JRoute::_(ContentHelperRoute::getSectionRoute($item->id).'&layout=blog'); ?>">
 	   <span class="<?php echo $background ?>"><?php echo $item->title;?></span>
		<img src="<?php echo $url ?>modules/mod_captifyContent/image.php?width=<?php echo $image_width ?>&amp;<?php echo $image_height ?>&amp;cropratio=<?php echo $crop_width ?>:<?php echo $crop_height ?>&amp;image=<?php echo JURI::root( true ) ?>/images/stories/<?php echo $item->image;?>" class="captify captify<?php echo $module_id ?>" alt="<?php echo $item->title;?>" <?php if ($imageDimensions) { ?>style="height:<?php echo $image_height ?>px;width:<?php echo $image_width ?>px" <?php } ?> />
	</a>
	</div>
	<?php if($titleBelow) {?>
	<a class="captifyTitle" href="<?php echo $item->link;?>">
		<?php echo $item->title;?>
	</a>
	<?php }?>
	</div>
	<?php 
	if (($imageNumber == $numMB) or ($rowFlag))
	{
		$startDiv = 1;
	?>
		</div>
		<div class="ccClear"></div>
	<?php }?>
<?php endif; ?>	
<?php endforeach; ?>
</div>
</div>
<?php }
elseif ($type == "category" or $type == "k2category") { 
?>
<div>
<div id="captifyContent<?php echo $module_id ?>" class="captifyContent cc<?php echo $background?>">
<?php
foreach ($list as $item) :
$sectionImage = $item->image;
if (!($sectionImage == "")) : 
	$imageNumber++;
	$imgRightMargin = ($imageNumber % $imagesPerRow) ? $rightMargin.'px' : '0px';
	$rowFlag = ($imageNumber % $imagesPerRow) ? 0 : 1;

	if (($imageNumber == 1) or ($startDiv)) {
		$startDiv = 0;
	?>
	<div class="ccRow">
	<?php }?>
<div class="ccItem" style="margin-right:<?php echo $imgRightMargin ?>; margin-bottom:<?php echo $bottomMargin ?>px;">  
<div class="viewport">
	<a href="<?php echo $item->link;?>">
		<span class="<?php echo $background ?>"><?php echo $item->title;?></span>
		<?php if($type == "category") {?>
		<img src="<?php echo $url ?>modules/mod_captifyContent/image.php?width=<?php echo $image_width ?>&amp;<?php echo $image_height ?>&amp;cropratio=<?php echo $crop_width ?>:<?php echo $crop_height ?>&amp;image=<?php echo JURI::root( true ) ?>/images/stories/<?php echo $item->image;?>" class="captify captify<?php echo $module_id ?>" alt="<?php echo $item->title;?>" <?php if ($imageDimensions) { ?>style="height:<?php echo $image_height ?>px;width:<?php echo $image_width ?>px" <?php } ?> />
		<?php }else{?>
		<img src="<?php echo $url ?>modules/mod_captifyContent/image.php?width=<?php echo $image_width ?>&amp;<?php echo $image_height ?>&amp;cropratio=<?php echo $crop_width ?>:<?php echo $crop_height ?>&amp;image=<?php echo JURI::root( true ) .'/'.$item->image;?>" class="captify captify<?php echo $module_id ?>" alt="<?php echo $item->title;?>"  <?php if ($imageDimensions) { ?>style="height:<?php echo $image_height ?>px;width:<?php echo $image_width ?>px" <?php } ?>/>
		<?php }?>
		
	</a>
	</div>
	<?php if($titleBelow) {?>
	<a class="captifyTitle" href="<?php echo $item->link;?>">
		<?php echo $item->title;?>
	</a>
	<?php }?>
	</div>
	<?php 
	if (($imageNumber == $numMB) or ($rowFlag))
	{
		$startDiv = 1;
	?>
		</div>
		<div class="ccClear"></div>
	<?php }?>

<?php endif; ?>	
<?php endforeach; ?>
</div>
</div>

<?php } 

elseif ($type == "content" or $type == "k2") { 
?>
<div>
<div id="captifyContent<?php echo $module_id ?>" class="captifyContent cc<?php echo $background?>">
<?php 

foreach ($list as $item) : 
		
		if($type == "k2" and $displayImages == "k2item")
		{
			$firstImage = $item->firstimage;
		}
		else
		{
			$html= $item->text;
			$html .= "alt='...' title='...' />";
			$pattern = '/<img[^>]+src[\\s=\'"]';
			$pattern .= '+([^"\'>\\s]+)/is';
	
			if(preg_match(
			$pattern,
			$html,
			$match))
			$firstImage = "$match[1]";
		}

 if (!($firstImage == "")) { 
	$imageNumber++;
	$imgRightMargin = ($imageNumber % $imagesPerRow) ? $rightMargin.'px' : '0px';
	$rowFlag = ($imageNumber % $imagesPerRow) ? 0 : 1;
	
	if (($imageNumber == 1) or ($startDiv)) {
		$startDiv = 0;
		
	?>
	<div class="ccRow">
	<?php }?>
<div class="ccItem" style="margin-right:<?php echo $imgRightMargin ?>; margin-bottom:<?php echo $bottomMargin ?>px;"> 
<div class="viewport">
			<a href="<?php echo $item->link; ?>">
			<span class="<?php echo $background ?>"><?php echo $item->title;?></span>
				<img src="modules/mod_captifyContent/image.php?width=<?php echo $image_width ?>&amp;=<?php echo $image_height ?>&amp;cropratio=<?php echo $crop_width ?>:<?php echo $crop_height ?>&amp;image=<?php echo JURI::root( true ) ?>/<?php echo $firstImage ?>" class="captify captify<?php echo $module_id ?>" alt="<?php echo $item->title; ?>" <?php if ($imageDimensions) { ?>style="height:<?php echo $image_height ?>px;width:<?php echo $image_width ?>px" <?php } ?> />
			</a>
		</div>
			<?php if($titleBelow) {?>
			<a class="captifyTitle" href="<?php echo $item->link;?>">
				<?php echo $item->title;?>
			</a>
			<?php }?>
</div>
<?php 
if (($imageNumber == $numMB) or ($rowFlag))
{
	$startDiv = 1;
?>
	</div>
	<div class="ccClear"></div>
<?php }?>		
<?php } ?>
<?php endforeach; ?>
</div>
</div>
<?php } ?>
<div class="clear"></div>

<?php // Load css into the body
if($ccCSS == 'body') { ?>
<link rel="stylesheet" href="modules/mod_captifyContent/css/captifyContent.css" type="text/css" />
<?php } 

if($ccJS == 'body' && $useCaptify == '2') { ?>
<script type="text/javascript" src="modules/mod_captifyContent/js/captify.tiny.js"></script>
<?php } ?>