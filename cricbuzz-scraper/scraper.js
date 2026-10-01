const { chromium } = require('playwright');
const axios = require('axios');
const fs = require('fs');
const path = require('path');

// Load configuration
let CONFIG;
try {
  const configPath = path.join(__dirname, 'config.json');
  const configData = JSON.parse(fs.readFileSync(configPath, 'utf8'));
  CONFIG = {
    CRICBUZZ_URL: configData.cricbuzzUrl,
    WEBHOOK_URL: configData.webhookUrl,
    POLLING_INTERVAL: configData.pollingInterval,
    MUTATION_OBSERVER_TIMEOUT: configData.mutationObserverTimeout,
    RECONNECT_DELAY: configData.reconnectDelay,
    MAX_RECONNECT_ATTEMPTS: configData.maxReconnectAttempts,
    HEADLESS: configData.headless,
    USER_AGENT: configData.userAgent
  };
} catch (error) {
  console.log('⚠️ Using default configuration (config.json not found)');
  CONFIG = {
    CRICBUZZ_URL: 'https://www.cricbuzz.com/cricket-match/live-scores',
    WEBHOOK_URL: 'http://localhost:8000/api/v1/cricket-webhook',
    POLLING_INTERVAL: 10000,
    MUTATION_OBSERVER_TIMEOUT: 10000,
    RECONNECT_DELAY: 5000,
    MAX_RECONNECT_ATTEMPTS: 5,
    HEADLESS: true,
    USER_AGENT: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
  };
}

// State management
let browser = null;
let page = null;
let reconnectAttempts = 0;
let isRunning = false;
let lastCommentaryData = null;

/**
 * Initialize browser with realistic settings
 */
async function initializeBrowser() {
  console.log('🚀 Initializing browser...');
  
  try {
    browser = await chromium.launch({
      headless: CONFIG.HEADLESS,
      args: [
        '--no-sandbox',
        '--disable-setuid-sandbox',
        '--disable-dev-shm-usage',
        '--disable-accelerated-2d-canvas',
        '--disable-gpu',
        '--window-size=1920,1080'
      ]
    });

    const context = await browser.newContext({
      userAgent: CONFIG.USER_AGENT,
      viewport: { width: 1920, height: 1080 },
      locale: 'en-US',
      timezoneId: 'Asia/Kolkata',
      // Additional headers to avoid detection
      extraHTTPHeaders: {
        'Accept-Language': 'en-US,en;q=0.9',
        'Accept-Encoding': 'gzip, deflate, br',
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Connection': 'keep-alive',
        'Upgrade-Insecure-Requests': '1'
      }
    });

    page = await context.newPage();
    
    // Set default timeout
    page.setDefaultTimeout(30000);
    
    console.log('✅ Browser initialized successfully');
    return true;
  } catch (error) {
    console.error('❌ Failed to initialize browser:', error.message);
    return false;
  }
}

/**
 * Network interceptor for API calls
 */
function setupNetworkInterceptor() {
  console.log('🔍 Setting up network interceptor...');
  
  let apiDataFound = false;

  page.on('response', async (response) => {
    const url = response.url();
    const request = response.request();
    const resourceType = request.resourceType();
    
    // Focus on XHR and fetch requests
    if (resourceType === 'xhr' || resourceType === 'fetch') {
      console.log(`📡 Network request detected: ${url}`);
      
      try {
        // Look for Cricbuzz API endpoints
        if (url.includes('cricbuzz') && 
            (url.includes('api') || url.includes('commentary') || url.includes('score'))) {
          
          const contentType = response.headers()['content-type'] || '';
          
          if (contentType.includes('application/json') || contentType.includes('text')) {
            try {
              const data = await response.json();
              console.log('📦 API data captured:', JSON.stringify(data).substring(0, 200) + '...');
              
              // Process and send to webhook
              await processApiData(data);
              apiDataFound = true;
            } catch (jsonError) {
              // If not JSON, try text
              try {
                const text = await response.text();
                console.log('📦 API text captured:', text.substring(0, 200) + '...');
                await processApiText(text);
                apiDataFound = true;
              } catch (textError) {
                console.log('⚠️ Could not parse response body');
              }
            }
          }
        }
      } catch (error) {
        console.error('❌ Error processing network response:', error.message);
      }
    }
  });

  return apiDataFound;
}

