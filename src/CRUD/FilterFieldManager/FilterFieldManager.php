<?php

namespace LeadingSystems\MerconisCustomHoehenflugBundle\CRUD\FilterFieldManager;

class FilterFieldManager
{
    private ?array $allExistingFilterFields = null;
    private string $aliasOfRecordToDuplicate = '';
    private bool $temporaryTableAlreadyCreated = false;
    private $temporaryTableName = 'tmp_shop_filter_field';


    public function __construct()
    {
        $this->readAllExistingFilterFields();
    }

    public function setAliasOfRecordToDuplicate(string $aliasOfRecordToDuplicate): void
    {
        $this->removeTemporaryTableForRecordToDuplicate();
        $this->aliasOfRecordToDuplicate = $aliasOfRecordToDuplicate;
        $this->createTemporaryTableForRecordToDuplicate();
    }

    public function createFilterFieldIfNecessary(string $alias, string $title, int $sourceAttributeId ): bool
    {
        if ($this->filterFieldExists($alias)) {
            return false;
        }

        $this->addFilterField($alias, $title, $sourceAttributeId);

        return true;
    }

    private function addFilterField(string $alias, string $title, int $sourceAttributeId): void
    {
        if (!$this->temporaryTableAlreadyCreated) {
            throw new \Exception('filter field can not be added because the temporary table is missing.' . (!$this->aliasOfRecordToDuplicate ? ' Set the alias of the record to duplicate with "setAliasOfRecordToDuplicate()" in order to create the temporary table.' : ''));
        }

        \Database::getInstance()
            ->prepare("
                    UPDATE " . $this->temporaryTableName . "
                    SET
                        tstamp = ?,
                        alias = ?,
                        title = ?,
                        title_de = ?,
                        sourceAttribute = ?,
                        published = ?
                ")
            ->execute(
                time(),
                $alias,
                $title,
                $title,
                $sourceAttributeId,
                '1'
            );

        $dbquery = \Database::getInstance()
            ->prepare("
                    INSERT INTO tl_ls_shop_filter_fields
                    SELECT * FROM " . $this->temporaryTableName . " LIMIT 1
                ")
            ->execute();

        $this->allExistingFilterFields[$alias] = (int) $dbquery->insertId;
    }

    private function filterFieldExists($alias): bool
    {
        return key_exists($alias, $this->allExistingFilterFields);
    }

    private function readAllExistingFilterFields(): void
    {
        if (is_array($this->allExistingFilterFields)) {
            /*
             * Information has already been read, so nothing to do
             */
            return;
        }

        $dbres_allExistingFilterFields = \Database::getInstance()
            ->prepare("
                SELECT
                    id,
                    alias
                FROM
                    tl_ls_shop_filter_fields
            ")
            ->execute();

        $this->allExistingFilterFields = [];

        while ($dbres_allExistingFilterFields->next()) {
            $this->allExistingFilterFields[$dbres_allExistingFilterFields->alias] = $dbres_allExistingFilterFields->id;
        }
    }

    private function removeTemporaryTableForRecordToDuplicate(): void
    {
        if (!$this->aliasOfRecordToDuplicate) {
            return;
        }

        \Database::getInstance()
            ->prepare("
                DROP TEMPORARY TABLE IF EXISTS " . $this->temporaryTableName . "
            ")
            ->execute();

        $this->temporaryTableAlreadyCreated = false;
    }

    private function createTemporaryTableForRecordToDuplicate(): void
    {
        if (!$this->aliasOfRecordToDuplicate) {
            throw new \Exception('cannot create temporary table if alias of record to duplicate is not set.');
        }

        $dbres_filterFieldTemplate = \Database::getInstance()
            ->prepare("
                SELECT *
                FROM tl_ls_shop_filter_fields
                WHERE alias = ?
            ")
            ->execute($this->aliasOfRecordToDuplicate);

        if (!$dbres_filterFieldTemplate->numRows) {
            throw new \Exception('No filter field record found for the given alias to duplicate ("' . $this->aliasOfRecordToDuplicate . '"). Temporary table was not created.');
        }

        if ($this->temporaryTableAlreadyCreated) {
            return;
        }

        $this->temporaryTableAlreadyCreated = true;

        /*
         * Create a temporary table that contains only the record that we want to duplicate. We do this because
         * we don't want to have to explicitly name all the fields that we want to copy but instead automatically
         * copy the whole record. But we need the new id of the newly created record to be set automatically with
         * auto increment. We achieve this by setting the id of the record in the temporary table to null
         */
        \Database::getInstance()
            ->prepare("
                CREATE TEMPORARY TABLE " . $this->temporaryTableName . "
                SELECT *
                FROM tl_ls_shop_filter_fields
                WHERE alias = ?
            ")
            ->execute(
                $this->aliasOfRecordToDuplicate
            );

        \Database::getInstance()
            ->prepare("
                ALTER TABLE " . $this->temporaryTableName . " MODIFY id INT(10) NULL;
            ")
            ->execute();

        \Database::getInstance()
            ->prepare("
                UPDATE " . $this->temporaryTableName . " SET id = NULL;
            ")
            ->execute();
    }
}