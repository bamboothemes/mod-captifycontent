<?php
/**
 * @package		##package##
 * @subpackage	##subpackage##
 * @author		##author##
 * @copyright 	##copyright##
 * @license		##license##
 * @version		##version##
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
?>

<?php if ($scripts && $cache) : ?>
	<link rel="stylesheet" href="<?php echo $modbase?>css/captifyContent.css" type="text/css" />

	<?php if ($useCaptify == '2') : ?>
		<script type="text/javascript" src="<?php echo $modbase?>js/captify.tiny.js"></script>
	<?php endif; ?>
<?php endif; ?>

<?
if ($useCaptify == '1' || $useCaptify == '2' || $fadeEffect) {?>

<script type="text/javascript">
	<!--//--><![CDATA[//><!--
	jQuery.noConflict();
	jQuery(document).ready(function(){

		<?php if ($useCaptify == '1') { ?>
			jQuery('.viewport').mouseenter(function(e) {
			var titleSpan = jQuery(this).children('a').children('span');
			if(titleSpan.is(':hidden')){

			<?php if($transition == "fade") {?>
				titleSpan.fadeIn(<?php echo $speed?>);
			<?php }else {?>
				titleSpan.slideToggle(<?php echo $speed?>);
			<?php }?>
			};

		}).mouseleave(function(e) {
			var titleSpan = jQuery(this).children('a').children('span');
			if(titleSpan.is(':visible')){
				<?php if($transition == "fade"){?>
					titleSpan.fadeOut(<?php echo $speed?>);
				<?php }else {?>
				titleSpan.slideToggle(<?php echo $speed?>);
				<?php }?>
			};
		});
	<?php }

if ($useCaptify == '2') { ?>

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
	<?php }

if ($fadeEffect) { ?>
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

$numMB = sizeof($list);
$imageNumber = 0;
$startDiv = 0;
$firstImage = "";

if ($contentSource == "category" || $contentSource == "k2category") { ?>
<div>
	<div id="captifyContent<?php echo $module_id ?>"
		class="captifyContent cc<?php echo $background?>">
		<?php
		foreach ($list as $item) :
		$html= $item->description;
		$html .= "alt='...' title='...' />";
		$pattern = '/<img[^>]+src[\\s=\'"]';
		$pattern .= '+([^"\'>\\s]+)/is';

		if(preg_match(
				$pattern,
				$html,
				$match))
				$item->image = "$match[1]";
				$sectionImage = $item->image;

				if (!($sectionImage == "")) :
				$imageNumber++;
				$imgRightMargin = ($imageNumber % $imagesPerRow) ? $rightMargin.'px' : '0px';
				$rowFlag = ($imageNumber % $imagesPerRow) ? 0 : 1;

				if (($imageNumber == 1) || ($startDiv)) {
				$startDiv = 0;

				?>

		<div class="ccRow">
			<?php }?>
			<div class="ccItem" style="margin-right:<?php echo $imgRightMargin ?>; margin-bottom:<?php echo $bottomMargin ?>px;">
				<div class="viewport">
					<a href="<?php echo $item->link;?>"> <?php if (!($transition == "slide" && $position == "bottom")) :?><span
						class="<?php echo $background ?>"><?php echo $item->title;?> </span>
						<?php endif;?> <?php if($type == "category") {?> <img src="<?php echo resizeImageHelper::getResizedImage('/'.$item->image, $image_width, $image_height, $option); ?>" class="captify captify<?php echo $module_id ?>" alt="<?php echo $item->title;?>" <?php if ($imageDimensions) { ?>style="height:<?php echo $image_height ?>px;width:<?php echo $image_width ?>px" <?php } ?> />

						<?php }else{?> <img src="<?php echo resizeImageHelper::getResizedImage('/'.$item->image, $image_width, $image_height, $option); ?>" class="captify captify<?php echo $module_id ?>" alt="<?php echo $item->title;?>"  <?php if ($imageDimensions) { ?>style="height:<?php echo $image_height ?>px;width:<?php echo $image_width ?>px" <?php } ?>/>
						<?php }?> <?php if ($transition == "slide" && $position == "bottom") :?><span
						class="<?php echo $background ?> bottom"><?php echo $item->title;?>
					</span> <?php endif;?>
					</a>
				</div>

				<?php if($titleBelow) {?>
				<a class="captifyTitle" href="<?php echo $item->link;?>"> <?php echo $item->title;?>
				</a>
				<?php }?>
			</div>
			<?php
			if (($imageNumber == $numMB) || ($rowFlag))
			{
$startDiv = 1; ?>
		</div>
		<div class="ccClear"></div>
		<?php }?>
		<?php endif; ?>
		<?php endforeach; ?>
	</div>
</div>

<?php }
elseif ($contentSource == "content" || $contentSource == "k2") { ?>
<div>
	<div id="captifyContent<?php echo $module_id ?>"
		class="captifyContent cc<?php echo $background?>">
		<?php
		foreach ($list as $item) :
			if ($type == "k2" && $displayImages == "k2item")
			{
				$firstImage = $item->firstimage;
			}
			else
			{
				$html = $item->text;
				$html .= "alt='...' title='...' />";
				$pattern = '/<img[^>]+src[\\s=\'"]';
				$pattern .= '+([^"\'>\\s]+)/is';

				if (preg_match($pattern, $html, $match))
				{
					$firstImage = "$match[1]";
				}
			}

			if (!($firstImage == ""))
			{
				$imageNumber++;
				$imgRightMargin = ($imageNumber % $imagesPerRow) ? $rightMargin.'px' : '0px';
				$rowFlag = ($imageNumber % $imagesPerRow) ? 0 : 1;

				if (($imageNumber == 1) || ($startDiv))
				{
					$startDiv = 0;
					?>
					<div class="ccRow">
					<?php
				}
				?>
				<div class="ccItem" style="margin-right:<?php echo $imgRightMargin ?>; margin-bottom:<?php echo $bottomMargin ?>px;width: <?php echo $image_width ?>px">
					<div class="viewport">
						<a href="<?php echo $item->link; ?>"> <?php if (!($transition == "slide" && $position == "bottom")) :?><span
							class="<?php echo $background ?>"><?php echo $item->title;?> </span>
							<?php endif;?> <img src="<?php echo resizeImageHelper::getResizedImage('/'.$firstImage, $image_width, $image_height, $option); ?>" class="captify captify<?php echo $module_id ?>" alt="<?php echo $item->title; ?>" <?php if ($imageDimensions) { ?>style="height:<?php echo $image_height ?>px;width:<?php echo $image_width ?>px" <?php } ?> />
							<?php if ($transition == "slide" && $position == "bottom") :?><span
							class="<?php echo $background ?> bottom"><?php echo $item->title;?>
						</span> <?php endif;?>
						</a>
					</div>

					<?php if($titleBelow) : ?>
						<a class="captifyTitle" href="<?php echo $item->link;?>"> <?php echo $item->title;?></a>
					<?php endif; ?>
				</div>
				<?php
				if (($imageNumber == $numMB) or ($rowFlag))
				{
					$startDiv = 1;
					?>
					</div>
					<div class="ccClear"></div>
					<?php
				}
			}

			endforeach;
		?>
	</div>
</div>
<?php
}
?>

<div class="clear"></div>
