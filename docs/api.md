## iis_config
IIS Start config helper

| Param | Type | Description |
| ----- | ---- | ----------- |
| $keys | string | the key to get the value for. Use dot notation for going deeper. |

__Return value__

| Type | Description |
| ---- | ----------- |
| mixed | The value (if found) for the given key. |

## iis_remember
Cache return value of callback if not already cached and return the contents

| Param | Type | Description |
| ----- | ---- | ----------- |
| $cache_key | string | The name of the cached content. |
| $cache_time | integer | How long the content should be cached. |
| $callback | callable | The callback that returns the content that should be cached. |

__Return value__

| Type | Description |
| ---- | ----------- |
| mixed|null |  |

## iis_safe_get_input
Escape input recursively

| Param | Type | Description |
| ----- | ---- | ----------- |
| $input | mixed | Input parameter. |

__Return value__

| Type | Description |
| ---- | ----------- |
| array|string |  |

## iis_safe_get
Return a safe GET value with an optional default value.

| Param | Type | Description |
| ----- | ---- | ----------- |
| $key | string | The key for the $_GET array. |
| $default | null|string | Default value if GET variable doesn't exist. |

__Return value__

| Type | Description |
| ---- | ----------- |
| null|string |  |

## iis_active_class
Echo an "active"-class if the comparison is true, otherwise
an empty string.

| Param | Type | Description |
| ----- | ---- | ----------- |
| $value | string | The value to compare against. |
| $compare_with | string | The value to compare with. |
| $class | string | The class that should be echoed if true. |
| $include_attr | boolean | True if class attribute should be included. |

__Return value__

| Type | Description |
| ---- | ----------- |
| void |  |

## iis_active
Echo a string if the comparison is true, otherwise empty string

| Param | Type | Description |
| ----- | ---- | ----------- |
| $value | string | The value to compare against. |
| $compare_with | string|array | The value to compare with. |
| $attr | string | The attribute that should be echoed if $echo is true. |
| $echo | boolean | True if class attribute should be included. |

__Return value__

| Type | Description |
| ---- | ----------- |
| boolean|string|void |  |

## iis_mix_manifest
Get the laravel mix manifest

| Param | Type | Description |
| ----- | ---- | ----------- |

__Return value__

| Type | Description |
| ---- | ----------- |
| array|null |  |

## iis_mix
Get the path to a versioned Mix file

| Param | Type | Description |
| ----- | ---- | ----------- |
| $path | string | Path tp mix manifest. |
| $base | string | Base path to scripts. |

__Return value__

| Type | Description |
| ---- | ----------- |
| string|null |  |

