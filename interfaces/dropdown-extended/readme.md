# Interface: Dropdown Extension

Renders a select component with additional utilities. 
Load dynamic values from a collection.

## Usage

Build for production

```
npm run build
```

---

## Dependents

```html
<v-select></v-select>
<v-spinner></v-spinner>
<v-icon></v-icon>
```

---

### Select Component Template and Guide

```html
<template>
	<div :class="{ icon }" class="v-select">
		<select
			v-if="other"
			:id="otherActive ? null : id"
			:disabled="disabled || readonly"
			:value="value"
			@change="change($event.target.value)"
		>
			<optgroup :label="$t('values')">
				<option v-for="(key, value) in parsedOptions" :key="value" :value="value">
					{{ key }}
				</option>
			</optgroup>
			<optgroup :label="$t('other')">
				<option :value="customValue || '__other'" :selected="otherActive">
					{{ customValue.length ? customValue : $t('enter_value') }}
				</option>
			</optgroup>
		</select>
		<select
			v-else
			:id="otherActive ? null : id"
			ref="select"
			:disabled="disabled || readonly"
			:value="value"
			@change="change($event.target.value)"
		>
			<option v-if="placeholder" ref="default" selected disabled value="">
				{{ placeholder }}
			</option>
			<option
				v-for="(key, optionValue) in parsedOptions"
				:key="optionValue"
				:value="optionValue"
				:selected="value == optionValue"
			>
				{{ key }}
			</option>
		</select>
		<input
			v-if="otherActive"
			:id="id"
			ref="input"
			v-focus
			:type="type"
			:value="customValue"
			:placeholder="placeholder"
			autofocus
			@input="changeCustom"
		/>
		<div class="value">
			<v-icon v-if="icon" :name="icon" />
			<span v-if="placeholder && !value" class="placeholder">{{ placeholder }}</span>
			<span v-if="parsedOptions[value]" class="no-wrap">{{ parsedOptions[value] }}</span>
			<span v-else class="no-wrap">{{ value }}</span>
		</div>
		<v-icon class="chevron" name="arrow_drop_down" />
	</div>
</template>
```