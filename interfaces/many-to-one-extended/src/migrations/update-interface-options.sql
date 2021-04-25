# Remove the default and move to interface.JSON.options.templates

UPDATE 
  `directus_fields` 
SET 
  `options` = REPLACE(
	`options`, '"filters":[{"field":"field","operator":"operator","value":"value"}]', 
	'"filters":[]'
  ) 
WHERE 
  `interface` = 'many-to-one-extended' 
  AND `options` LIKE '%"filters":[{"field":"field","operator":"operator","value":"value"}]%';