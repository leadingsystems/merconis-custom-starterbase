<?php

namespace LeadingSystems\MerconisCustomHoehenflugBundle\ImportFileHandler;

use Contao\Database;
use LeadingSystems\MerconisCustomHoehenflugBundle\ImportFileHandler\Enum\Mode;
use LeadingSystems\MerconisCustomHoehenflugBundle\ImportFileHandler\Enum\ProgressMemoryMode;
use Merconis\Core\ls_shop_singularStorage;
use SimpleXMLElement;
use SplFileObject;

class ImportFileHandler
{
    private string $projectDir;
    private string $pathToInputFolder;
    private string $pathToReadingFolder;
    private string $pathToSuccessFolder;
    private ?string $pathToFailedFolder;
    private string $fileNamePattern = '*.[cC][sS][vV]';
    private Mode $mode = Mode::CSV;
    private ProgressMemoryMode $progressMemoryMode = ProgressMemoryMode::FAST;
    private string $csvSeparator = ',';
    private string $csvEnclosure = '"';
    private string $csvEscape = '\\';
    private bool $csvHasHeadline = false;
    private ?array $csvArrayKeys = null;
    private bool $moveBrokenCsvFilesToFailedFolder = false;
    private SplFileObject|SimpleXMLElement|null $openedFile = null;
    private array|null $openedDatabaseResult = null;
    private ?string $dbQuery = null;
    private ?string $dbQueryPrepared = null;
    private ?string $dbQueryExecutedOn = null;
    private ?array $dbQueryParams = null;
    private ?string $xpath = null;
    private string $singularStorageKeyLastLineRead = '';
    private string $singularStorageKeyLastLineReadPrefix = 'int_importFileHandler_lastLineRead_'; // The prefix "int" is important because singularStorage detects the required field type in the db from this prefix
    private string $singularStorageKeyTmpFileNotes = '';
    private string $singularStorageKeyTmpFileNotesPrefix = 'arr_importFileHandler_tmpFileNotes_'; // The prefix "int" is important because singularStorage detects the required field type in the db from this prefix
    private ?int $maxLinesToRead = 10;
    private bool $lastLineInRound = false;
    private bool $nearTimeout = false;
    private int $lineNr = 0;
    private bool $xmlOutputVerbose = false;
    private array $elementNamesToAlwaysReadAsList = [];

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    public function setPathToInputFolder(string $pathToInputFolder): bool
    {
        if (empty($pathToInputFolder)) {
            throw new \Exception('pathToInputFolder must not be empty.');
        }
        $this->handleSettingChangeWithAlreadyOpenedDataSource();
        $this->pathToInputFolder = $this->projectDir . '/' . $pathToInputFolder;
        if (!file_exists($this->pathToInputFolder)) {
            if (!mkdir($this->pathToInputFolder, 0777, true)) {
                return false;
            }
        }
        return true;
    }

    public function setPathToReadingFolder(string $pathToReadingFolder): bool
    {
        if (empty($pathToReadingFolder)) {
            throw new \Exception('pathToReadingFolder must not be empty.');
        }
        $this->handleSettingChangeWithAlreadyOpenedDataSource();
        $this->pathToReadingFolder = $this->projectDir . '/' . $pathToReadingFolder;
        if (!file_exists($this->pathToReadingFolder)) {
            if (!mkdir($this->pathToReadingFolder, 0777, true)) {
                return false;
            }
        }
        return true;
    }

    public function setPathToSuccessFolder(string $pathToSuccessFolder): bool
    {
        if (empty($pathToSuccessFolder)) {
            throw new \Exception('pathToSuccessFolder must not be empty.');
        }
        $this->handleSettingChangeWithAlreadyOpenedDataSource();
        $this->pathToSuccessFolder = $this->projectDir . '/' . $pathToSuccessFolder;
        if (!file_exists($this->pathToSuccessFolder)) {
            if (!mkdir($this->pathToSuccessFolder, 0777, true)) {
                return false;
            }
        }
        return true;
    }

