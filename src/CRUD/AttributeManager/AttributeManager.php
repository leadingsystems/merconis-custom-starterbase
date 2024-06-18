<?php

namespace LeadingSystems\MerconisCustomStarterbaseBundle\CRUD\AttributeManager;

class AttributeManager
{
    private ?array $allExistingAttributesAndValues = null;

    public function __construct()
    {
        $this->readAllExistingAttributesAndValues();
    }

    public function createAttributeIfNecessary(string $attributeAlias, string $attributeName): bool
    {
        if ($this->attributeExists($attributeAlias)) {
            return false;
        }

        $this->addAttribute($attributeAlias, $attributeName);

        return true;
    }

    private function attributeExists(string $attributeAlias): bool
    {
        return key_exists($attributeAlias, $this->allExistingAttributesAndValues);
    }

    /*
     * Do me! Check if this class could be optimized regarding its performance by using BatchInsertUpdate
     */
    private function addAttribute(string $attributeAlias, string $attributeName): void
    {
        $dbquery = \Database::getInstance()->prepare("
            INSERT INTO tl_ls_shop_attributes
            SET
                tstamp = ?,
                alias = ?,
                title = ?,
                title_de = ?
        ")
        ->execute(
            time(),
            $attributeAlias,
            $attributeName,
            $attributeName
        );

        $this->allExistingAttributesAndValues[$attributeAlias] = [
            'id' => $dbquery->insertId,
            'values' => []
        ];
    }

    /**
     * @throws \Exception
     */
    public function createValueIfNecessary(string $attributeAlias, string $valueAlias, string $valueName): bool
    {
        if (!$this->attributeExists($attributeAlias)){
            throw new \Exception('Tried to deal with value for non-existing attribute "' . $attributeAlias . '"');
        }

        if ($this->valueExistsInAttribute($attributeAlias, $valueAlias)) {
            return false;
        }

        if ($this->valueExistsAnywhere($valueAlias)) {
            throw new \Exception('value "' . $valueAlias . '" exists but not in the given attribute "' . $attributeAlias . '"');
        }

        $this->addValue($attributeAlias, $valueAlias, $valueName);

        return true;
    }

    /**
     * @throws \Exception
     */
    private function addValue(string $attributeAlias, string $valueAlias, string $valueName): void
    {
        if (!($this->allExistingAttributesAndValues[$attributeAlias]['id'] ?? null)) {
            throw new \Exception('pid required for creating value "' . $valueAlias . '" could not be determined.');
        }

        \Database::getInstance()->prepare("
            INSERT INTO tl_ls_shop_attribute_values
            SET
                tstamp = ?,
                alias = ?,
                title = ?,
                title_de = ?,
                pid = ?
        ")
        ->execute(
            time(),
            $valueAlias,
            $valueName,
            $valueName,
            $this->allExistingAttributesAndValues[$attributeAlias]['id']
        );

        $this->allExistingAttributesAndValues[$attributeAlias]['values'][] = $valueAlias;
        $this->allExistingAttributesAndValues['__allValues'][] = $valueAlias;
    }

    private function valueExistsAnywhere(string $valueAlias): bool
    {
        return in_array($valueAlias, $this->allExistingAttributesAndValues['__allValues']);
    }

    private function valueExistsInAttribute(string $attributeAlias, string $valueAlias): bool
    {
        return in_array($valueAlias, $this->allExistingAttributesAndValues[$attributeAlias]['values']);
    }

    private function readAllExistingAttributesAndValues(): void
    {
        if (is_array($this->allExistingAttributesAndValues)) {
            /*
             * Information has already been read, so nothing to do.
             */
            return;
        }

        $this->allExistingAttributesAndValues = [
            '__allValues' => []
        ];

        $dbres_allExistingAttributesAndValues = \Database::getInstance()->prepare("
            SELECT
                a.id AS attributeId,
                a.alias AS attributeAlias,
                v.alias AS valueAlias
            FROM 
                tl_ls_shop_attributes a
            LEFT JOIN 
                tl_ls_shop_attribute_values v ON v.pid = a.id
            
            UNION
            
            SELECT
                a.id AS attributeId,
                a.alias AS attributeAlias,
                v.alias AS valueAlias
            FROM 
                tl_ls_shop_attributes a
            RIGHT JOIN 
                tl_ls_shop_attribute_values v ON v.pid = a.id
        ")
        ->execute();

        while ($dbres_allExistingAttributesAndValues->next()) {
            if (
                $dbres_allExistingAttributesAndValues->attributeAlias
                && !isset($this->allExistingAttributesAndValues[$dbres_allExistingAttributesAndValues->attributeAlias])
            ) {
                $this->allExistingAttributesAndValues[$dbres_allExistingAttributesAndValues->attributeAlias] = [
                    'id' => $dbres_allExistingAttributesAndValues->attributeId,
                    'values' => []
                ];
            }

            if (!$dbres_allExistingAttributesAndValues->valueAlias) {
                continue;
            }

            if ($dbres_allExistingAttributesAndValues->attributeAlias) {
                $this->allExistingAttributesAndValues[$dbres_allExistingAttributesAndValues->attributeAlias]['values'][] = $dbres_allExistingAttributesAndValues->valueAlias;
            }
            $this->allExistingAttributesAndValues['__allValues'][] = $dbres_allExistingAttributesAndValues->valueAlias;
        }
    }
}