/**
 * Process API data and extract cricket information
 */
async function processApiData(data) {
  try {
    const cricketData = extractCricketData(data);
    if (cricketData) {
      await sendToWebhook(cricketData);
    }
  } catch (error) {
    console.error('❌ Error processing API data:', error.message);
  }
}

/**
 * Process API text data
 */
async function processApiText(text) {
  try {
    // Try to parse as JSON
    const data = JSON.parse(text);
    return await processApiData(data);
  } catch (error) {
    // If not JSON, try to extract cricket data from text
    const cricketData = extractCricketDataFromText(text);
    if (cricketData) {
      await sendToWebhook(cricketData);
    }
  }
}

/**
 * Extract cricket data from API response
 */
function extractCricketData(data) {
  try {
    // Handle different API response structures
    let matchData = null;
    
    // Cricbuzz API structure variations
    if (data.matchInfo) {
      matchData = data;
    } else if (data.data && data.data.matchInfo) {
      matchData = data.data;
    } else if (Array.isArray(data) && data.length > 0) {
      matchData = data[0];
    } else {
      matchData = data;
    }

    if (!matchData) return null;

    // Extract commentary if available
    let commentary = null;
    if (matchData.commentary) {
      commentary = matchData.commentary;
    } else if (matchData.commentaryList) {
      commentary = matchData.commentaryList;
    }

    // Extract latest ball information
    let latestBall = null;
    if (commentary && Array.isArray(commentary) && commentary.length > 0) {
      latestBall = commentary[commentary.length - 1];
    }

    // Extract match info
    const matchInfo = matchData.matchInfo || {};
    const scoreCard = matchData.scoreCard || {};

    // Extract team information
    const team1 = matchInfo.team1 || {};
    const team2 = matchInfo.team2 || {};
    
    // Extract scores
    const team1Score = scoreCard.team1Score || {};
    const team2Score = scoreCard.team2Score || {};

    // Extract current over/ball
    let ball = extractBallInfo(latestBall, team1Score, team2Score);
    
    // Extract batsman and bowler
    const batsman = extractBatsmanInfo(latestBall, scoreCard);
    const bowler = extractBowlerInfo(latestBall, scoreCard);

    // Extract runs
    const runs = extractRunsInfo(latestBall, team1Score, team2Score);

    // Extract commentary text
    const commentaryText = latestBall?.commText || latestBall?.commentary || 'No commentary available';

    const structuredData = {
      ball: ball,
      batsman: batsman,
      bowler: bowler,
      runs: runs,
      commentary: commentaryText,
      matchId: matchInfo.matchId || 'unknown',
      team1: team1.teamName || 'Team 1',
      team2: team2.teamName || 'Team 2',
      team1Score: `${team1Score.runs || 0}/${team1Score.wickets || 0}`,
      team2Score: `${team2Score.runs || 0}/${team2Score.wickets || 0}`,
      timestamp: new Date().toISOString(),
      source: 'network-interception'
    };

    // Check if data has changed
    if (JSON.stringify(structuredData) !== JSON.stringify(lastCommentaryData)) {
      lastCommentaryData = structuredData;
      console.log('🎯 Extracted cricket data:', JSON.stringify(structuredData, null, 2));
      return structuredData;
    }

    return null;
  } catch (error) {
    console.error('❌ Error extracting cricket data:', error.message);
    return null;
  }
}

/**
 * Extract cricket data from text (fallback)
 */
function extractCricketDataFromText(text) {
  try {
    // Try to find JSON patterns in text
    const jsonMatch = text.match(/\{[\s\S]*\}/);
    if (jsonMatch) {
      const data = JSON.parse(jsonMatch[0]);
      return extractCricketData(data);
    }
    return null;
  } catch (error) {
    return null;
  }
}

