# نظام البحث عن المنتجات العربية - Arabic Product Search System

A complete, production-ready Arabic product search system that provides advanced fuzzy matching and intelligent search results for Arabic text.

## 🌟 Features

### Core Functionality
- **Arabic Text Normalization**: Handles diacritics, letter variations, and different spellings
- **Fuzzy Matching Algorithm**: Finds similar terms even with typos using multiple similarity algorithms
- **Smart Scoring System**: Ranks results by relevance across multiple product fields
- **Intelligent Suggestions**: "Did you mean?" functionality for better user experience
- **Performance Optimized**: Caching system for faster search results
- **RTL Support**: Full Arabic right-to-left text support

### Search Capabilities
- Search across product names, descriptions, brands, categories, colors, and attributes
- Weighted scoring system (name: 60%, description: 30%, brand: 20%, etc.)
- Minimum similarity threshold for filtering relevant results
- Popular search terms suggestions
- Real-time search with debouncing

### User Interface
- Modern, responsive design with Tailwind CSS
- Arabic-first interface with RTL support
- Real-time search results with loading indicators
- Product cards with images, prices, and match scores
- Search statistics and performance metrics
- Error handling with user-friendly messages

## 📁 Project Structure

```
ryia_products/
├── index.php                 # Main frontend interface
├── search.php                # Search API backend
├── products.json             # Product database (JSON format)
├── products_1.csv           # Original product data (CSV format)
├── convert_csv_to_json.php  # CSV to JSON converter utility
├── search_cache.json       # Search results cache (auto-generated)
└── README.md               # This documentation
```

## 🚀 Quick Start

### Prerequisites
- PHP 7.4+ with `mbstring` extension
- Web server (Apache/Nginx) or PHP built-in server
- Modern web browser with JavaScript enabled

### Installation

1. **Clone or download the repository**
   ```bash
   git clone https://github.com/PythonEErian/ryia_products.git
   cd ryia_products
   ```

2. **Convert CSV data to JSON (if needed)**
   ```bash
   php convert_csv_to_json.php
   ```

3. **Start the development server**
   ```bash
   php -S localhost:8000
   ```

4. **Open in browser**
   ```
   http://localhost:8000
   ```

## 🔧 API Documentation

### Search Endpoint

**URL**: `search.php?action=search&q={query}&limit={limit}`

**Parameters**:
- `q` (required): Search query in Arabic or English
- `limit` (optional): Maximum number of results (default: 20)

**Response**:
```json
{
  "success": true,
  "query": "فستان",
  "results": [
    {
      "product": {
        "id": 63471,
        "name_ar": "فستان نسائي متوسط ​​الطول كاجوال مطرز",
        "desc_ar": "المواد: 100% فيسكوز طول المنتج: 125 سم",
        "final_selling_price": 52.04,
        "image": "https://example.com/image.webp",
        "brand": "بيج دارت",
        "category": "فساتين",
        "sizes": "36",
        "colors": "أسود",
        "attributes": {...}
      },
      "score": 0.95,
      "reasons": ["name", "category"]
    }
  ],
  "total": 15,
  "suggestions": [],
  "popular_terms": []
}
```

### Suggestions Endpoint

**URL**: `search.php?action=suggestions&q={query}`

**Response**:
```json
{
  "success": true,
  "suggestions": [
    {
      "word": "فساتين",
      "similarity": 0.85
    }
  ]
}
```

### Popular Terms Endpoint

**URL**: `search.php?action=popular&limit={limit}`

**Response**:
```json
{
  "success": true,
  "popular_terms": ["فستان", "قميص", "جاكيت", "حذاء"]
}
```

## 🧠 Search Algorithm

### Arabic Text Normalization
1. **Diacritics Removal**: Removes harakat (تشكيل) for consistent matching
2. **Letter Normalization**: 
   - أ، إ، آ، ء → ا
   - ة → ه
   - ى → ي
   - ؤ → و
   - ئ → ي
