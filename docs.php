<?php // phpcs:ignoreFile

$structure = new SimpleXMLElement( file_get_contents( __DIR__ . '/docs/structure.xml' ) );
$markdown = "# API\n";
$markdown .= "[View code](../src/helpers.php)\n\n";

foreach ($structure->file->function as $function) {
	$line = $function->docblock['line'];
	$symbol = [];
	$tags = [];

	foreach ($function->docblock->tag as $tag) {
		$attributes = ((array)$tag->attributes())['@attributes'];
		$name = $attributes['name'];

		if ($name == 'return') {
			$tags[$name] = [
				'description' => $attributes['description'],
				'type' => $attributes['type'],
			];

			continue;
		}

		if (!isset($tags[$name])) {
			$tags[$name] = [];
		}

		$tags[$name][$attributes['variable']] = [
			'description' => $attributes['description'],
			'type' => $attributes['type'],
			'variable' => $attributes['variable'],
		];
	}

	foreach ($function->argument as $argument) {
		$name = (string)$argument->name;

		if (isset($tags['param'][$name])) {
			$tags['param'][$name]['default'] = $argument->default;
		}
	}

	$params  = "| Param | Type | Default | Description |\n";
	$params .= "| ----- | ---- | ------- | ----------- |\n";

	foreach ($tags['param'] as $param) {
		$variable = strip_tags($param['variable']);
		$type = strip_tags($param['type']);
		$description = strip_tags($param['description']);
		$default = strip_tags($param['default']);

		$params .= "| {$variable} | {$type} | {$default} | {$description} |\n";

		$symbol[] = "{$type} {$string}";
	}

	$markdown .= "## {$function->name}\n";
	$markdown .= "_[View code at line {$line}](../src/helpers.php#L{$line})_\n\n";
	$markdown .= "{$function->docblock->description}\n\n";

	$markdown .= "{$params}\n";
	$markdown .= "__Return value__\n\n";

	if (isset($tags['return'])) {
		$return = $tags['return'];
		$type = strip_tags($return['type']);
		$description = strip_tags($return['description']);

		$markdown .= "| Type | Description |\n";
		$markdown .= "| ---- | ----------- |\n";
		$markdown .= "| {$type} | {$description} |\n\n";
	}
}

file_put_contents(__DIR__ . '/docs/api.md', $markdown);
