# Bible Application

A modern Bible reading and management application built with Laravel, Vue.js, and Inertia.js.

## Sister Mobile App

Looking for the mobile version? Check out our companion mobile app: [**kea137/Bible-app**](https://github.com/kea137/Bible-app) - Take your Bible study on the go!

## Features

- Browse and read different Bible translations and languages
- **Public API for Bible verses** (no authentication required, rate-limited)
- **Verse sharing with beautiful backgrounds** - Create shareable images with gradient or photo backgrounds from Pexels
- **Bible study lessons** - Create, manage, and track progress through single or serialized lessons with scripture references
- **Memory verses with spaced repetition** - Memorize verses effectively with smart scheduling and daily reminders using the SM-2 algorithm
- **Verse Link Canvas** - Visual canvas for creating connections between Bible verses and building verse relationship maps
- **Privacy-safe analytics** - Optional usage tracking without collecting personal information
- **Feature flags** - Gradual rollout system for controlling feature availability
- **Accessibility-first design** - WCAG AA compliant with skip navigation, ARIA labels, and keyboard support
- User authentication and role management
- Dark mode support
- Responsive design with mobile-optimized interface
- Cross-reference system for Bible verses
- Bible reading progress tracking
- Verse highlighting and notes
- Parallel Bible view for comparing translations with cross-references and selected reference viewing
- Onboarding for new users
- SEO optimized with sitemap generation for hosting

## Installation

1. Clone the repository
2. Install PHP dependencies: `composer install`
3. Install Node dependencies: `npm install`
4. Copy `.env.example` to `.env` and configure your environment
5. Generate application key: `php artisan key:generate`
6. (Optional) Add your Pexels API key to `.env` for verse sharing image backgrounds:
   - Get a free API key from [Pexels](https://www.pexels.com/api/)
   - Set `PEXELS_API_KEY=your-api-key-here` in `.env`
7. Run migrations: `php artisan migrate`
8. Build assets: `npm run build`
9. Start the development server: `php artisan serve`

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

### API Documentation

- **Interactive API Documentation**: Visit `/api/docs` on your running instance to explore the full API with Swagger UI
- **OpenAPI Specification**: Available at `/api/docs.json` (OpenAPI 3.1)
- For additional details, see [DOCUMENTATION.md](DOCUMENTATION.md#public-bible-api)

## Verse Sharing

The application includes a powerful verse sharing feature that allows users to create beautiful images with Bible verses for social media sharing.

### Features:
- **Gradient Backgrounds**: Choose from 15 pre-designed beautiful gradient backgrounds
- **Photo Backgrounds**: Use serene nature images from Pexels (when API key is configured)
- **Customizable Text**: Adjust font family, size, and style
- **Custom Colors**: Create your own gradient color combinations
- **Download & Share**: Download images or use native device sharing

### Setup (Optional):
To enable photo backgrounds from Pexels:
1. Sign up for a free API key at [Pexels](https://www.pexels.com/api/)
2. Add `PEXELS_API_KEY=your-api-key-here` to your `.env` file
3. The app will automatically fetch serene nature images for verse backgrounds

**Note**: The feature works perfectly without a Pexels API key - users can still use the beautiful gradient backgrounds.

## Privacy & Analytics

This application implements privacy-safe analytics that respects user privacy:

- **No Personal Information**: Analytics never collects PII (names, emails, IP addresses)
- **User Control**: Users can opt-in or opt-out of analytics at any time
- **Transparent**: Clear documentation of what is tracked
- **Local Storage**: Preferences stored locally in the browser

See [ANALYTICS_AND_FEATURE_FLAGS.md](ANALYTICS_AND_FEATURE_FLAGS.md) for detailed documentation.

## Feature Flags

The application includes a feature flag system for controlled feature rollouts:

- Enable/disable features without code changes
- Gradual rollout capabilities
- User-friendly toggle interface
- Persistent settings across sessions

All major features can be controlled via feature flags, allowing for safe deployments and A/B testing.

## Accessibility

We are committed to making this application accessible to all users:

- **WCAG AA Compliant**: Meets Web Content Accessibility Guidelines Level AA
- **Keyboard Navigation**: Full keyboard support with skip navigation links
- **Screen Reader Support**: Proper ARIA labels and semantic HTML
- **Focus Management**: Clear focus indicators throughout the application
- **Color Contrast**: All text meets minimum contrast requirements

See [ACCESSIBILITY.md](ACCESSIBILITY.md) for detailed accessibility documentation.

## Related Projects

- **[kea137/Bible-app](https://github.com/kea137/Bible-app)** - The companion mobile application for Bible study on the go. Provides a native mobile experience with offline reading capabilities.

## Credits & Acknowledgments

This project utilizes resources from the following repositories:

- **Bible Translations**: [jadenzaleski/BibleTranslations](https://github.com/jadenzaleski/BibleTranslations) - Provides various Bible translation JSON files found in the `resources/Bibles` directory
- **Bible Cross References**: [josephilipraja/bible-cross-reference-json](https://github.com/josephilipraja/bible-cross-reference-json) - Provides cross-reference data found in the `resources/References` directory
- **Background Images**: [Pexels](https://www.pexels.com/) - Provides free stock photos for verse sharing backgrounds (when API key is configured)

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
