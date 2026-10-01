# Cricbuzz Playwright Scraper - Setup Guide

## 🎯 Overview

This guide will help you set up a robust Playwright-based scraper for extracting live cricket data from Cricbuzz without using any API keys. The scraper uses network interception with intelligent fallback mechanisms.

## 📋 Prerequisites

### Required
- **Node.js 16.0.0 or higher** (Playwright 1.28.0 works with Node.js 16+)
- **npm** or **yarn**
- **Laravel backend** (optional - can use any webhook endpoint)

### Recommended
- **Node.js 20.0.0+** for latest Playwright features
- **MySQL/PostgreSQL** for data storage (optional - cache works too)

## 🚀 Quick Start

### Step 1: Navigate to Scraper Directory
```bash
cd C:\wamp\www\criclivem\DevCricket\cricbuzz-scraper
```

### Step 2: Install Dependencies
```bash
npm install
```

This installs:
- `playwright` (v1.28.0 - compatible with Node.js 16+)
- `axios` (for webhook requests)

### Step 3: Install Playwright Browsers
```bash
npx playwright install chromium
```

### Step 4: Configure Webhook URL

Edit `config.json`:
```json
{
  "cricbuzzUrl": "https://www.cricbuzz.com/cricket-match/live-scores",
  "webhookUrl": "http://localhost:8000/api/v1/cricket-webhook",
  "pollingInterval": 10000,
  "mutationObserverTimeout": 10000,
  "reconnectDelay": 5000,
  "maxReconnectAttempts": 5,
  "headless": true,
  "userAgent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
}
```

### Step 5: Test Installation
```bash
node test-scraper.js
```

Expected output:
```
🧪 Testing Playwright installation...
Test 1: Launching browser...
✅ Browser launched successfully
Test 2: Creating page...
✅ Page created successfully
Test 3: Navigating to Cricbuzz...
✅ Page loaded successfully
Test 4: Checking for cricket content...
✅ Cricket content found
Test 5: Taking screenshot...
✅ Screenshot saved to test-screenshot.png
Test 6: Extracting sample data...
✅ Sample data extracted: { matchCardsFound: X, pageText: '...' }
✅ Browser closed
🎉 All tests passed!
✅ Playwright is working correctly
✅ You can now run the main scraper: node scraper.js
```

### Step 6: Start Laravel Backend (Optional)

If using Laravel webhook:
```bash
cd C:\wamp\www\criclivem\DevCricket
php artisan serve
```

The webhook endpoint will be available at: `http://localhost:8000/api/v1/cricket-webhook`

### Step 7: Start the Scraper
```bash
node scraper.js
```

Expected output:
```
🎯 Starting Cricbuzz Live Scraper...
📍 Target URL: https://www.cricbuzz.com/cricket-match/live-scores
🎣 Webhook URL: http://localhost:8000/api/v1/cricket-webhook

🚀 Initializing browser...
✅ Browser initialized successfully
🌐 Navigating to Cricbuzz...
✅ Page loaded successfully
🔍 Setting up network interceptor...
👀 Setting up MutationObserver as fallback...
⏰ Starting periodic polling...
✅ Scraper is running and monitoring for live data...
📡 Press Ctrl+C to stop
```

## 🔧 Laravel Backend Setup (Optional)

### Step 1: Add Webhook Controller

The controller is already created at:
`app/Http/Controllers/CricketWebhookController.php`

### Step 2: Add Webhook Route

The route is already added in `routes/api.php`:
```php
Route::post('/v1/cricket-webhook', [CricketWebhookController::class, 'handleWebhook']);
```

### Step 3: Run Migration (Optional)

If you want to store data in database:
```bash
php artisan migrate
```

This creates the `cricket_live_data` table.

**Note:** If database is not configured, the webhook will still work using cache storage only.

### Step 4: Start Laravel Server
```bash
php artisan serve
```

## 🧪 Testing Webhook Endpoint

### Manual Test
```bash
curl -X POST http://localhost:8000/api/v1/cricket-webhook \
  -H "Content-Type: application/json" \
  -d '{
    "ball": "14.2",
    "batsman": "Virat Kohli",
    "bowler": "Pat Cummins",
    "runs": "4",
    "commentary": "FOUR! Beautiful cover drive",
    "matchId": "test-123",
    "team1": "India",
    "team2": "Australia",
    "team1Score": "145/3",
    "team2Score": "120/4",
    "timestamp": "2024-09-30T12:30:45.000Z",
    "source": "test"
  }'
```

Expected response:
```json
{
  "success": true,
  "message": "Cricket data received successfully",
  "data": { ... }
}
```

## 📊 Data Flow Verification

### 1. Check Scraper Logs
The scraper console will show:
- Network requests detected
- Data extraction attempts
- Webhook transmission status
- Error messages if any

### 2. Check Laravel Logs
```bash
tail -f storage/logs/laravel.log
```