/**
 * Extract ball information
 */
function extractBallInfo(latestBall, team1Score, team2Score) {
  if (latestBall?.ball) {
    return latestBall.ball;
  }
  
  // Try to get from score
  const overs = team1Score?.overs || team2Score?.overs;
  if (overs) {
    return overs.toString();
  }
  
  return '0.0';
}

/**
 * Extract batsman information
 */
function extractBatsmanInfo(latestBall, scoreCard) {
  if (latestBall?.batsman) {
    return latestBall.batsman;
  }
  
  // Try to get from scorecard
  const batsmen = scoreCard?.batsmen || [];
  if (batsmen.length > 0) {
    const striker = batsmen.find(b => b.striker === true) || batsmen[0];
    return striker?.name || 'Unknown';
  }
  
  return 'Unknown';
}

/**
 * Extract bowler information
 */
function extractBowlerInfo(latestBall, scoreCard) {
  if (latestBall?.bowler) {
    return latestBall.bowler;
  }
  
  // Try to get from scorecard
  const bowlers = scoreCard?.bowlers || [];
  if (bowlers.length > 0) {
    return bowlers[0]?.name || 'Unknown';
  }
  
  return 'Unknown';
}

/**
 * Extract runs information
 */
function extractRunsInfo(latestBall, team1Score, team2Score) {
  if (latestBall?.runs) {
    return latestBall.runs.toString();
  }
  
  // Try to get from score
  const runs = team1Score?.runs || team2Score?.runs;
  if (runs !== undefined) {
    return runs.toString();
  }
  
  return '0';
}

/**
 * Setup MutationObserver for DOM changes (fallback)
 */
async function setupMutationObserver() {
  console.log('👀 Setting up MutationObserver as fallback...');
  
  await page.evaluate(() => {
    window.fallbackActive = true;
    window.lastBallData = null;
    
    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        if (mutation.type === 'childList' || mutation.type === 'characterData') {
          // Look for commentary wrapper
          const commentaryWrapper = document.querySelector('.cb-col-100.cb-com-wraper') ||
                                   document.querySelector('.cb-com-ln') ||
                                   document.querySelector('[class*="commentary"]');
          
          if (commentaryWrapper) {
            const ballData = extractBallDataFromDOM(commentaryWrapper);
            if (ballData && JSON.stringify(ballData) !== JSON.stringify(window.lastBallData)) {
              window.lastBallData = ballData;
              window.postMessage({ type: 'CRICKET_DATA', data: ballData }, '*');
            }
          }
        }
      });
    });
    
    // Start observing the entire document
    observer.observe(document.body, {
      childList: true,
      subtree: true,
      characterData: true
    });
    
    // Initial extraction
    setTimeout(() => {
      const commentaryWrapper = document.querySelector('.cb-col-100.cb-com-wraper') ||
                               document.querySelector('.cb-com-ln') ||
                               document.querySelector('[class*="commentary"]');
      if (commentaryWrapper) {
        const ballData = extractBallDataFromDOM(commentaryWrapper);
        if (ballData) {
          window.lastBallData = ballData;
          window.postMessage({ type: 'CRICKET_DATA', data: ballData }, '*');
        }
      }
    }, 2000);
    
    // Helper function to extract data from DOM
    function extractBallDataFromDOM(wrapper) {
      try {
        const ballElement = wrapper.querySelector('[class*="ball"]') || 
                          wrapper.querySelector('.cb-col-8') ||
                          wrapper.querySelector('span:first-child');
        
        const batsmanElement = wrapper.querySelector('[class*="batsman"]') ||
                               wrapper.querySelector('.cb-col-40');
        
        const bowlerElement = wrapper.querySelector('[class*="bowler"]') ||
                              wrapper.querySelector('.cb-col-25');
        
        const runsElement = wrapper.querySelector('[class*="runs"]') ||
                           wrapper.querySelector('.cb-col-10');
        
        const commentaryElement = wrapper.querySelector('[class*="comm"]') ||
                                  wrapper.querySelector('.cb-col-60');
        
        return {
          ball: ballElement?.textContent?.trim() || '0.0',
          batsman: batsmanElement?.textContent?.trim() || 'Unknown',
          bowler: bowlerElement?.textContent?.trim() || 'Unknown',
          runs: runsElement?.textContent?.trim() || '0',
          commentary: commentaryElement?.textContent?.trim() || 'No commentary',
          source: 'mutation-observer'
        };
      } catch (error) {
        return null;
      }
    }
  });
  
  // Listen for messages from the page
  page.on('console', msg => {
    if (msg.text().includes('CRICKET_DATA')) {
      console.log('📊 MutationObserver detected data change');
    }
  });
  
  page.on('framenavigated', async () => {
    console.log('🔄 Page navigated, re-setting up observer');
    await setupMutationObserver();
  });
}