    public function setPathToFailedFolder(?string $pathToFailedFolder): bool
    {
        if (empty($pathToFailedFolder)) {
            throw new \Exception('pathToFailedFolder must not be empty.');
        }
        $this->handleSettingChangeWithAlreadyOpenedDataSource();
        $this->pathToFailedFolder = $this->projectDir . '/' . $pathToFailedFolder;
        if (!file_exists($this->pathToFailedFolder)) {
            if (!mkdir($this->pathToFailedFolder, 0777, true)) {
                return false;
            }
        }
        return true;
    }

    public function setFileNamePattern(string $fileNamePattern): void
    {
        $this->handleSettingChangeWithAlreadyOpenedDataSource();
        $this->fileNamePattern = $fileNamePattern;
    }

    public function setMode(Mode $mode): void
    {
        $this->handleSettingChangeWithAlreadyOpenedDataSource();
        $this->mode = $mode;
    }

    public function getMode(): Mode
    {
        return $this->mode;
    }

    public function setProgressMemoryMode(ProgressMemoryMode $progressMemoryMode): void
    {
        $this->handleSettingChangeWithAlreadyOpenedDataSource();
        /*
         * TODO: Implement functionality for HYBRID.
         *
         * HYBRID should work as follows:
         *
         * - CPU time spent (if that is reasonable, otherwise actual time spent) must be measured.
         *   Property "$this->>nearTimeout" already exists and is meant to be used for that.
         *
         * - If time spent comes close to max_execution_time, progress memory should be handled as
         *   it would be with "ACCURATE", until then it should be handled as it would be with "FAST".
         */
        if ($progressMemoryMode === ProgressMemoryMode::HYBRID) {
            throw new \Exception('ProgressMemoryMode::HYBRID is not supported yet.');
        }
        $this->progressMemoryMode = $progressMemoryMode;
    }

    public function setCsvSeparator(string $csvSeparator): void
    {
        $this->handleSettingChangeWithAlreadyOpenedDataSource();
        $this->csvSeparator = $csvSeparator;
    }

    public function setCsvEnclosure(string $csvEnclosure): void
    {
        $this->handleSettingChangeWithAlreadyOpenedDataSource();
        $this->csvEnclosure = $csvEnclosure;
    }

    public function setCsvEscape(string $csvEscape): void
    {
        $this->handleSettingChangeWithAlreadyOpenedDataSource();
        $this->csvEscape = $csvEscape;
    }

    public function setCsvHasHeadline(bool $csvHasHeadline): void
    {
        $this->handleSettingChangeWithAlreadyOpenedDataSource();
        $this->csvHasHeadline = $csvHasHeadline;
    }

    public function setCsvArrayKeys(?array $csvArrayKeys): void
    {
        $this->handleSettingChangeWithAlreadyOpenedDataSource();
        $this->csvArrayKeys = $csvArrayKeys;
    }

    public function setMaxLinesToRead(?int $maxLinesToRead): void
    {
        $this->handleSettingChangeWithAlreadyOpenedDataSource();
        $this->maxLinesToRead = $maxLinesToRead;
    }

    public function setMoveBrokenCsvFilesToFailedFolder(bool $moveBrokenCsvFilesToFailedFolder): void
    {
        $this->moveBrokenCsvFilesToFailedFolder = $moveBrokenCsvFilesToFailedFolder;
    }

    public function setXpath(?string $xpath): void
    {
        $this->xpath = $xpath;
    }

    public function setXmlOutputVerbose(bool $xmlOutputVerbose): void
    {
        $this->xmlOutputVerbose = $xmlOutputVerbose;
    }

    public function setElementNamesToAlwaysReadAsList(array $elementNamesToAlwaysReadAsList): void
    {
        $this->elementNamesToAlwaysReadAsList = $elementNamesToAlwaysReadAsList;
    }

