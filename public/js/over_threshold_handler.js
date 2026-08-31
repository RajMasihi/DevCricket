/**
 * Over Threshold Handler - WebSocket Version with Fallback
 * Handles dynamic UI updates based on over thresholds (6.2, 6.3, 6.4)
 * Uses Laravel Reverb with WebSocket for real-time updates with polling fallback
 * Implements over progression validation to prevent regression (6.0 → 5.4)
 */

class OverThresholdHandler {
    constructor() {
        this.matchId = null;
        this.activeTab = null;
        this.channel = null;
        this.echo = null;
        this.currentThreshold = null;
        this.lastKnownOvers = {}; // Track last known overs for each team/innings
        this.lastUpdateTime = null;
        this.connectionAttempts = 0;
        this.maxConnectionAttempts = 3;
        this.fallbackToPolling = false;
        this.pollingInterval = null;
    }

    async init(matchId, activeTab = 'informe') {
        this.matchId = matchId;
        this.activeTab = activeTab;
        
        if (!this.matchId) {
            console.warn('No match ID provided for over threshold handler');
            return;
        }

        // Try WebSocket connection first
        const webSocketSuccess = await this.initializeEcho();
        
        if (webSocketSuccess) {
            // Subscribe to match-specific channel
            this.subscribeToMatchChannel();
        } else {
            console.warn('WebSocket connection failed, falling back to polling');
            this.fallbackToPolling = true;
            this.startPolling();
        }
        
        // Get initial threshold data
        await this.fetchInitialThreshold();
    }

    async initializeEcho() {
        // Load Laravel Echo dynamically if not available
        if (typeof Echo === 'undefined') {
            const scriptsLoaded = await this.loadEchoScripts();
            if (!scriptsLoaded) {
                return false;
            }
        }

        // Get Reverb configuration from meta tags
        const reverbKey = document.querySelector('meta[name="reverb-app-key"]')?.content;
        const reverbHost = document.querySelector('meta[name="reverb-host"]')?.content;
        const reverbPort = document.querySelector('meta[name="reverb-port"]')?.content;
        const reverbScheme = document.querySelector('meta[name="reverb-scheme"]')?.content;

        if (!reverbKey) {
            console.warn('Reverb app key not found in meta tags');
            return false;
        }

        try {
            // Initialize Echo with Reverb configuration
            this.echo = new Echo({
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

            console.log('Laravel Echo initialized with Reverb');
            return true;
        } catch (error) {
            console.error('Failed to initialize Echo:', error);
            return false;
        }
    }

    async loadEchoScripts() {
        try {
            // Load Laravel Echo and Pusher JS (compatible with Reverb)
            const scripts = [
                'https://cdn.jsdelivr.net/npm/@pusher/pusher-js@8.4.0-rc.1/dist/pusher.min.js',
                'https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.min.js'
            ];

            for (const script of scripts) {
                await this.loadScript(script);
            }
            return true;
        } catch (error) {
            console.error('Failed to load Echo scripts:', error);
            return false;
        }
    }

    loadScript(src) {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = src;
            script.onload = resolve;
            script.onerror = () => reject(new Error(`Failed to load script: ${src}`));
            document.head.appendChild(script);
        });
    }

    subscribeToMatchChannel() {
        if (!this.echo) {
            console.error('Echo not initialized');
            return;
        }

        try {
            // Subscribe to match-specific channel
            this.channel = this.echo.channel(`match.${this.matchId}`);

            // Listen for match score updates
            this.channel.listen('.MatchScoreUpdated', (data) => {
                console.log('WebSocket update received:', data);
                this.handleMatchUpdate(data);
            });

            // Listen for connection events
            this.channel.subscribed(() => {
                console.log(`Successfully subscribed to match.${this.matchId} channel`);
                this.connectionAttempts = 0; // Reset connection attempts on success
            });

            this.channel.error((error) => {
                console.error('Channel error:', error);
                this.handleConnectionError();
            });

            console.log(`Attempting to subscribe to match.${this.matchId} channel`);
        } catch (error) {
            console.error('Error subscribing to channel:', error);
            this.handleConnectionError();
        }
    }

