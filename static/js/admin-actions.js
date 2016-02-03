function sendAdminRequest(request_data) {
  $.post(
    'admin.php',
    request_data
  ).fail(function() {
    // TODO: Make this a modal
    console.log('ERROR');
  }).done(function(data) {
    var responseData = JSON.parse(data);
    if (responseData.result == 'OK') {
      console.log('OK');
    } else {
      // TODO: Make this a modal
      console.log('Failed');
    }
  });
}

function deleteTeam() {
}

function saveTeam() {
}

function deleteSession() {
}

function saveSession() {
}
