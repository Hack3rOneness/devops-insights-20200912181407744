function verifyTeamName() {
  var teamName = $('.fb-form input[name="teamname"')[0].value;
  if (teamName.length == 0) {
    $('.el--text')[0].classList.add('form-error');
    $('.fb-form input[name="teamname"').on('change', function() {
      $('.el--text')[0].classList.remove('form-error');
    });
    return false;
  } else {
    return teamName;
  }
}

function verifyTeamPassword() {
  var teamPassword = $('.fb-form input[name="password"')[0].value;
  if (teamPassword.length == 0) {
    $('.el--text')[1].classList.add('form-error');
    $('.fb-form input[name="password"').on('change', function() {
      $('.el--text')[1].classList.remove('form-error');
    });
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
    $('.fb-choose-emblem')[0].style.color = 'red';
    $('.fb-choose-emblem').on('click', function() {
      $('.fb-choose-emblem')[0].style.color = '';
    });
    return false;
  }
}

function registerTeam() {
  var name = verifyTeamName();
  var password = verifyTeamPassword();
  var logo = verifyTeamLogo();

  if (name && password && logo) {
    $.post('index.php',
      {
        action: 'register_team',
        teamname: name,
        password: password,
        logo: logo
      }
    ).fail(function() {
      // TODO: Make this a modal
      console.log('ERROR');
    }).done(function(data) {
      var responseData = JSON.parse(data);
      if (responseData.result == 'OK') {
        console.log('Registration OK');
      } else {
        // TODO: Make this a modal
        console.log('Registration failed');
      }
    });
  }
}

function loginTeam() {
  var name = verifyTeamName();
  var password = verifyTeamPassword();

  if (name && password) {
    $.post('index.php',
      {
        action: 'login_team',
        teamname: name,
        password: password
      }
    ).fail(function() {
      // TODO: Make this a modal
      console.log('ERROR');
    }).done(function(data) {
      var responseData = JSON.parse(data);
      if (responseData.result == 'OK') {
        console.log('Login OK');
      } else {
        // TODO: Make this a modal
        console.log('Login failed');
      }
    });
  }
}
