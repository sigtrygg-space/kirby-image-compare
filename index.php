<?php

use Kirby\Cms\App as Kirby;

Kirby::plugin('sigtrygg-space/kirby-image-compare', [
	'blueprints' => [
		'blocks/image-compare' => __DIR__ . '/blueprints/blocks/image-compare.yml'
	],
	'snippets' => [
		'blocks/image-compare'  => __DIR__ . '/snippets/blocks/image-compare.php',
		'image-compare-picture' => __DIR__ . '/snippets/image-compare-picture.php'
	],
	'assets' => [
		'image-compare.css' => __DIR__ . '/assets/image-compare.css',
		'image-compare.js'  => __DIR__ . '/assets/image-compare.js'
	],
	'translations' => [
		'en' => [
			'image-compare.drag' => 'Drag to compare'
		],
		'de' => [
			'image-compare.drag' => 'Bildvergleich verschieben'
		]
	]
]);
