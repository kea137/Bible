# Bible Application

A modern Bible reading and management application built with Laravel, Vue.js, and Inertia.js.

## Features

- Browse and read different Bible translations
- **Public API for Bible verses** (no authentication required, rate-limited)
- User authentication and role management
- Two-factor authentication support
- Dark mode support
- Responsive design
- Cross-reference system for Bible verses
- Reading progress tracking
- Verse highlighting and notes

## Installation

1. Clone the repository
2. Install PHP dependencies: `composer install`
3. Install Node dependencies: `npm install`
4. Copy `.env.example` to `.env` and configure your environment
5. Generate application key: `php artisan key:generate`
6. Run migrations: `php artisan migrate`
7. Build assets: `npm run build`
8. Start the development server: `php artisan serve`

## Development

- Run development server with hot reload: `npm run dev`
- Run tests: `php artisan test`
- Lint code: `npm run lint`
- Format code: `npm run format`

## Public API

The application includes a public API for fetching Bible verses without authentication:

```bash
# Example: Get John 3:16 from KJV in English (without cross-references)
curl "http://your-domain.com/api/English/KJV/false/John/3/16"
```

**URL Format:** `/api/{language}/{version}/{references}/{book}/{chapter}/{verse?}`

**Path Parameters:**
- `language`: Bible language (e.g., `English`, `Swahili`)
- `version`: Bible version/abbreviation (e.g., `KJV`, `NIV`)
- `references`: Include cross-references (`true`, `false`, `1`, or `0`)
- `book`: Book name or number (e.g., `Genesis` or `1`)
- `chapter`: Chapter number
- `verse` (optional): Specific verse number

**Rate Limit:** 30 requests per minute

For detailed API documentation, see [DOCUMENTATION.md](DOCUMENTATION.md#public-bible-api).

## Credits & Acknowledgments

This project utilizes resources from the following repositories:

- **Bible Translations**: [jadenzaleski/BibleTranslations](https://github.com/jadenzaleski/BibleTranslations) - Provides various Bible translation JSON files found in the `resources/Bibles` directory
- **Bible Cross References**: [josephilipraja/bible-cross-reference-json](https://github.com/josephilipraja/bible-cross-reference-json) - Provides cross-reference data found in the `resources/References` directory

We are grateful to these projects for making their resources available to the community. For more information about the Bible translations and cross-reference system, please visit the repositories linked above.

## Disclaimer

**IMPORTANT**: This software is provided free of charge under the MIT License. While we strive to provide a quality application:

- This application is provided "AS IS" without warranty of any kind
- The authors and contributors are NOT liable for any damages, claims, or issues arising from the use of this software
- Users are solely responsible for their use of this application and any consequences thereof
- The Bible translations and cross-reference data are sourced from third-party repositories - please refer to their respective licenses and terms of use
- This is an open-source project maintained by volunteers with no guarantees of availability, accuracy, or support

By using this application, you acknowledge and accept these terms.

## License

MIT License

Copyright (c) 2025 Kea Rajabu Baruan

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

## Contact

For questions, issues, or contributions, please visit the [GitHub repository](https://github.com/kea137/Bible).
