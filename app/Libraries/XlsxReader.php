<?php

namespace App\Libraries;

/**
 * Minimal .xlsx reader built on PHP's Zip/SimpleXML extensions -- avoids
 * pulling in a full library (e.g. PhpSpreadsheet) just for occasional bulk
 * data imports such as PhAddressSeeder. Reads plain cell values from one
 * named worksheet only; no styles, formulas, or merged cells.
 */
class XlsxReader
{
    /**
     * Returns every row of $sheetName as [columnNumber => stringValue],
     * 1-indexed columns, in row order (including the header row).
     */
    public static function rows(string $path, string $sheetName): array
    {
        $zip = new \ZipArchive();

        if ($zip->open($path) !== true) {
            throw new \RuntimeException("Cannot open xlsx file: {$path}");
        }

        $shared = self::loadSharedStrings($zip);
        $sheetFile = self::resolveSheetFile($zip, $sheetName);
        $sheetXml = simplexml_load_string($zip->getFromName($sheetFile));

        $rows = [];

        foreach ($sheetXml->sheetData->row as $row) {
            $cells = [];

            foreach ($row->c as $c) {
                $colIndex = self::columnLetterToIndex((string) $c['r']);
                $type = (string) $c['t'];
                $raw = isset($c->v) ? (string) $c->v : '';
                $cells[$colIndex] = ($type === 's') ? ($shared[(int) $raw] ?? '') : $raw;
            }

            $rows[] = $cells;
        }

        $zip->close();

        return $rows;
    }

    private static function loadSharedStrings(\ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');

        if ($xml === false) {
            return [];
        }

        $shared = [];

        foreach (simplexml_load_string($xml)->si as $si) {
            if (isset($si->t)) {
                $shared[] = (string) $si->t;
                continue;
            }

            // Rich text runs (mixed formatting within one cell) split the
            // text across multiple <r><t> nodes instead of a single <t>.
            $text = '';
            foreach ($si->r as $r) {
                $text .= (string) $r->t;
            }
            $shared[] = $text;
        }

        return $shared;
    }

    private static function resolveSheetFile(\ZipArchive $zip, string $sheetName): string
    {
        $workbook = simplexml_load_string($zip->getFromName('xl/workbook.xml'));
        $workbook->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');

        $rId = null;
        foreach ($workbook->sheets->sheet as $sheet) {
            if ((string) $sheet['name'] === $sheetName) {
                $rId = (string) $sheet->attributes('r', true)['id'];
                break;
            }
        }

        if ($rId === null) {
            throw new \RuntimeException("Sheet not found in workbook: {$sheetName}");
        }

        $rels = simplexml_load_string($zip->getFromName('xl/_rels/workbook.xml.rels'));
        foreach ($rels->Relationship as $rel) {
            if ((string) $rel['Id'] === $rId) {
                return 'xl/' . (string) $rel['Target'];
            }
        }

        throw new \RuntimeException("Relationship not found for sheet: {$sheetName}");
    }

    private static function columnLetterToIndex(string $cellRef): int
    {
        $letters = preg_replace('/[0-9]/', '', $cellRef);
        $index = 0;

        foreach (str_split($letters) as $char) {
            $index = $index * 26 + (ord($char) - ord('A') + 1);
        }

        return $index;
    }
}