/**
 * Send data to Laravel webhook
 */
async function sendToWebhook(data) {
  if (!data) return;
  
  try {
    console.log('📤 Sending data to webhook:', CONFIG.WEBHOOK_URL);
    
    const response = await axios.post(CONFIG.WEBHOOK_URL, data, {
      headers: {
        'Content-Type': 'application/json',
        'User-Agent': CONFIG.USER_AGENT
      },
      timeout: 10000
    });
    
    console.log('✅ Webhook response:', response.status, response.statusText);
    return true;
  } catch (error) {
    if (error.response) {
      console.error('❌ Webhook error:', error.response.status, error.response.data);
    } else if (error.request) {
      console.error('❌ Webhook no response:', error.message);
    } else {
      console.error('❌ Webhook error:', error.message);
    }
    return false;
  }
}

/**
 * Navigate to Cricbuzz and setup monitoring
 */
async function navigateAndMonitor() {
  console.log('🌐 Navigating to Cricbuzz...');
  
  try {
    // Setup network interceptor before navigation
    const networkInterceptorSetup = setupNetworkInterceptor();
    
    // Navigate to the URL
    await page.goto(CONFIG.CRICBUZZ_URL, {
      waitUntil: 'networkidle',
      timeout: 30000
    });
    
    console.log('✅ Page loaded successfully');
    
    // Wait for content to load
    await page.waitForTimeout(3000);
    
    // Check if network interceptor found data
    let apiDataFound = false;
    
    // If network interception didn't work, setup MutationObserver
    await page.waitForTimeout(5000); // Give network interceptor time to catch data
    
    if (!apiDataFound) {
      console.log('⚠️ Network interception didn\'t find data, setting up MutationObserver');
      await setupMutationObserver();
    }
    
    // Start periodic polling as additional fallback
    startPeriodicPolling();
    
    return true;
  } catch (error) {
    console.error('❌ Error navigating to Cricbuzz:', error.message);
    return false;
  }
}

/**
 * Start periodic polling for data extraction
 */
