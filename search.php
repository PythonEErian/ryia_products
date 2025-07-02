<?php
/**
 * Arabic Product Search System with Fuzzy Matching
 * Handles Arabic text normalization, fuzzy matching, and smart suggestions
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

class ArabicSearchEngine {
    private $products = [];
    private $cache = [];
    private $cacheFile = 'search_cache.json';
    
    public function __construct($jsonFile = 'products.json') {
        $this->loadProducts($jsonFile);
        $this->loadCache();
    }
    
    private function loadProducts($jsonFile) {
        if (!file_exists($jsonFile)) {
            throw new Exception("Products file not found: $jsonFile");
        }
        
        $json = file_get_contents($jsonFile);
        $this->products = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON in products file");
        }
    }
    
    private function loadCache() {
        if (file_exists($this->cacheFile)) {
            $this->cache = json_decode(file_get_contents($this->cacheFile), true) ?: [];
        }
    }
    
    private function saveCache() {
        file_put_contents($this->cacheFile, json_encode($this->cache, JSON_UNESCAPED_UNICODE));
    }
    
    /**
     * Normalize Arabic text for better matching
     */
    private function normalizeArabicText($text) {
        if (empty($text)) return '';
        
        // Remove diacritics (harakat)
        $text = preg_replace('/[\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}]/u', '', $text);
        
        // Normalize different forms of Arabic letters
        $text = str_replace(['أ', 'إ', 'آ', 'ء'], 'ا', $text);
        $text = str_replace(['ة'], 'ه', $text);
        $text = str_replace(['ى'], 'ي', $text);
        $text = str_replace(['ؤ'], 'و', $text);
        $text = str_replace(['ئ'], 'ي', $text);
        
        // Remove extra spaces and normalize
        $text = trim(preg_replace('/\s+/', ' ', $text));
        
        return mb_strtolower($text, 'UTF-8');
    }
    
    /**
     * Calculate similarity between two strings using multiple algorithms
     */
    private function calculateSimilarity($str1, $str2) {
        $str1 = $this->normalizeArabicText($str1);
        $str2 = $this->normalizeArabicText($str2);
        
        if ($str1 === $str2) return 1.0;
        if (empty($str1) || empty($str2)) return 0.0;
        
        // Exact substring match bonus
        if (strpos($str2, $str1) !== false || strpos($str1, $str2) !== false) {
            $bonus = 0.3;
        } else {
            $bonus = 0.0;
        }
        
        // Levenshtein distance similarity
        $maxLen = max(mb_strlen($str1, 'UTF-8'), mb_strlen($str2, 'UTF-8'));
        $distance = levenshtein($str1, $str2);
        $levSimilarity = 1 - ($distance / $maxLen);
        
        // Similar text percentage
        similar_text($str1, $str2, $percent);
        $simTextSimilarity = $percent / 100;
        
        // Combine similarities with weights
        $finalSimilarity = ($levSimilarity * 0.4) + ($simTextSimilarity * 0.6) + $bonus;
        
        return min($finalSimilarity, 1.0);
    }
    
    /**
     * Search products with fuzzy matching and scoring
     */
    public function search($query, $limit = 20, $minSimilarity = 0.3) {
        $cacheKey = md5($query . $limit . $minSimilarity);
        
        // Check cache first
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        $normalizedQuery = $this->normalizeArabicText($query);
        $results = [];
        
        foreach ($this->products as $product) {
            $score = 0;
            $reasons = [];
            
            // Search in product name (highest weight)
            $nameScore = $this->calculateSimilarity($normalizedQuery, $product['name_ar']);
            if ($nameScore >= $minSimilarity) {
                $score += $nameScore * 0.6;
                $reasons[] = 'name';
            }
            
            // Search in description (medium weight)
            $descScore = $this->calculateSimilarity($normalizedQuery, $product['desc_ar']);
            if ($descScore >= $minSimilarity) {
                $score += $descScore * 0.3;
                $reasons[] = 'description';
            }
            
            // Search in brand (medium weight)
            $brandScore = $this->calculateSimilarity($normalizedQuery, $product['brand']);
            if ($brandScore >= $minSimilarity) {
                $score += $brandScore * 0.2;
                $reasons[] = 'brand';
            }
            
            // Search in category (low weight)
            $categoryScore = $this->calculateSimilarity($normalizedQuery, $product['category']);
            if ($categoryScore >= $minSimilarity) {
                $score += $categoryScore * 0.1;
                $reasons[] = 'category';
            }
            
            // Search in colors
            $colorScore = $this->calculateSimilarity($normalizedQuery, $product['colors']);
            if ($colorScore >= $minSimilarity) {
                $score += $colorScore * 0.1;
                $reasons[] = 'color';
            }
            
            // Search in attributes
            if (is_array($product['attributes'])) {
                foreach ($product['attributes'] as $key => $value) {
                    if (is_string($value)) {
                        $attrScore = $this->calculateSimilarity($normalizedQuery, $value);
                        if ($attrScore >= $minSimilarity) {
                            $score += $attrScore * 0.05;
                            $reasons[] = 'attributes';
                            break;
                        }
                    } elseif (is_array($value)) {
                        foreach ($value as $subValue) {
                            if (is_string($subValue)) {
                                $attrScore = $this->calculateSimilarity($normalizedQuery, $subValue);
                                if ($attrScore >= $minSimilarity) {
                                    $score += $attrScore * 0.05;
                                    $reasons[] = 'attributes';
                                    break 2;
                                }
                            }
                        }
                    }
                }
            }
            
            if ($score > 0) {
                $results[] = [
                    'product' => $product,
                    'score' => $score,
                    'reasons' => array_unique($reasons)
                ];
            }
        }
        
        // Sort by score descending
        usort($results, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        // Limit results
        $results = array_slice($results, 0, $limit);
        
        // Cache results
        $this->cache[$cacheKey] = $results;
        $this->saveCache();
        
        return $results;
    }
    
    /**
     * Generate smart suggestions for "Did you mean?" functionality
     */
    public function getSuggestions($query, $limit = 5) {
        $suggestions = [];
        $normalizedQuery = $this->normalizeArabicText($query);
        
        // Collect unique words from all products
        $allWords = [];
        foreach ($this->products as $product) {
            $words = array_merge(
                explode(' ', $this->normalizeArabicText($product['name_ar'])),
                explode(' ', $this->normalizeArabicText($product['desc_ar'])),
                explode(' ', $this->normalizeArabicText($product['brand'])),
                explode(' ', $this->normalizeArabicText($product['category']))
            );
            
            foreach ($words as $word) {
                $word = trim($word);
                if (mb_strlen($word, 'UTF-8') > 2) {
                    $allWords[$word] = true;
                }
            }
        }
        
        // Find similar words
        foreach (array_keys($allWords) as $word) {
            $similarity = $this->calculateSimilarity($normalizedQuery, $word);
            if ($similarity > 0.4 && $similarity < 0.9) {
                $suggestions[] = [
                    'word' => $word,
                    'similarity' => $similarity
                ];
            }
        }
        
        // Sort by similarity
        usort($suggestions, function($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });
        
        return array_slice($suggestions, 0, $limit);
    }
    
    /**
     * Get popular search terms based on product data
     */
    public function getPopularTerms($limit = 10) {
        $terms = [];
        
        foreach ($this->products as $product) {
            // Extract meaningful terms from names
            $words = explode(' ', $this->normalizeArabicText($product['name_ar']));
            foreach ($words as $word) {
                $word = trim($word);
                if (mb_strlen($word, 'UTF-8') > 2) {
                    $terms[$word] = ($terms[$word] ?? 0) + 1;
                }
            }
            
            // Add categories and brands
            $category = $this->normalizeArabicText($product['category']);
            if (!empty($category)) {
                $terms[$category] = ($terms[$category] ?? 0) + 2;
            }
            
            $brand = $this->normalizeArabicText($product['brand']);
            if (!empty($brand)) {
                $terms[$brand] = ($terms[$brand] ?? 0) + 1;
            }
        }
        
        arsort($terms);
        return array_slice(array_keys($terms), 0, $limit);
    }
}

