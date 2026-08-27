

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

//     $(document).on('click', '.second-header .nav-link', function (e) {
//         e.preventDefault();

//         $('.second-header .nav-link')
//             .removeClass('active')
//             .css({
//                 'background-color': '',
//                 'color': ''
//             });

//         $(this)
//             .addClass('active')
//             .css({
//                 'background-color': '#053259',
//                 'color': '#fff'
//             });
//     });

//     $('.second-header .nav-item .link').on('click', function (e) {
//         if ($(this).hasClass('point-table-nav') || $(this).attr('id') === 'point-table') {
//             return;
//         }

//         e.preventDefault();

//         let te = $(this).text().trim().toLowerCase();
//         $('#search').val(te);

//         if (te === 'all' || te === 'mens') {
//             $('.match-item').show();
//             return;
//         }

//         $('.match-item').each(function () {
//             let text = $(this).text().toLowerCase();

//             if (text.indexOf(te) > -1) {
//                 $(this).show();
//             } else {
//                 $(this).hide();
//             }
//         });
//     });

//     $('#team1').click(function () {
//         $('#scorecard1').show();
//     });

//     $('#team2').on('click', function () {
//         $('#scorecard1').hide();
//         $('#scorecard2').removeClass('d-block').show();
//     });

//     initIndexLiveMatchesRefresh();
//     initMatchdetailLiveRefresh();
// });

// function initMatchdetailLiveRefresh() {
//     var root = document.getElementById('cricket-matchdetail-page');
//     if (!root) {
//         return;
//     }

//     var activeTab = (root.getAttribute('data-active-tab') || 'informe').toLowerCase();
//     var matchState = (root.getAttribute('data-match-state') || '').toLowerCase();
//     var matchId = root.getAttribute('data-match-id') || '';

//     if (!matchId || matchState !== 'in progress') {
//         return;
//     }

//     var refreshTimer = null;
//     var isRefreshing = false;
//     var intervalMs = activeTab === 'scoreboard' ? 8000 : 15000;

//     async function refreshMatchDetail() {
//         if (isRefreshing) {
//             return;
//         }

//         isRefreshing = true;
//         try {
//             if (activeTab === 'informe') {
//                 var infoResponse = await fetch('/api/match/' + matchId + '/info', {
//                     headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
//                 });

//                 if (infoResponse.ok) {
//                     var infoData = await infoResponse.json();
//                     var statusEl = document.getElementById('informe_match_status');
//                     if (statusEl && infoData.info && infoData.info.status) {
//                         statusEl.textContent = infoData.info.status;
//                     }
//                 }
//             }

//             if (activeTab === 'scoreboard') {
//                 var scoreboardResponse = await fetch('/api/match/' + matchId + '/scoreboard', {
//                     headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
//                 });

//                 if (scoreboardResponse.ok) {
//                     var scoreData = await scoreboardResponse.json();
//                     var statusText = document.getElementById('match_status_text');
//                     var status = scoreData.scorecard && scoreData.scorecard.status
//                         ? scoreData.scorecard.status
//                         : (scoreData.info && scoreData.info.status ? scoreData.info.status : '');

//                     if (statusText && status) {
//                         statusText.textContent = status;
//                     }
//                 }

//                 var commentaryResponse = await fetch('/api/match/' + matchId + '/commentary', {
//                     headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
//                 });

//                 if (commentaryResponse.ok) {
//                     var commData = await commentaryResponse.json();
//                     updateCommentaryList(commData.commentary || {});
//                 }
//             }
//         } catch (err) {
//             console.error('Live match detail refresh failed:', err);
//         } finally {
//             isRefreshing = false;
//         }
//     }

//     function updateCommentaryList(commentary) {
//         var listEl = document.getElementById('commentary_list');
//         if (!listEl) {
//             return;
//         }

//         var commList = commentary.commentaryList || commentary.commentary || [];
//         if (!Array.isArray(commList) || commList.length === 0) {
//             return;
//         }

