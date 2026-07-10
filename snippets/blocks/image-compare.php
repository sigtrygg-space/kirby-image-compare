<?php

/** @var \Kirby\Cms\Block $block */

$before  = $block->image_before()->toFile();
$after   = $block->image_after()->toFile();
$caption = $block->caption();
$start   = max(0, min(100, $block->start()->or(50)->toInt()));

if (!$before || !$after) {
	return;
}

// derive the frame ratio from the before image so the stage
// keeps its final size even while the images are still loading
$ratio = $before->width() > 0 && $before->height() > 0
	? ' --image-compare-ratio: ' . $before->width() . ' / ' . $before->height() . ';'
	: '';
?>
<?php if (($GLOBALS['kirbyImageCompareAssets'] ?? false) !== true) : ?>
<?php
	// inject the frontend assets once per request; keeps the plugin zero-config
	$GLOBALS['kirbyImageCompareAssets'] = true;
	$base = $block->kirby()->plugin('sigtrygg-space/kirby-image-compare')->mediaUrl();
?>
<link rel="stylesheet" href="<?= $base ?>/image-compare.css">
<script src="<?= $base ?>/image-compare.js" defer></script>
<?php endif ?>
<figure class="image-compare">
	<div
		class="image-compare-stage"
		data-image-compare
		data-start="<?= $start ?>"
		style="--image-compare-start: <?= $start ?>%;<?= $ratio ?>"
	>
		<div class="image-compare-before"><?php snippet('image-compare-picture', ['image' => $before]) ?></div>
		<div class="image-compare-after"><?php snippet('image-compare-picture', ['image' => $after]) ?></div>
		<button class="image-compare-handle" type="button" aria-label="<?= t('image-compare.drag', 'Drag to compare') ?>"></button>
	</div>
	<?php if ($caption->isNotEmpty()) : ?>
	<figcaption><?= $caption->html() ?></figcaption>
	<?php endif ?>
</figure>
