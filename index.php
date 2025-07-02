<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام البحث عن المنتجات العربية - Arabic Product Search</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Noto Sans Arabic', sans-serif;
        }
        .rtl {
            direction: rtl;
            text-align: right;
        }
        .ltr {
            direction: ltr;
            text-align: left;
        }
        .search-highlight {
            background-color: #fef3cd;
            font-weight: 600;
        }
        .loading {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .product-card {
            transition: all 0.3s ease;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .suggestion-item {
            transition: all 0.2s ease;
        }
        .suggestion-item:hover {
            background-color: #e5e7eb;
        }
    </style>
</head>
<body class="bg-gray-50 rtl">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-900">نظام البحث عن المنتجات</h1>
                    <div class="text-sm text-gray-500">
                        <span>Arabic Product Search System</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Search Section -->
            <div class="mb-8">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="mb-4">
                        <label for="searchInput" class="block text-lg font-medium text-gray-700 mb-2">
                            ابحث عن المنتجات
                        </label>
                        <div class="relative">
                            <input 
                                type="text" 
                                id="searchInput" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-lg"
                                placeholder="اكتب اسم المنتج، العلامة التجارية، أو الفئة..."
                                autocomplete="off"
                            >
                            <div id="searchLoader" class="absolute left-3 top-1/2 transform -translate-y-1/2 hidden">
                                <div class="loading"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Search Stats -->
                    <div id="searchStats" class="text-sm text-gray-600 mb-4 hidden">
                        <span id="statsText"></span>
                    </div>
                    
                    <!-- Suggestions -->
                    <div id="suggestions" class="hidden">
                        <div class="mb-3">
                            <span class="text-sm font-medium text-gray-700">هل تقصد:</span>
                        </div>
                        <div id="suggestionsList" class="flex flex-wrap gap-2 mb-4"></div>
                    </div>
                    
                    <!-- Popular Terms -->
                    <div id="popularTerms" class="mb-4">
                        <div class="mb-3">
                            <span class="text-sm font-medium text-gray-700">البحث الشائع:</span>
                        </div>
                        <div id="popularTermsList" class="flex flex-wrap gap-2"></div>
                    </div>
                </div>
            </div>

            <!-- Results Section -->
            <div id="resultsSection" class="hidden">
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">نتائج البحث</h2>
                        <p id="resultsCount" class="text-gray-600 mt-1"></p>
                    </div>
                    <div id="resultsContainer" class="p-6">
                        <!-- Results will be populated here -->
                    </div>
                </div>
            </div>

            <!-- No Results Section -->
            <div id="noResults" class="hidden">
                <div class="bg-white rounded-lg shadow-sm p-8 text-center">
                    <div class="text-gray-400 mb-4">
                        <svg class="mx-auto h-16 w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">لم يتم العثور على نتائج</h3>
                    <p class="text-gray-600 mb-4">جرب البحث بكلمات مختلفة أو تحقق من الإملاء</p>
                    <div id="noResultsSuggestions"></div>
                </div>
            </div>

            <!-- Error Section -->
            <div id="errorSection" class="hidden">
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="mr-3">
                            <h3 class="text-sm font-medium text-red-800">خطأ في البحث</h3>
                            <p id="errorMessage" class="text-sm text-red-700 mt-1"></p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        class ArabicProductSearch {
            constructor() {
                this.searchInput = document.getElementById('searchInput');
                this.searchLoader = document.getElementById('searchLoader');
                this.resultsSection = document.getElementById('resultsSection');
                this.resultsContainer = document.getElementById('resultsContainer');
                this.resultsCount = document.getElementById('resultsCount');
                this.searchStats = document.getElementById('searchStats');
                this.statsText = document.getElementById('statsText');
                this.suggestions = document.getElementById('suggestions');
                this.suggestionsList = document.getElementById('suggestionsList');
                this.noResults = document.getElementById('noResults');
                this.noResultsSuggestions = document.getElementById('noResultsSuggestions');
                this.errorSection = document.getElementById('errorSection');
                this.errorMessage = document.getElementById('errorMessage');
                this.popularTerms = document.getElementById('popularTerms');
                this.popularTermsList = document.getElementById('popularTermsList');
                
                this.searchTimeout = null;
                this.currentQuery = '';
                
                this.init();
            }
            
            init() {
                this.loadPopularTerms();
                this.bindEvents();
            }
            
            bindEvents() {
                this.searchInput.addEventListener('input', (e) => {
                    clearTimeout(this.searchTimeout);
                    const query = e.target.value.trim();
                    
                    if (query.length === 0) {
                        this.clearResults();
                        this.showPopularTerms();
                        return;
                    }
                    
                    if (query.length < 2) {
                        return;
                    }
                    
                    this.searchTimeout = setTimeout(() => {
                        this.performSearch(query);
                    }, 300);
                });
                
                this.searchInput.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        clearTimeout(this.searchTimeout);
                        const query = e.target.value.trim();
                        if (query.length >= 2) {
                            this.performSearch(query);
                        }
                    }
                });
            }
            
            async loadPopularTerms() {
                try {
                    const response = await fetch('search.php?action=popular&limit=10');
                    const data = await response.json();
                    
                    if (data.success && data.popular_terms) {
                        this.displayPopularTerms(data.popular_terms);
                    }
                } catch (error) {
                    console.error('Error loading popular terms:', error);
                }
            }
            
            displayPopularTerms(terms) {
                this.popularTermsList.innerHTML = '';
                terms.forEach(term => {
                    const button = document.createElement('button');
                    button.className = 'px-3 py-1 text-sm bg-blue-100 text-blue-800 rounded-full hover:bg-blue-200 transition-colors';
                    button.textContent = term;
                    button.addEventListener('click', () => {
                        this.searchInput.value = term;
                        this.performSearch(term);
                    });
                    this.popularTermsList.appendChild(button);
                });
            }
            
            showPopularTerms() {
                this.popularTerms.classList.remove('hidden');
            }
            
            hidePopularTerms() {
                this.popularTerms.classList.add('hidden');
            }
            
            async performSearch(query) {
                if (query === this.currentQuery) return;
                
                this.currentQuery = query;
                this.showLoader();
                this.hidePopularTerms();
                this.hideError();
                
                try {
                    const startTime = Date.now();
                    const response = await fetch(`search.php?action=search&q=${encodeURIComponent(query)}&limit=20`);
                    const data = await response.json();
                    const endTime = Date.now();
                    
                    this.hideLoader();
                    
                    if (data.success) {
                        const searchTime = (endTime - startTime) / 1000;
                        this.displayResults(data.results, query, searchTime);
                        
                        if (data.suggestions && data.suggestions.length > 0) {
                            this.displaySuggestions(data.suggestions);
                        } else {
                            this.hideSuggestions();
                        }
                    } else {
                        this.showError(data.message || 'حدث خطأ في البحث');
                    }
                } catch (error) {
                    this.hideLoader();
                    this.showError('خطأ في الاتصال بالخادم');
                    console.error('Search error:', error);
                }
            }
            
            displayResults(results, query, searchTime) {
                if (results.length === 0) {
                    this.showNoResults(query);
                    return;
                }
                
                this.hideNoResults();
                this.showSearchStats(results.length, searchTime);
                
                this.resultsContainer.innerHTML = '';
                this.resultsCount.textContent = `تم العثور على ${results.length} منتج`;
                
                const grid = document.createElement('div');
                grid.className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6';
                
                results.forEach((result, index) => {
                    const productCard = this.createProductCard(result, query, index);
                    grid.appendChild(productCard);
                });
                
                this.resultsContainer.appendChild(grid);
                this.resultsSection.classList.remove('hidden');
                this.resultsSection.classList.add('fade-in');
            }
            
            createProductCard(result, query, index) {
                const { product, score, reasons } = result;
                
                const card = document.createElement('div');
                card.className = 'product-card bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden';
                card.style.animationDelay = `${index * 0.1}s`;
                
                const priceFormatted = parseFloat(product.final_selling_price).toLocaleString('ar-SA', {
                    style: 'currency',
                    currency: 'SAR'
                });
                
                card.innerHTML = `
                    <div class="aspect-w-1 aspect-h-1 bg-gray-200">
                        <img 
                            src="${product.image || 'https://via.placeholder.com/300x300?text=No+Image'}" 
                            alt="${product.name_ar}"
                            class="w-full h-48 object-cover"
                            onerror="this.src='https://via.placeholder.com/300x300?text=No+Image'"
                        >
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2">${this.highlightText(product.name_ar, query)}</h3>
                        <p class="text-sm text-gray-600 mb-2 line-clamp-2">${this.highlightText(product.desc_ar || '', query)}</p>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-lg font-bold text-green-600">${priceFormatted}</span>
                            <span class="text-xs text-gray-500">النتيجة: ${Math.round(score * 100)}%</span>
                        </div>
                        <div class="flex items-center justify-between text-sm text-gray-500">
                            <span>${product.brand}</span>
                            <span>${product.category}</span>
                        </div>
                        ${product.colors ? `<div class="mt-2"><span class="text-xs bg-gray-100 px-2 py-1 rounded">${product.colors}</span></div>` : ''}
                        <div class="mt-2 text-xs text-blue-600">
                            مطابق في: ${reasons.map(r => this.translateReason(r)).join('، ')}
                        </div>
                    </div>
                `;
                
                return card;
            }
            
            translateReason(reason) {
                const translations = {
                    'name': 'اسم المنتج',
                    'description': 'الوصف',
                    'brand': 'العلامة التجارية',
                    'category': 'الفئة',
                    'color': 'اللون',
                    'attributes': 'الخصائص'
                };
                return translations[reason] || reason;
            }
            
            highlightText(text, query) {
                if (!text || !query) return text;
                
                const regex = new RegExp(`(${query})`, 'gi');
                return text.replace(regex, '<span class="search-highlight">$1</span>');
            }
            
            displaySuggestions(suggestions) {
                this.suggestionsList.innerHTML = '';
                suggestions.forEach(suggestion => {
                    const button = document.createElement('button');
                    button.className = 'suggestion-item px-3 py-1 text-sm bg-yellow-100 text-yellow-800 rounded-full hover:bg-yellow-200 transition-colors';
                    button.textContent = suggestion.word;
                    button.addEventListener('click', () => {
                        this.searchInput.value = suggestion.word;
                        this.performSearch(suggestion.word);
                    });
                    this.suggestionsList.appendChild(button);
                });
                this.suggestions.classList.remove('hidden');
            }
            
            hideSuggestions() {
                this.suggestions.classList.add('hidden');
            }
            
            showSearchStats(count, time) {
                this.statsText.textContent = `تم العثور على ${count} نتيجة في ${time.toFixed(2)} ثانية`;
                this.searchStats.classList.remove('hidden');
            }
            
            showNoResults(query) {
                this.resultsSection.classList.add('hidden');
                this.loadSuggestionsForNoResults(query);
                this.noResults.classList.remove('hidden');
            }
            
            hideNoResults() {
                this.noResults.classList.add('hidden');
            }
            
            async loadSuggestionsForNoResults(query) {
                try {
                    const response = await fetch(`search.php?action=suggestions&q=${encodeURIComponent(query)}`);
                    const data = await response.json();
                    
                    if (data.success && data.suggestions && data.suggestions.length > 0) {
                        const suggestionsHtml = data.suggestions.map(suggestion => 
                            `<button class="suggestion-item px-3 py-1 text-sm bg-blue-100 text-blue-800 rounded-full hover:bg-blue-200 transition-colors mr-2" 
                                     onclick="searchInstance.searchInput.value='${suggestion.word}'; searchInstance.performSearch('${suggestion.word}')">${suggestion.word}</button>`
                        ).join('');
                        
                        this.noResultsSuggestions.innerHTML = `
                            <p class="text-gray-600 mb-3">جرب البحث عن:</p>
                            <div class="flex flex-wrap gap-2">${suggestionsHtml}</div>
                        `;
                    }
                } catch (error) {
                    console.error('Error loading suggestions:', error);
                }
            }
            
            showError(message) {
                this.errorMessage.textContent = message;
                this.errorSection.classList.remove('hidden');
            }
            
            hideError() {
                this.errorSection.classList.add('hidden');
            }
            
            showLoader() {
                this.searchLoader.classList.remove('hidden');
            }
            
            hideLoader() {
                this.searchLoader.classList.add('hidden');
            }
            
            clearResults() {
                this.resultsSection.classList.add('hidden');
                this.hideNoResults();
                this.hideSuggestions();
                this.hideError();
                this.searchStats.classList.add('hidden');
            }
        }
        
        // Initialize the search system
        const searchInstance = new ArabicProductSearch();
    </script>
</body>
</html>