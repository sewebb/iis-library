# API
[View code](src/helpers.php)

## iis_config
_[View code at line 10](../src/helpers.php#L10)_

IIS Start config helper

| Param | Type | Default | Description |
| ----- | ---- | ------- | ----------- |
| $keys | string |  | the key to get the value for. Use dot notation for going deeper. |

__Return value__

| Type | Description |
| ---- | ----------- |
|  |  |

## iis_remember
_[View code at line 36](src/helpers.php#L36)_

Cache return value of callback if not already cached and return the contents

| Param | Type | Default | Description |
| ----- | ---- | ------- | ----------- |
| $cache_key | string |  | The name of the cached content. |
| $cache_time | integer |  | How long the content should be cached. |
| $callback | callable |  | The callback that returns the content that should be cached. |

__Return value__

| Type | Description |
| ---- | ----------- |
|  |  |

## iis_safe_get_input
_[View code at line 54](src/helpers.php#L54)_

Escape input recursively

| Param | Type | Default | Description |
| ----- | ---- | ------- | ----------- |
| $input | mixed |  | Input parameter. |

__Return value__

| Type | Description |
| ---- | ----------- |
|  |  |

## iis_safe_get
_[View code at line 74](src/helpers.php#L74)_

Return a safe GET value with an optional default value.

| Param | Type | Default | Description |
| ----- | ---- | ------- | ----------- |
| $key | string |  | The key for the $_GET array. |
| $default | null|string | null | Default value if GET variable doesn't exist. |

__Return value__

| Type | Description |
| ---- | ----------- |
|  |  |

## iis_active_class
_[View code at line 95](src/helpers.php#L95)_

Echo an "active"-class if the comparison is true, otherwise
an empty string.

| Param | Type | Default | Description |
| ----- | ---- | ------- | ----------- |
| $value | string |  | The value to compare against. |
| $compare_with | string | null | The value to compare with. |
| $class | string | 'is-active' | The class that should be echoed if true. |
| $include_attr | boolean | true | True if class attribute should be included. |

__Return value__

| Type | Description |
| ---- | ----------- |
|  |  |

## iis_active
_[View code at line 110](src/helpers.php#L110)_

Echo a string if the comparison is true, otherwise empty string

| Param | Type | Default | Description |
| ----- | ---- | ------- | ----------- |
| $value | string |  | The value to compare against. |
| $compare_with | string|array | null | The value to compare with. |
| $attr | string | ' checked' | The attribute that should be echoed if $echo is true. |
| $echo | boolean | true | True if class attribute should be included. |

__Return value__

| Type | Description |
| ---- | ----------- |
|  |  |

## iis_mix_manifest
_[View code at line 131](src/helpers.php#L131)_

Get the laravel mix manifest

| Param | Type | Default | Description |
| ----- | ---- | ------- | ----------- |

__Return value__

| Type | Description |
| ---- | ----------- |
|  |  |

## iis_mix
_[View code at line 156](src/helpers.php#L156)_

Get the path to a versioned Mix file

| Param | Type | Default | Description |
| ----- | ---- | ------- | ----------- |
| $path | string |  | Path tp mix manifest. |
| $base | string | '/assets/' | Base path to scripts. |

__Return value__

| Type | Description |
| ---- | ----------- |
|  |  |

