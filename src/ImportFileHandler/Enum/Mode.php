<?php

namespace LeadingSystems\MerconisCustomStarterbaseBundle\ImportFileHandler\Enum;

enum Mode
{
    case CSV;
    case DB;
    case RAW;
    case XML;
}