    public function setDbQuery(?string $dbQuery): void
    {
        $this->dbQuery = $dbQuery;
    }

    public function setDbQueryParams(?array $dbQueryParams): void
    {
        $this->dbQueryParams = $dbQueryParams;
    }

    public function hasFileToRead(): bool
    {
        $this->openDataSource();
        if ($this->mode === Mode::DB) {
            return is_array($this->openedDatabaseResult);
        } else {
            return (bool)$this->openedFile;
        }
    }

    public function readFile(): string|false
    {
        if ($this->mode !== Mode::RAW) {
            throw new \Exception(__METHOD__ . ' only works in mode "RAW"');
        }
        $this->openDataSource();
        if (!$this->openedFile) {
            return false;
        }
        $this->openedFile->rewind();
        return $this->openedFile->fread($this->openedFile->getSize());
    }

    public function readDbResult(): array|false
    {
        if ($this->mode !== Mode::DB) {
            throw new \Exception(__METHOD__ . ' only works in mode "DB"');
        }
        $this->openDataSource();
        if (is_null($this->openedDatabaseResult)) {
            return false;
        }
        return $this->openedDatabaseResult;
    }

    /**
     * @throws \Exception
     */
    public function readLine(?int $lineNr = null): \Generator|false
    {
        $this->openDataSource();

        if ($this->mode === Mode::DB) {
            if (!is_array($this->openedDatabaseResult)) {
                return false;
            }
        } else {
            if (!$this->openedFile) {
                return false;
            }
        }

        if ($this->mode === Mode::CSV && $this->csvHasHeadline) {
            $this->handleCsvHeadline();
        }

        if ($lineNr) {
            $this->lineNr = $lineNr;
            ls_shop_singularStorage::getInstance()->{$this->singularStorageKeyLastLineRead} = $this->lineNr;
        }

        switch ($this->mode) {
            case MODE::XML:
                yield from $this->readFromXml();
                break;

            case MODE::DB:
                yield from $this->readFromDbResult();
                break;

            case MODE::RAW:
            case Mode::CSV:
            default:
                yield from $this->readFromRawOrCsv();
                break;
        }

        if ($this->progressMemoryMode === ProgressMemoryMode::FAST || $this->progressMemoryMode === ProgressMemoryMode::HYBRID) {
            ls_shop_singularStorage::getInstance()->{$this->singularStorageKeyLastLineRead} = $this->lineNr;
        }

        if ($this->mode === MODE::DB && $this->endReached()) {
            ls_shop_singularStorage::getInstance()->{$this->singularStorageKeyLastLineRead} = null;
            ls_shop_singularStorage::getInstance()->{$this->singularStorageKeyTmpFileNotes} = null;
        }
    }

    private function readFromDbResult(): ?\Generator
    {
        $maxLinesCounter = $this->maxLinesToRead;
        if (!$this->openedDatabaseResult) {
            return false;
        }

        $count = count($this->openedDatabaseResult);

        if ($this->lineNr > $count - 1) {
            return null;
        }

        for ($i = $this->lineNr; $i < $count; $i++) {
            if (!$maxLinesCounter) {
                break;
            } else if ($maxLinesCounter === 1) {
                $this->lastLineInRound = true;
            }
            $maxLinesCounter--;
            yield $this->openedDatabaseResult[$i];

            $this->lineNr++;
            if ($this->progressMemoryMode === ProgressMemoryMode::ACCURATE || ($this->progressMemoryMode === ProgressMemoryMode::HYBRID && $this->nearTimeout)) {
                ls_shop_singularStorage::getInstance()->{$this->singularStorageKeyLastLineRead} = $this->lineNr;
            }
        }
    }

