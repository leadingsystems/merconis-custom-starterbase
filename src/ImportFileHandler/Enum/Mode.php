<?php

namespace LeadingSystems\MerconisCustomHoehenflugBundle\ImportFileHandler\Enum;

enum Mode
{
    case CSV;
    case DB;
    case RAW;
    case XML;
}
