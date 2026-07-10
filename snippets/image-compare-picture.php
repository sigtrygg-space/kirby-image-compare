<?php

/**
 * Self-contained responsive <picture> for the image comparison block,
 * built on Kirby's own srcset() — no external image pipeline required.
 *
 * @var \Kirby\Cms\File|null $image
 * @var string|null $sizes
 */

if (isset($image) === false || $image->type() !== 'image') {
	return;
}

$sizes ??= '(min-width: 1200px) 720px, 100vw';
$widths  = [480, 768, 1024, 1200, 1440];

$srcset = function (string $format) use ($image, $widths): string|null {
	$set = [];

	foreach ($widths as $width) {
		$set[$width . 'w'] = [
			'width'   => $width,
			'format'  => $format,
			'quality' => 88
		];
	}

	return $image->srcset($set);
};

$webp     = $srcset('webp');
$jpg      = $srcset('jpg');
$fallback = $image->resize(1200);
$alt      = $image->alt()->or($image->caption())->value() ?? '';
?>
<picture class="image-compare-pic">
	<?php if ($webp) : ?>
	<source type="image/webp" srcset="<?= $webp ?>" sizes="<?= $sizes ?>">
	<?php endif ?>
	<?php if ($jpg) : ?>
	<source type="image/jpeg" srcset="<?= $jpg ?>" sizes="<?= $sizes ?>">
	<?php endif ?>
	<img
		src="<?= $fallback->url() ?>"
		<?php if ($fallback->width() > 0 && $fallback->height() > 0) : ?>
		width="<?= $fallback->width() ?>"
		height="<?= $fallback->height() ?>"
		<?php endif ?>
		alt="<?= esc($alt) ?>"
		loading="lazy"
		decoding="async"
	>
</picture>
