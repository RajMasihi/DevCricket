
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

  $('.second-header .nav-item .link').on('click', function (e) {
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

})
