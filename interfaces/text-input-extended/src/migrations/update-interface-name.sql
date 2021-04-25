# Normalize the interface nomenclature

UPDATE 
  `directus_fields` 
SET 
  `interface` = 'text-input-extended' 
WHERE 
  `interface` = 'text-input-html';