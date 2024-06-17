<?php

namespace LeadingSystems\MerconisCustomHoehenflugBundle\Importer\Cash\ArticleConditionZentrallager;

use LeadingSystems\MerconisCustomHoehenflugBundle\Importer\ImporterBase;
use LeadingSystems\MerconisCustomHoehenflugBundle\ImportFileHandler\Enum\Mode;
use LeadingSystems\MerconisCustomHoehenflugBundle\ImportFileHandler\ImportFileHandler;

abstract class BaseDefinition extends ImporterBase
{
    /*
     * If the import files always contain the complete data, it is probably best to move an invalid file into
     * a specific "invalid" folder so that it doesn't block later files from being automatically validated and
     * eventually processed. But if import files contain only data that changed, there might be situations when
     * it is important that one invalid file stops the whole validation and processing chain until an administrator
     * handles the situation manually. In this case it is best to set the "invalid" folder to the same directory
     * as the "validator input" folder. This will have the effect that an invalid file will be validated and
     * recognized as invalid over and over again until someone fixes the problem.
     *
     * The same logic might apply to the processing part of the chain.
     */
    protected string $pathToValidatorInputFolder = 'files/importer/Cash/ArticleConditionZentrallager/1_input';
    protected string $pathToValidatorReadingFolder = 'files/importer/Cash/ArticleConditionZentrallager/2_validating';
    protected string $pathToValidatorSuccessFolder = 'files/importer/Cash/ArticleConditionZentrallager/3_valid';
    protected string $pathToValidatorFailedFolder = 'files/importer/Cash/ArticleConditionZentrallager/x_invalid';

    /*
     * The valid files are the files to be processed, so the "valid" folder is usually the input folder
     * for the processor
     */
    protected string $pathToProcessorInputFolder = 'files/importer/Cash/ArticleConditionZentrallager/3_valid';
    protected string $pathToProcessorReadingFolder = 'files/importer/Cash/ArticleConditionZentrallager/4_processing';
    protected string $pathToProcessorSuccessFolder = 'files/importer/Cash/ArticleConditionZentrallager/5_imported';
    protected string $pathToProcessorFailedFolder = 'files/importer/Cash/ArticleConditionZentrallager/x_failed';

    protected ImportFileHandler $importFileHandler;
    protected ?array $lineData = null;

    public function __construct(ImportFileHandler $importFileHandler)
    {
        parent::__construct($importFileHandler);

        $this->importFileHandler = $importFileHandler;

        $this->importFileHandler->setMode(Mode::CSV);
        $this->importFileHandler->setFileNamePattern('*.[cC][sS][vV]');
        $this->importFileHandler->setCsvSeparator(';');
        $this->importFileHandler->setCsvHasHeadline(true);

        switch ($this->callerBasename()) {
            case 'Processor':
                $this->importFileHandler->setPathToInputFolder($this->pathToProcessorInputFolder);
                $this->importFileHandler->setPathToReadingFolder($this->pathToProcessorReadingFolder);
                $this->importFileHandler->setPathToSuccessFolder($this->pathToProcessorSuccessFolder);
                $this->importFileHandler->setPathToFailedFolder($this->pathToProcessorFailedFolder);
                break;

            case 'Validator':
                $this->importFileHandler->setPathToInputFolder($this->pathToValidatorInputFolder);
                $this->importFileHandler->setPathToReadingFolder($this->pathToValidatorReadingFolder);
                $this->importFileHandler->setPathToSuccessFolder($this->pathToValidatorSuccessFolder);
                $this->importFileHandler->setPathToFailedFolder($this->pathToValidatorFailedFolder);
                break;
        }
    }

    private function callerBasename(): string
    {
        return basename(str_replace('\\', '/', get_called_class()));
    }
}