<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use App\Libraries\FileCodeGenerator;

/**
 * FileCodeGenerator Unit Tests
 *
 * Covers only the pure-logic pieces (no database): the base-26 letter/number
 * conversion used for numeric-prefix codes, isValid()/parse() format
 * recognition, and a regression guard on generate() (relied on by the
 * seeders). The company-name-driven scenarios (CASE A/B/C, rollover,
 * malformed-data defense) touch `folders` and are verified separately via a
 * transaction-wrapped script rather than as permanent tests, since
 * FolderModel's DBGroup is 'default' -- a DB-touching test here would hit the
 * real database, not an isolated test DB.
 */
class FileCodeGeneratorTest extends CIUnitTestCase
{
    /**
     * TEST 1: numberToLetters produces the expected value at known points,
     * including the Z -> AA rollover boundary.
     */
    public function testNumberToLettersKnownValues()
    {
        $this->assertSame('A', FileCodeGenerator::numberToLetters(1));
        $this->assertSame('Z', FileCodeGenerator::numberToLetters(26));
        $this->assertSame('AA', FileCodeGenerator::numberToLetters(27));
        $this->assertSame('AB', FileCodeGenerator::numberToLetters(28));
        $this->assertSame('AZ', FileCodeGenerator::numberToLetters(52));
        $this->assertSame('BA', FileCodeGenerator::numberToLetters(53));
    }

    /**
     * TEST 2: lettersToNumber is the exact inverse of numberToLetters.
     */
    public function testLettersToNumberRoundTrip()
    {
        for ($n = 1; $n <= 100; $n++) {
            $letters = FileCodeGenerator::numberToLetters($n);
            $this->assertSame(
                $n,
                FileCodeGenerator::lettersToNumber($letters),
                "Round-trip failed for {$n} -> {$letters}"
            );
        }
    }

    /**
     * TEST 3: isValid() recognizes both code shapes.
     */
    public function testIsValidAcceptsBothShapes()
    {
        // Letter prefix, numeric suffix (original shape)
        $this->assertTrue(FileCodeGenerator::isValid('AS-1'));
        $this->assertTrue(FileCodeGenerator::isValid('TE-14'));

        // Numeric prefix, letter suffix (new shape), including rollover
        $this->assertTrue(FileCodeGenerator::isValid('2-A'));
        $this->assertTrue(FileCodeGenerator::isValid('2-AA'));
    }

    /**
     * TEST 4: isValid() rejects malformed or mismatched-shape codes.
     */
    public function testIsValidRejectsMalformed()
    {
        $this->assertFalse(FileCodeGenerator::isValid('2-1'));       // numeric prefix, numeric suffix
        $this->assertFalse(FileCodeGenerator::isValid('ASDFG-1A'));  // letter prefix, mixed suffix
        $this->assertFalse(FileCodeGenerator::isValid(''));
        $this->assertFalse(FileCodeGenerator::isValid('2-'));
    }

    /**
     * TEST 5: parse() decodes the letter-prefix/numeric-suffix shape.
     */
    public function testParseAlphaShape()
    {
        $result = FileCodeGenerator::parse('HR-100');

        $this->assertSame('HR', $result['prefix']);
        $this->assertSame('alpha', $result['type']);
        $this->assertSame(100, $result['number']);
        $this->assertNull($result['letter']);
    }

    /**
     * TEST 6: parse() decodes the numeric-prefix/letter-suffix shape.
     */
    public function testParseNumericShape()
    {
        $result = FileCodeGenerator::parse('2-AA');

        $this->assertSame('2', $result['prefix']);
        $this->assertSame('numeric', $result['type']);
        $this->assertNull($result['number']);
        $this->assertSame('AA', $result['letter']);
    }

    /**
     * TEST 7: generate() is unchanged -- the seeders depend on this exact
     * behavior (letter prefix + plain incrementing int).
     */
    public function testGenerateUnchangedForSeeders()
    {
        $this->assertSame('GEN-3', FileCodeGenerator::generate('gen', 3));
        $this->assertSame('PE-1', FileCodeGenerator::generate('PE', 1));
    }

    /**
     * TEST 8: generateWithLetterSuffix() formats and uppercases consistently.
     */
    public function testGenerateWithLetterSuffix()
    {
        $this->assertSame('2-A', FileCodeGenerator::generateWithLetterSuffix('2', 'a'));
        $this->assertSame('3-AA', FileCodeGenerator::generateWithLetterSuffix('3', 'aa'));
    }
}
