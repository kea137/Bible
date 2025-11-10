<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Verse;

class ScriptureReferenceService
{
    /**
     * Map of standard book codes to book numbers (1-66)
     */
    private const BOOK_CODE_MAP = [
        'GEN' => 1, 'EXO' => 2, 'LEV' => 3, 'NUM' => 4, 'DEU' => 5,
        'JOS' => 6, 'JDG' => 7, 'RUT' => 8, '1SA' => 9, '2SA' => 10,
        '1KI' => 11, '2KI' => 12, '1CH' => 13, '2CH' => 14, 'EZR' => 15,
        'NEH' => 16, 'EST' => 17, 'JOB' => 18, 'PSA' => 19, 'PRO' => 20,
        'ECC' => 21, 'SNG' => 22, 'ISA' => 23, 'JER' => 24, 'LAM' => 25,
        'EZK' => 26, 'DAN' => 27, 'HOS' => 28, 'JOL' => 29, 'AMO' => 30,
        'OBA' => 31, 'JON' => 32, 'MIC' => 33, 'NAM' => 34, 'HAB' => 35,
        'ZEP' => 36, 'HAG' => 37, 'ZEC' => 38, 'MAL' => 39, 'MAT' => 40,
        'MRK' => 41, 'LUK' => 42, 'JHN' => 43, 'ACT' => 44, 'ROM' => 45,
        '1CO' => 46, '2CO' => 47, 'GAL' => 48, 'EPH' => 49, 'PHP' => 50,
        'COL' => 51, '1TH' => 52, '2TH' => 53, '1TI' => 54, '2TI' => 55,
        'TIT' => 56, 'PHM' => 57, 'HEB' => 58, 'JAS' => 59, '1PE' => 60,
        '2PE' => 61, '1JN' => 62, '2JN' => 63, '3JN' => 64, 'JUD' => 65,
        'REV' => 66,
    ];

    /**
     * Map of common localized book codes to book numbers
     * This includes Swahili and other language variants
     */
    private const LOCALIZED_BOOK_CODE_MAP = [
        // Swahili codes
        'MWA' => 1,  // Genesis (Mwanzo)
        'KUT' => 2,  // Exodus (Kutoka)
        'WAL' => 3,  // Leviticus (Walawi)
        'HES' => 4,  // Numbers (Hesabu)
        'KUM' => 5,  // Deuteronomy (Kumbukumbu)
        'YOS' => 6,  // Joshua (Yoshua)
        'WAA' => 7,  // Judges (Waamuzi)
        'RUT' => 8,  // Ruth
        '1SAM' => 9,  // 1 Samuel
        '2SAM' => 10, // 2 Samuel
        '1WAF' => 11, // 1 Kings (Wafalme)
        '2WAF' => 12, // 2 Kings
        '1NYA' => 13, // 1 Chronicles (Nyakati)
        '2NYA' => 14, // 2 Chronicles
        'EZR' => 15,  // Ezra
        'NEH' => 16,  // Nehemiah
        'EST' => 17,  // Esther
        'AYU' => 18,  // Job (Ayubu)
        'ZAB' => 19,  // Psalms (Zaburi)
        'MIT' => 20,  // Proverbs (Mithali)
        'MHU' => 21,  // Ecclesiastes (Mhubiri)
        'WIM' => 22,  // Song of Solomon (Wimbo)
        'ISA' => 23,  // Isaiah (Isaya)
        'YER' => 24,  // Jeremiah (Yeremia)
        'MAO' => 25,  // Lamentations (Maombolezo)
        'EZE' => 26,  // Ezekiel (Ezekieli)
        'DAN' => 27,  // Daniel (Danieli)
        'HOS' => 28,  // Hosea (Hosea)
        'YOE' => 29,  // Joel (Yoeli)
        'AMO' => 30,  // Amos (Amosi)
        'OBA' => 31,  // Obadiah (Obadia)
        'YON' => 32,  // Jonah (Yona)
        'MIK' => 33,  // Micah (Mika)
        'NAH' => 34,  // Nahum (Nahumu)
        'HAB' => 35,  // Habakkuk (Habakuki)
        'SEF' => 36,  // Zephaniah (Sefania)
        'HAG' => 37,  // Haggai (Hagai)
        'ZAK' => 38,  // Zechariah (Zekaria)
        'MAL' => 39,  // Malachi (Malaki)
        'MAT' => 40,  // Matthew (Mathayo)
        'MRK' => 41,  // Mark (Marko)
        'LUK' => 42,  // Luke (Luka)
        'YOH' => 43,  // John (Yohana)
        'MDA' => 44,  // Acts (Matendo)
        'RUM' => 45,  // Romans (Warumi)
        '1KOR' => 46, // 1 Corinthians (Wakorintho)
        '2KOR' => 47, // 2 Corinthians
        'GAL' => 48,  // Galatians (Wagalatia)
        'EFE' => 49,  // Ephesians (Waefeso)
        'FIL' => 50,  // Philippians (Wafilipi)
        'KOL' => 51,  // Colossians (Wakolosai)
        '1THE' => 52, // 1 Thessalonians (Wathesalonike)
        '2THE' => 53, // 2 Thessalonians
        '1TIM' => 54, // 1 Timothy (Timotheo)
        '2TIM' => 55, // 2 Timothy
        'TIT' => 56,  // Titus (Tito)
        'FIL' => 57,  // Philemon (Filemoni)
        'EBR' => 58,  // Hebrews (Waebrania)
        'YAK' => 59,  // James (Yakobo)
        '1PET' => 60, // 1 Peter (Petro)
        '2PET' => 61, // 2 Peter
        '1YOH' => 62, // 1 John (Yohana)
        '2YOH' => 63, // 2 John
        '3YOH' => 64, // 3 John
        'YUD' => 65,  // Jude (Yuda)
        'UFU' => 66,  // Revelation (Ufunuo)
    ];

