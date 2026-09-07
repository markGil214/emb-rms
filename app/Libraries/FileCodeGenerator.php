<?php

namespace App\Libraries;

class FileCodeGenerator
{
    /**
     * Generate file code: [PREFIX]-[NUMBER]
     * Example: FI-1, HR-101, OP-50
     */
    public static function generate(string $prefix = 'GEN', int $sequenceNumber = 1): string
    {
        return strtoupper($prefix) . '-' . $sequenceNumber;
    }

    /**
     * Get next file code based on company name
     *
     * A company name whose first character (after trimming whitespace) is a
     * digit gets a single-digit numeric prefix -- only that first digit is
     * used, e.g. "214 BOG STATION" -> prefix "2", not "21" or "214" -- and
     * the incrementing part becomes a letter (A, B, C...) instead of a
     * number, so numeric-prefix codes are visually distinct from the
     * letter-prefix ones. Everything else keeps the original behavior:
     * first two letters of the company name + an incrementing number.
     */
    public static function getNextFromCompany(string $companyName): string
    {
        $info = self::derivePrefix($companyName);

        if ($info['type'] === 'numeric') {
            $nextLetter = self::getNextLetterSuffix($info['prefix']);
            return self::generateWithLetterSuffix($info['prefix'], $nextLetter);
        }

        $db = \Config\Database::connect();
        $prefix = $info['prefix'];

        // Find existing codes with this prefix
        $query = $db->query("
            SELECT MAX(CAST(SUBSTRING_INDEX(file_code, '-', -1) AS UNSIGNED)) AS max_number
            FROM folders
            WHERE file_code LIKE ?
        ", [$prefix . '-%']);

        $row = $query->getRow();

        $nextNumber = ($row && $row->max_number)
            ? ((int) $row->max_number + 1)
            : 1;

        return self::generate($prefix, $nextNumber);
    }

    /**
     * Decide whether a company name should get a numeric (digit) prefix or
     * the original letter prefix, based on its first non-whitespace character.
     */
    private static function derivePrefix(string $companyName): array
    {
        $trimmed = ltrim($companyName);
        $firstChar = substr($trimmed, 0, 1);

        if ($firstChar !== '' && ctype_digit($firstChar)) {
            return ['type' => 'numeric', 'prefix' => $firstChar];
        }

        return ['type' => 'alpha', 'prefix' => self::extractCompanyPrefix($companyName)];
    }

    /**
     * Find the next letter suffix for a numeric prefix (A, B, ... Z, AA, AB...).
     *
     * Done in PHP rather than SQL MAX() because a plain string MAX() is
     * lexicographic, which is only correct for single-character suffixes --
     * "AA" sorts before "Z" as text, the opposite of the order needed once a
     * prefix rolls past Z. The per-row regex also ignores any file_code that
     * doesn't match the expected "{digit}-{letters}" shape (e.g. manually
     * edited or imported data), so it can't corrupt the count.
     */
    private static function getNextLetterSuffix(string $digitPrefix): string
    {
        $db = \Config\Database::connect();
        $query = $db->query("
            SELECT file_code
            FROM folders
            WHERE file_code LIKE ?
        ", [$digitPrefix . '-%']);

        $maxNumber = 0;
        $pattern = '/^' . preg_quote($digitPrefix, '/') . '-([A-Z]+)$/';

        foreach ($query->getResultArray() as $row) {
            if (preg_match($pattern, strtoupper((string) $row['file_code']), $m)) {
                $n = self::lettersToNumber($m[1]);
                if ($n > $maxNumber) {
                    $maxNumber = $n;
                }
            }
        }

        return self::numberToLetters($maxNumber + 1);
    }

    /**
     * Build a "{prefix}-{letters}" code. Kept separate from generate()
     * because that method's second parameter is typed int (used by seeders).
     */
    public static function generateWithLetterSuffix(string $prefix, string $letterSuffix): string
    {
        return strtoupper($prefix) . '-' . strtoupper($letterSuffix);
    }

    /**
     * Convert a letter suffix to its ordinal position, bijective base-26
     * (spreadsheet column style): A=1, B=2, ... Z=26, AA=27, AB=28...
     */
    public static function lettersToNumber(string $letters): int
    {
        $n = 0;
        foreach (str_split(strtoupper($letters)) as $char) {
            $n = $n * 26 + (ord($char) - ord('A') + 1);
        }

        return $n;
    }

    /**
     * Inverse of lettersToNumber(): 1=A, 26=Z, 27=AA, 28=AB...
     */
    public static function numberToLetters(int $num): string
    {
        $result = '';
        while ($num > 0) {
            $num--;
            $result = chr(ord('A') + ($num % 26)) . $result;
            $num = intdiv($num, 26);
        }

        return $result;
    }

    /**
     * Extract first two letters from company name
     */
    private static function extractCompanyPrefix(string $companyName): string
    {
        // Clean up and remove spaces/special characters
        $companyName = strtoupper($companyName);
        $companyName = preg_replace('/[^A-Z]/', '', $companyName);

        // Take first two letters
        return substr($companyName, 0, 2);
    }

    /**
     * Generate location code: [Rack][Shelf]
     * Example: 1A, 2B, 3C
     */
    public static function generateLocationCode($rack, $shelf): string
    {
        // Ensure we only take the core identifiers (e.g., "3" from "Rack 3", "A" from "Shelf A")
        $rack = preg_replace('/[^0-9A-Z]/i', '', (string)$rack);
        $shelf = preg_replace('/[^0-9A-Z]/i', '', (string)$shelf);
        
        return strtoupper(trim((string)$rack)) . strtoupper(trim((string)$shelf));
    }

    /**
     * Parse file code into prefix and suffix.
     * Example: "HR-100" -> alpha prefix, numeric suffix.
     *          "2-AA"   -> numeric prefix, letter suffix.
     */
    public static function parse(string $fileCode): array
    {
        $code = strtoupper($fileCode);
        [$prefix, $suffix] = array_pad(explode('-', $code, 2), 2, '');

        if ($suffix !== '' && ctype_digit($suffix)) {
            return ['prefix' => $prefix, 'type' => 'alpha', 'number' => (int) $suffix, 'letter' => null];
        }

        if ($suffix !== '' && ctype_alpha($suffix)) {
            return ['prefix' => $prefix, 'type' => 'numeric', 'number' => null, 'letter' => $suffix];
        }

        return ['prefix' => $prefix, 'type' => 'unknown', 'number' => 0, 'letter' => null];
    }

    /**
     * Validate file code format
     * Example valid: FI-1, HR-100, DOC-5 (letter prefix, numeric suffix)
     *             or: 2-A, 2-AA (numeric prefix, letter suffix)
     */
    public static function isValid(string $fileCode): bool
    {
        return (bool) preg_match('/^([A-Z]{1,5}-\d+|\d-[A-Z]+)$/', strtoupper($fileCode));
    }

    /**
     * Validate location code format
     * Example valid: 1A, 2B, 10C
     */
    public static function isValidLocationCode(string $locationCode): bool
    {
        return (bool) preg_match('/^\d+[A-Z]$/', strtoupper($locationCode));
    }
}