    /*
     * This method reads nodes from the opened XML file and does not interfere with reading nodes "line by line"
     * with the readLine() method. This means that if a file is read with readLine() in multiple steps while
     * storing the current position between those steps, reading an XML node with this method does not change
     * the position.
     */
    public function readXmlXpath(string $xpath): array
    {
        if ($this->mode !== Mode::XML) {
            throw new \Exception('reading XML node requires mode to be set to XML');
        }

        $nodes = $this->openedFile->xpath($xpath);
        $data = [];

        if (is_array($nodes)) {
            foreach ($nodes as $node) {
                $data[] = $this->getNodeData($node);
            }
        }

        if (array_is_list($data) && count($data) === 1) {
            $data = $data[0];
        }

        return $data;
    }

    private function readFromXml(): ?\Generator
    {
        if (!$this->xpath) {
            throw new \Exception('Mode::XML requires the xpath to be set');
        }

        $maxLinesCounter = $this->maxLinesToRead;
        $nodes = $this->openedFile->xpath($this->xpath);
        if (!$nodes) {
            return false;
        }

        $count = count($nodes);

        if ($this->lineNr > $count - 1) {
            return null;
        }

        for ($i = $this->lineNr; $i < $count; $i++) {
            if (!$maxLinesCounter) {
                break;
            } else if ($maxLinesCounter === 1) {
                $this->lastLineInRound = true;
            }
            $maxLinesCounter--;
            $node = $nodes[$i];
            $data = $this->getNodeData($node);

            yield $data;

            $this->lineNr++;
            if ($this->progressMemoryMode === ProgressMemoryMode::ACCURATE || ($this->progressMemoryMode === ProgressMemoryMode::HYBRID && $this->nearTimeout)) {
                ls_shop_singularStorage::getInstance()->{$this->singularStorageKeyLastLineRead} = $this->lineNr;
            }
        }
    }

    private function getNodeData(SimpleXMLElement $node): array|string
    {
        $data = [];

        // Include attributes
        foreach ($node->attributes() as $key => $value) {
            $data['@attributes'][$key] = (string) $value;
        }

        // Include text content of the node if any
        $textContent = trim((string) $node);
        if ($textContent !== '') {
            $data['@text'] = $textContent;
        }

        foreach ($node->children() as $child) {
            $childValue = $this->getNodeData($child);

            if (!isset($data[$child->getName()])) {
                if (in_array($child->getName(), $this->elementNamesToAlwaysReadAsList)) {
                    $data[$child->getName()][] = $childValue;
                } else {
                    $data[$child->getName()] = $childValue;
                }
            } else {
                if (!is_array($data[$child->getName()]) || !array_is_list($data[$child->getName()])) {
                    $data[$child->getName()] = [$data[$child->getName()]];
                }
                $data[$child->getName()][] = $childValue;
            }
        }

        if (!$this->xmlOutputVerbose && count($data) === 1 && key_exists('@text', $data)) {
            $data = $data['@text'];
        }

        return $data;
    }

