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
     * Creates unique code from company name initials + auto-increment number
     */
    public static function getNextFromCompany(string $companyName): string
    {
        $db = \Config\Database::connect();
        
        // Extract first two letters from company name
        $prefix = self::extractCompanyPrefix($companyName);
        
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
     * Generate location code: [Cabinet][Rack]
     * Example: 1A, 2B, 3C
     */
    public static function generateLocationCode($cabinet, $rack): string
    {
        return strtoupper(trim($cabinet)) . strtoupper(trim($rack));
    }

    /**
     * Parse file code into prefix and number
     */
    public static function parse(string $fileCode): array
    {
        $parts = explode('-', strtoupper($fileCode));

        return [
            'prefix' => $parts[0] ?? '',
            'number' => isset($parts[1]) ? (int) $parts[1] : 0,
        ];
    }

    /**
     * Validate file code format
     * Example valid: FI-1, HR-100, DOC-5
     */
    public static function isValid(string $fileCode): bool
    {
        return (bool) preg_match('/^[A-Z]{1,5}-\d+$/', strtoupper($fileCode));
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