# Interface: Text Input HTML - Extension

Renders a Text Field for displaying Raw HTML Snippets.

## Usage

Build for production

```
npm run build
```

## ⚙️ Options

Checkout common interface options(passed via `props`) [here.](https://github.com/directus/app/blob/develop/src/interfaces/README.md)

| Option             | Default | Interface      | Desc                                                                  |
| ------------------ | ------- | -------------- | --------------------------------------------------------------------- |
| placeholder        |         | [text-input]() | Placeholder Text.                                                     |
| trim               | `true`  | [toggle]()     | Trims the blank space from the start & end of the text.               |
| showCharacterCount | `true`  | [toggle]()     | Displays the number of characters written in textbox.                 |
| iconLeft           |         | [icon]()       | Material Icon to display on the left side.                            |
| iconRight          |         | [icon]()       | Material Icon to display on the right side.                           |
| formatValue        | `false` | [toggle]()     | Pretty output by converting the value to title case.                  |
| width              | auto    | [dropdown]()   | Set textbox width. Available choices are "small", "medium" & "large". |

## 🚧 Notes

-   Not for displaying large HTML codes - use a WYSIWYG Interface instead.