    private function readFromRawOrCsv(): \Generator
    {
        $maxLinesCounter = $this->maxLinesToRead;
        $this->openedFile->seek($this->lineNr);
        while (!$this->openedFile->eof() && $maxLinesCounter > 0) {
            if ($maxLinesCounter === 1) {
                $this->lastLineInRound = true;
            }
            $maxLinesCounter--;
            $lineData = $this->openedFile->current();

            if ($this->lineNr === 0) {
                $lineData[0] = $this->removeBom($lineData[0]);
            }

            /*
             * Skip empty lines because SplFileObject::DROP_NEW_LINE and SplFileObject::SKIP_EMPTY don't seem to work
             */
            if (is_array($lineData) && count($lineData) === 1 && $lineData[0] === null) {
                $maxLinesCounter++;
                $this->openedFile->next();
                $this->lineNr++;
                if ($this->progressMemoryMode === ProgressMemoryMode::ACCURATE || ($this->progressMemoryMode === ProgressMemoryMode::HYBRID && $this->nearTimeout)) {
                    ls_shop_singularStorage::getInstance()->{$this->singularStorageKeyLastLineRead} = $this->lineNr;
                }
                continue;
            }
            if ($this->mode === Mode::CSV && is_array($this->csvArrayKeys)) {
                if (count($this->csvArrayKeys) !== count($lineData)) {
                    /*
                     * In case of a broken CSV format we can't just leave the file in the reading folder.
                     * The error would repeat over and over again until an admin would eventually remove the file.
                     * In this situation the admin would probably forget to also remove the corresponding
                     * "lastLineRead" entry in the singular storage in the database which would later cause another
                     * file with the same name to be read from the wrong line. Therefore, we move the file either
                     * to the failed folder or back to the input folder, based on the settings and whether
                     * the failed folder even exists.
                     */
                    if ($this->moveBrokenCsvFilesToFailedFolder) {
                        $this->moveToFailedFolder(prefix: 'BROKEN_CSV');
                    } else {
                        $this->moveToInputFolder(prefix: 'BROKEN_CSV', addTimestamp: false);
                    }
                    throw new \Exception('Number of ' . ($this->csvHasHeadline ? 'headline fields' : 'given array keys') .' does not match the number of data fields in row ' . $this->lineNr);
                }

                yield array_combine($this->csvArrayKeys, $lineData);
            } else {
                yield $lineData;
            }
            $this->openedFile->next();
            $this->lineNr++;
            if ($this->progressMemoryMode === ProgressMemoryMode::ACCURATE || ($this->progressMemoryMode === ProgressMemoryMode::HYBRID && $this->nearTimeout)) {
                ls_shop_singularStorage::getInstance()->{$this->singularStorageKeyLastLineRead} = $this->lineNr;
            }
        }
    }

    public function getCurrentLineNumber(): int|false
    {
        if (!$this->openedFile && !is_array($this->openedDatabaseResult)) {
            return false;
        }

        return $this->lineNr;
    }

    public function getCurrentDataSourceName(): string|false
    {
        if ($this->mode === Mode::DB) {
            return $this->getCurrentDbQuery();
        } else {
            return $this->getCurrentFileName();
        }
    }

    private function getCurrentDbQuery(): string|false
    {
        if (!is_array($this->openedDatabaseResult)) {
            return false;
        }

        return $this->dbQueryPrepared;
    }

    private function getCurrentFileName(): string|false
    {
        if (!$this->openedFile) {
            return false;
        }

        if ($this->mode === Mode::XML) {
            return basename($this->getFileCurrentlyInReadingFolder());
        } else {
            return $this->openedFile->getFilename();
        }
    }

    public function getCurrentFilePath(): string|false
    {
        if (!$this->openedFile) {
            return false;
        }

        if ($this->mode === Mode::XML) {
            return str_replace($this->projectDir, '', $this->getFileCurrentlyInReadingFolder());
        } else {
            return str_replace($this->projectDir, '', $this->openedFile->getPathname());
        }
    }

    public function getCurrentFileDirectoryPath(): string|false
    {
        if (!$this->openedFile) {
            return false;
        }

        if ($this->mode === Mode::XML) {
            return str_replace($this->projectDir, '', dirname($this->getFileCurrentlyInReadingFolder()));
        } else {
            return str_replace($this->projectDir, '', $this->openedFile->getPath());
        }
    }

    public function getCurrentDataSourceTime(string $format = 'Y-m-d H:i:s'): string
    {
        if ($this->mode === Mode::DB) {
            return date($format, $this->dbQueryExecutedOn);
        } else {
            return $this->getCurrentFileMtime($format);
        }
    }

    private function getCurrentFileMtime(string $format = 'Y-m-d H:i:s'): string
    {
        if (!$this->openedFile) {
            return false;
        }

        if ($this->mode === Mode::XML) {
            return date($format, filemtime($this->getFileCurrentlyInReadingFolder()));
        } else {
            return date($format, $this->openedFile->getMTime());
        }
    }

