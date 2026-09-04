


$(function () {
// navebar active class 

//    filter in t20, ODi, test, other 

$(document).on('click', '.second-header .nav-link', function (e) {
    e.preventDefault();

    $('.second-header .nav-link')
        .removeClass('active')
        .css({
            'background-color': '',
            'color': ''
        });

    $(this)
        .addClass('active')
        .css({
            'background-color': '#053259', // Bootstrap primary
            'color': '#fff'
        });
});


 //Searching nav working.........

  // Ensure "Point Table" link ('.point-table-nav') works as a normal link and does not trigger this handler.
  $('.second-header .nav-item .link').on('click', function (e) {
    // Skip if this is the Point Table nav (identified by class or id)
    if ($(this).hasClass('point-table-nav') || $(this).attr('id') === 'point-table') {
      // Allow default navigation for Point Table
      return;
    }

    e.preventDefault();

    let te = $(this).text().trim().toLowerCase();
    $('#search').val(te);

    if (te === 'all' || te ==='mens') {
        $('.match-item').show();
        return;
    }

    $('.match-item').each(function () {
        let text = $(this).text().toLowerCase();

        if (text.indexOf(te) > -1) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
});


 $('#team1').click(function(){
  $('#scorecard1').show();
//   $('#scorecard2').hide();
});

$('#team2').on('click', function(){
      $('#scorecard1').hide();
      $('#scorecard2').removeClass('d-block').show();
  });

    // initIndexLiveMatchesRefresh();
    // initMatchdetailScoreboardRefresh();
});


// $(function () {
//     let currentUrl = window.location.href;
//     $('.first-header .nav-item a').each(function () {
//         if (this.href === currentUrl) {
//             $('.first-header .nav-item a').removeClass('active');
//             $(this).addClass('active');
//         }
//     });
function initMatchdetailLiveRefresh() {
    // This functionality is now handled by over_threshold_handler.js
    // which uses WebSocket with polling fallback
    if (typeof window.overThresholdHandler !== 'undefined') {
        console.log('Match detail live refresh handled by over_threshold_handler.js');
    }
}
// live match 
function initIndexLiveMatchesRefresh() {
    var root = document.getElementById('cricket-index-page');
    if (!root) {
        return;
    }

    var activeTab = (root.getAttribute('data-active-tab') || 'live').toLowerCase();
    if (activeTab !== 'live') {
        return;
    }

    // Try WebSocket first, fallback to polling
    initWebSocketForLiveMatches();
}

function initWebSocketForLiveMatches() {
    // Load Laravel Echo and Pusher JS if not available
    if (typeof Echo === 'undefined') {
        loadEchoScripts().then(function() {
            setupLiveMatchesWebSocket();
        }).catch(function() {
            console.warn('Failed to load Echo scripts, using polling fallback');
            startLiveMatchesPolling();
        });
    } else {
        setupLiveMatchesWebSocket();
    }
}

function loadEchoScripts() {
    return new Promise(function(resolve, reject) {
        var scriptsLoaded = 0;
        var totalScripts = 2;
        
        function checkLoaded() {
            scriptsLoaded++;
            if (scriptsLoaded === totalScripts) {
                resolve();
            }
        }
        
        var pusherScript = document.createElement('script');
        pusherScript.src = 'https://cdn.jsdelivr.net/npm/@pusher/pusher-js@8.4.0-rc.1/dist/pusher.min.js';
        pusherScript.onload = checkLoaded;
        pusherScript.onerror = reject;
        document.head.appendChild(pusherScript);
        
        var echoScript = document.createElement('script');
        echoScript.src = 'https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.min.js';
        echoScript.onload = checkLoaded;
        echoScript.onerror = reject;
        document.head.appendChild(echoScript);
    });
}

function setupLiveMatchesWebSocket() {
    // Get Reverb configuration from meta tags
    var reverbKey = document.querySelector('meta[name="reverb-app-key"]')?.content;
    var reverbHost = document.querySelector('meta[name="reverb-host"]')?.content;
    var reverbPort = document.querySelector('meta[name="reverb-port"]')?.content;
    var reverbScheme = document.querySelector('meta[name="reverb-scheme"]')?.content;

    if (!reverbKey) {
        console.warn('Reverb app key not found, using polling fallback');
        startLiveMatchesPolling();
        return;
    }

    try {
        var echo = new Echo({
            broadcaster: 'reverb',
            key: reverbKey,
            wsHost: reverbHost || window.location.hostname,
            wsPort: reverbPort || 8080,
            wssPort: reverbPort || 8080,
            forceTLS: reverbScheme === 'https' || window.location.protocol === 'https:',
            enabledTransports: ['ws', 'wss'],
            disableStats: true,
            authEndpoint: '/broadcasting/auth',
        });

        // Subscribe to live matches channel
        var channel = echo.channel('live-matches');
        
        channel.listen('.MatchScoreUpdated', function(data) {
            console.log('Live match update received:', data);
            updateLiveMatchCard(data);
        });

        channel.subscribed(function() {
            console.log('Successfully subscribed to live-matches channel');
        });

        channel.error(function(error) {
            console.error('Live matches channel error:', error);
            startLiveMatchesPolling();
        });

    } catch (error) {
        console.error('Failed to setup WebSocket for live matches:', error);
        startLiveMatchesPolling();
    }
}

var liveMatchesPollingInterval = null;

function startLiveMatchesPolling() {
    if (liveMatchesPollingInterval) {
        clearInterval(liveMatchesPollingInterval);
    }

    console.log('Starting polling fallback for live matches');
    liveMatchesPollingInterval = setInterval(function() {
        refreshLiveMatches();
    }, 20000); // Poll every 20 seconds

    // Initial fetch
    refreshLiveMatches();
    
    // Test over validation logic only if requested
    if (window.location.search.includes('test=true')) {
        testOverValidation();
    }
}

function testOverValidation() {
    console.log('=== Testing Over Validation Logic ===');
    
    var testCases = [
        { current: 0.1, last: 0.0, currentStr: '0.1', expected: true, desc: 'Initial value' },
        { current: 0.2, last: 0.1, currentStr: '0.2', expected: true, desc: 'Valid progression 0.1 → 0.2' },
        { current: 0.3, last: 0.2, currentStr: '0.3', expected: true, desc: 'Valid progression 0.2 → 0.3' },
        { current: 0.4, last: 0.3, currentStr: '0.4', expected: true, desc: 'Valid progression 0.3 → 0.4' },
        { current: 0.5, last: 0.4, currentStr: '0.5', expected: true, desc: 'Valid progression 0.4 → 0.5' },
        { current: 0.6, last: 0.5, currentStr: '0.6', expected: true, desc: 'Valid progression 0.5 → 0.6' },
        { current: 1.0, last: 0.6, currentStr: '1.0', expected: true, desc: 'Valid over completion 0.6 → 1.0' },
        { current: 1.1, last: 1.0, currentStr: '1.1', expected: true, desc: 'Valid progression 1.0 → 1.1' },
        // New flexible tests for missing data handling
        { current: 1.0, last: 0.3, currentStr: '1.0', expected: true, desc: 'Over jump with missing data 0.3 → 1.0' },
        { current: 0.5, last: 0.2, currentStr: '0.5', expected: true, desc: 'Forward progression with missing balls 0.2 → 0.5' },
        { current: 2.0, last: 1.2, currentStr: '2.0', expected: true, desc: 'Over jump across multiple overs 1.2 → 2.0' },
        { current: 1.0, last: 0.5, currentStr: '1.0', expected: true, desc: 'Over completion from incomplete over 0.5 → 1.0' },
        // Still reject actual regressions
        { current: 0.1, last: 0.3, currentStr: '0.1', expected: false, desc: 'Invalid regression 0.3 → 0.1' },
        { current: 0.2, last: 0.5, currentStr: '0.2', expected: false, desc: 'Invalid regression 0.5 → 0.2' },
        { current: 0.5, last: 0.6, currentStr: '0.5', expected: false, desc: 'Invalid regression 0.6 → 0.5' },
        { current: 1.5, last: 1.6, currentStr: '1.5', expected: false, desc: 'Invalid regression 1.6 → 1.5' },
    ];
    
    var passed = 0;
    var failed = 0;
    
    for (var i = 0; i < testCases.length; i++) {
        var test = testCases[i];
        var result = validateBallByBallProgression(test.current, test.last, test.currentStr);
        var status = result.valid === test.expected ? 'PASS' : 'FAIL';
        
        if (result.valid === test.expected) {
            passed++;
        } else {
            failed++;
        }
        
        console.log(status + ': ' + test.desc + ' (Expected: ' + test.expected + ', Got: ' + result.valid + ')');
        if (!result.valid) {
            console.log('  Reason: ' + result.reason);
        }
    }
    
    console.log('=== Test Results: ' + passed + ' passed, ' + failed + ' failed ===');
}

async function refreshLiveMatches() {
    try {
        var response = await fetch('/api/live-matches', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });

        if (!response.ok) {
            return;
        }

        var data = await response.json();
        var matches = data.matches || [];

        matches.forEach(function (match) {
            updateLiveMatchCard({ info: match.matchInfo, scorecard: match.matchScore });
        });
    } catch (err) {
        console.error('Live match refresh failed:', err);
    }
}

function updateLiveMatchCard(data) {
    var info = data.info || {};
    var scorecard = data.scorecard || {};
    var matchId = info.matchId || '';
    
    if (!matchId) {
        return;
    }

    var card = document.querySelector('.match-item[data-match-id="' + matchId + '"]');
    if (!card) {
        return;
    }

    // Update status
    var statusEl = card.querySelector('.status-else, .status-complete');
    if (statusEl && info.status) {
        statusEl.textContent = info.status;
    }

    // Update team scores
    var team1Score = scorecard.team1Score || {};
    var team2Score = scorecard.team2Score || {};
    
    updateTeamScore(card, team1Score, 0);
    updateTeamScore(card, team2Score, 1);
}

// Store last known overs for index page validation
var indexPageLastKnownOvers = {};

function updateTeamScore(card, teamScore, teamIndex) {
    if (!teamScore) {
        return;
    }

    var inngs = teamScore.inngs1 || teamScore.inngs2 || teamScore;
    if (!inngs) {
        return;
    }

    var scoreSpans = card.querySelectorAll('.score-span');
    if (!scoreSpans[teamIndex]) {
        return;
    }

    var matchId = card.getAttribute('data-match-id');
    var teamKey = teamIndex === 0 ? 'team1' : 'team2';
    var inningsKey = teamScore.inngs1 ? 'inngs1' : (teamScore.inngs2 ? 'inngs2' : 'inngs1');
    var overKey = matchId + '_' + teamKey + '_' + inningsKey;
    
    var currentOverStr = inngs.overs ?? '-';
    var currentOver = parseOverValue(currentOverStr);
    var lastOver = indexPageLastKnownOvers[overKey] || 0;
    
    // Validate over progression
    var progressionValid = validateBallByBallProgression(currentOver, lastOver, currentOverStr);
    
    if (progressionValid.valid) {
        var runs = inngs.runs ?? '-';
        var wickets = inngs.wickets ?? '0';
        var overs = currentOverStr;
        
        scoreSpans[teamIndex].textContent = runs + '/' + wickets + ' (' + overs + ' ovs)';
        
        // Update last known over
        if (currentOver >= lastOver) {
            indexPageLastKnownOvers[overKey] = currentOver;
        }
        
        console.log('Updated score for match ' + matchId + ': ' + teamKey + ' ' + overs);
    } else {
        console.log('Skipping invalid over update for match ' + matchId + ': ' + progressionValid.reason);
    }
}

function parseOverValue(overString) {
    if (!overString || overString === '--' || overString === '-') {
        return 0.0;
    }

    var parts = overString.toString().split('.');
    var overs = parts.length > 0 ? parseInt(parts[0]) : 0;
    var balls = parts.length > 1 ? parseInt(parts[1]) : 0;

    // Handle over-end conversion: N.6 → (N+1).0
    if (balls == 6) {
        overs += 1;
        balls = 0;
    }

    var decimal = overs + (balls / 6);

    return decimal;
}

function validateBallByBallProgression(currentOver, lastOver, currentOverStr) {
    // If no previous data, accept current value
    if (lastOver === 0) {
        return { valid: true, reason: 'Initial value' };
    }

    // Check for regression (decrease in over value) - this is the only strict validation
    if (currentOver < lastOver - 0.01) {
        return { 
            valid: false, 
            reason: 'Over regression: ' + formatOverDisplay(lastOver) + ' → ' + currentOverStr 
        };
    }

    // Allow any forward progression - handles missing intermediate values
    // This ensures we don't miss updates like 0.6 → 1.0 if 0.6 wasn't displayed
    if (currentOver > lastOver) {
        var currentOvers = Math.floor(currentOver);
        var currentBalls = Math.round((currentOver - currentOvers) * 6) / 6;
        var lastOvers = Math.floor(lastOver);
        var lastBalls = Math.round((lastOver - lastOvers) * 6) / 6;

        // Handle over completion scenarios
        if (currentOvers > lastOvers) {
            // Proper over completion (e.g., 0.6 → 1.0) or jump with missing data
            if (currentBalls === 0.0) {
                return { valid: true, reason: 'Valid over completion or jump' };
            }
            // Jump within same over (e.g., 0.2 → 0.5 due to missing data)
            if (currentOvers === lastOvers) {
                return { valid: true, reason: 'Forward progression with missing balls' };
            }
            // Jump to different over (e.g., 0.3 → 1.2 due to missing data)
            return { valid: true, reason: 'Over jump with missing data' };
        }

        // Normal ball progression within same over
        if (currentOvers === lastOvers && currentBalls > lastBalls) {
            return { valid: true, reason: 'Valid ball progression' };
        }
    }

    // Same over value - no change
    if (Math.abs(currentOver - lastOver) < 0.01) {
        return { valid: true, reason: 'Same over value' };
    }

    return { valid: true, reason: 'Accepted forward progression' };
}

function formatOverDisplay(decimalOver) {
    var overs = Math.floor(decimalOver);
    var balls = Math.round((decimalOver - overs) * 6) / 6;

    // Handle over-end conversion: N.6 → (N+1).0
    if (balls == 0.6) {
        overs += 1;
        balls = 0;
    }

    // Display format: if balls is 0, show as whole number (e.g., "3" instead of "3.0")
    if (balls == 0) {
        return overs.toString();
    } else {
        return overs + '.' + balls;
    }
}


//   JavaScript Team Switcher Script 
 

    
