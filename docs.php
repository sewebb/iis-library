<?php // phpcs:ignoreFile

$structure = new SimpleXMLElement( file_get_contents( __DIR__ . '/docs/structure.xml' ) );
$markdown = '';

foreach ($structure->file->function as $function) {
	$markdown .= "## {$function->name}\n";
	$markdown .= "{$function->docblock->description}\n\n";

	$params  = "| Param | Type | Description |\n";
	$params .= "| ----- | ---- | ----------- |\n";
	$tags = [];

	foreach ($function->docblock->tag as $tag) {
		$attributes = ((array)$tag->attributes())['@attributes'];
		$name = $attributes['name'];

		if (!isset($tags[$name])) {
			$tags[$name] = [];
		}

		$tags[$name][] = [
			'description' => $attributes['description'],
			'type' => $attributes['type'],
			'variable' => $attributes['variable'],
		];
	}

	foreach ($tags['param'] as $param) {
		$variable = strip_tags($param['variable']);
		$type = strip_tags($param['type']);
		$description = strip_tags($param['description']);

		$params .= "| {$variable} | {$type} | {$description} |\n";
	}

	$markdown .= "{$params}\n";
	$markdown .= "__Return value__\n\n";

	if (isset($tags['return'])) {
		$return = $tags['return'][0];
		$type = strip_tags($return['type']);
		$description = strip_tags($return['description']);

		$markdown .= "| Type | Description |\n";
		$markdown .= "| ---- | ----------- |\n";
		$markdown .= "| {$type} | {$description} |\n\n";
	}
}

file_put_contents(__DIR__ . '/docs/api.md', $markdown);
