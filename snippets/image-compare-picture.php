<?php

/**
 * Self-contained responsive <picture> for the image comparison block,
 * built on Kirby's own srcset() — no external image pipeline required.
 *
 * Tunable site-wide via plugin options (widths, formats, quality, sizes,
 * fallback); `sizes` can also be passed per call as a snippet variable.
 *
 * @var \Kirby\Cms\File|null $image
 * @var string|null $sizes
 */

if (isset($image) === false || $image->type() !== 'image') {
	return;
}

$mimes = [
	'avif' => 'image/avif',
	'webp' => 'image/webp',
	'jpg'  => 'image/jpeg',
	'png'  => 'image/png'
];

$sizes   ??= option('sigtrygg-space.kirby-image-compare.sizes');
$widths    = option('sigtrygg-space.kirby-image-compare.widths');
$formats   = option('sigtrygg-space.kirby-image-compare.formats');
$quality   = option('sigtrygg-space.kirby-image-compare.quality');
$fallback  = $image->resize(option('sigtrygg-space.kirby-image-compare.fallback'));

$srcset = function (string $format) use ($image, $widths, $quality): string|null {
	$set = [];

	foreach ($widths as $width) {
		$options = [
			'width'  => $width,
			'format' => $format
		];

		// int, per-format map, or null (= Kirby's thumbs config default)
		$q = is_array($quality) ? ($quality[$format] ?? null) : $quality;

		if ($q !== null) {
			$options['quality'] = $q;
		}

		$set[$width . 'w'] = $options;
	}

	return $image->srcset($set);
};

$alt = $image->alt()->or($image->caption())->value() ?? '';
?>
<picture class="image-compare-pic">
	<?php foreach ($formats as $format) : ?>
	<?php if (isset($mimes[$format]) && ($set = $srcset($format))) : ?>
	<source type="<?= $mimes[$format] ?>" srcset="<?= $set ?>" sizes="<?= $sizes ?>">
	<?php endif ?>
	<?php endforeach ?>
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