function startPeriodicPolling() {
  console.log('⏰ Starting periodic polling...');
  
  const pollInterval = setInterval(async () => {
    if (!isRunning) {
      clearInterval(pollInterval);
      return;
    }
    
    try {
      // Extract data from page
      const data = await page.evaluate(() => {
        const commentaryWrapper = document.querySelector('.cb-col-100.cb-com-wraper') ||
                                 document.querySelector('.cb-com-ln') ||
                                 document.querySelector('[class*="commentary"]');
        
        if (!commentaryWrapper) return null;
        
        const ballElement = commentaryWrapper.querySelector('[class*="ball"]') || 
                          commentaryWrapper.querySelector('.cb-col-8');
        
        const batsmanElement = commentaryWrapper.querySelector('[class*="batsman"]') ||
                               commentaryWrapper.querySelector('.cb-col-40');
        
        const bowlerElement = commentaryWrapper.querySelector('[class*="bowler"]') ||
                              commentaryWrapper.querySelector('.cb-col-25');
        
        const runsElement = commentaryWrapper.querySelector('[class*="runs"]') ||
                           commentaryWrapper.querySelector('.cb-col-10');
        
        const commentaryElement = commentaryWrapper.querySelector('[class*="comm"]') ||
                                  commentaryWrapper.querySelector('.cb-col-60');
        
        return {
          ball: ballElement?.textContent?.trim() || '0.0',
          batsman: batsmanElement?.textContent?.trim() || 'Unknown',
          bowler: bowlerElement?.textContent?.trim() || 'Unknown',
          runs: runsElement?.textContent?.trim() || '0',
          commentary: commentaryElement?.textContent?.trim() || 'No commentary',
          source: 'periodic-polling'
        };
      });
      
      if (data && JSON.stringify(data) !== JSON.stringify(lastCommentaryData)) {
        lastCommentaryData = data;
        console.log('📊 Polling extracted data:', JSON.stringify(data));
        await sendToWebhook(data);
      }
    } catch (error) {
      console.error('❌ Error during polling:', error.message);
    }
  }, CONFIG.POLLING_INTERVAL);
}

/**
 * Handle browser crash and reconnection
 */
async function handleCrash() {
  console.log('💥 Browser crash detected, attempting reconnection...');
  
  if (reconnectAttempts >= CONFIG.MAX_RECONNECT_ATTEMPTS) {
    console.error('❌ Max reconnection attempts reached. Stopping.');
    return false;
  }
  
  reconnectAttempts++;
  console.log(`🔄 Reconnection attempt ${reconnectAttempts}/${CONFIG.MAX_RECONNECT_ATTEMPTS}`);
  
  // Close existing browser if possible
  try {
    if (browser) {
      await browser.close();
    }
  } catch (error) {
    console.log('⚠️ Error closing browser:', error.message);
  }
  
  // Wait before reconnection
  await new Promise(resolve => setTimeout(resolve, CONFIG.RECONNECT_DELAY));
  
  // Reinitialize
  const initialized = await initializeBrowser();
  if (initialized) {
    const navigated = await navigateAndMonitor();
    if (navigated) {
      reconnectAttempts = 0; // Reset on successful reconnection
      return true;
    }
  }
  
  return false;
}

/**
 * Main execution function
 */
async function main() {
  console.log('🎯 Starting Cricbuzz Live Scraper...');
  console.log('📍 Target URL:', CONFIG.CRICBUZZ_URL);
  console.log('🎣 Webhook URL:', CONFIG.WEBHOOK_URL);
  console.log('');
  
  isRunning = true;
  
  // Initialize browser
  const initialized = await initializeBrowser();
  if (!initialized) {
    console.error('❌ Failed to initialize browser. Exiting.');
    process.exit(1);
  }
  
  // Navigate and start monitoring
  const navigated = await navigateAndMonitor();
  if (!navigated) {
    console.error('❌ Failed to navigate to Cricbuzz. Exiting.');
    process.exit(1);
  }
  
  console.log('✅ Scraper is running and monitoring for live data...');
  console.log('📡 Press Ctrl+C to stop');
  
  // Handle graceful shutdown
  process.on('SIGINT', async () => {
    console.log('\n🛑 Stopping scraper...');
    isRunning = false;
    
    try {
      if (browser) {
        await browser.close();
      }
    } catch (error) {
      console.error('❌ Error closing browser:', error.message);
    }
    
    console.log('✅ Scraper stopped gracefully');
    process.exit(0);
  });
  
  // Handle errors
  page.on('error', async (error) => {
    console.error('❌ Page error:', error.message);
    await handleCrash();
  });
  
  browser.on('disconnected', async () => {
    console.error('❌ Browser disconnected');
    await handleCrash();
  });
  
  // Keep the process running
  process.stdin.resume();
}

// Start the scraper
main().catch(error => {
  console.error('❌ Fatal error:', error);
  process.exit(1);
});
