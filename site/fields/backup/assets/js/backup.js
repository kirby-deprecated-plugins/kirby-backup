/* /////////////////////////////////////////////////////////////////
// EXTEND LOADER FUNCTION, FIRE BEFORE - AND AFTER
///////////////////////////////////////////////////////////////// */

NProgress.done = (function() {

var NProgressDone = NProgress.done;

  return function() {

    var result = NProgressDone.apply(this, arguments);

      setTimeout(function(){

        set_backup();

      }, 100);

    return result;

  };

}());

/* //////////////////////////////////////////////////////////////// */

$(document).ready(function() {

  set_backup();

});

function set_backup() {

console.log('[backup] fired');

  $(document).on('click','.clear-list',function() {

    $('.fileList').css('visibility','hidden');
    $('#backupList ul').hide();

  });

  $(document).on('click','#backupList button',function() {

    $('.fileList').css('visibility','hidden');
    $('#backupList ul').fadeIn();

  });

/* ---------------------------------------------- */
/* check for existing backups */
/* ---------------------------------------------- */

  $.ajax({

    url  : '../content-backup/',
    type : 'POST',
    data : ({

      action : 'check'

    }),

  }).done(function(data){

    $('#backupStatus').html(data);

  });

/* ---------------------------------------------- */
/* delete any existing backups */
/* ---------------------------------------------- */

  $(document).on('click','#deleteBackup',function() {

    $.ajax({

      url  : '../content-backup/',
      type : 'POST',
      data : ({

        action : 'delete'

      }),

    }).done(function(data){

      $('#backupStatus').html(data);

    });

  });

/* ---------------------------------------------- */
/* create new backup */
/* ---------------------------------------------- */

  $("#createBackup").click(function() {

    $('#createBackup, #creatingBackup').toggleClass('btn-show');
    $('#backupStatus ul').html('<li>Generating a new backup.</li><li>Please, be patient.</li>');

    $.ajax({

      url  : '../content-backup/',
      type : 'POST',
      data : ({

        action:'create'

      }),

    }).done(function(data){

      $('#createBackup, #creatingBackup').toggleClass('btn-show');
      $('#backupStatus').html(data);

    });

  });

/* ---------------------------------------------- */

}