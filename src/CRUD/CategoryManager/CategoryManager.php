<?php

namespace LeadingSystems\MerconisCustomStarterbaseBundle\CRUD\CategoryManager;

class CategoryManager
{
    private ?array $allExistingPages = null;
    private string $aliasOfPageToDuplicate = '';
    private ?array $pageToDuplicateOriginalData = null;
    private bool $temporaryTableAlreadyCreated = false;

    public function __construct()
    {
        $this->readAllExistingPages();
    }

    public function setAliasOfPageToDuplicate(string $aliasOfPageToDuplicate): void
    {
        $this->removeTemporaryTableForPageToDuplicate();
        $this->aliasOfPageToDuplicate = $aliasOfPageToDuplicate;
        $this->createTemporaryTableForPageToDuplicate();
    }

    /**
     * Creates a category page in the page structure. The alias is made up of the transferred bindingAliasPart
     * and the optionalAliasExtension
     * Pages that already exist are not created (check in categoryAlreadyExists)
     * Optionally, the alias of a parent page ($parentAlias) can also be specified.
     *
     * The creation itself takes place via a page data record serving as a template, which is used to fill a temporary
     * table is filled.
     * (The reason for this is that you do not want to take field or structure changes to the tl_page into account in the future).
     * The original data of the template is stored in $this->pageToDuplicateOriginalData
     * This temporary entry is always updated with the respective alias, title or parentid data for each new page.
     * and is then used for the new entry.
     * In allExistingPages the aliases are cached and in $GLOBALS['merconis-custom-cache']['pageIDForAlias'] the
     * Assignment of aliases to PageIDs
     *
     * @param   {string}    $bindingAliasPart       main part of alias
     * @param   {string}    $title                  page title
     * @param   {string}    $optionalAliasExtension optional alias prefix
     * @param   {string}    $parentAlias            alias of parent page
     * @throws \Exception
     * @return  {bool}                              true if new page inserted, false if already existed
     */
    public function createCategoryIfNecessary(string $bindingAliasPart, string $title, ?string $optionalAliasExtension = null, ?string $parentAlias = null): bool
    {
        if (!$this->temporaryTableAlreadyCreated) {
            throw new \Exception('creating categories requires the temporary table to be created and this requires the alias of the page to duplicate to be set');
        }

        if ($this->categoryAlreadyExists($bindingAliasPart)) {
            return false;
        }

        $alias = $bindingAliasPart . ($optionalAliasExtension ? '_' . $optionalAliasExtension : '');


        //If the ParentAlias has been specified, the page should be placed under it
        if ($parentAlias) {

            $parentID = $this->getPageIDForAlias($parentAlias);

            \Database::getInstance()
                ->prepare("
                        UPDATE tmp_shop_category_page
                        SET alias = ?,
                            title = ?,
                            published = ?,
                            pid = ?
                    ")
                ->execute(
                    $alias,
                    $title,
                    '1',
                    $parentID
                );

            //Note: at this moment the Tmp table has the pid of the passed $parentAlias. Consequently
            //this pid is retained if NO own $parentAlias is passed in the next call.
            //For performance reasons, there is now no further update to reset the pid. Instead
            //the pid of the copy template is stored in $this->pageToDuplicateOriginalData

        } else {

            //Because the pid of the tmp table may have been changed, it is reset here to the pid of the copy template
            //reset

            \Database::getInstance()
                ->prepare("
                        UPDATE tmp_shop_category_page
                        SET alias = ?,
                            title = ?,
                            published = ?,
                            pid = ?
                    ")
                ->execute(
                    $alias,
                    $title,
                    '1',
                    $this->pageToDuplicateOriginalData['pid']
                );
        }

        $insertPage= \Database::getInstance()
            ->prepare("
                    INSERT INTO tl_page
                    SELECT * FROM tmp_shop_category_page LIMIT 1
                ")
            ->execute();

        //For performance reasons, the pageid for this page alias is stored in the globals
        $pageID = (int) $insertPage->insertId;
        if (!isset($GLOBALS['merconis-custom-cache']['pageIDForAlias'][$alias])) {
            $GLOBALS['merconis-custom-cache']['pageIDForAlias'][$alias] = $pageID;
        }

        $this->allExistingPages[] = $alias;

        return true;
    }

    /** Returns the page ID of a page for the transferred alias.
     *  Results are cached in globals.
     *
     * @param   {string}    $alias              unique Alias from tl_page
     * @return  {integer}
     */
    public function getPageIDForAlias(string $alias): ?int
    {
        if (!isset($GLOBALS['merconis-custom-cache']['pageIDForAlias'][$alias])) {

            $pageQuery = \Database::getInstance()
                ->prepare("
                        SELECT id FROM tl_page WHERE alias = ?
                    ")
                ->execute(
                    $alias
                );

            if (!$pageQuery->numRows) {
                throw new \Exception('The alias of a page ´'.$alias.'´ that does not exist was passed');
            }

            $pageID = $pageQuery->first()->row()['id'];

            $GLOBALS['merconis-custom-cache']['pageIDForAlias'][$alias] = $pageID;
        }

        return $GLOBALS['merconis-custom-cache']['pageIDForAlias'][$alias];
    }


    private function categoryAlreadyExists(string $bindingAliasPart): bool
    {
        if (!isset($GLOBALS['merconis-custom-cache'][__METHOD__][$bindingAliasPart])) {
            $categoryAlreadyExists = false;
            foreach ($this->allExistingPages as $pageAlias) {
                if (str_starts_with($pageAlias, $bindingAliasPart)) {
                    $categoryAlreadyExists = true;
                    break;
                }
            }

            if (!$categoryAlreadyExists) {
                /*
                 * We don't cache the result if the page didn't exist because we expect that to change during runtime.
                 */
                return $categoryAlreadyExists;
            }

            $GLOBALS['merconis-custom-cache'][__METHOD__][$bindingAliasPart] = $categoryAlreadyExists;
        }

        return $GLOBALS['merconis-custom-cache'][__METHOD__][$bindingAliasPart];
    }

    private function createTemporaryTableForPageToDuplicate(): void
    {
        if ($this->temporaryTableAlreadyCreated) {
            return;
        }

        $this->temporaryTableAlreadyCreated = true;

        $pageQuery = \Database::getInstance()
            ->prepare("
                    SELECT * FROM tl_page WHERE alias = ?
                ")
            ->execute(
                $this->aliasOfPageToDuplicate
            );

            if (!$pageQuery->numRows) {
                throw new \Exception('The alias of a page ´'.$this->aliasOfPageToDuplicate.'´ that does not exist was passed');
            }

        $this->pageToDuplicateOriginalData = $pageQuery->first()->row();


        /*
         * Create a temporary table that contains only the page record that we want to duplicate. We do this because
         * we don't want to have to explicitly name all the fields that we want to copy but instead automatically
         * copy the whole record. But we need the new id of the newly created page record to be set automatically with
         * auto increment. We achieve this by setting the id of the record in the temporary table to null
         */
        \Database::getInstance()
            ->prepare("
                CREATE TEMPORARY TABLE tmp_shop_category_page
                SELECT *
                FROM tl_page
                WHERE alias = ?
            ")
            ->execute(
                $this->aliasOfPageToDuplicate
            );

        \Database::getInstance()
            ->prepare("
                ALTER TABLE tmp_shop_category_page MODIFY id INT(10) NULL;
            ")
            ->execute();

        \Database::getInstance()
            ->prepare("
                UPDATE tmp_shop_category_page SET id = NULL;
            ")
            ->execute();
    }

    private function removeTemporaryTableForPageToDuplicate(): void
    {
        if (!$this->aliasOfPageToDuplicate) {
            return;
        }

        \Database::getInstance()
            ->prepare("
                DROP TEMPORARY TABLE IF EXISTS tmp_shop_category_page
            ")
            ->execute();

        $this->pageToDuplicateOriginalData = null;

        $this->temporaryTableAlreadyCreated = false;
    }

    /*
     * Cleaning up the temporary table with the TerminateListener should not really be necessary, because when the script
     * ends the temporary table should be removed anyway, but it seems to be cleaner to do it, and you never know...
     */
    public function cleanup(): void
    {
        $this->removeTemporaryTableForPageToDuplicate();
    }

    private function readAllExistingPages(): void
    {
        if (is_array($this->allExistingPages)) {
            return;
        }

        $dbres_allExistingPages = \Database::getInstance()
            ->prepare("
                        SELECT  alias
                        FROM    tl_page
                    ")
            ->execute();

        $this->allExistingPages = [];

        while ($dbres_allExistingPages->next()) {
            $this->allExistingPages[] =  $dbres_allExistingPages->alias;
        }
    }
}