    public function moveToInputFolder(bool $addTimestamp = true, string $prefix = ''): bool
    {
        if ($this->mode === Mode::DB) {
            throw new \Exception(__METHOD__ . ' does not work in mode "DB"');
        }

        if (!is_dir($this->pathToInputFolder)) {
            throw new \Exception('pathToInputFolder does not exist. pathToInputFolder must be defined using ImportFileHandler->setPathToInputFolder()');
        }
        return $this->moveTo($this->pathToInputFolder, $addTimestamp, $prefix);
    }

    public function moveToSuccessFolder(bool $addTimestamp = true, string $prefix = ''): bool
    {
        if ($this->mode === Mode::DB) {
            throw new \Exception(__METHOD__ . ' does not work in mode "DB"');
        }

        if (!is_dir($this->pathToSuccessFolder)) {
            throw new \Exception('pathToSuccessFolder does not exist. pathToSuccessFolder must be defined using ImportFileHandler->setPathToSuccessFolder()');
        }
        return $this->moveTo($this->pathToSuccessFolder, $addTimestamp, $prefix);
    }

    public function moveToFailedFolder(bool $addTimestamp = true, string $prefix = ''): bool
    {
        if ($this->mode === Mode::DB) {
            throw new \Exception(__METHOD__ . ' does not work in mode "DB"');
        }

        if (!is_dir($this->pathToFailedFolder)) {
            throw new \Exception('pathToFailedFolder does not exist. pathToFailedFolder must be defined using ImportFileHandler->setPathToFailedFolder()');
        }
        return $this->moveTo($this->pathToFailedFolder, $addTimestamp, $prefix);
    }

    public function rewind(): bool
    {
        /*
         * TODO: This method should probably also set $this->lineNr to the first line and also write that
         * to singular storage.
         */
        if (!$this->openedFile) {
            return false;
        }

        $this->openedFile->rewind();
        return true;
    }

    public function endReached(): bool
    {
        if (!$this->openedFile && !is_array($this->openedDatabaseResult)) {
            throw new \Exception('checking for endReached without opened data source');
        }

        if ($this->mode === Mode::XML) {
            if (!$this->xpath) {
                throw new \Exception('Mode::XML requires the xpath to be set');
            }

            $nodes = $this->openedFile->xpath($this->xpath);
            if (!$nodes) {
                return true;
            }

            $count = count($nodes);

            if ($this->lineNr > $count - 1) {
                return true;
            }
            return false;
        } else if ($this->mode === Mode::DB) {
            $count = count($this->openedDatabaseResult);

            if ($this->lineNr > $count - 1) {
                return true;
            }
            return false;
        } else {
            return $this->openedFile->eof();
        }
    }

    public function lastLineInRoundReached(): bool
    {
        return $this->lastLineInRound;
    }

    public function writeTmpFileNotes(array $notes): void
    {
        ls_shop_singularStorage::getInstance()->{$this->singularStorageKeyTmpFileNotes} = $notes;
    }

    public function readTmpFileNotes(): array|null
    {
        return ls_shop_singularStorage::getInstance()->{$this->singularStorageKeyTmpFileNotes};
    }

    private function moveTo(string $to, bool $addTimestamp, string $prefix = ''): bool
    {
        $fileToMove = $this->getFileCurrentlyInReadingFolder();
        if (!$fileToMove) {
            return false;
        }

        /*
         * If the filename already begins with the prefix and a timestamp should not be added to the name,
         * we don't add the prefix again.
         */
        if (strlen($prefix) && strpos(basename($fileToMove), $prefix) === 0 && !$addTimestamp) {
            $prefix = '';
        }

        if (rename($fileToMove, $to . '/' . (strlen($prefix) ? $prefix . '_' : '') . ($addTimestamp ? date('Y-m-d_H-i-s') . '_' : '') . basename($fileToMove))) {
            /*
             * Since this function always moves a file out of the reading folder,
             * no matter what the target is, the line counter and tmp file notes
             * in singular storage must be deleted if moving the file was successful.
             */
            ls_shop_singularStorage::getInstance()->{$this->singularStorageKeyLastLineRead} = null;
            ls_shop_singularStorage::getInstance()->{$this->singularStorageKeyTmpFileNotes} = null;
            return true;
        }
        return false;
    }

