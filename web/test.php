<div id="allListTeam">
  <ul id="userFriendUl">

  </ul>
</div>

<select id="teamSelect">
</select>

<input type="text" id="addEmailFriend" name="email"/>

<script>

// chargement de la page
  let urlInitTeam = "http://api.php/initteam/"+userId;
  let urlAddFriend = "http://api.php/addFriend/"+userId;

  // chargement de la page
  updateAllListTeam();

  // chargement de la page initial
  function updateAllListTeam() {
    $('#allListTeam').load(urlInitTeam);
    resetEvent('allListTeam');
  }


  $('#addEmailFriend').change(function() {
     let email = $('#addEmailFriend').val();
     $.get( urlAddFriend+'/'+email, function( data ) {
          let html = "<li>"+data.user.name+" <span id='deleteFriend-"+data.user.id+"' class='deleteFriend'>x</span></li>";
          $('#userFriendUl').append(html); // affiche la ligne
          resetEvent('deleteFriend'); // fait fonctionner la croix
      });
  })

  $('.deleteFriend').click(function() {
    let id = $(this).attr('id').split('-')[1];
    $.get( urlDeleteFriend+'/'+id, function( data ) {
      $('#deleteFriend-'+id).remove();
    }
  })

  function stopEvent(target)
  {
    $('#allListTeam li').off('click');
  }

  function resetEvent(target)
  {
    stopEvent(target);
    if( target == "allListTeam") {
      $('#allListTeam li').click(function() {
        // do what u want
      })
    }
  }


</script>