// Handle API requests
try {
    $searchEngine = new ArabicSearchEngine();
    
    $action = $_GET['action'] ?? 'search';
    $query = trim($_GET['q'] ?? '');
    $limit = (int)($_GET['limit'] ?? 20);
    
    switch ($action) {
        case 'search':
            if (empty($query)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Query parameter is required',
                    'results' => [],
                    'suggestions' => [],
                    'popular_terms' => $searchEngine->getPopularTerms()
                ], JSON_UNESCAPED_UNICODE);
            } else {
                $results = $searchEngine->search($query, $limit);
                $suggestions = count($results) < 3 ? $searchEngine->getSuggestions($query) : [];
                
                echo json_encode([
                    'success' => true,
                    'query' => $query,
                    'results' => $results,
                    'total' => count($results),
                    'suggestions' => $suggestions,
                    'popular_terms' => []
                ], JSON_UNESCAPED_UNICODE);
            }
            break;
            
        case 'suggestions':
            $suggestions = $searchEngine->getSuggestions($query);
            echo json_encode([
                'success' => true,
                'suggestions' => $suggestions
            ], JSON_UNESCAPED_UNICODE);
            break;
            
        case 'popular':
            $popular = $searchEngine->getPopularTerms($limit);
            echo json_encode([
                'success' => true,
                'popular_terms' => $popular
            ], JSON_UNESCAPED_UNICODE);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ], JSON_UNESCAPED_UNICODE);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'results' => []
    ], JSON_UNESCAPED_UNICODE);
}
?>