<?php

use Kirby\Cms\App as Kirby;

Kirby::plugin('sigtrygg-space/kirby-image-compare', [
	'options' => [
		// overrides the handle's aria-label (useful on single-language
		// sites, where t() always resolves the English translation)
		'label' => null
	],
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
	],
	'hooks' => [
		// inject the frontend assets into the head of every rendered
		// document that contains the block — per document (not per
		// request), and before Kirby's page cache stores the HTML
		'page.render:after' => function (string $contentType, array $data, string $html, $page) {
			if (
				$contentType !== 'html' ||
				str_contains($html, 'data-image-compare') === false ||
				($head = strpos($html, '</head>')) === false
			) {
				return $html;
			}

			$plugin = $this->plugin('sigtrygg-space/kirby-image-compare');
			$tags   = '<link rel="stylesheet" href="' . $plugin->asset('image-compare.css')->url() . '">' . PHP_EOL
				. '<script src="' . $plugin->asset('image-compare.js')->url() . '" defer></script>' . PHP_EOL;

			return substr_replace($html, $tags, $head, 0);
		}
	]
]);