    private function handleCsvHeadline(): void
    {
        if (!is_array($this->csvArrayKeys)) {
            /*
             * If csvArrayKeys were not set explicitly, we read them from the file
             */
            $this->readCsvHeadline();
        }
        if ($this->lineNr == 0) {
            $this->lineNr = 1;
        }
    }

    private function readCsvHeadline(): void
    {
        $this->openedFile->seek(0);
        $this->csvArrayKeys = $this->openedFile->current();
        $this->csvArrayKeys[0] = $this->removeBom($this->csvArrayKeys[0]);
    }

    private function removeBom(string $text): string
    {
        if (0 === strpos($text, "\xEF\xBB\xBF")) {
            $text = substr($text, 3);
        }
        return $text;
    }

    /*
     * We always want to read the oldest file in the input folder first to make sure that old data
     * can never overwrite more recent data.
     */
    private function moveNextFileToReadingFolder(): void
    {
        if (!is_dir($this->pathToInputFolder)) {
            throw new \Exception('pathToInputFolder does not exist. pathToInputFolder must be defined using ImportFileHandler->setPathToInputFolder()');
        }

        if (!is_dir($this->pathToReadingFolder)) {
            throw new \Exception('pathToReadingFolder does not exist. pathToReadingFolder must be defined using ImportFileHandler->pathToReadingFolder()');
        }

        $filesInInputFolder = glob($this->pathToInputFolder . "/" . $this->fileNamePattern);
        if (!is_array($filesInInputFolder) || !count($filesInInputFolder)) {
            return;
        }

        array_multisort(array_map('filemtime', $filesInInputFolder), SORT_ASC, $filesInInputFolder);
        $fileToReadNext = $filesInInputFolder[0];
        $str_movedFilename = $this->pathToReadingFolder . '/' . basename($fileToReadNext);
        rename($fileToReadNext, $str_movedFilename);
        $this->singularStorageKeyTmpFileNotes = $this->singularStorageKeyTmpFileNotesPrefix . str_replace($this->projectDir . '/', '', $str_movedFilename) . '_' . filemtime($str_movedFilename);
        $this->singularStorageKeyLastLineRead = $this->singularStorageKeyLastLineReadPrefix . str_replace($this->projectDir . '/', '', $str_movedFilename) . '_' . filemtime($str_movedFilename);
        ls_shop_singularStorage::getInstance()->{$this->singularStorageKeyLastLineRead} = 0;
    }

    private function getFileCurrentlyInReadingFolder(): string|false
    {
        if (!is_dir($this->pathToReadingFolder)) {
            throw new \Exception('pathToReadingFolder does not exist. pathToReadingFolder must be defined using ImportFileHandler->pathToReadingFolder()');
        }

        $filesInReadingFolder = glob($this->pathToReadingFolder . "/" . $this->fileNamePattern);
        if (!is_array($filesInReadingFolder) || !count($filesInReadingFolder)) {
            return false;
        }

        if (count($filesInReadingFolder) > 1) {
            throw new \Exception('More than one file in reading folder. This is not expected and can not be handled.');
        }
        if (!$this->singularStorageKeyLastLineRead) {
            $this->singularStorageKeyTmpFileNotes = $this->singularStorageKeyTmpFileNotesPrefix . str_replace($this->projectDir . '/', '', $filesInReadingFolder[0]) . '_' . filemtime($filesInReadingFolder[0]);
            $this->singularStorageKeyLastLineRead = $this->singularStorageKeyLastLineReadPrefix . str_replace($this->projectDir . '/', '', $filesInReadingFolder[0]) . '_' . filemtime($filesInReadingFolder[0]);
        }
        return $filesInReadingFolder[0];
    }

