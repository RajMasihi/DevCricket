# Cricbuzz Live Scraper with Playwright

A robust Node.js Playwright-based scraper for extracting live cricket ball-by-ball commentary and scores from Cricbuzz using network interception with intelligent fallback mechanisms.

## 🎯 Features

- **Network Interception**: Captures Cricbuzz API calls (XHR/Fetch) for real-time data
- **MutationObserver Fallback**: Monitors DOM changes when network interception fails
- **Periodic Polling**: Additional 10-second interval polling as backup
- **Automatic Reconnection**: Handles browser crashes with smart reconnection logic
- **Webhook Integration**: Sends structured data to Laravel backend endpoint
- **No API Key Required**: Works purely with Playwright (no Cricbuzz API key needed)
- **Realistic User-Agent**: Avoids detection with proper browser simulation
- **Modular Code**: Clean, maintainable architecture with proper error handling

## 📋 Requirements

- Node.js 20.0.0 or higher
- npm or yarn
- Laravel backend (running on http://localhost:8000)

## 🚀 Installation

### 1. Navigate to scraper directory
```bash
cd C:\wamp\www\criclivem\DevCricket\cricbuzz-scraper
```

### 2. Install dependencies
```bash
npm install
```

This will install:
- `playwright` - Browser automation library
- `axios` - HTTP client for webhook requests

### 3. Install Playwright browsers
```bash
npx playwright install chromium
```

### 4. Run database migration (Laravel)
```bash
cd C:\wamp\www\criclivem\DevCricket
php artisan migrate
```

This creates the `cricket_live_data` table for storing scraped data.

## ⚙️ Configuration

Edit `config.json` to customize settings:

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

### Configuration Options

- `cricbuzzUrl`: Target Cricbuzz URL to scrape
- `webhookUrl`: Laravel backend webhook endpoint
- `pollingInterval`: Interval for periodic polling (milliseconds)
- `mutationObserverTimeout`: Timeout for MutationObserver (milliseconds)
- `reconnectDelay`: Delay between reconnection attempts (milliseconds)
- `maxReconnectAttempts`: Maximum reconnection attempts before giving up
- `headless`: Run browser in headless mode (true/false)
- `userAgent`: Custom user agent string

## 🧪 Testing

### Test Playwright Installation
```bash
node test-scraper.js
```

This will:
1. Launch a headless browser
2. Navigate to Cricbuzz
3. Check for cricket content
4. Take a screenshot
5. Extract sample data

## 🎮 Usage

### Start the Scraper
```bash
node scraper.js
```

The scraper will:
1. Launch a headless Chromium browser
2. Navigate to Cricbuzz live scores page
3. Setup network interception for API calls
4. Fallback to MutationObserver if needed
5. Start periodic polling
6. Send extracted data to Laravel webhook
7. Handle crashes and reconnection automatically

### Stop the Scraper
Press `Ctrl+C` to stop gracefully.

## 📊 Data Structure

The scraper sends structured JSON data to the webhook:

```json
{
  "ball": "14.2",
  "batsman": "Virat Kohli",
  "bowler": "Pat Cummins",
  "runs": "4",
  "commentary": "FOUR! Beautiful cover drive",
  "matchId": "match-12345",
  "team1": "India",
  "team2": "Australia",
  "team1Score": "145/3",
  "team2Score": "120/4",
  "timestamp": "2024-09-30T12:30:45.000Z",
  "source": "network-interception"
}
```

## 🔧 Laravel Backend Setup

### 1. Add Webhook Route
The route is already added in `routes/api.php`:
```php
Route::post('/v1/cricket-webhook', [CricketWebhookController::class, 'handleWebhook']);
```

### 2. Webhook Controller
The controller is in `app/Http/Controllers/CricketWebhookController.php`:
- Validates incoming data
- Stores to database
- Caches for quick access
- Logs all activities

### 3. Database Table
Migration in `database/migrations/2024_09_30_000001_create_cricket_live_data_table.php`:
- Stores all scraped data
- Includes indexes for performance
- Raw data preservation

## 🔄 Data Flow

```
Cricbuzz Website → Playwright Browser → Network Interception
                                                    ↓
                                               API Response (JSON)
                                                    ↓
                                         Extract Cricket Data
                                                    ↓
                                            Structured JSON
                                                    ↓
                                       Laravel Webhook Endpoint
                                                    ↓
                                          Database & Cache
                                                    ↓
                                            Real-time Updates
```

## 🛡️ Anti-Detection Features

- Realistic User-Agent string
- Proper browser headers
- Viewport configuration
- Locale and timezone settings
- Network throttling simulation
- Human-like navigation patterns

## 🚨 Error Handling

The scraper includes comprehensive error handling:

- **Browser Crashes**: Automatic reconnection with exponential backoff
- **Network Failures**: Fallback to MutationObserver
- **API Errors**: Graceful degradation with logging
- **Webhook Failures**: Retry logic with error logging
- **Data Validation**: Schema validation before processing

## 📈 Performance

- **Network Interception**: Real-time (near-zero latency)
- **MutationObserver**: ~100ms latency
- **Periodic Polling**: 10-second intervals
- **Memory Usage**: ~100-200MB
- **CPU Usage**: Low (headless mode)

## 🔍 Logging

The scraper provides detailed console logging:
- Browser initialization status
- Network request detection
- Data extraction success/failure
- Webhook transmission status
- Error details and stack traces
- Reconnection attempts

## 🛠️ Troubleshooting

### Issue: Playwright browsers not installed
```bash
npx playwright install chromium
```

### Issue: Node.js version too old
```bash
# Upgrade to Node.js 20+
nvm install 20
nvm use 20
```

### Issue: Webhook not receiving data
1. Check Laravel is running: `php artisan serve`
2. Verify webhook URL in config.json
3. Check Laravel logs: `storage/logs/laravel.log`
4. Test webhook endpoint manually

### Issue: No data being extracted
1. Run test script: `node test-scraper.js`
2. Check screenshot: `test-screenshot.png`
3. Enable headless mode: `"headless": false` in config.json
4. Check console logs for errors

### Issue: Browser crashes frequently
1. Increase `reconnectDelay` in config.json
2. Increase `maxReconnectAttempts`
3. Check system resources (memory/CPU)
4. Disable headless mode for debugging

## 📝 Advanced Usage

### Custom Selectors
Modify the DOM extraction logic in `scraper.js`:
```javascript
const commentaryWrapper = document.querySelector('.your-custom-selector');
```

### Additional Data Fields
Extend the data extraction in `extractCricketData()`:
```javascript
const structuredData = {
  // ... existing fields
  customField: extractCustomField(data)
};
```

### Multiple Webhooks
Add multiple webhook endpoints:
```javascript
await Promise.all([
  sendToWebhook(data, 'http://localhost:8000/api/v1/webhook1'),
  sendToWebhook(data, 'http://localhost:8000/api/v1/webhook2')
]);
```

## 🔐 Security Considerations

- The scraper runs on your local network
- Webhook endpoint should be protected in production
- Consider adding authentication tokens
- Rate limiting for webhook requests
- Input validation on webhook endpoint

## 📄 License

MIT License - Feel free to use and modify as needed.

## 🤝 Contributing

Contributions are welcome! Please ensure:
- Code follows existing patterns
- Error handling is comprehensive
- Logging is detailed
- Documentation is updated

## 🆘 Support

For issues or questions:
1. Check this README
2. Review console logs
3. Check Laravel logs
4. Run test script for diagnostics

## 🎯 Future Enhancements

- [ ] Support for multiple match tracking
- [ ] WebSocket integration for real-time updates
- [ ] Dashboard for monitoring scraper status
- [ ] Historical data analysis
- [ ] Machine learning for pattern detection
- [ ] Support for other cricket websites

---

**Note**: This scraper is for educational purposes. Always respect website terms of service and robots.txt files.