    /**
     * Parse scripture references from text
     * Supports: 'REF' for short references and '''REF''' for full verses
     * e.g., '2KI 2:2' or '''GEN 1:1'''
     */
    public function parseReferences(string $text): array
    {
        $references = [];

        // Pattern for full verse references: '''BOOK CHAPTER:VERSE'''
        preg_match_all("/'''([A-Z0-9]{3})\s+(\d+):(\d+)'''/", $text, $fullMatches, PREG_SET_ORDER);
        foreach ($fullMatches as $match) {
            $references[] = [
                'type' => 'full',
                'book_code' => $match[1],
                'chapter' => (int) $match[2],
                'verse' => (int) $match[3],
                'original' => $match[0],
            ];
        }

        // Pattern for short references: 'BOOK CHAPTER:VERSE'
        preg_match_all("/'([A-Z0-9]{3})\s+(\d+):(\d+)'/", $text, $shortMatches, PREG_SET_ORDER);
        foreach ($shortMatches as $match) {
            $references[] = [
                'type' => 'short',
                'book_code' => $match[1],
                'chapter' => (int) $match[2],
                'verse' => (int) $match[3],
                'original' => $match[0],
            ];
        }

        return $references;
    }

    /**
     * Get book number from book code (supports both English and localized codes)
     */
    private function getBookNumber(string $bookCode): ?int
    {
        $upperCode = strtoupper($bookCode);

        // Try standard English code first
        if (isset(self::BOOK_CODE_MAP[$upperCode])) {
            return self::BOOK_CODE_MAP[$upperCode];
        }

        // Try localized code
        if (isset(self::LOCALIZED_BOOK_CODE_MAP[$upperCode])) {
            return self::LOCALIZED_BOOK_CODE_MAP[$upperCode];
        }

        return null;
    }

    /**
     * Fetch verse data for a reference
     */
    public function fetchVerse(string $bookCode, int $chapterNumber, int $verseNumber, int $bibleId): ?array
    {
        $bookNumber = $this->getBookNumber($bookCode);

        if (! $bookNumber) {
            return null;
        }

        $book = Book::where('book_number', $bookNumber)
            ->where('bible_id', $bibleId)
            ->first();

        if (! $book) {
            return null;
        }

        $verse = Verse::whereHas('chapter', function ($query) use ($book, $chapterNumber) {
            $query->where('book_id', $book->id)
                ->where('chapter_number', $chapterNumber);
        })
            ->where('verse_number', $verseNumber)
            ->with(['chapter.book'])
            ->first();

        if (! $verse) {
            return null;
        }

        return [
            'verse_id' => $verse->id,
            'text' => $verse->text,
            'book_code' => $bookCode,
            'book_title' => $book->title,
            'chapter_number' => $chapterNumber,
            'verse_number' => $verseNumber,
            'reference' => "{$bookCode} {$chapterNumber}:{$verseNumber}",
        ];
    }

    /**
     * Replace scripture references in text with formatted content
     */
    public function replaceReferences(string $text, int $bibleId): string
    {
        $references = $this->parseReferences($text);

        foreach ($references as $ref) {
            $verseData = $this->fetchVerse($ref['book_code'], $ref['chapter'], $ref['verse'], $bibleId);

            if ($verseData) {
                if ($ref['type'] === 'full') {
                    // Replace full verse references with the actual verse text
                    $replacement = $verseData['text'];
                    $text = str_replace($ref['original'], $replacement, $text);
                } else {
                    // Short references remain as clickable references
                    // They will be handled in the frontend
                }
            }
        }

        return $text;
    }
}
