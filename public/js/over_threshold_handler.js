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
        }, 10000); // Poll every 10 seconds

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
                    const currentOver = this.parseOverValue(teamScore[innings].overs);
                    const lastOver = this.lastKnownOvers[key] || 0;
                    
                    // Allow updates if current over is greater than or equal to last known over
                    // This prevents regression (e.g., 6.0 → 5.4)
                    if (currentOver < lastOver - 0.1) { // Small tolerance for floating point errors
                        isValid = false;
                        invalidReasons.push(`${key}: ${currentOver} < ${lastOver}`);
                    }
                }
            });
        });

        if (!isValid) {
            console.warn('Invalid over progression:', invalidReasons);
        }

        return isValid;
    }

    updateLastKnownOvers(data) {
        if (!data || !data.info || !data.info.matchScore) return;

        Object.keys(data.info.matchScore).forEach(teamKey => {
            const teamScore = data.info.matchScore[teamKey];
            
            ['inngs1', 'inngs2'].forEach(innings => {
                if (teamScore[innings] && teamScore[innings].overs) {
                    const key = `${teamKey}_${innings}`;
                    const currentOver = this.parseOverValue(teamScore[innings].overs);
                    const lastOver = this.lastKnownOvers[key] || 0;
                    
                    // Only update if current over is greater than last known over
                    if (currentOver > lastOver) {
                        this.lastKnownOvers[key] = currentOver;
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
            'increased_refresh' => maxOvers >= 6.5
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
}

// Global instance
window.overThresholdHandler = new OverThresholdHandler();

// Auto-initialize if match data is available
document.addEventListener('DOMContentLoaded', () => {
    const matchPage = document.getElementById('cricket-matchdetail-page');
    if (matchPage) {
        const matchId = matchPage.getAttribute('data-match-id');
        const activeTab = matchPage.getAttribute('data-active-tab') || 'informe';
        
        if (matchId) {
            window.overThresholdHandler.init(matchId, activeTab);
        }
    }
});