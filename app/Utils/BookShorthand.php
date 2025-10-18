<?php

namespace App\Utils;

class BookShorthand
{
    /**
     * Mapping of book numbers to English shorthand codes
     */
    public const BOOK_SHORTHANDS = [
        1 => 'GEN', 2 => 'EXO', 3 => 'LEV', 4 => 'NUM', 5 => 'DEU',
        6 => 'JOS', 7 => 'JDG', 8 => 'RUT', 9 => '1SA', 10 => '2SA',
        11 => '1KI', 12 => '2KI', 13 => '1CH', 14 => '2CH', 15 => 'EZR',
        16 => 'NEH', 17 => 'EST', 18 => 'JOB', 19 => 'PSA', 20 => 'PRO',
        21 => 'ECC', 22 => 'SNG', 23 => 'ISA', 24 => 'JER', 25 => 'LAM',
        26 => 'EZK', 27 => 'DAN', 28 => 'HOS', 29 => 'JOL', 30 => 'AMO',
        31 => 'OBA', 32 => 'JON', 33 => 'MIC', 34 => 'NAM', 35 => 'HAB',
        36 => 'ZEP', 37 => 'HAG', 38 => 'ZEC', 39 => 'MAL', 40 => 'MAT',
        41 => 'MAR', 42 => 'LUK', 43 => 'JOH', 44 => 'ACT', 45 => 'ROM',
        46 => '1CO', 47 => '2CO', 48 => 'GAL', 49 => 'EPH', 50 => 'PHP',
        51 => 'COL', 52 => '1TH', 53 => '2TH', 54 => '1TI', 55 => '2TI',
        56 => 'TIT', 57 => 'PHM', 58 => 'HEB', 59 => 'JAM', 60 => '1PE',
        61 => '2PE', 62 => '1JO', 63 => '2JO', 64 => '3JO', 65 => 'JUD',
        66 => 'REV',
    ];

    /**
     * English full names
     */
    public const ENGLISH_NAMES = [
        'GEN' => 'Genesis', 'EXO' => 'Exodus', 'LEV' => 'Leviticus', 'NUM' => 'Numbers', 'DEU' => 'Deuteronomy',
        'JOS' => 'Joshua', 'JDG' => 'Judges', 'RUT' => 'Ruth', '1SA' => '1 Samuel', '2SA' => '2 Samuel',
        '1KI' => '1 Kings', '2KI' => '2 Kings', '1CH' => '1 Chronicles', '2CH' => '2 Chronicles', 'EZR' => 'Ezra',
        'NEH' => 'Nehemiah', 'EST' => 'Esther', 'JOB' => 'Job', 'PSA' => 'Psalms', 'PRO' => 'Proverbs',
        'ECC' => 'Ecclesiastes', 'SNG' => 'Song of Solomon', 'ISA' => 'Isaiah', 'JER' => 'Jeremiah', 'LAM' => 'Lamentations',
        'EZK' => 'Ezekiel', 'DAN' => 'Daniel', 'HOS' => 'Hosea', 'JOL' => 'Joel', 'AMO' => 'Amos',
        'OBA' => 'Obadiah', 'JON' => 'Jonah', 'MIC' => 'Micah', 'NAM' => 'Nahum', 'HAB' => 'Habakkuk',
        'ZEP' => 'Zephaniah', 'HAG' => 'Haggai', 'ZEC' => 'Zechariah', 'MAL' => 'Malachi', 'MAT' => 'Matthew',
        'MAR' => 'Mark', 'LUK' => 'Luke', 'JOH' => 'John', 'ACT' => 'Acts', 'ROM' => 'Romans',
        '1CO' => '1 Corinthians', '2CO' => '2 Corinthians', 'GAL' => 'Galatians', 'EPH' => 'Ephesians', 'PHP' => 'Philippians',
        'COL' => 'Colossians', '1TH' => '1 Thessalonians', '2TH' => '2 Thessalonians', '1TI' => '1 Timothy', '2TI' => '2 Timothy',
        'TIT' => 'Titus', 'PHM' => 'Philemon', 'HEB' => 'Hebrews', 'JAM' => 'James', '1PE' => '1 Peter',
        '2PE' => '2 Peter', '1JO' => '1 John', '2JO' => '2 John', '3JO' => '3 John', 'JUD' => 'Jude',
        'REV' => 'Revelation',
    ];