//         var html = '';
//         commList.slice(0, 15).forEach(function (comm) {
//             var over = comm.over || '';
//             var text = comm.commText || comm.text || '';
//             html += '<div class="border-bottom py-1"><span class="text-muted">' + over + '</span> ' + text + '</div>';
//         });

//         listEl.innerHTML = html;
//     }

//     refreshTimer = window.setInterval(refreshMatchDetail, intervalMs);
//     window.addEventListener('beforeunload', function () {
//         if (refreshTimer) {
//             window.clearInterval(refreshTimer);
//         }
//     });
// }
// // live match 
// function initIndexLiveMatchesRefresh() {
//     var root = document.getElementById('cricket-index-page');
//     if (!root) {
//         return;
//     }

//     var activeTab = (root.getAttribute('data-active-tab') || 'live').toLowerCase();
//     if (activeTab !== 'live') {
//         return;
//     }

//     var refreshTimer = null;
//     var isRefreshing = false;

//     async function refreshLiveMatches() {
//         if (isRefreshing) {
//             return;
//         }

//         isRefreshing = true;
//         try {
//             var response = await fetch('/api/live-matches', {
//                 headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
//             });

//             if (!response.ok) {
//                 return;
//             }

//             var data = await response.json();
//             var matches = data.matches || [];

//             matches.forEach(function (match) {
//                 var mi = match.matchInfo || {};
//                 var score = match.matchScore || {};
//                 var matchId = mi.matchId || '';
//                 if (!matchId) {
//                     return;
//                 }

//                 var card = document.querySelector('.match-item[data-match-id="' + matchId + '"]');
//                 if (!card) {
//                     return;
//                 }

//                 var statusEl = card.querySelector('.status-else, .status-complete');
//                 if (statusEl && mi.status) {
//                     statusEl.textContent = mi.status;
//                 }

//                 updateTeamScore(card, score.team1Score, 0);
//                 updateTeamScore(card, score.team2Score, 1);
//             });
//         } catch (err) {
//             console.error('Live match refresh failed:', err);
//         } finally {
//             isRefreshing = false;
//         }
//     }

//     function updateTeamScore(card, teamScore, teamIndex) {
//         if (!teamScore) {
//             return;
//         }

//         var inngs = teamScore.inngs1 || teamScore.inngs2 || teamScore;
//         if (!inngs) {
//             return;
//         }

//         var scoreSpans = card.querySelectorAll('.score-span');
//         if (!scoreSpans[teamIndex]) {
//             return;
//         }

//         var runs = inngs.runs ?? '-';
//         var wickets = inngs.wickets ?? '0';
//         var overs = inngs.overs ?? '-';
//         scoreSpans[teamIndex].textContent = runs + '/' + wickets + ' (' + overs + ' ovs)';
//     }

//     refreshTimer = window.setInterval(refreshLiveMatches, 15000);
//     window.addEventListener('beforeunload', function () {
//         if (refreshTimer) {
//             window.clearInterval(refreshTimer);
//         }
//     });
// }


//   JavaScript Team Switcher Script 
 
        function showScorecardTeam(index, btnObj) {
            document.querySelectorAll('.sc-team-card-wrapper').forEach(function(card) {
                card.style.display = 'none';
            });

            var targetCard = document.getElementById('sc_team_card_' + index);
            if (targetCard) {
                targetCard.style.display = 'block';
            }

            document.querySelectorAll('.sc-team-btn').forEach(function(btn) {
                btn.classList.remove('active');
            });
            if (btnObj) {
                btnObj.classList.add('active');
            }
        }

        function showSquadTeam(teamKey, btnObj) {
            document.querySelectorAll('.squad-team-wrapper').forEach(function(card) {
                card.style.display = 'none';
            });

            var targetCard = document.getElementById('squad_' + teamKey);
            if (targetCard) {
                targetCard.style.display = 'block';
            }

            document.querySelectorAll('.squad-team-btn').forEach(function(btn) {
                btn.classList.remove('active');
            });
            if (btnObj) {
                btnObj.classList.add('active');
            }
        }
    
