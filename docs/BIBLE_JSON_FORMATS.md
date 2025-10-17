# Bible JSON Format Support

The Bible application supports multiple JSON formats for uploading Bible translations. The system automatically detects the format and processes it accordingly.

## Supported Formats

### 1. Swahili Format (Original)

This is the original format used for Swahili Bible translations. It features a hierarchical structure with uppercase keys.

**Structure:**
```json
{
  "BIBLEBOOK": [
    {
      "book_number": 1,
      "book_name": "Genesis",
      "author": "Moses",
      "published_year": "1500 BC",
      "introduction": "Book introduction...",
      "summary": "Book summary...",
      "CHAPTER": [
        {
          "chapter_number": 1,
          "title": "Chapter title",
          "introduction": "Chapter introduction...",
          "VERSES": [
            {
              "verse_number": 1,
              "verse_text": "In the beginning God created the heaven and the earth."
            },
            {
              "verse_number": 2,
              "verse_text": "And the earth was without form, and void..."
            }
          ]
        }
      ]
    }
  ]
}
```

**Key Features:**
- Supports book metadata (author, published year, introduction, summary)
- Supports chapter metadata (title, introduction)
- Uses uppercase keys for main structures (BIBLEBOOK, CHAPTER, VERSES)

### 2. Flat Verses Format (BibleTranslations)

This is a simpler format commonly used in Bible translation repositories. Each verse is a separate object in an array.

**Structure:**
```json
[
  {
    "book": "Genesis",
    "chapter": 1,
    "verse": 1,
    "text": "In the beginning God created the heaven and the earth."
  },
  {
    "book": "Genesis",
    "chapter": 1,
    "verse": 2,
    "text": "And the earth was without form, and void..."
  },
  {
    "book": "Genesis",
    "chapter": 2,
    "verse": 1,
    "text": "Thus the heavens and the earth were finished..."
  }
]
```

**Key Features:**
- Simple, flat structure
- Each verse is a separate object
- Books and chapters are automatically created as needed
- Book numbers are assigned based on standard Bible book order

### 3. Nested Books Format

This format organizes data hierarchically with books containing chapters, and chapters containing verses.

**Structure:**
```json
{
  "books": [
    {
      "name": "Genesis",
      "number": 1,
      "author": "Moses",
      "chapters": [
        {
          "number": 1,
          "verses": [
            {
              "number": 1,
              "text": "In the beginning God created the heaven and the earth."
            },
            {
              "number": 2,
              "text": "And the earth was without form, and void..."
            }
          ]
        }
      ]
    }
  ]
}
```

**Alternative Structure (without "books" wrapper):**
```json
[
  {
    "name": "Genesis",
    "number": 1,
    "chapters": [
      {
        "number": 1,
        "verses": [
          {
            "number": 1,
            "text": "In the beginning..."
          }
        ]
      }
    ]
  }
]
```

**Key Features:**
- Hierarchical organization
- Flexible field naming (supports "name"/"title"/"book" for book names)
- Flexible numbering (supports "number"/"book_number"/"chapter_number"/"verse_number")
- Can include or exclude the "books" wrapper object

## Format Detection

The parser automatically detects which format is being used by checking:

1. **Swahili Format**: Presence of `BIBLEBOOK` key
2. **Flat Verses Format**: Array of objects with `book`, `chapter`, `verse`, and `text` keys
3. **Nested Books Format**: Presence of `books` key or array of objects with `chapters` key

## Usage

When uploading a Bible JSON file through the web interface:

1. Navigate to the Bible upload page
2. Fill in the Bible details (name, abbreviation, language, version, description)
3. Select your JSON file
4. Submit the form

The system will automatically detect the format and import all books, chapters, and verses.

## Error Handling

If the JSON file doesn't match any of the supported formats, an error will be displayed and the Bible entry will not be created.

## Testing

The application includes comprehensive tests for all three formats. Run tests with:

```bash
php artisan test tests/Feature/BibleJsonParserTest.php
```

## Example Files

Example files for each format are available in the `/tmp/bible-test-files/` directory during development:

- `swahili-format.json` - Example of Swahili format
- `flat-format.json` - Example of flat verses format
- `nested-format.json` - Example of nested books format
