<?php

/** @var \Kirby\Cms\Block $block */

$before  = $block->image_before()->toFile();
$after   = $block->image_after()->toFile();
$caption = $block->caption();
$start   = max(0, min(100, $block->start()->or(50)->toInt()));
$label   = option('sigtrygg-space.kirby-image-compare.label')
	?? t('image-compare.drag', 'Drag to compare');

if (!$before || !$after) {
	return;
}

// derive the frame ratio from the before image so the stage keeps its
// final size even while the images are still loading; the custom
// properties live on the figure so that site rules targeting the stage
// itself can still override them (inline style on the stage would win)
$style = '--image-compare-start: ' . $start . '%;';

if ($before->width() > 0 && $before->height() > 0) {
	$style .= ' --image-compare-ratio: ' . $before->width() . ' / ' . $before->height() . ';';
}
?>
<figure class="image-compare" style="<?= $style ?>">
	<div class="image-compare-stage" data-image-compare data-start="<?= $start ?>">
		<div class="image-compare-before"><?php snippet('image-compare-picture', ['image' => $before]) ?></div>
		<div class="image-compare-after"><?php snippet('image-compare-picture', ['image' => $after]) ?></div>
		<button class="image-compare-handle" type="button" aria-label="<?= esc($label) ?>"></button>
	</div>
	<?php if ($caption->isNotEmpty()) : ?>
	<figcaption><?= $caption->html() ?></figcaption>
	<?php endif ?>
</figure>
