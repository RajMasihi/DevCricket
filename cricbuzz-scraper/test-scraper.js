const { chromium } = require('playwright');

/**
 * Test script to verify Playwright installation and basic functionality
 */
async function testPlaywright() {
  console.log('🧪 Testing Playwright installation...');
  
  try {
    // Test 1: Launch browser
    console.log('Test 1: Launching browser...');
    const browser = await chromium.launch({
      headless: true,
      args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    console.log('✅ Browser launched successfully');
    
    // Test 2: Create page
    console.log('Test 2: Creating page...');
    const page = await browser.newPage();
    console.log('✅ Page created successfully');
    
    // Test 3: Navigate to Cricbuzz
    console.log('Test 3: Navigating to Cricbuzz...');
    await page.goto('https://www.cricbuzz.com/cricket-match/live-scores', {
      waitUntil: 'networkidle',
      timeout: 30000
    });
    console.log('✅ Page loaded successfully');
    
    // Test 4: Check for content
    console.log('Test 4: Checking for cricket content...');
    const hasContent = await page.evaluate(() => {
      const body = document.body.innerText;
      return body.includes('cricket') || body.includes('match') || body.includes('score');
    });
    
    if (hasContent) {
      console.log('✅ Cricket content found');
    } else {
      console.log('⚠️ No cricket content found (might be loading issue)');
    }
    
    // Test 5: Take screenshot
    console.log('Test 5: Taking screenshot...');
    await page.screenshot({ path: 'test-screenshot.png' });
    console.log('✅ Screenshot saved to test-screenshot.png');
    
    // Test 6: Extract some sample data
    console.log('Test 6: Extracting sample data...');
    const sampleData = await page.evaluate(() => {
      const matchCards = document.querySelectorAll('[class*="match"]');
      return {
        matchCardsFound: matchCards.length,
        pageText: document.body.innerText.substring(0, 500)
      };
    });
    console.log('✅ Sample data extracted:', sampleData);
    
    // Cleanup
    await browser.close();
    console.log('✅ Browser closed');
    
    console.log('\n🎉 All tests passed!');
    console.log('✅ Playwright is working correctly');
    console.log('✅ You can now run the main scraper: node scraper.js');
    
  } catch (error) {
    console.error('❌ Test failed:', error.message);
    console.error('Stack trace:', error.stack);
    process.exit(1);
  }
}

// Run tests
testPlaywright();