    /**
     * Swahili names using English shorthands
     */
    public const SWAHILI_NAMES = [
        'GEN' => 'Mwanzo', 'EXO' => 'Kutoka', 'LEV' => 'Mambo ya Walawi', 'NUM' => 'Hesabu', 'DEU' => 'Kumbukumbu la Torati',
        'JOS' => 'Yoshua', 'JDG' => 'Waamuzi', 'RUT' => 'Ruthu', '1SA' => '1 Samweli', '2SA' => '2 Samweli',
        '1KI' => '1 Wafalme', '2KI' => '2 Wafalme', '1CH' => '1 Mambo ya Nyakati', '2CH' => '2 Mambo ya Nyakati', 'EZR' => 'Ezra',
        'NEH' => 'Nehemia', 'EST' => 'Esta', 'JOB' => 'Ayubu', 'PSA' => 'Zaburi', 'PRO' => 'Mithali',
        'ECC' => 'Mhubiri', 'SNG' => 'Wimbo Ulio Bora', 'ISA' => 'Isaya', 'JER' => 'Yeremia', 'LAM' => 'Maombolezo',
        'EZK' => 'Ezekieli', 'DAN' => 'Danieli', 'HOS' => 'Hosea', 'JOL' => 'Yoeli', 'AMO' => 'Amosi',
        'OBA' => 'Obadia', 'JON' => 'Yona', 'MIC' => 'Mika', 'NAM' => 'Nahumu', 'HAB' => 'Habakuki',
        'ZEP' => 'Sefania', 'HAG' => 'Hagai', 'ZEC' => 'Zekaria', 'MAL' => 'Malaki', 'MAT' => 'Mathayo',
        'MAR' => 'Marko', 'LUK' => 'Luka', 'JOH' => 'Yohana', 'ACT' => 'Matendo', 'ROM' => 'Warumi',
        '1CO' => '1 Wakorintho', '2CO' => '2 Wakorintho', 'GAL' => 'Wagalatia', 'EPH' => 'Waefeso', 'PHP' => 'Wafilipi',
        'COL' => 'Wakolosai', '1TH' => '1 Wathesalonike', '2TH' => '2 Wathesalonike', '1TI' => '1 Timotheo', '2TI' => '2 Timotheo',
        'TIT' => 'Tito', 'PHM' => 'Filemoni', 'HEB' => 'Waebrania', 'JAM' => 'Yakobo', '1PE' => '1 Petro',
        '2PE' => '2 Petro', '1JO' => '1 Yohana', '2JO' => '2 Yohana', '3JO' => '3 Yohana', '3JO' => '3 Yohana', 'JUD' => 'Yuda',
        'REV' => 'Ufunuo',
    ];

    /**
     * Get shorthand for a book number
     */
    public static function getShorthand(int $bookNumber): ?string
    {
        return self::BOOK_SHORTHANDS[$bookNumber] ?? null;
    }

    /**
     * Get book name by shorthand and language
     */
    public static function getName(string $shorthand, string $language = 'en'): ?string
    {
        if ($language === 'sw') {
            return self::SWAHILI_NAMES[$shorthand] ?? null;
        }

        return self::ENGLISH_NAMES[$shorthand] ?? null;
    }

    /**
     * Parse reference string (e.g., "GEN 1 1") to components
     */
    public static function parseReference(string $reference): array
    {
        $parts = explode(' ', $reference);
        if (count($parts) !== 3) {
            return [];
        }

        return [
            'book' => $parts[0],
            'chapter' => (int) $parts[1],
            'verse' => (int) $parts[2],
        ];
    }

    /**
     * Get book number from shorthand
     */
    public static function getBookNumber(string $shorthand): ?int
    {
        $flipped = array_flip(self::BOOK_SHORTHANDS);

        return $flipped[$shorthand] ?? null;
    }
}
