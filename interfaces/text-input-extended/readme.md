# Interface: Text Input - Extension

Renders a Text Field with additional utilities.  
Support mustache templating.

## Usage

Build for production

```
npm run build
```

---

## Dependents

```html
<v-input></v-input>
```

---

## Options

| Option             | Default | Interface      | Desc                                                                   |
| ------------------ | ------- | -------------- | ---------------------------------------------------------------------- |
| placeholder        |         | [text-input]() | Placeholder Text.                                                      |
| trim               | `true`  | [toggle]()     | Trims the blank space from the start & end of the text.                |
| showCharacterCount | `true`  | [toggle]()     | Displays the number of characters written in text-box.                 |
| iconLeft           |         | [icon]()       | Material Icon to display on the left side.                             |
| iconRight          |         | [icon]()       | Material Icon to display on the right side.                            |
| formatValue        | `false` | [toggle]()     | Pretty output by converting the value to title case.                   |
| width              | auto    | [dropdown]()   | Set text-box width. Available choices are "small", "medium" & "large". |

### Attributes & Events

The HTML `<input>` element supports a huge amount of attributes and events. 
In order to support all of these, all props that aren't specially handled (see list below) will be passed on to the `<input>` element directly. 
This allows you to change anything you want on the input.

### Prefixes / Suffixes

You can add any custom (text) prefix/suffix to the value in the input using the `prefix` and `suffix` slots.

### Props
| Prop        | Description                                    | Default |
|-------------|------------------------------------------------|---------|
| `autofocus` | Auto focus the input on render                 | `false` |
| `disabled`  | Set the disabled state for the input           | `false` |
| `monospace` | Render the entered value in the monospace font | `false` |
| `prefix`    | Prefix the users value with a value            | --      |
| `suffix`    | Show a value at the end of the input           | --      |

Note: more props - all other attached attributes are bound to the input HTMLElement in the component. 
This allows you to attach any of the standard HTML attributes like `min`, `length`, or `pattern`.

### Slots

| Slot            | Description                                       | Data                                             |
|-----------------|---------------------------------------------------|--------------------------------------------------|
| `default`       | The input                                         | `See Props...`                                   |
| `prepend-outer` | Before the input                                  | `{ disabled: boolean, value: string | number; }` |
| `prepend`       | In the input, before the value, before the prefix | `{ disabled: boolean, value: string | number; }` |
| `append`        | In the input, after the value, after the suffix   | `{ disabled: boolean, value: string | number; }` |
| `append-outer`  | After the input                                   | `{ disabled: boolean, value: string | number; }` |

### Events

| Events                | Description                                  | Value |
|-----------------------|----------------------------------------------|-------|
| `input`               | Updates `v-model`                            | `any` |
| `click:prepend-outer` | User clicks on content of outer prepend slot | --    |
| `click:prepend`       | User clicks on content of inner prepend slot | --    |
| `click:append`        | User clicks on content of inner append slot  | --    |
| `click:append-outer`  | User clicks on content of outer append slot  | --    |

Note: more events - all other listeners are bound to the input HTMLElement, allowing you to handle everything from `keydown` to `emptied`.

### Input Component Template and Guide

```html
<template>
<div class="v-input">
	<div v-if="$slots['prepend-outer']" class="prepend-outer">
		<slot name="prepend-outer" :value="value" :disabled="disabled" />
	</div>
	<div class="input" :class="{ disabled, monospace, 'full-width': fullWidth }">
		<div v-if="$slots.prepend" class="prepend">
			<slot name="prepend" :value="value" :disabled="disabled" />
		</div>
		<span v-if="prefix" class="prefix">{{ prefix }}</span>
		<input
			v-bind="$attrs"
			v-focus="autofocus"
			v-on="_listeners"
			:disabled="disabled"
			:value="value"
		/>
		<span v-if="suffix" class="suffix">{{ suffix }}</span>
		<div v-if="$slots.append" class="append">
			<slot name="append" :value="value" :disabled="disabled" />
		</div>
	</div>
	<div v-if="$slots['append-outer']" class="append-outer">
		<slot name="append-outer" :value="value" :disabled="disabled" />
	</div>
</div>
</template>
```

