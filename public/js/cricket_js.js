
$(function () {
// navebar active class 
    let currentUrl = window.location.href;
    $('.first-header .nav-item a').each(function () {
        if (this.href === currentUrl) {
            $('.first-header .nav-item a').removeClass('active');
            $(this).addClass('active');
        }
    });
//    filter in t20, ODi, test, 

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


//  Searching nav working.........

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

    initIndexLiveMatchesRefresh();
    initMatchdetailScoreboardRefresh();
});

function initIndexLiveMatchesRefresh() {
    var root = document.getElementById('cricket-index-page');
    if (!root) {
        return;
    }
    var activeTab = (root.getAttribute('data-active-tab') || 'live').toLowerCase();
    if (activeTab !== 'live') {
        return;
    }

    var liveContainerId = 'live_matches_container';
    var liveRefreshTimer = null;
    var isLiveRefreshRunning = false;

    async function refreshLiveMatches() {
        if (isLiveRefreshRunning) {
            return;
        }

        var currentContainer = document.getElementById(liveContainerId);
        if (!currentContainer) {
            return;
        }

        isLiveRefreshRunning = true;
        try {
            var url = new URL(window.location.href);
            url.searchParams.set('tab', 'live');
            url.searchParams.set('_ts', Date.now());

            var response = await fetch(url.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!response.ok) {
                return;
            }

            var html = await response.text();
            var doc = new DOMParser().parseFromString(html, 'text/html');
            var updatedContainer = doc.getElementById(liveContainerId);
            if (updatedContainer) {
                currentContainer.innerHTML = updatedContainer.innerHTML;
            }
        } catch (err) {
            console.error('Live match refresh failed:', err);
        } finally {
            isLiveRefreshRunning = false;
        }
    }

    liveRefreshTimer = window.setInterval(refreshLiveMatches, 20000);
    window.addEventListener('beforeunload', function () {
        if (liveRefreshTimer) {
            window.clearInterval(liveRefreshTimer);
        }
    });
}

function initMatchdetailScoreboardRefresh() {
    var root = document.getElementById('cricket-matchdetail-page');
    if (!root) {
        return;
    }
    var activeTab = (root.getAttribute('data-active-tab') || 'informe').toLowerCase();
    var matchState = (root.getAttribute('data-match-state') || '').toLowerCase();
    if (activeTab !== 'scoreboard' || matchState !== 'in progress') {
        return;
    }

    var sectionId = 'scoreboard_live_container';
    var refreshTimer = null;
    var isRefreshing = false;

    async function refreshScoreboardSection() {
        if (isRefreshing) {
            return;
        }

        var currentSection = document.getElementById(sectionId);
        if (!currentSection) {
            return;
        }

        isRefreshing = true;
        try {
            var url = new URL(window.location.href);
            url.searchParams.set('tab', 'scoreboard');
            url.searchParams.set('_ts', Date.now());

            var response = await fetch(url.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!response.ok) {
                return;
            }

            var html = await response.text();
            var doc = new DOMParser().parseFromString(html, 'text/html');
            var updatedSection = doc.getElementById(sectionId);
            if (updatedSection) {
                currentSection.innerHTML = updatedSection.innerHTML;
            }
        } catch (err) {
            console.error('Live scoreboard refresh failed:', err);
        } finally {
            isRefreshing = false;
        }
    }

    refreshTimer = window.setInterval(refreshScoreboardSection, 20000);
    window.addEventListener('beforeunload', function () {
        if (refreshTimer) {
            window.clearInterval(refreshTimer);
        }
    });
}


