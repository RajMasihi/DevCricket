// function LiveData() {
//     $.get('/live', function(data, status) {
//         $("#live-data-match").empty(); // Clear old matches
//         $.each(data, function(index, match) {
//             if (match.event_status === 'In Progress' || match.event_status === 'Stumps') {
//                 let matchType = '';
//                 if (match.event_type === 'ODI') matchType = '<span>ODI</span>';
//                 else if (match.event_type === 'T20') matchType = '<span class="t20-series">T20</span>';
//                 else if (match.event_type.toUpperCase() === 'TEST') matchType = '<span class="test-series">Test</span>';

//                 let statusColor = (match.event_status === 'Stumps') ? '#de280c' : '#045aa5';
//                 let liveTag = (match.event_status === 'In Progress') ? 
//                     "<span class='live col-1'>Live <div class='animation'></div></span>" : "";

//                 $("#live-data-match").append(`
//                     <a href="#score/${match.event_key}" style="text-decoration:none; color:#141010;">
//                         <div class="col">
//                             <div class="card h-100" style="box-shadow:2px 2px 6px 1px #053259;">
//                                 <div class="row card-body">
//                                     <p class="card-text col-8" style="color:#817373">
//                                         ${match.league_round}. ${match.league_name}
//                                     </p>
//                                     <p class="match-formate col-3">${matchType}</p>
//                                     ${liveTag}
//                                     <div class="col-6">
//                                         <h5 class="card-title">
//                                             <img class="img" src="${match.event_home_team_logo}" alt="">
//                                             &nbsp;&nbsp;&nbsp; ${match.event_home_team}
//                                         </h5>
//                                     </div>
//                                     <div class="col-6 text-center">${match.event_home_final_result}</div>
//                                     <div class="col-6">
//                                         <h5 class="card-title">
//                                             <img class="img" src="${match.event_away_team_logo}" alt="">
//                                             &nbsp;&nbsp;&nbsp; ${match.event_away_team}
//                                         </h5>
//                                     </div>
//                                     <div class="col-6 text-center">${match.event_away_final_result}</div>
//                                     <p class="card-text" style="color:${statusColor}">
//                                         ${match.event_status_info}
//                                     </p>
//                                 </div>
//                             </div>
//                         </div>
//                     </a>
//                 `);
//             }
//         });
//         console.log("Status: " + status);
//     });
// }

// // Call it automatically every 10 seconds
// setInterval(LiveData, 3000);
// LiveData();

//<div class="row row-cols-1 row-cols-md-2 g-4">
                // if (match.event_type === 'ODI') matchType = '<span>ODI</span>';
                // else if (match.event_type === 'T20') matchType = '<span class="t20-series">T20</span>';
                // else if (match.event_type.toUpperCase() === 'TEST') matchType = '<span class="test-series">Test</span>';

                // let statusColor = (match.event_status === 'Stumps') ? '#de280c' : '#045aa5';
                // let liveTag = (match.event_status === 'In Progress') ? 
                //     "<span class='live col-1'>Live <div class='animation'></div></span>" : "";
                //     <a href="#score/${match.event_key}" style="text-decoration:none; color:#141010;">
                //         <div class="col">
                //             <div class="card h-100" style="box-shadow:2px 2px 6px 1px #053259;">
                //                 <div class="row card-body">
                //                     <p class="card-text col-8" style="color:#817373">
                //                         ${match.league_round}. ${match.league_name}
                //                     </p>
                //                     <p class="match-formate col-3">${matchType}</p>
                //                     ${liveTag}
                //                     <div class="col-6">
                //                         <h5 class="card-title">
                //                             <img class="img" src="${match.event_home_team_logo}" alt="">
                //                             &nbsp;&nbsp;&nbsp; ${match.event_home_team}
                //                         </h5>
                //                     </div>
                //                     <div class="col-6 text-center">${match.event_home_final_result}</div>
                //                     <div class="col-6">
                //                         <h5 class="card-title">
                //                             <img class="img" src="${match.event_away_team_logo}" alt="">
                //                             &nbsp;&nbsp;&nbsp; ${match.event_away_team}
                //                         </h5>
                //                     </div>
                //                     <div class="col-6 text-center">${match.event_away_final_result}</div>
                //                     <p class="card-text" style="color:${statusColor}">
                //                         ${match.event_status_info}
                //                     </p>
                //                 </div>
                //             </div>
                //         </div>
                //     </a>
                // </div>