    private function openDataSource(): void
    {
        if ($this->mode === MODE::DB) {
            if (!is_null($this->openedDatabaseResult)) {
                return;
            }
            $this->queryDatabase();
        } else {
            if ($this->openedFile) {
                return;
            }
            $this->openFile();
        }

        if ($this->singularStorageKeyLastLineRead) {
            $lineNrFromDb = ls_shop_singularStorage::getInstance()->{$this->singularStorageKeyLastLineRead};
            if (!is_int($lineNrFromDb)) {
                $lineNrFromDb = 0;
            }
            $this->lineNr = $lineNrFromDb;
        }
    }

    private function queryDatabase(): void
    {
        if (!is_null($this->openedDatabaseResult)) {
            return;
        }

        $queryResult = Database::getInstance()
            ->prepare($this->dbQuery)
            ->execute($this->dbQueryParams);

        $this->openedDatabaseResult = $queryResult->fetchAllAssoc();
        $this->dbQueryPrepared = $queryResult->query . ' | params: ' . implode(',', $this->dbQueryParams ?: []);
        $this->dbQueryExecutedOn = time();

        $queryAndParamHash = md5($this->dbQuery . ($this->dbQueryParams ? implode(',', $this->dbQueryParams) : ''));

        $this->singularStorageKeyTmpFileNotes = $this->singularStorageKeyTmpFileNotesPrefix . $queryAndParamHash;
        $this->singularStorageKeyLastLineRead = $this->singularStorageKeyLastLineReadPrefix . $queryAndParamHash;

        if (ls_shop_singularStorage::getInstance()->{$this->singularStorageKeyLastLineRead} === null) {
            ls_shop_singularStorage::getInstance()->{$this->singularStorageKeyLastLineRead} = 0;
        }
    }

    private function openFile(): void
    {
        if ($this->openedFile) {
            return;
        }

        $filenameToOpen = $this->getFileCurrentlyInReadingFolder();
        if (!$filenameToOpen) {
            $this->moveNextFileToReadingFolder();
            $filenameToOpen = $this->getFileCurrentlyInReadingFolder();
        }

        if (!$filenameToOpen) {
            return;
        }

        if ($this->mode === Mode::XML) {
            libxml_use_internal_errors(true);
            $sxe = simplexml_load_file($filenameToOpen);
            if ($sxe === false) {
                $libxmlErrors = '';
                foreach (libxml_get_errors() as $libxmlError) {
                    if (!$libxmlErrors) {
                        $libxmlErrors = 'Error loading xml file "' . str_replace($this->projectDir, '', $libxmlError->file) . '"' . "\r\n\r\n";
                    }
                    $libxmlErrors .= $libxmlError->message . "\r\n";
                }
                throw new \Exception($libxmlErrors);
            }
            $this->openedFile = $sxe;
        } else {
            $this->openedFile = new SplFileObject($filenameToOpen);
            if ($this->mode === Mode::CSV) {
                $this->openedFile->setFlags(SplFileObject::READ_AHEAD);
                $this->openedFile->setFlags(SplFileObject::SKIP_EMPTY);
                $this->openedFile->setFlags(SplFileObject::DROP_NEW_LINE);
                $this->openedFile->setFlags(SplFileObject::READ_CSV);
                $this->openedFile->setCsvControl($this->csvSeparator, $this->csvEnclosure, $this->csvEscape);
            }
        }
    }

    private function handleSettingChangeWithAlreadyOpenedDataSource(): void
    {
        if ($this->openedFile || !is_null($this->openedDatabaseResult)) {
            throw new \Exception('Settings cannot be changed when data source has already been opened, e.g. by calling "hasFileToRead()", "readFile()", "readDbResult()" or "readLine()".');
        }
    }
}