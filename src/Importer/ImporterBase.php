<?php
namespace LeadingSystems\MerconisCustomStarterbaseBundle\Importer;

use LeadingSystems\MerconisCustomStarterbaseBundle\ImportFileHandler\Enum\Mode;
use LeadingSystems\MerconisCustomStarterbaseBundle\ImportFileHandler\ImportFileHandler;
use LeadingSystems\MerconisCustomStarterbaseBundle\Scheduler\Traits\SchedulableTrait;

abstract class ImporterBase {
    use SchedulableTrait;

    protected array $messages = [];
    protected array $errorMessages = [];
    protected ImportFileHandler $importFileHandler;

    public function __construct(ImportFileHandler $importFileHandler)
    {
        $this->importFileHandler = $importFileHandler;
    }

    protected function run(): void
    {
        if (!$this->fileAvailable()) {
            return;
        }

        $this->getMessagesFromTmpFileNotes();

        $lineNumberBefore = $this->importFileHandler->getCurrentLineNumber();

        $this->work();

        $dateSourceType = $this->importFileHandler->getMode() === Mode::DB ? 'DB Query' : 'file';

        if ($this->importFileHandler->endReached()) {
            $this->executionResultMessage = 'Finished ' . $dateSourceType . ' "' . $this->importFileHandler->getCurrentDataSourceName() .'" (' .$this->importFileHandler->getCurrentDataSourceTime(). '), last run from line ' . $lineNumberBefore . ' to ' . ($this->importFileHandler->getCurrentLineNumber());

            if ($this->hasMessages()) {
                $combinedMessage = <<<EOT
                    \r\n\r\n>> {$this->implodeMessages("\r\n\r\n>> ")}
                    \r\n\r\n
                    EOT;
                $this->executionResultMessage .= $combinedMessage;
            }

            if ($this->hasErrorMessages()) {
                $combinedErrorMessage = <<<EOT
                    \r\n
                    Errors occured!
                    \r\n\r\n>> {$this->implodeErrorMessages("\r\n\r\n>> ")}
                    \r\n\r\n
                    EOT;
                $this->executionResultMessage .= $combinedErrorMessage;
                if ($this->importFileHandler->getMode() !== Mode::DB) {
                    $this->importFileHandler->moveToFailedFolder();
                }
            } else {
                if ($this->importFileHandler->getMode() !== Mode::DB) {
                    $this->importFileHandler->moveToSuccessFolder();
                }
            }
        } else {
            $this->writeMessagesToTmpFileNotes();
            $this->executionResultMessage = 'Currently working on ' . $dateSourceType . ' "' . $this->importFileHandler->getCurrentDataSourceName() .'" (' .$this->importFileHandler->getCurrentDataSourceTime(). '), last run from line ' . $lineNumberBefore . ' to ' . ($this->importFileHandler->getCurrentLineNumber());
        }
    }

    private function getMessagesFromTmpFileNotes(): void
    {
        $tmpFileNotes = $this->importFileHandler->readTmpFileNotes();
        if (is_array($tmpFileNotes['errorMessages'])) {
            $this->setErrorMessages($tmpFileNotes['errorMessages']);
        }
        if (is_array($tmpFileNotes['messages'])) {
            $this->setMessages($tmpFileNotes['messages']);
        }
    }

    private function writeMessagesToTmpFileNotes(): void
    {
        $this->importFileHandler->writeTmpFileNotes(
            [
                'errorMessages' => $this->errorMessages,
                'messages' => $this->messages
            ]
        );
    }

    abstract protected function work();

    protected function fileAvailable(): bool
    {
        if (!$this->importFileHandler->hasFileToRead()) {
            $this->executionResultMessage = 'No file available!';
            return false;
        }
        return true;
    }

    protected function setErrorMessages(array $errorMessages): void
    {
        $this->errorMessages = $errorMessages;
    }

    protected function addErrorMessage(string $errorMessage, bool $addLineNr = false): void
    {
        $this->errorMessages[] = ($addLineNr ? ('Line ' . $this->importFileHandler->getCurrentLineNumber() . ': ') : '') . $errorMessage;
    }

    protected function hasErrorMessages(): bool
    {
        return count($this->errorMessages) > 0;
    }

    protected function implodeErrorMessages(string $glue = "\r\n"): string
    {
        return implode($glue, $this->errorMessages);
    }

    protected function setMessages(array $messages): void
    {
        $this->messages = $messages;
    }

    protected function addMessage(string $errorMsg, bool $addLineNr = false): void
    {
        $this->messages[] = ($addLineNr ? ('Line ' . $this->importFileHandler->getCurrentLineNumber() . ': ') : '') . $errorMsg;
    }

    protected function hasMessages(): bool
    {
        return count($this->messages) > 0;
    }

    protected function implodeMessages(string $glue = "\r\n"): string
    {
        return implode($glue, $this->messages);
    }
}