    handleConnectionError() {
        this.connectionAttempts++;
        
        if (this.connectionAttempts >= this.maxConnectionAttempts) {
            console.warn('Max connection attempts reached, falling back to polling');
            this.fallbackToPolling = true;
            this.startPolling();
        }
    }

    startPolling() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
        }

        console.log('Starting polling fallback for match updates');
        this.pollingInterval = setInterval(() => {
            this.fetchMatchUpdates();
        }, 20000); // Poll every 20 seconds

        // Initial fetch
        this.fetchMatchUpdates();
    }

    async fetchMatchUpdates() {
        try {
            const response = await fetch(`/api/match/${this.matchId}/info`, {
                headers: { 
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.handleMatchUpdate({
                    info: data.info,
                    scorecard: {},
                    commentary: {},
                    threshold: data.over_threshold
                });
            }
        } catch (error) {
            console.error('Error fetching match updates:', error);
        }
    }

    async fetchInitialThreshold() {
        try {
            const response = await fetch(`/api/match/${this.matchId}/info`, {
                headers: { 
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const data = await response.json();
                const threshold = data.over_threshold || {};
                this.currentThreshold = threshold;
                
                // Initialize last known overs from initial data
                this.initializeLastKnownOvers(data.info);
                
                this.updateUIBasedOnThreshold(threshold);
            }
        } catch (error) {
            console.error('Error fetching initial threshold:', error);
        }
    }

    initializeLastKnownOvers(info) {
        if (!info || !info.matchScore) return;

        Object.keys(info.matchScore).forEach(teamKey => {
            const teamScore = info.matchScore[teamKey];
            
            ['inngs1', 'inngs2'].forEach(innings => {
                if (teamScore[innings] && teamScore[innings].overs) {
                    const key = `${teamKey}_${innings}`;
                    this.lastKnownOvers[key] = this.parseOverValue(teamScore[innings].overs);
                }
            });
        });
    }

    handleMatchUpdate(data) {
        console.log('Processing match update:', data);
        
        // Validate over progression before processing
        if (!this.validateOverProgression(data)) {
            console.warn('Over progression validation failed - skipping update');
            return;
        }
        
        // Calculate threshold from incoming data
        const threshold = this.calculateThresholdFromData(data);
        this.currentThreshold = threshold;
        
        // Update last known overs
        this.updateLastKnownOvers(data);
        
        // Update UI based on new threshold
        this.updateUIBasedOnThreshold(threshold);
        
        // Update match-specific data based on active tab
        this.updateMatchData(data);
        
        this.lastUpdateTime = new Date().toISOString();
    }

    validateOverProgression(data) {
        if (!data || !data.info || !data.info.matchScore) {
            return false;
        }

        let isValid = true;
        let invalidReasons = [];

        Object.keys(data.info.matchScore).forEach(teamKey => {
            const teamScore = data.info.matchScore[teamKey];
            
            ['inngs1', 'inngs2'].forEach(innings => {
                if (teamScore[innings] && teamScore[innings].overs) {
                    const key = `${teamKey}_${innings}`;
                    const currentOverStr = teamScore[innings].overs;
                    const currentOver = this.parseOverValue(currentOverStr);
                    const lastOver = this.lastKnownOvers[key] || 0;
                    
                    // Validate ball-by-ball progression
                    const progressionValid = this.validateBallByBallProgression(currentOver, lastOver, currentOverStr);
                    
                    if (!progressionValid.valid) {
                        isValid = false;
                        invalidReasons.push(`${key}: ${progressionValid.reason}`);
                    }
                }
            });
        });

        if (!isValid) {
            console.warn('Invalid over progression:', invalidReasons);
        }

        return isValid;
    }

    validateBallByBallProgression(currentOver, lastOver, currentOverStr) {
        // If no previous data, accept current value
        if (lastOver === 0) {
            return { valid: true, reason: 'Initial value' };
        }

        // Check for regression (decrease in over value) - this is the only strict validation
        if (currentOver < lastOver - 0.01) { // Small tolerance for floating point errors
            return { 
                valid: false, 
                reason: `Over regression: ${this.formatOverDisplay(lastOver)} → ${currentOverStr}` 
            };
        }

        // Allow any forward progression - handles missing intermediate values
        // This ensures we don't miss updates like 0.6 → 1.0 if 0.6 wasn't displayed
        if (currentOver > lastOver) {
            const currentOvers = Math.floor(currentOver);
            const currentBalls = Math.round((currentOver - currentOvers) * 6) / 6;
            const lastOvers = Math.floor(lastOver);
            const lastBalls = Math.round((lastOver - lastOvers) * 6) / 6;

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

    formatOverDisplay(decimalOver) {
        const overs = Math.floor(decimalOver);
        const balls = Math.round((decimalOver - overs) * 6) / 6;
        return `${overs}.${balls}`;
    }

    updateLastKnownOvers(data) {
        if (!data || !data.info || !data.info.matchScore) return;

        Object.keys(data.info.matchScore).forEach(teamKey => {
            const teamScore = data.info.matchScore[teamKey];
            
            ['inngs1', 'inngs2'].forEach(innings => {
                if (teamScore[innings] && teamScore[innings].overs) {
                    const key = `${teamKey}_${innings}`;
                    const currentOverStr = teamScore[innings].overs;
                    const currentOver = this.parseOverValue(currentOverStr);
                    const lastOver = this.lastKnownOvers[key] || 0;
                    
                    // Validate progression before updating
                    const progressionValid = this.validateBallByBallProgression(currentOver, lastOver, currentOverStr);
                    
                    // Only update if progression is valid and current over is greater than or equal to last known
                    if (progressionValid.valid && currentOver >= lastOver) {
                        this.lastKnownOvers[key] = currentOver;
                        console.log(`Updated ${key}: ${this.formatOverDisplay(lastOver)} → ${currentOverStr}`);
                    } else if (!progressionValid.valid) {
                        console.warn(`Skipping invalid over update for ${key}: ${progressionValid.reason}`);
                    }
                }
            });
        });
    }

    calculateThresholdFromData(data) {
        let maxOvers = 0.0;
        let thresholdLevel = 'normal';

        // Extract overs from match data
        if (data.scorecard && data.scorecard.scorecard) {
            data.scorecard.scorecard.forEach(scorecard => {
                const overs = this.parseOverValue(scorecard.overs || '0');
                maxOvers = Math.max(maxOvers, overs);
            });
        }

        if (data.info && data.info.matchScore) {
            Object.values(data.info.matchScore).forEach(teamScore => {
                if (teamScore.inngs1) {
                    const overs = this.parseOverValue(teamScore.inngs1.overs || '0');
                    maxOvers = Math.max(maxOvers, overs);
                }
                if (teamScore.inngs2) {
                    const overs = this.parseOverValue(teamScore.inngs2.overs || '0');
                    maxOvers = Math.max(maxOvers, overs);
                }
            });
        }

        // Use threshold from data if available
        if (data.threshold) {
            return data.threshold;
        }

        // Calculate threshold level based on decimal values
        if (maxOvers >= 6.67) {
            thresholdLevel = 'critical'; // 6.4+ overs
        } else if (maxOvers >= 6.5) {
            thresholdLevel = 'high';     // 6.3+ overs
        } else if (maxOvers >= 6.33) {
            thresholdLevel = 'medium';   // 6.2+ overs
        }
        
        return {
            'max_overs': maxOvers,
            'threshold_level': thresholdLevel,
            'show_ball_by_ball': maxOvers >= 6.33,
            'real_time_update': maxOvers >= 6.67,
            'increased_refresh': maxOvers >= 6.5,
        };
    }

    parseOverValue(overString) {
        if (!overString || overString === '--' || overString === '') {
            return 0.0;
        }

        const parts = overString.toString().split('.');
        const overs = parts.length > 0 ? parseInt(parts[0]) : 0;
        const balls = parts.length > 1 ? parseInt(parts[1]) : 0;
        const decimal = overs + (balls / 6);

        return decimal;
    }

    updateUIBasedOnThreshold(threshold) {
        const matchContainer = document.getElementById('cricket-matchdetail-page');
        if (!matchContainer) return;

        // Remove existing threshold classes
        matchContainer.classList.remove('threshold-medium', 'threshold-high', 'threshold-critical');

        // Add appropriate threshold class
        if (threshold.threshold_level === 'critical') {
            matchContainer.classList.add('threshold-critical');
            this.enableRealTimeUpdates();
        } else if (threshold.threshold_level === 'high') {
            matchContainer.classList.add('threshold-high');
        } else if (threshold.threshold_level === 'medium') {
            matchContainer.classList.add('threshold-medium');
            this.enableBallByBallDisplay();
        }

        // Update over displays with breakdown if needed
        if (threshold.show_ball_by_ball) {
            this.updateOverDisplays();
        }
    }

    enableRealTimeUpdates() {
        console.log('Enabling real-time updates for critical over threshold');
        
        const statusIndicator = document.getElementById('real_time_indicator');
        if (statusIndicator) {
            statusIndicator.style.display = 'block';
            statusIndicator.classList.add('active');
            statusIndicator.innerHTML = this.fallbackToPolling ? 
                '<i class="fas fa-sync"></i> Live Updates (Polling)' : 
                '<i class="fas fa-bolt"></i> Live Updates (WebSocket)';
        }
    }

    enableBallByBallDisplay() {
        console.log('Enabling ball-by-ball display for medium over threshold');
        
        const overElements = document.querySelectorAll('.score-line-overs');
        overElements.forEach(element => {
            const overText = element.textContent.replace(/[()]/g, '').trim();
            if (overText && overText !== '--') {
                const breakdown = this.createOverBreakdown(overText);
                if (breakdown) {
                    element.innerHTML = `(${overText}) <span class="ball-breakdown">${breakdown}</span>`;
                }
            }
        });
    }

    createOverBreakdown(overString) {
        const parts = overString.split('.');
        if (parts.length !== 2) return null;

        const overs = parseInt(parts[0]);
        const balls = parseInt(parts[1]);

        let breakdown = '';
        for (let i = 1; i <= overs; i++) {
            breakdown += `<span class="over-complete">●</span>`;
        }
        
        for (let i = 1; i <= balls; i++) {
            breakdown += `<span class="over-partial">○</span>`;
        }

        return breakdown;
    }

    updateOverDisplays() {
        const scoreElements = document.querySelectorAll('.score-span');
        scoreElements.forEach(element => {
            const overMatch = element.textContent.match(/\(([\d.]+)\s*ovs?\)/);
            if (overMatch) {
                const overValue = overMatch[1];
                const overData = this.parseOverValue(overValue);
                
                if (overData >= 6.33) {
                    element.classList.add('over-threshold-active');
                }
            }
        });
    }

    updateMatchData(data) {
        // Update match status
        const statusElement = document.getElementById('match_status_text');
        if (statusElement && data.info && data.info.status) {
            statusElement.textContent = data.info.status;
        }

        const infoStatusElement = document.getElementById('informe_match_status');
        if (infoStatusElement && data.info && data.info.status) {
            infoStatusElement.textContent = data.info.status;
        }

        // Update scorecard if in scoreboard tab
        if (this.activeTab === 'scoreboard' && data.scorecard) {
            this.updateScorecardData(data.scorecard);
        }

        // Update commentary if available
        if (data.commentary) {
            this.updateCommentary(data.commentary);
        }

        // Update team scores with validated over values
        this.updateTeamScoresWithValidation(data);
    }

    updateTeamScoresWithValidation(data) {
        if (!data || !data.info || !data.info.matchScore) return;

        Object.keys(data.info.matchScore).forEach(teamKey => {
            const teamScore = data.info.matchScore[teamKey];
            
            ['inngs1', 'inngs2'].forEach(innings => {
                if (teamScore[innings] && teamScore[innings].overs) {
                    const key = `${teamKey}_${innings}`;
                    const currentOverStr = teamScore[innings].overs;
                    const currentOver = this.parseOverValue(currentOverStr);
                    const lastOver = this.lastKnownOvers[key] || 0;
                    
                    // Only update UI if we have a valid over progression
                    const progressionValid = this.validateBallByBallProgression(currentOver, lastOver, currentOverStr);
                    
                    if (progressionValid.valid) {
                        this.updateTeamScoreUI(teamKey, innings, teamScore[innings]);
                    } else {
                        console.log(`Skipping UI update for ${key}: ${progressionValid.reason}`);
                    }
                }
            });
        });
    }

    updateTeamScoreUI(teamKey, innings, inningsData) {
        // Find the score elements for this team/innings and update them
        // This would need to be implemented based on the actual DOM structure
        const scoreElements = document.querySelectorAll(`[data-team="${teamKey}"][data-innings="${innings}"] .score-span`);
        scoreElements.forEach(element => {
            const runs = inningsData.runs ?? '-';
            const wickets = inningsData.wickets ?? '0';
            const overs = inningsData.overs ?? '-';
            element.textContent = `${runs}/${wickets} (${overs} ovs)`;
        });
    }

    updateScorecardData(scorecardData) {
        // This would update the scoreboard UI with new data
        // For now, just update the overs display
        if (scorecardData.scorecard) {
            scorecardData.scorecard.forEach((sc, index) => {
                const totalOvers = sc.overs || '';
                const oversElement = document.querySelector(`#sc_team_card_${index} .score-line-overs`);
                if (oversElement && totalOvers) {
                    const overData = this.parseOverValue(totalOvers);
                    if (overData >= 6.33) {
                        const breakdown = this.createOverBreakdown(totalOvers);
                        oversElement.innerHTML = `(${totalOvers}) <span class="ball-breakdown">${breakdown}</span>`;
                    }
                }
            });
        }
    }

    updateCommentary(commentaryData) {
        const commentaryList = document.getElementById('commentary_list');
        if (!commentaryList) return;

        const commList = commentaryData.commentaryList || commentaryData.commentary || [];
        if (!Array.isArray(commList) || commList.length === 0) return;

        let html = '';
        commList.slice(0, 15).forEach(function (comm) {
            const over = comm.over || '';
            const text = comm.commText || comm.text || '';
            html += '<div class="border-bottom py-1"><span class="text-muted">' + over + '</span> ' + text + '</div>';
        });

        commentaryList.innerHTML = html;
    }

    destroy() {
        // Stop polling if active
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
            this.pollingInterval = null;
        }

        // Unsubscribe from channel
        if (this.channel) {
            this.echo.leaveChannel(`match.${this.matchId}`);
            this.channel = null;
        }

        // Disconnect Echo
        if (this.echo) {
            this.echo.disconnect();
            this.echo = null;
        }

        console.log('OverThresholdHandler destroyed');
    }

    testOverValidation() {
        console.log('=== Testing Over Validation Logic (OverThresholdHandler) ===');
        
        const testCases = [
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
        
        let passed = 0;
        let failed = 0;
        
        for (let i = 0; i < testCases.length; i++) {
            const test = testCases[i];
            const result = this.validateBallByBallProgression(test.current, test.last, test.currentStr);
            const status = result.valid === test.expected ? 'PASS' : 'FAIL';
            
            if (result.valid === test.expected) {
                passed++;
            } else {
                failed++;
            }
            
            console.log(`${status}: ${test.desc} (Expected: ${test.expected}, Got: ${result.valid})`);
            if (!result.valid) {
                console.log(`  Reason: ${result.reason}`);
            }
        }
        
        console.log(`=== Test Results: ${passed} passed, ${failed} failed ===`);
    }
}

// Global instance
window.overThresholdHandler = new OverThresholdHandler();

// Auto-initialize if match data is available
document.addEventListener('DOMContentLoaded', function() {
    const matchPage = document.getElementById('cricket-matchdetail-page');
    if (matchPage) {
        const matchId = matchPage.getAttribute('data-match-id');
        const activeTab = matchPage.getAttribute('data-active-tab') || 'informe';
        const matchState = matchPage.getAttribute('data-match-state') || '';
        
        console.log('Over threshold handler auto-init - Match ID:', matchId, 'Tab:', activeTab, 'State:', matchState);
        
        // Only initialize for live matches to avoid unnecessary connections
        if (matchId && matchState === 'in progress') {
            window.overThresholdHandler.init(matchId, activeTab);
        }
        
        // Test over validation logic
        if (window.location.search.includes('test=true')) {
            window.overThresholdHandler.testOverValidation();
        }
    }
});