3. **Space Normalization**: Removes extra spaces and standardizes format

### Similarity Calculation
The system uses multiple algorithms for accurate matching:

1. **Levenshtein Distance**: Measures character-level differences
2. **Similar Text**: PHP's built-in similarity function
3. **Substring Matching**: Bonus scoring for exact substrings
4. **Weighted Combination**: Combines all scores with optimized weights

### Scoring System
Results are scored based on field weights:
- Product Name: 60%
- Description: 30%
- Brand: 20%
- Category: 10%
- Colors: 10%
- Attributes: 5%

## 🎨 Customization

### Modifying Search Weights
Edit the `search()` method in `search.php`:

```php
// Adjust these multipliers to change field importance
$score += $nameScore * 0.6;        // Name weight
$score += $descScore * 0.3;        // Description weight
$score += $brandScore * 0.2;       // Brand weight
```

### Changing Similarity Threshold
Modify the minimum similarity in the search call:

```php
$results = $searchEngine->search($query, $limit, 0.3); // 0.3 = 30% similarity
```

### UI Customization
The interface uses Tailwind CSS classes. Modify colors and styles in `index.php`:

```html
<!-- Change primary color from blue to green -->
<input class="focus:ring-2 focus:ring-green-500 focus:border-transparent">
```

## 📊 Performance Optimization

### Caching
- Search results are cached in `search_cache.json`
- Cache keys are generated from query + parameters
- Automatic cache invalidation and management

### Data Structure
- JSON format for faster parsing than CSV
- Indexed access patterns for efficient searching
- Minimal memory footprint with lazy loading

### Search Optimization
- Debounced search requests (300ms delay)
- Minimum query length requirement (2 characters)
- Progressive result loading
- Efficient similarity algorithms

## 🧪 Testing Examples

### Arabic Search Queries
Try these searches to test the system:

1. **Exact Matches**: `فستان`, `قميص`, `جاكيت`
2. **With Typos**: `فسسان`, `قمسص`, `جاكت`
3. **Partial Matches**: `فس`, `قم`, `جا`
4. **Brand Names**: `بيج دارت`
5. **Categories**: `فساتين`, `قمصان`, `جاكيتات`
6. **Colors**: `أسود`, `أزرق`, `أبيض`
7. **Descriptions**: `كاجوال`, `رسمي`, `مطرز`

### Expected Results
- All searches should return relevant results
- Typos should be handled gracefully with suggestions
- Results should be ranked by relevance
- Search should be fast (<1 second for most queries)

## 🐛 Troubleshooting

### Common Issues

1. **Empty Results**
   - Check if `products.json` exists and is readable
   - Verify PHP `mbstring` extension is enabled
   - Try lowering the similarity threshold

2. **Search Too Slow**
   - Check if caching is working (`search_cache.json` exists)
   - Reduce the number of products or add pagination
   - Optimize server configuration

3. **Arabic Text Issues**
   - Ensure UTF-8 encoding throughout the system
   - Check font support for Arabic characters
   - Verify database/file encoding is UTF-8

4. **API Errors**
   - Check PHP error logs
   - Verify file permissions
   - Test API endpoints directly

### Debug Mode
Enable debug output by modifying `search.php`:

```php
// Add at the top of search.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly with Arabic text
5. Submit a pull request

## 📝 License

This project is open source and available under the [MIT License](LICENSE).

## 🔗 Related Links

- [PHP Documentation](https://www.php.net/docs.php)
- [Tailwind CSS](https://tailwindcss.com/)
- [Arabic Typography Best Practices](https://www.w3.org/International/articles/arabic-typography/)
- [RTL Web Development Guide](https://rtlstyling.com/)

## 📧 Support

For questions or issues, please:
1. Check the troubleshooting section above
2. Search existing issues in the repository
3. Create a new issue with detailed information
4. Include sample queries and expected vs actual results

---

Made with ❤️ for the Arabic web community