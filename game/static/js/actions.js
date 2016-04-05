function teamNameFormError() {
  $('.el--text')[0].classList.add('form-error');
  $('.fb-form input[name="teamname"').on('change', function() {
    $('.el--text')[0].classList.remove('form-error');
  });
}

function teamPasswordFormError() {
  $('.el--text')[1].classList.add('form-error');
  $('.fb-form input[name="password"').on('change', function() {
    $('.el--text')[1].classList.remove('form-error');
  });
}

function teamLogoFormError() {
  $('.fb-choose-emblem')[0].style.color = 'red';
  $('.fb-choose-emblem').on('click', function() {
    $('.fb-choose-emblem')[0].style.color = '';
  });
}

function verifyTeamName(context) {
  if (context === 'register') {
    var teamName = String($('.fb-form input[name="teamname"]')[0].value);
    if (teamName.length == 0) {
      teamNameFormError();
      return false;
    } else {
      return teamName;
    }
  }
  if (context === 'login') {
    var teamId = $(".fb-form select option:selected")[0].value;
    return teamId;
  }
}

function verifyTeamPassword() {
  var teamPassword = $('.fb-form input[name="password"]')[0].value;
  if (teamPassword.length == 0) {
    teamPasswordFormError();
    return false;
  } else {
   return teamPassword;
  }
}

function verifyTeamLogo() {
  try {
    var teamLogo = $('.fb-slider .active .icon--badge use').attr('xlink:href').replace('#icon--badge-', '');
    return teamLogo;
  } catch(err) {
    teamLogoFormError();
    return false;
  }
}

function goToPage(page) {
  window.location.href = '/index.php?page=' + page;
}

function loginError() {
  $('.fb-form')[0].classList.add('form-error');
}

function sendIndexRequest(request_data) {
  $.post(
    'indexaction.php',
    request_data
  ).fail(function() {
    // TODO: Make this a modal
    console.log('ERROR');
  }).done(function(data) {
    var responseData = JSON.parse(data);
    if (responseData.result === 'OK') {
      console.log('OK:' + responseData.message);
      goToPage(responseData.redirect); 
    } else {
      // TODO: Make this a modal
      console.log('Failed');
      teamNameFormError();
      teamPasswordFormError();
    }
  });
}

function registerTeam() {
  var name = verifyTeamName('register');
  var password = verifyTeamPassword();
  var logo = verifyTeamLogo();

  if (name && password && logo) {
    var register_data = {
      action: 'register_team',
      teamname: name,
      password: password,
      logo: logo
    };
    sendIndexRequest(register_data);
  }
}

function loginTeam() {
  var teamId = verifyTeamName('login');
  var password = verifyTeamPassword();

  if (teamId && password) {
    var login_data = {
      action: 'login_team',
      team_id: teamId,
      password: password
    };
    sendIndexRequest(login_data);
  }
}

// Capture enter key presses on login and registration forms
$(document).on('keypress', 'input', function(e) {
  if (e.keyCode == 13) {
    e.preventDefault();
    var form_action = $('input[name=action]', e.target.form)[0].value;
    if (form_action == 'register_team') {
      registerTeam();
    }
    if (e.target.form[0].value == 'login_team') {
      loginTeam();
    }
  } 
});