You should see entries like:
```
[2024-09-30 12:30:45] local.INFO: Cricket webhook received {"ball":"14.2","batsman":"Virat Kohli",...}
[2024-09-30 12:30:45] local.INFO: Cricket data stored successfully {"cache_key":"cricket_live_data_test-123","ball":"14.2"}
```

### 3. Check Cache Data
In Laravel:
```php
$data = cache()->get('latest_cricket_data');
print_r($data);
```

## 🛠️ Troubleshooting

### Issue: Node.js Version Incompatible
**Error:** `Unsupported engine for playwright@...`

**Solution:** 
- The scraper uses Playwright 1.28.0 which works with Node.js 16+
- Current version: Node.js v16.20.2 ✅ Compatible
- If you want latest Playwright, upgrade to Node.js 20+

### Issue: Playwright Browsers Not Installed
**Error:** `Executable doesn't exist at ...`

**Solution:**
```bash
npx playwright install chromium
```

### Issue: Webhook Not Receiving Data
**Check:**
1. Laravel server running: `php artisan serve`
2. Correct webhook URL in `config.json`
3. Network connectivity between scraper and Laravel
4. Laravel logs: `storage/logs/laravel.log`

**Test:**
```bash
# Start Laravel in one terminal
php artisan serve

# Test webhook in another terminal
curl -X POST http://localhost:8000/api/v1/cricket-webhook \
  -H "Content-Type: application/json" \
  -d '{"ball":"1.1","batsman":"Test","bowler":"Test","runs":"0","commentary":"Test"}'
```

### Issue: No Data Being Extracted
**Check:**
1. Run test script: `node test-scraper.js`
2. Check screenshot: `test-screenshot.png`
3. Enable non-headless mode for debugging:
   ```json
   "headless": false
   ```
4. Check Cricbuzz website is accessible

### Issue: Database Migration Fails
**Error:** `No database selected`

**Solution:**
- The webhook controller now handles this gracefully
- Data will be stored in cache even without database
- Database is optional for basic functionality

To configure database:
1. Edit `.env` file
2. Set `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
3. Run `php artisan migrate`

## 🔄 Upgrade to Node.js 20+ (Optional)

For latest Playwright features:

### Using NVM
```bash
nvm install 20
nvm use 20
nvm alias default 20
```

### Update package.json
```json
{
  "dependencies": {
    "playwright": "^1.40.0"
  }
}
```

### Reinstall
```bash
rm -rf node_modules package-lock.json
npm install
npx playwright install chromium
```

## 📈 Performance Tuning

### Reduce Latency
```json
{
  "pollingInterval": 5000,
  "mutationObserverTimeout": 5000
}
```

### Increase Reliability
```json
{
  "maxReconnectAttempts": 10,
  "reconnectDelay": 10000
}
```

### Debug Mode
```json
{
  "headless": false
}
```

## 🔐 Security Recommendations

### Production Deployment
1. Add authentication to webhook endpoint
2. Use HTTPS for webhook URL
3. Add rate limiting
4. Validate and sanitize all input data
5. Use environment variables for sensitive config

### Authentication Example
```php
// In CricketWebhookController.php
public function handleWebhook(Request $request)
{
    $token = $request->header('X-Webhook-Token');
    if ($token !== config('webhook.token')) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    // ... rest of the code
}
```

## 📝 Configuration Options

### Full config.json Example
```json
{
  "cricbuzzUrl": "https://www.cricbuzz.com/cricket-match/live-scores",
  "webhookUrl": "http://localhost:8000/api/v1/cricket-webhook",
  "pollingInterval": 10000,
  "mutationObserverTimeout": 10000,
  "reconnectDelay": 5000,
  "maxReconnectAttempts": 5,
  "headless": true,
  "userAgent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
}
```

### Option Descriptions
- `cricbuzzUrl`: Target URL to scrape
- `webhookUrl`: Laravel backend endpoint
- `pollingInterval`: Polling interval in milliseconds
- `mutationObserverTimeout`: Observer timeout in milliseconds
- `reconnectDelay`: Delay between reconnection attempts
- `maxReconnectAttempts`: Maximum reconnection attempts
- `headless`: Run browser headless (true/false)
- `userAgent`: Custom user agent string

## 🎯 Next Steps

1. ✅ Install dependencies
2. ✅ Configure webhook URL
3. ✅ Test Playwright installation
4. ✅ Start Laravel backend (optional)
5. ✅ Run scraper
6. ✅ Monitor logs
7. ✅ Verify data flow

## 🆘 Support

If you encounter issues:

1. Check this setup guide
2. Review console logs
3. Check Laravel logs
4. Run test script
5. Enable debug mode (headless: false)
6. Check network connectivity

## 📚 Additional Resources

- [Playwright Documentation](https://playwright.dev/)
- [Axios Documentation](https://axios-http.com/)
- [Laravel Documentation](https://laravel.com/docs)
- [Project README](./README.md)

---

**Note:** This scraper is designed for educational purposes. Always respect website terms of service and robots.txt files.
